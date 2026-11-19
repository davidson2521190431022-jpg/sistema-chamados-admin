
<?php

use App\Config\Conexao;
use App\Helpers\Auth;
use App\Services\WhatsAppService;

require_once __DIR__ . '/../app/Config/Conexao.php';
require_once __DIR__ . '/../app/Helpers/Auth.php';
require_once __DIR__ . '/../app/Services/WhatsAppService.php';

Auth::proteger();

$pdo = Conexao::getConexao();

// Proteção contra formulários enviados por outros sites.
if (empty($_SESSION['csrf_chamados'])) {
    $_SESSION['csrf_chamados'] = bin2hex(random_bytes(32));
}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['chamado_id'], $_POST['acao'])
) {
    if (!hash_equals(
        $_SESSION['csrf_chamados'],
        (string) ($_POST['csrf_token'] ?? '')
    )) {
        http_response_code(403);
        exit('Solicitação inválida. Atualize a página e tente novamente.');
    }

    $chamadoId = (int) $_POST['chamado_id'];
    $usuarioId = (int) ($_SESSION['usuario_id'] ?? 0);
    $acao = (string) $_POST['acao'];

    $acoes = [
        'aceitar' => 'em_andamento',
        'resolver' => 'resolvido',
        'encerrar' => 'fechado'
    ];

    if (
        $chamadoId > 0 &&
        $usuarioId > 0 &&
        isset($acoes[$acao])
    ) {
        try {
            $pdo->beginTransaction();

            // Registra o técnico que aceitou o chamado.
            if ($acao === 'aceitar') {

                $stmt = $pdo->prepare(
                    "UPDATE chamados
                     SET status = 'em_andamento',
                         id_usuario = :usuario
                     WHERE id_chamado = :chamado
                       AND status IN ('aberto', 'aguardando_cliente')
                       AND id_usuario IS NULL"
                );

                $stmt->execute([
                    'usuario' => $usuarioId,
                    'chamado' => $chamadoId
                ]);

            } elseif ($acao === 'resolver') {

                $stmt = $pdo->prepare(
                    "UPDATE chamados
                     SET status = 'resolvido'
                     WHERE id_chamado = :chamado
                       AND status NOT IN ('resolvido', 'fechado')"
                );

                $stmt->execute([
                    'chamado' => $chamadoId
                ]);

            } else {

                $stmt = $pdo->prepare(
                    "UPDATE chamados
                     SET status = 'fechado'
                     WHERE id_chamado = :chamado
                       AND status <> 'fechado'"
                );

                $stmt->execute([
                    'chamado' => $chamadoId
                ]);
            }

            $alterou = $stmt->rowCount() > 0;

            $pdo->commit();

            if ($alterou) {

                // Busca o nome de quem aceitou o chamado.
                $responsavel = $pdo->prepare(
                    "SELECT u.nome
                     FROM chamados c
                     LEFT JOIN usuarios u
                       ON u.id_usuario = c.id_usuario
                     WHERE c.id_chamado = ?
                     LIMIT 1"
                );

                $responsavel->execute([$chamadoId]);

                $nomeResponsavel = (string) (
                    $responsavel->fetchColumn() ?: ''
                );

                try {
                    $resultadoNotificacao =
                        WhatsAppService::notificarStatus(
                            $chamadoId,
                            $acoes[$acao],
                            $nomeResponsavel
                        );

                    if (empty($resultadoNotificacao['sucesso'])) {
                        error_log(
                            'SCT WhatsApp: ' .
                            ($resultadoNotificacao['mensagem']
                            ?? 'Falha ao solicitar notificacao.')
                        );
                    }

                } catch (Throwable $e) {
                    error_log(
                        'SCT WhatsApp: ' . $e->getMessage()
                    );
                }
            }

        } catch (Throwable $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log(
                'SCT: Falha ao atualizar chamado: ' .
                $e->getMessage()
            );
        }
    }

    header('Location: chamados.php', true, 303);
    exit;
}

/*
|--------------------------------------------------------------------------
| LISTAGEM DOS CHAMADOS
|--------------------------------------------------------------------------
*/

$chamados = [];
$erro = false;

try {

    $stmt = $pdo->query("
        SELECT
            c.id_chamado AS id,
            cl.nome AS solicitante,
            cat.nome AS categoria,
            c.assunto,
            c.descricao,
            c.prioridade,
            c.status,
            u.nome AS responsavel_nome,
            c.data_criacao
        FROM chamados c
        LEFT JOIN clientes cl
            ON c.id_cliente = cl.id_cliente
        LEFT JOIN categorias cat
            ON c.id_categoria = cat.id_categoria
        LEFT JOIN usuarios u
            ON c.id_usuario = u.id_usuario
        ORDER BY c.id_chamado DESC
    ");

    $chamados = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    error_log($e->getMessage());
    $erro = true;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Gerenciar Chamados - Sistema de Chamados</title>

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="css/painel.css">
</head>

<body>

    <aside class="sidebar">

        <div class="sidebar-brand">
            <span class="material-symbols-rounded">
                headset_mic
            </span>
            Suporte Técnico
        </div>

        <a href="painel.php">
            <span class="material-symbols-rounded">home</span>
            Painel
        </a>

        <a href="chamados.php" class="active">
            <span class="material-symbols-rounded">
                confirmation_number
            </span>
            Chamados
        </a>

        <a href="clientes.php">
            <span class="material-symbols-rounded">person</span>
            Clientes
        </a>

        <a href="relatorios.php">
            <span class="material-symbols-rounded">bar_chart</span>
            Relatórios
        </a>

        <a href="configuracoes.php">
            <span class="material-symbols-rounded">settings</span>
            Configurações
        </a>

        <a href="logout.php">
            <span class="material-symbols-rounded">logout</span>
            Sair
        </a>

    </aside>

    <main class="main-content">

        <header class="top-header">
            <h1>Gerenciamento de Chamados</h1>
        </header>

        <div class="table-container">

            <div class="table-header">
                <h3>
                    Todos os Chamados (<?= count($chamados) ?>)
                </h3>
            </div>

            <?php if ($erro): ?>

                <p style="color:red; padding:20px;">
                    Erro ao consultar os chamados.
                    Verifique a conexão com o banco de dados.
                </p>

            <?php else: ?>

                <table>

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Solicitante</th>
                            <th>Assunto</th>
                            <th>Status</th>
                            <th>Responsável</th>
                            <th>Data</th>
                            <th>Ações</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (!empty($chamados)): ?>

                        <?php foreach ($chamados as $chamado): ?>

                            <?php

                            $status = trim(
                                $chamado['status'] ?? ''
                            );

                            $statusNormalizado = mb_strtolower(
                                $status
                            );

                            $statusClass = 'abertos';

                            if ($statusNormalizado === 'em_andamento') {
                                $statusClass = 'andamento';

                            } elseif (
                                $statusNormalizado === 'aguardando_cliente'
                            ) {
                                $statusClass = 'aguardando';

                            } elseif (
                                $statusNormalizado === 'resolvido'
                            ) {
                                $statusClass = 'resolvido';

                            } elseif (
                                $statusNormalizado === 'fechado'
                            ) {
                                $statusClass = 'resolvido';
                            }

                            $statusExibicao = [
                                'aberto' => 'Aberto',
                                'em_andamento' => 'Em andamento',
                                'aguardando_cliente' => 'Aguardando cliente',
                                'resolvido' => 'Resolvido',
                                'fechado' => 'Fechado'
                            ];

                            $statusTexto =
                                $statusExibicao[$statusNormalizado]
                                ?? $status;

                            $descricao =
                                $chamado['descricao'] ?? '';

                            ?>

                            <tr>

                                <td>
                                    #<?= htmlspecialchars(
                                        (string) $chamado['id'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $chamado['solicitante'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>

                                    <strong>
                                        <?= htmlspecialchars(
                                            $chamado['assunto'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </strong>

                                    <br>

                                    <small>
                                        <?= htmlspecialchars(
                                            $descricao,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </small>

                                </td>

                                <td>
                                    <span class="badge <?= $statusClass ?>">
                                        <?= htmlspecialchars(
                                            $statusTexto,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $chamado['responsavel_nome']
                                            ?: 'Não atribuído',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <?= !empty($chamado['data_criacao'])
                                        ? date(
                                            'd/m/Y H:i',
                                            strtotime(
                                                $chamado['data_criacao']
                                            )
                                        )
                                        : '—'
                                    ?>
                                </td>

                                <td>

                                    <div class="action-buttons">

                                        <?php if (
                                            in_array(
                                                $statusNormalizado,
                                                [
                                                    'aberto',
                                                    'aguardando_cliente'
                                                ],
                                                true
                                            )
                                        ): ?>

                                            <form
                                                method="POST"
                                                style="display:inline;"
                                            >

                                                <input
                                                    type="hidden"
                                                    name="csrf_token"
                                                    value="<?= htmlspecialchars(
                                                        $_SESSION['csrf_chamados'],
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    ) ?>"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="chamado_id"
                                                    value="<?= (int) $chamado['id'] ?>"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="acao"
                                                    value="aceitar"
                                                >

                                                <button
                                                    type="submit"
                                                    class="btn-action-sm btn-accept"
                                                >
                                                    <span
                                                        class="material-symbols-rounded"
                                                        style="font-size:16px;"
                                                    >play_arrow</span>

                                                    Aceitar
                                                </button>

                                            </form>

                                        <?php endif; ?>

                                        <?php if (
                                            !in_array(
                                                $statusNormalizado,
                                                ['resolvido', 'fechado'],
                                                true
                                            )
                                        ): ?>

                                            <form
                                                method="POST"
                                                style="display:inline;"
                                            >

                                                <input
                                                    type="hidden"
                                                    name="csrf_token"
                                                    value="<?= htmlspecialchars(
                                                        $_SESSION['csrf_chamados'],
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    ) ?>"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="chamado_id"
                                                    value="<?= (int) $chamado['id'] ?>"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="acao"
                                                    value="resolver"
                                                >

                                                <button
                                                    type="submit"
                                                    class="btn-action-sm btn-resolve"
                                                >
                                                    <span
                                                        class="material-symbols-rounded"
                                                        style="font-size:16px;"
                                                    >check</span>

                                                    Resolver
                                                </button>

                                            </form>

                                        <?php endif; ?>

                                        <?php if (
                                            $statusNormalizado !== 'fechado'
                                        ): ?>

                                            <form
                                                method="POST"
                                                style="display:inline;"
                                            >

                                                <input
                                                    type="hidden"
                                                    name="csrf_token"
                                                    value="<?= htmlspecialchars(
                                                        $_SESSION['csrf_chamados'],
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    ) ?>"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="chamado_id"
                                                    value="<?= (int) $chamado['id'] ?>"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="acao"
                                                    value="encerrar"
                                                >

                                                <button
                                                    type="submit"
                                                    class="btn-action-sm btn-close"
                                                >
                                                    <span
                                                        class="material-symbols-rounded"
                                                        style="font-size:16px;"
                                                    >archive</span>

                                                    Encerrar
                                                </button>

                                            </form>

                                        <?php endif; ?>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td
                                colspan="7"
                                style="text-align:center; padding:20px;"
                            >
                                Nenhum chamado encontrado.
                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            <?php endif; ?>

        </div>

    </main>

</body>
</html>

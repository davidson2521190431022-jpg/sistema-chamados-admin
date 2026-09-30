
<?php
use App\Config\Conexao;

require_once '../app/Config/Conexao.php';

$pdo = Conexao::getConexao();

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['chamado_id'], $_POST['acao'])
) {
    $chamadoId = (int) $_POST['chamado_id'];
    $acao = $_POST['acao'];

    $acoes = [
        'aceitar'  => 'Em Andamento',
        'resolver' => 'Resolvido',
        'encerrar' => 'Fechado'
    ];

    if ($chamadoId > 0 && isset($acoes[$acao])) {
        $stmt = $pdo->prepare(
            "UPDATE chamados SET status = ? WHERE id = ?"
        );

        $stmt->execute([$acoes[$acao], $chamadoId]);
    }

    echo '<script>window.location.replace("chamados.php");</script>';
    exit;
}

$chamados = [];
$erro = false;

try {
    $stmt = $pdo->query("
        SELECT
            id,
            nome AS solicitante,
            categoria,
            descricao,
            prioridade,
            status
        FROM chamados
        ORDER BY id DESC
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

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
            <span class="material-symbols-rounded">headset_mic</span>
            Suporte Técnico
        </div>

        <a href="painel.php">
            <span class="material-symbols-rounded">home</span>
            Painel
        </a>

        <a href="chamados.php" class="active">
            <span class="material-symbols-rounded">confirmation_number</span>
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
                <h3>Todos os Chamados (<?= count($chamados) ?>)</h3>
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
                            <th>Data</th>
                            <th>Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!empty($chamados)): ?>

                            <?php foreach ($chamados as $chamado): ?>
                                <?php
                                    $status = trim($chamado['status'] ?? '');
                                    $statusNormalizado = mb_strtolower($status);

                                    $statusClass = 'abertos';

                                    if (str_contains($statusNormalizado, 'andamento')) {
                                        $statusClass = 'andamento';
                                    } elseif (str_contains($statusNormalizado, 'aguardando')) {
                                        $statusClass = 'aguardando';
                                    } elseif (str_contains($statusNormalizado, 'resolvido')) {
                                        $statusClass = 'resolvido';
                                    } elseif (str_contains($statusNormalizado, 'fechado')) {
                                        $statusClass = 'resolvido';
                                    }

                                    $categoria = $chamado['categoria'] ?? '';
                                    $descricao = $chamado['descricao'] ?? '';
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
                                                $categoria,
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
                                                $status,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </span>
                                    </td>

                                    <td>—</td>

                                    <td>
                                        <div class="action-buttons">

                                            <?php if (
                                                in_array(
                                                    $statusNormalizado,
                                                    ['aberto', 'aguardando'],
                                                    true
                                                )
                                            ): ?>
                                                <form method="POST" style="display:inline;">
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
                                                <form method="POST" style="display:inline;">
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

                                            <?php if ($statusNormalizado !== 'fechado'): ?>
                                                <form method="POST" style="display:inline;">
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
                                                        Baixar
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align:center; padding:20px;">
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

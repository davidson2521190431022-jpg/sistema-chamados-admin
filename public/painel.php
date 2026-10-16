<?php
use App\Config\Conexao;
use App\Helpers\Auth;
use App\Models\Notificacao;

require_once __DIR__ . '/../app/Config/Conexao.php';
require_once __DIR__ . '/../app/Helpers/Auth.php';
require_once __DIR__ . '/../app/Models/Notificacao.php';


Auth::proteger();

$pdo = Conexao::getConexao();
$usuarioId = $_SESSION['usuario_id'];
$notificacoesNaoLidas = [];

try {
    $notificacoesNaoLidas = Notificacao::naoLidas($usuarioId);
} catch (Throwable $e) {
    error_log("Erro ao buscar notificações: " . $e->getMessage());
}


$totalAbertos = 0;
$totalAndamento = 0;
$totalAguardando = 0;
$totalResolvidos = 0;

try {

    $totalAbertos = $pdo
        ->query("SELECT COUNT(*) FROM chamados WHERE status = 'aberto'")
        ->fetchColumn() ?: 0;

    $totalAndamento = $pdo
        ->query("SELECT COUNT(*) FROM chamados WHERE status = 'em_andamento'")
        ->fetchColumn() ?: 0;

    $totalAguardando = $pdo
        ->query("SELECT COUNT(*) FROM chamados WHERE status = 'aguardando_cliente'")
        ->fetchColumn() ?: 0;

    $totalResolvidos = $pdo
        ->query("
            SELECT COUNT(*)
            FROM chamados
            WHERE status IN ('resolvido', 'fechado')
        ")
        ->fetchColumn() ?: 0;

} catch (Exception $e) {

    error_log("Erro ao buscar contadores: " . $e->getMessage());

}

$chamadosRecentes = [];

try {

    $stmt = $pdo->query("
        SELECT
            c.id_chamado AS id,
            cl.nome AS nome,
            cat.nome AS categoria,
            c.assunto,
            c.descricao,
            c.prioridade,
            c.status
        FROM chamados c
        LEFT JOIN clientes cl
            ON c.id_cliente = cl.id_cliente
        LEFT JOIN categorias cat
            ON c.id_categoria = cat.id_categoria
        ORDER BY c.id_chamado DESC
        LIMIT 5
    ");

    $chamadosRecentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {

    error_log("Erro ao buscar chamados: " . $e->getMessage());

}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Suporte Técnico - Chamados</title>

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

        <a href="painel.php" class="active">

            <span class="material-symbols-rounded">
                home
            </span>

            Painel

        </a>

        <a href="chamados.php">

            <span class="material-symbols-rounded">
                confirmation_number
            </span>

            Chamados

        </a>

        <a href="clientes.php">

            <span class="material-symbols-rounded">
                person
            </span>

            Histórico de atendimentos

        </a>

        <a href="relatorios.php">

            <span class="material-symbols-rounded">
                bar_chart
            </span>

            Relatórios

        </a>

        <a href="configuracoes.php">

            <span class="material-symbols-rounded">
                settings
            </span>

            Configurações

        </a>

        <a href="logout.php">

            <span class="material-symbols-rounded">
                logout
            </span>

            Sair

        </a>

    </aside>


    <main class="main-content">

       
<header class="top-header">

    <h1>Chamados</h1>

    <div class="header-actions">

        <button
            type="button"
            id="botaoNotificacoes"
            class="botao-notificacoes"
            aria-label="Abrir notificações"
            aria-expanded="false"
        >
            <span class="material-symbols-rounded">notifications</span>

            <?php if (count($notificacoesNaoLidas) > 0): ?>
                <span class="contador-notificacoes">
                    <?= count($notificacoesNaoLidas) ?>
                </span>
            <?php endif; ?>
        </button>

    
<div id="listaNotificacoes" class="lista-notificacoes">

    <h3>Notificações</h3>

    <?php if (!empty($notificacoesNaoLidas)): ?>

        <?php foreach ($notificacoesNaoLidas as $notificacao): ?>
            <div class="item-notificacao">

                <p>
                    <?= htmlspecialchars(
                        $notificacao['mensagem'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </p>

                <form method="POST" action="marcar_notificacao.php">
                    <input
                        type="hidden"
                        name="id_notificacao"
                        value="<?= (int) $notificacao['id_notificacao'] ?>"
                    >

                    <button type="submit" class="botao-marcar-lida">
                        Marcar como lida
                    </button>
                </form>

            </div>
        <?php endforeach; ?>

    <?php else: ?>

        <p class="sem-notificacoes">
            Você não tem notificações novas.
        </p>

    <?php endif; ?>

</div>

<a href="chamados.php" class="header-action" aria-label="Ver chamados">
    <span class="material-symbols-rounded">arrow_forward</span>
</a>

</div>


</header>



        <div class="stats-grid">

            <div class="stat-card">

                <h3>Abertos</h3>

                <div class="number">

                    <?php echo $totalAbertos; ?>

                </div>

            </div>


            <div class="stat-card">

                <h3>Em andamento</h3>

                <div class="number">

                    <?php echo $totalAndamento; ?>

                </div>

            </div>


            <div class="stat-card">

                <h3>Aguardando</h3>

                <div class="number">

                    <?php echo $totalAguardando; ?>

                </div>

            </div>


            <div class="stat-card">

                <h3>Resolvidos</h3>

                <div class="number">

                    <?php echo $totalResolvidos; ?>

                </div>

            </div>

        </div>


        <div class="table-container">

            <div class="table-header">

                <h3>Chamados Recentes</h3>

                <span class="material-symbols-rounded close-icon">
                    close
                </span>

            </div>


            <table>

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Solicitante</th>

                        <th>Categoria</th>

                        <th>Assunto</th>

                        <th>Descrição</th>

                        <th>Prioridade</th>

                        <th>Status</th>

                    </tr>

                </thead>


                <tbody>

                    <?php if (!empty($chamadosRecentes)): ?>

                        <?php foreach ($chamadosRecentes as $chamado): ?>

                            <?php

                            $status = $chamado['status'] ?? '';

                            $statusStr = strtolower($status);

                            $statusClass = 'abertos';

                            if ($statusStr === 'em_andamento') {

                                $statusClass = 'andamento';

                            } elseif ($statusStr === 'aguardando_cliente') {

                                $statusClass = 'aguardando';

                            } elseif (
                                $statusStr === 'resolvido' ||
                                $statusStr === 'fechado'
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
                                $statusExibicao[$statusStr]
                                ?? $status;


                            $prioridade =
                                $chamado['prioridade']
                                ?? '';

                            $prioridadeStr =
                                strtolower($prioridade);

                            $prioridadeClass = 'media';

                            if (
                                $prioridadeStr === 'alta' ||
                                $prioridadeStr === 'urgente'
                            ) {

                                $prioridadeClass = 'alta';

                            } elseif (
                                $prioridadeStr === 'baixa'
                            ) {

                                $prioridadeClass = 'baixa';

                            }

                            ?>

                            <tr>

                                <td>

                                    #<?php echo htmlspecialchars(
                                        $chamado['id'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>

                                </td>


                                <td>

                                    <?php echo htmlspecialchars(
                                        $chamado['nome'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>

                                </td>


                                <td>

                                    <?php echo htmlspecialchars(
                                        $chamado['categoria'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>

                                </td>


                                <td>

                                    <strong>

                                        <?php echo htmlspecialchars(
                                            $chamado['assunto'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>

                                    </strong>

                                </td>


                                <td>

                                    <?php echo htmlspecialchars(
                                        $chamado['descricao'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>

                                </td>


                                <td>

                                    <span class="badge <?php echo $prioridadeClass; ?>">

                                        <?php echo htmlspecialchars(
                                            $prioridade,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>

                                    </span>

                                </td>


                                <td>

                                    <span class="badge <?php echo $statusClass; ?>">

                                        <?php echo htmlspecialchars(
                                            $statusTexto,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>

                                    </span>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>

                            <td
                                colspan="7"
                                style="text-align: center; color: #777; padding: 20px;"
                            >

                                Nenhum chamado recente encontrado.

                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>


            <div class="table-footer">

                <a href="chamados.php" class="btn-view-all">

                    Ver todos

                </a>

            </div>

        </div>

    </main>

<script src="js/notificacoes.js"></script>
    <!-- ATUALIZA O PAINEL AUTOMATICAMENTE A CADA 5 SEGUNDOS -->

    <script>

        setInterval(function () {

            location.reload();

        }, 5000);

    </script>


</body>

</html>
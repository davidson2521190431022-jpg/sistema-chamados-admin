
<?php

use App\Config\Conexao;

require_once '../app/Config/Conexao.php';

$pdo = Conexao::getConexao();

$totalAbertos = 0;
$totalAndamento = 0;
$totalAguardando = 0;
$totalResolvidos = 0;

try {

    $totalAbertos = $pdo
        ->query("SELECT COUNT(*) FROM chamados WHERE status = 'Aberto'")
        ->fetchColumn() ?: 0;

    $totalAndamento = $pdo
        ->query("SELECT COUNT(*) FROM chamados WHERE status = 'Em Andamento'")
        ->fetchColumn() ?: 0;

    $totalAguardando = $pdo
        ->query("SELECT COUNT(*) FROM chamados WHERE status = 'Aguardando Cliente'")
        ->fetchColumn() ?: 0;

    $totalResolvidos = $pdo
        ->query("SELECT COUNT(*) FROM chamados WHERE status = 'Resolvido'")
        ->fetchColumn() ?: 0;

} catch (Exception $e) {

    error_log("Erro ao buscar contadores: " . $e->getMessage());

}

$chamadosRecentes = [];

try {

    $stmt = $pdo->query("
        SELECT
            id,
            nome,
            matricula,
            setor,
            categoria,
            descricao,
            prioridade,
            status
        FROM chamados
        ORDER BY id DESC
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

             Historico de atendimentos

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

            <a href="chamados.php" class="header-action">

                <span class="material-symbols-rounded">
                    arrow_forward
                </span>

            </a>

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
                        <th>Setor</th>
                        <th>Categoria</th>
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

                            if (strpos($statusStr, 'andamento') !== false) {

                                $statusClass = 'andamento';

                            } elseif (strpos($statusStr, 'aguardando') !== false) {

                                $statusClass = 'aguardando';

                            } elseif (strpos($statusStr, 'resolvido') !== false) {

                                $statusClass = 'resolvido';

                            }


                            $prioridade = $chamado['prioridade'] ?? '';

                            $prioridadeStr = strtolower($prioridade);

                            $prioridadeClass = 'media';

                            if (strpos($prioridadeStr, 'alta') !== false) {

                                $prioridadeClass = 'alta';

                            } elseif (strpos($prioridadeStr, 'baixa') !== false) {

                                $prioridadeClass = 'baixa';

                            }

                            ?>

                            <tr>

                                <td>
                                    #<?php echo htmlspecialchars($chamado['id']); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($chamado['nome'] ?? ''); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($chamado['setor'] ?? ''); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($chamado['categoria'] ?? ''); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($chamado['descricao'] ?? ''); ?>
                                </td>

                                <td>

                                    <span class="badge <?php echo $prioridadeClass; ?>">

                                        <?php echo htmlspecialchars($prioridade); ?>

                                    </span>

                                </td>

                                <td>

                                    <span class="badge <?php echo $statusClass; ?>">

                                        <?php echo htmlspecialchars($status); ?>

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


    <!-- ATUALIZA O PAINEL AUTOMATICAMENTE A CADA 5 SEGUNDOS -->

    <script>

        setInterval(function () {

            location.reload();

        }, 5000);

    </script>


</body>

</html>
```

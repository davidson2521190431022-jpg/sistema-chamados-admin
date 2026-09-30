
<?php
use App\Config\Conexao;

require_once '../app/Config/Conexao.php';

$pdo = Conexao::getConexao();

$chamadosClientes = [];
$erro = false;

try {
    $stmt = $pdo->query("
        SELECT
            id,
            nome AS solicitante,
            categoria,
            descricao,
            status
        FROM chamados
        ORDER BY id DESC
    ");

    $chamadosClientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

    <title>Chamados dos Clientes - Sistema de Chamados</title>

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

        <a href="chamados.php">
            <span class="material-symbols-rounded">confirmation_number</span>
            Chamados
        </a>

        <a href="clientes.php" class="active">
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
            <h1>Chamados Enviados por Clientes</h1>
        </header>

        <div class="table-container">
            <div class="table-header">
                <h3>
                    Histórico Geral de Solicitações
                    (<?= count($chamadosClientes) ?>)
                </h3>
            </div>

            <?php if ($erro): ?>

                <p style="color: red; padding: 20px;">
                    Erro ao consultar os chamados.
                    Verifique a conexão com o banco de dados.
                </p>

            <?php else: ?>

                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Solicitante</th>
                            <th>E-mail</th>
                            <th>Assunto</th>
                            <th>Status</th>
                            <th>Data</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!empty($chamadosClientes)): ?>

                            <?php foreach ($chamadosClientes as $chamado): ?>
                                <?php
                                    $status = trim($chamado['status'] ?? '');
                                    $statusStr = mb_strtolower($status);

                                    $statusClass = 'abertos';

                                    if (str_contains($statusStr, 'andamento')) {
                                        $statusClass = 'andamento';
                                    } elseif (str_contains($statusStr, 'aguardando')) {
                                        $statusClass = 'aguardando';
                                    } elseif (
                                        str_contains($statusStr, 'resolvido') ||
                                        str_contains($statusStr, 'fechado')
                                    ) {
                                        $statusClass = 'resolvido';
                                    }
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

                                    <td>—</td>

                                    <td>
                                        <strong>
                                            <?= htmlspecialchars(
                                                $chamado['categoria'] ?? '',
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </strong>
                                        <br>
                                        <small>
                                            <?= htmlspecialchars(
                                                $chamado['descricao'] ?? '',
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
                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td
                                    colspan="6"
                                    style="text-align: center; color: #777; padding: 20px;"
                                >
                                    Nenhum chamado de cliente registrado até o momento.
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
           

<?php
use App\Config\Conexao;

require_once '../app/Config/Conexao.php';

$pdo = Conexao::getConexao();

$chamadosClientes = [];
try {
    $stmt = $pdo->query("
        SELECT c.id, cl.nome AS solicitante, cl.email, c.titulo AS assunto, c.status, c.criado_em AS data_criacao 
        FROM chamados c 
        JOIN clientes cl ON c.cliente_id = cl.id 
        ORDER BY c.criado_em DESC
    ");
    $chamadosClientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $chamadosClientes = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chamados dos Clientes - Sistema de Chamados</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet" />
    <link rel="stylesheet" href="css/painel.css">
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-brand">
            <span class="material-symbols-rounded">headset_mic</span>
            Suporte Técnico
        </div>
        <a href="painel.php"><span class="material-symbols-rounded">home</span> Painel</a>
        <a href="chamados.php"><span class="material-symbols-rounded">confirmation_number</span> Chamados</a>
        <a href="clientes.php" class="active"><span class="material-symbols-rounded">person</span> Clientes</a>
        <a href="relatorios.php"><span class="material-symbols-rounded">bar_chart</span> Relatórios</a>
        <a href="configuracoes.php"><span class="material-symbols-rounded">settings</span> Configurações</a>
        <a href="logout.php"><span class="material-symbols-rounded">logout</span> Sair</a>
    </aside>

    <main class="main-content">
        <header class="top-header">
            <h1>Chamados Enviados por Clientes</h1>
        </header>

        <div class="table-container">
            <div class="table-header">
                <h3>Histórico Geral de Solicitações</h3>
            </div>
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
                            <tr>
                                <td>#<?php echo htmlspecialchars($chamado['id']); ?></td>
                                <td><?php echo htmlspecialchars($chamado['solicitante']); ?></td>
                                <td><?php echo htmlspecialchars($chamado['email']); ?></td>
                                <td><?php echo htmlspecialchars($chamado['assunto']); ?></td>
                                <td>
                                    <?php 
                                        $statusStr = strtolower($chamado['status']);
                                        $statusClass = 'abertos';
                                        if (strpos($statusStr, 'andamento') !== false) {
                                            $statusClass = 'andamento';
                                        } elseif (strpos($statusStr, 'aguardando') !== false) {
                                            $statusClass = 'aguardando';
                                        } elseif (strpos($statusStr, 'resolvido') !== false) {
                                            $statusClass = 'resolvido';
                                        }
                                    ?>
                                    <span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($chamado['status']); ?></span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($chamado['data_criacao'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: #777; padding: 20px;">Nenhum chamado de cliente registrado até o momento.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>
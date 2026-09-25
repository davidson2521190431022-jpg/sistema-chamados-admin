<?php
use App\Config\Conexao;

require_once '../app/Config/Conexao.php';

$pdo = Conexao::getConexao();

$totalChamados = 0;
$totalResolvidos = 0;
$taxaResolucao = 0;

try {
    $totalChamados = $pdo->query("SELECT COUNT(*) FROM chamados")->fetchColumn() ?: 0;
    $totalResolvidos = $pdo->query("SELECT COUNT(*) FROM chamados WHERE status = 'Resolvido'")->fetchColumn() ?: 0;
    if ($totalChamados > 0) {
        $taxaResolucao = round(($totalResolvidos / $totalChamados) * 100);
    }
} catch (Exception $e) {
    // Tratamento em caso de erro na consulta
}

$relatorioStatus = [];
try {
    $stmt = $pdo->query("
        SELECT status, COUNT(*) as quantidade 
        FROM chamados 
        GROUP BY status
    ");
    $relatorioStatus = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $relatorioStatus = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios - Sistema de Chamados</title>
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
        <a href="clientes.php"><span class="material-symbols-rounded">person</span> Clientes</a>
        <a href="relatorios.php" class="active"><span class="material-symbols-rounded">bar_chart</span> Relatórios</a>
        <a href="configuracoes.php"><span class="material-symbols-rounded">settings</span> Configurações</a>
        <a href="logout.php"><span class="material-symbols-rounded">logout</span> Sair</a>
    </aside>

    <main class="main-content">
        <header class="top-header">
            <h1>Relatórios e Estatísticas</h1>
        </header>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total de Chamados</h3>
                <div class="number"><?php echo $totalChamados; ?></div>
            </div>
            <div class="stat-card">
                <h3>Concluídos</h3>
                <div class="number"><?php echo $totalResolvidos; ?></div>
            </div>
            <div class="stat-card">
                <h3>Taxa de Eficiência</h3>
                <div class="number"><?php echo $taxaResolucao; ?>%</div>
            </div>
        </div>

        <div class="table-container">
            <div class="table-header">
                <h3>Distribuição por Status</h3>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Total de Registros</th>
                        <th>Proporção</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($relatorioStatus)): ?>
                        <?php foreach ($relatorioStatus as $row): ?>
                            <?php 
                                $percentual = $totalChamados > 0 ? round(($row['quantidade'] / $totalChamados) * 100, 1) : 0;
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($row['status']); ?></strong></td>
                                <td><?php echo $row['quantidade']; ?></td>
                                <td><?php echo $percentual; ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" style="text-align: center; color: #777; padding: 20px;">Nenhum dado encontrado para emissão do relatório por aqui por favor tente novamente.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>
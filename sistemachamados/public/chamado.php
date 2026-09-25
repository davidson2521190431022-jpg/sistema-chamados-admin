<?php

use App\Config\Conexao;

require_once '../app/Config/Conexao.php';

$pdo = Conexao::getConexao();

$idChamado = isset($_GET['id']) ? intval($_GET['id']) : 0;
$chamado = null;

if ($idChamado > 0) {
    try {
        $stmt = $pdo->prepare("
            SELECT c.*, u.nome as tecnico_nome 
            FROM chamados c 
            LEFT JOIN usuarios u ON c.tecnico_id = u.id 
            WHERE c.id = ?
        ");
        $stmt->execute([$idChamado]);
        $chamado = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $chamado = null;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Chamado #<?php echo $idChamado; ?> - Suporte Técnico</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet" />
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/telas.css">
</head>
<body>

    <aside class="sidebar">
        <h2>Suporte Técnico</h2>
        <a href="painel.php"><span class="material-symbols-rounded">dashboard</span> Painel</a>
        <a href="conhecimento.php"><span class="material-symbols-rounded">menu_book</span> Base de Conhecimento</a>
        <a href="logout.php"><span class="material-symbols-rounded">logout</span> Sair</a>
    </aside>

    <main class="main-content">
        <a href="painel.php" class="back-link" style="display: inline-flex; align-items: center; gap: 5px; margin-bottom: 20px; text-decoration: none; color: #1a73e8; font-weight: 500;">
            <span class="material-symbols-rounded">arrow_back</span> Voltar ao Painel
        </a>

        <?php if ($chamado): ?>
            <div class="article-container" style="background: white; padding: 30px; border-radius: 12px; border: 1px solid #eaeaea; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
                <div class="article-meta" style="font-size: 0.85rem; color: #666; margin-bottom: 10px;">
                    <span>Solicitante: <strong><?php echo htmlspecialchars($chamado['solicitante'] ?? 'Não informado'); ?></strong></span> • 
                    <span>Status: <strong><?php echo htmlspecialchars($chamado['status']); ?></strong></span> •
                    <span>Data: <strong><?php echo htmlspecialchars($chamado['data_criacao']); ?></strong></span>
                </div>
                
                <h1 style="font-size: 1.8rem; color: #0d1b2a; margin-bottom: 20px;">Chamado #<?php echo htmlspecialchars($chamado['id']); ?> - <?php echo htmlspecialchars($chamado['assunto']); ?></h1>
                
                <hr style="border: none; border-top: 1px solid #eee; margin-bottom: 20px;">

                <div class="article-content" style="font-size: 1rem; color: #333; line-height: 1.6; margin-bottom: 30px;">
                    <h3 style="font-size: 1.1rem; color: #0d1b2a; margin-bottom: 10px;">Descrição do Problema:</h3>
                    <p><?php echo nl2br(htmlspecialchars($chamado['descricao'] ?? 'Nenhuma descrição detalhada fornecida.')); ?></p>
                </div>

                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #eaeienna;">
                    <h3 style="font-size: 1rem; color: #0d1b2a; margin-bottom: 10px;">Ações do Técnico</h3>
                    <form action="atualizar_chamado.php" method="POST" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                        <input type="hidden" name="id" value="<?php echo $chamado['id']; ?>">
                        <select name="status" style="padding: 10px; border-radius: 6px; border: 1px solid #ddd; font-family: 'Inter', sans-serif;">
                            <option value="Aberto" <?php echo ($chamado['status'] == 'Aberto') ? 'selected' : ''; ?>>Aberto</option>
                            <option value="Em andamento" <?php echo ($chamado['status'] == 'Em andamento') ? 'selected' : ''; ?>>Em andamento</option>
                            <option value="Aguardando" <?php echo ($chamado['status'] == 'Aguardando') ? 'selected' : ''; ?>>Aguardando</option>
                            <option value="Resolvido" <?php echo ($chamado['status'] == 'Resolvido') ? 'selected' : ''; ?>>Resolvido</option>
                        </select>
                        <button type="submit" style="background: #1a73e8; color: white; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; cursor: pointer;">Atualizar Status</button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div style="background: white; padding: 40px; border-radius: 12px; text-align: center; border: 1px solid #eaeaea;">
                <span class="material-symbols-rounded" style="font-size: 3rem; color: #d93025; margin-bottom: 15px;">error</span>
                <h2 style="font-size: 1.4rem; color: #0d1b2a; margin-bottom: 10px;">Chamado não encontrado</h2>
                <p style="color: #666; margin-bottom: 20px;">O chamado solicitado não existe ou foi removido do sistema.</p>
                <a href="painel.php" style="background: #1a73e8; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 500;">Retornar ao Painel</a>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
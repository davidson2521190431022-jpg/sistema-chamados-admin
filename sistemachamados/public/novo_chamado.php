<?php

use App\Config\Conexao;

require_once '../app/Config/Conexao.php';

$pdo = Conexao::getConexao();

$mensagemSucesso = '';
$mensagemErro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $solicitante = trim($_POST['solicitante'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $assunto = trim($_POST['assunto'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');

    if (!empty($solicitante) && !empty($email) && !empty($assunto) && !empty($descricao)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO chamados (solicitante, email, assunto, descricao, status, data_criacao) 
                VALUES (?, ?, ?, ?, 'Aberto', NOW())
            ");
            $stmt->execute([$solicitante, $email, $assunto, $descricao]);
            $mensagemSucesso = "Chamado criado com sucesso!";
        } catch (Exception $e) {
            $mensagemErro = "Erro ao registrar o chamado. Tente novamente.";
        }
    } else {
        $mensagemErro = "Por favor, preencha todos os campos obrigatórios.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Chamado - Suporte Técnico</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet" />
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/telas.css">
</head>
<body>

    <aside class="sidebar">
        <h2>Suporte Técnico</h2>
        <a href="painel.php"><span class="material-symbols-rounded">dashboard</span> Painel</a>
        <a href="clientes.php"><span class="material-symbols-rounded">group</span> Chamados Clientes</a>
        <a href="novo_chamado.php" class="active"><span class="material-symbols-rounded">add_circle</span> Novo Chamado</a>
        <a href="conhecimento.php"><span class="material-symbols-rounded">menu_book</span> Base de Conhecimento</a>
        <a href="logout.php"><span class="material-symbols-rounded">logout</span> Sair</a>
    </aside>

    <main class="main-content">
        <header class="page-header">
            <h1>Abrir Novo Chamado</h1>
            <p>Registre uma nova solicitação de atendimento técnico internamente.</p>
        </header>

        <?php if (!empty($mensagemSucesso)): ?>
            <div style="background: #e6f4ea; color: #137333; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; border: 1px solid #ceead6;">
                <?php echo htmlspecialchars($mensagemSucesso); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($mensagemErro)): ?>
            <div style="background: #fce8e6; color: #c5221f; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; border: 1px solid #fad2cf;">
                <?php echo htmlspecialchars($mensagemErro); ?>
            </div>
        <?php endif; ?>

        <div style="background: white; padding: 30px; border-radius: 12px; border: 1px solid #eaeaea; box-shadow: 0 4px 15px rgba(0,0,0,0.02); max-width: 800px;">
            <form action="novo_chamado.php" method="POST">
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 500; font-size: 0.9rem; color: #0d1b2a; margin-bottom: 8px;">Nome do Solicitante</label>
                    <input type="text" name="solicitante" required placeholder="Ex: Carlos Silva" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-family: 'Inter', sans-serif; font-size: 0.95rem;">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 500; font-size: 0.9rem; color: #0d1b2a; margin-bottom: 8px;">E-mail de Contato</label>
                    <input type="email" name="email" required placeholder="carlos@empresa.com" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-family: 'Inter', sans-serif; font-size: 0.95rem;">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 500; font-size: 0.9rem; color: #0d1b2a; margin-bottom: 8px;">Assunto</label>
                    <input type="text" name="assunto" required placeholder="Ex: Problema com rede wi-fi" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-family: 'Inter', sans-serif; font-size: 0.95rem;">
                </div>

                <div style="margin-bottom: 25px;">
                    <label style="display: block; font-weight: 500; font-size: 0.9rem; color: #0d1b2a; margin-bottom: 8px;">Descrição Detalhada</label>
                    <textarea name="descricao" rows="5" required placeholder="Descreva os detalhes da ocorrência..." style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-family: 'Inter', sans-serif; font-size: 0.95rem; resize: vertical;"></textarea>
                </div>

                <button type="submit" style="background: #1a73e8; color: white; border: none; padding: 12px 24px; border-radius: 6px; font-weight: 600; font-size: 0.95rem; cursor: pointer;">
                    Cadastrar Chamado
                </button>
            </form>
        </div>
    </main>

</body>
</html>
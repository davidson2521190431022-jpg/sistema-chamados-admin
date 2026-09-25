<?php

use App\Config\Conexao;

require_once '../app/Config/Conexao.php';

$pdo = Conexao::getConexao();

$mensagemErro = '';
$mensagemSucesso = '';


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
            $mensagemSucesso = "Chamado aberto com sucesso! Nossa equipe técnica entrará em contato em breve.";
        } catch (Exception $e) {
            $mensagemErro = "Erro ao registrar o chamado. Tente novamente mais tarde.";
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
    <title>Portal do Cliente - Abrir Chamado</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet" />
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/telas.css">
</head>
<body style="background-color: #f4f7fa; font-family: 'Inter', sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 20px;">

    <div style="background: white; width: 100%; max-width: 600px; padding: 40px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); border: 1px solid #eaeaea;">
        
        <div style="text-align: center; margin-bottom: 30px;">
            <span class="material-symbols-rounded" style="font-size: 3.5rem; color: #1a73e8; margin-bottom: 10px;">support_agent</span>
            <h1 style="font-size: 1.8rem; color: #0d1b2a; margin-bottom: 5px;">Portal do Solicitante</h1>
            <p style="color: #666; font-size: 0.95rem;">Abra um chamado de suporte técnico e acompanhe sua solicitação.</p>
        </div>

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

        <form action="cliente.php" method="POST">
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 500; font-size: 0.9rem; color: #0d1b2a; margin-bottom: 8px;">Seu Nome Completo</label>
                <input type="text" name="solicitante" required placeholder="Ex: Maria Oliveira" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-family: 'Inter', sans-serif; font-size: 0.95rem;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 500; font-size: 0.9rem; color: #0d1b2a; margin-bottom: 8px;">Seu E-mail Corporativo</label>
                <input type="email" name="email" required placeholder="seu.email@empresa.com" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-family: 'Inter', sans-serif; font-size: 0.95rem;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 500; font-size: 0.9rem; color: #0d1b2a; margin-bottom: 8px;">Assunto do Problema</label>
                <input type="text" name="assunto" required placeholder="Ex: Erro ao acessar o sistema de faturamento" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-family: 'Inter', sans-serif; font-size: 0.95rem;">
            </div>

            <div style="margin-bottom: 25px;">
                <label style="display: block; font-weight: 500; font-size: 0.9rem; color: #0d1b2a; margin-bottom: 8px;">Descrição Detalhada</label>
                <textarea name="descricao" rows="4" required placeholder="Descreva os passos que geraram o erro ou o problema ocorrido..." style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-family: 'Inter', sans-serif; font-size: 0.95rem; resize: vertical;"></textarea>
            </div>

            <button type="submit" style="width: 100%; background: #1a73e8; color: white; border: none; padding: 14px; border-radius: 6px; font-weight: 600; font-size: 1rem; cursor: pointer; transition: 0.2s;">
                Enviar Chamado
            </button>
        </form>

        <div style="text-align: center; margin-top: 25px; border-top: 1px solid #eee; padding-top: 20px;">
            <a href="login.php" style="color: #1a73e8; text-decoration: none; font-size: 0.9rem; font-weight: 500;">Área restrita para equipe técnica</a>
        </div>

    </div>

</body>
</html>
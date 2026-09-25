<?php
use App\Config\Conexao;

require_once '../app/Config/Conexao.php';

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    
    if ($email && $senha) {
        header("Location: painel.php");
        exit;
    } else {
        $erro = 'Preencha todos os campos para prosseguir.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Suporte Técnico</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/login.css?v=3.2">
</head>
<body>

    <div class="login-card">
        <div class="login-header">
            <h2>Suporte Técnico</h2>
            <p>Faça login para acessar o sistema</p>
        </div>

        <?php if (!empty($erro)): ?>
            <div class="alert-error"><?php echo htmlspecialchars($erro); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="seu@email.com" required>
            </div>

            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" class="form-control" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn-submit">Entrar no Sistema</button>
        </form>

        <div class="card-footer-link">
            <a href="esqueci_senha.php" class="forgot-password-link">Esqueci minha senha.</a>
        </div>
    </div>

</body>
</html>
<?php
use App\Config\Conexao;

require_once '../app/Config/Conexao.php';

$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if ($email) {
        try {
            $pdo = Conexao::getConexao();

            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email");
            $stmt->execute([':email' => $email]);

            if ($stmt->fetch()) {
                $token = bin2hex(random_bytes(32));
                $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));

                $stmtInsert = $pdo->prepare("INSERT INTO recuperacao_senha (email, token, expira_em) VALUES (:email, :token, :expira)");
                $stmtInsert->execute([
                    ':email'  => $email,
                    ':token'  => $token,
                    ':expira' => $expira
                ]);

                $link = "redefinir_senha.php?token=" . $token;
                $mensagem = "Link de recuperação gerado! <br><a href='$link' style='color:#16a34a; font-weight:bold;'>Clique aqui para redefinir</a>";
                $tipo_mensagem = 'success';
            } else {
                $mensagem = 'E-mail não localizado no sistema.';
                $tipo_mensagem = 'error';
            }
        } catch (PDOException $e) {
            $mensagem = 'Erro no banco de dados: ' . $e->getMessage();
            $tipo_mensagem = 'error';
        }
    } else {
        $mensagem = 'Informe o e-mail cadastrado.';
        $tipo_mensagem = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha - Suporte Técnico</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/login.css?v=3.2">
</head>
<body>

    <div class="login-card">
        <div class="login-header">
            <h2>Recuperar Senha</h2>
            <p>Informe seu e-mail cadastrado</p>
        </div>

        <?php if (!empty($mensagem)): ?>
            <div class="alert-<?php echo $tipo_mensagem === 'success' ? 'success' : 'error'; ?>">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="email">E-mail Cadastrado</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="seu@email.com" required>
            </div>

            <button type="submit" class="btn-submit">Enviar Instruções</button>
        </form>

        <div class="card-footer-link">
            <a href="login.php" class="forgot-password-link">Voltar para o Login</a>
        </div>
    </div>

</body>
</html>
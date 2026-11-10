<?php
use App\Config\Conexao;
use App\Helpers\Auth;

require_once '../app/Config/Conexao.php';
require_once '../app/Helpers/Auth.php';
 
Auth::iniciar();

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($email && $senha) {
        try {
            $pdo = Conexao::getConexao();

            $sql = "SELECT id_usuario, nome, email, senha, nivel_acesso
                    FROM usuarios
                    WHERE email = :email
                    LIMIT 1";

            $stmt = $pdo->prepare($sql);
            $stmt->execute(['email' => $email]);

            $usuario = $stmt->fetch();

            if ($usuario && password_verify($senha, $usuario['senha'])) {

                session_regenerate_id(true);

                $_SESSION['usuario_id'] = $usuario['id_usuario'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                $_SESSION['usuario_email'] = $usuario['email'];
                $_SESSION['usuario_perfil'] = $usuario['nivel_acesso'];

                header("Location: painel.php");
                exit;

            } else {
                $erro = 'E-mail ou senha incorretos.';
            }

        } catch (\Throwable $e) {
            error_log($e->getMessage());
            $erro = 'Não foi possível realizar o login. Tente novamente.';
        }

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
            <div class="alert-error">
                <?php echo htmlspecialchars($erro); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email"
                       class="form-control"
                       placeholder="seu@email.com" required>
            </div>

           
            <div class="form-group" style="position: relative;">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha"
                       class="form-control"
                       placeholder="••••••••" required>
                
                <button type="button" id="togglePassword" class="btn-eye" aria-label="Mostrar senha">
                    <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </button>
            </div>

            <button type="submit" class="btn-submit">
                Entrar no Sistema
            </button>
        </form>
        <div class="card-footer-link">
            <a href="esqueci_senha.php" class="forgot-password-link">
                Esqueci minha senha.
            </a>
        </div>
    </div>
    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#senha');
        const eyeIcon = document.querySelector('#eyeIcon');

        togglePassword.addEventListener('click', function () {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            
            if (type === 'text') {
                eyeIcon.setAttribute('stroke', '#3b82f6'); 
            } else {
                eyeIcon.setAttribute('stroke', '#666'); 
            }
        });
    </script>
</body>
</html>

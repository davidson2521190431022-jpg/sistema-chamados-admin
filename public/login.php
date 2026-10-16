
<?php
use App\Config\Conexao;

ob_start();

require_once __DIR__ . '/../app/Config/Conexao.php';


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (!empty($_SESSION['usuario_id'])) {
    header('Location: painel.php');
    exit;
}

$erro = '';
$emailDigitado = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailDigitado = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($emailDigitado === '' || $senha === '') {
        $erro = 'Preencha todos os campos para prosseguir.';
    } elseif (!filter_var($emailDigitado, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Digite um endereço de e-mail válido.';
    } else {
        try {
            $pdo = Conexao::getConexao();

            $stmt = $pdo->prepare(
                'SELECT id_usuario, nome, email, senha, nivel_acesso
                 FROM usuarios
                 WHERE email = :email
                 LIMIT 1'
            );

            $stmt->execute([
                ':email' => $emailDigitado
            ]);

            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            $senhaValida = false;

            if ($usuario && isset($usuario['senha'])) {
                $senhaArmazenada = (string) $usuario['senha'];

                if (password_verify($senha, $senhaArmazenada)) {
                    $senhaValida = true;
                }

                elseif (
                    strlen($senhaArmazenada) < 30 &&
                    hash_equals($senhaArmazenada, $senha)
                ) {
                    $senhaValida = true;

                    $novoHash = password_hash(
                        $senha,
                        PASSWORD_DEFAULT
                    );

                    $update = $pdo->prepare(
                        'UPDATE usuarios
                         SET senha = :senha
                         WHERE id_usuario = :id'
                    );

                    $update->execute([
                        ':senha' => $novoHash,
                        ':id' => $usuario['id_usuario']
                    ]);
                }
            }

            if ($usuario && $senhaValida) {
                session_regenerate_id(true);

                $_SESSION['usuario_id'] = $usuario['id_usuario'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                $_SESSION['usuario_perfil'] = $usuario['nivel_acesso'];

                header('Location: painel.php');
                exit;
            }

            $erro = 'E-mail ou senha inválidos.';

        } catch (Throwable $e) {
            error_log(
                'Erro ao autenticar usuário: ' . $e->getMessage()
            );

            $erro = 'Não foi possível realizar o login. Verifique a configuração do sistema e tente novamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - Suporte Técnico</title>

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="css/login.css?v=3.3">
</head>
<body>

    <div class="login-card">

        <div class="login-header">
            <h2>Suporte Técnico</h2>
            <p>Faça login para acessar o sistema</p>
        </div>

        <?php if ($erro !== ''): ?>
            <div class="alert-error" role="alert">
                <?= htmlspecialchars(
                    $erro,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">

            <div class="form-group">
                <label for="email">E-mail</label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control"
                    placeholder="seu@email.com"
                    value="<?= htmlspecialchars(
                        $emailDigitado,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    autocomplete="username"
                    required
                >
            </div>

            <div class="form-group">
                <label for="senha">Senha</label>

                <input
                    type="password"
                    id="senha"
                    name="senha"
                    class="form-control"
                    placeholder="Digite sua senha"
                    autocomplete="current-password"
                    required
                >
            </div>

            <button type="submit" class="btn-submit">
                Entrar no Sistema
            </button>

        </form>

        <div class="card-footer-link">
            <a
                href="esqueci_senha.php"
                class="forgot-password-link"
            >
                Esqueci minha senha.
            </a>
        </div>

    </div>

</body>
</html>
```

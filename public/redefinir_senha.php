<?php
use App\Config\Conexao;

require_once '../app/Config/Conexao.php';

$token = $_GET['token'] ?? '';
$mensagem = '';
$tipo_mensagem = '';
$token_valido = false;


if ($token) {
    try {
        $pdo = Conexao::getConexao();

        $stmt = $pdo->prepare("
            SELECT id_recuperacao, id_usuario
            FROM recuperacao_senha
            WHERE token = :token
              AND data_expiracao > NOW()
        ");
        $stmt->execute([':token' => $token]);
        $registro = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($registro) {
            $token_valido = true;

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $nova_senha = $_POST['senha'] ?? '';

                if (strlen($nova_senha) >= 6) {
                    $senha_hash = password_hash(
                        $nova_senha,
                        PASSWORD_DEFAULT
                    );

                    $update = $pdo->prepare("
                        UPDATE usuarios
                        SET senha = :senha
                        WHERE id_usuario = :id_usuario
                    ");
                    $update->execute([
                        ':senha' => $senha_hash,
                        ':id_usuario' => $registro['id_usuario']
                    ]);

                    $delete = $pdo->prepare("
                        DELETE FROM recuperacao_senha
                        WHERE id_recuperacao = :id_recuperacao
                    ");
                    $delete->execute([
                        ':id_recuperacao' => $registro['id_recuperacao']
                    ]);

                    $mensagem = 'Senha redefinida com sucesso!';
                    $tipo_mensagem = 'success';
                    $token_valido = false;
                } else {
                    $mensagem = 'A senha deve conter no mínimo 6 caracteres.';
                    $tipo_mensagem = 'error';
                }
            }
        } else {
            $mensagem = 'Este link é inválido ou já expirou.';
            $tipo_mensagem = 'error';
        }
    } catch (PDOException $e) {
        error_log('Erro ao redefinir senha: ' . $e->getMessage());
        $mensagem = 'Não foi possível redefinir a senha. Tente novamente.';
        $tipo_mensagem = 'error';
    }
} else {
    header("Location: login.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha - Suporte Técnico</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/login.css?v=3.2">
</head>
<body>

    <div class="login-card">
        <div class="login-header">
            <h2>Nova Senha</h2>
            <p>Crie uma nova senha de acesso</p>
        </div>

        <?php if (!empty($mensagem)): ?>
            <div class="alert-<?php echo $tipo_mensagem === 'success' ? 'success' : 'error'; ?>">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

        <?php if ($token_valido): ?>
            <form method="POST">
                <div class="form-group">
                    <label for="senha">Nova Senha</label>
                    <input type="password" id="senha" name="senha" class="form-control" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn-submit">salvar Nova Senha </button>
            </form>
        <?php else: ?>
            <div class="card-footer-link">
                <a href="login.php" class="forgot-password-link">ir para tela de login </a>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>
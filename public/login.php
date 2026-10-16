<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
use App\Config\Conexao;

require_once __DIR__ . '/../app/Config/Conexao.php';

if (!empty($_SESSION['usuario_id'])) {
    header('Location: painel.php');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha =$_POST['senha'] ?? '';

    if ($email === '' || $senha === '') {$erro = 'Preencha todos os campos para prosseguir.';
    } else {
        try {
            $pdo = Conexao::getConexao();

            $stmt =$pdo->prepare(
                'SELECT id_usuario, nome, email, senha, nivel_acesso
                 FROM usuarios
                 WHERE email = :email
                 LIMIT 1'
            );

            $stmt->execute([':email' =>$email]);
            $usuario =$stmt->fetch(PDO::FETCH_ASSOC);

            $senhaValida = false;

            if ($usuario) {
                $senhaArmazenada =$usuario['senha'];

                if (password_verify($senha, $senhaArmazenada)) {$senhaValida = true;
                } elseif (
                    strlen($senhaArmazenada) < 30 &&
                    hash_equals($senhaArmazenada,$senha)
                ) {
                
                    $senhaValida = true;

                    $novoHash = password_hash($senha, PASSWORD_DEFAULT);

                    $update =$pdo->prepare(
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
            if ($senhaValida) {
                session_regenerate_id(true);

                $_SESSION['usuario_id'] =$usuario['id_usuario'];
                $_SESSION['usuario_nome'] =$usuario['nome'];
                $_SESSION['usuario_perfil'] =$usuario['nivel_acesso'];

                header('Location: painel.php');
                exit;
            }

            $erro = 'E-mail ou senha inválidos.';
        } catch (PDOException $e) {
            error

<?php
use App\Config\Conexao;
require_once '../app/Config/Conexao.php';
$mensagem = '';
$tipo_mensagem = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome  = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $nivel = $_POST['nivel'] ?? 'tecnico';
    if ($nome && $email && $senha) {
        try {
            $pdo = Conexao::getConexao();
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email");
            $stmt->execute([':email' => $email]);
            if ($stmt->fetch()) {
                $mensagem = 'Este e-mail já está cadastrado.';
                $tipo_mensagem = 'error';
            } else {
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                $sql = "INSERT INTO usuarios (nome, email, senha, nivel) VALUES (:nome, :email, :senha, :nivel)";
                $insert = $pdo->prepare($sql);
                $insert->execute([
                    ':nome'  => $nome,
                    ':email' => $email,
                    ':senha' => $senha_hash,
                    ':nivel' => $nivel
                ]);

                $mensagem = 'Técnico cadastrado com sucesso!';
                $tipo_mensagem = 'success';
            }
        } catch (PDOException $e) {
            $mensagem = 'Erro no banco de dados: ' . $e->getMessage();
            $tipo_mensagem = 'error';
        }
    } else {
        $mensagem = 'Preencha todos os campos obrigatórios.';
        $tipo_mensagem = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Técnico - Suporte</title>
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
        <a href="cadastrar_tecnico.php" class="active"><span class="material-symbols-rounded">badge</span> Técnicos</a>
        <a href="configuracoes.php"><span class="material-symbols-rounded">settings</span> Configurações</a>
        <a href="logout.php"><span class="material-symbols-rounded">logout</span> Sair</a>
    </aside>
    <main class="main-content">
        <header class="top-header">
            <h1>Cadastrar Novo Técnico</h1>
        </header>
        <div class="table-container" style="max-width: 600px;">
            <div class="table-header">
                <h3>Dados do Profissional</h3>
            </div>
            <?php if (!empty($mensagem)): ?>
                <div class="alert-<?php echo $tipo_mensagem === 'success' ? 'success' : 'error'; ?>">
                    <?php echo htmlspecialchars($mensagem); ?>
                </div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label for="nome">Nome Completo</label>
                    <input type="text" id="nome" name="nome" class="form-control" required placeholder="Ex: Carlos Silva">
                </div>
                <div class="form-group">
                    <label for="email">E-mail de Acesso</label>
                    <input type="email" id="email" name="email" class="form-control" required placeholder="carlos@empresa.com">
                </div>
                <div class="form-group">
                    <label for="senha">Senha Inicial</label>
                    <input type="password" id="senha" name="senha" class="form-control" required placeholder="••••••••">
                </div>
                <div class="form-group">
                    <label for="nivel">Nível de Permissão</label>
                    <select id="nivel" name="nivel" class="form-control">
                        <option value="tecnico">Técnico</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
                <button type="submit" class="btn-submit">Salvar Técnico</button>
            </form>
        </div>
    </main>
</body>
</html> 
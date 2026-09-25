<?php
use App\Config\Conexao;

require_once '../app/Config/Conexao.php';

$msg_config = '';
$msg_tecnico = '';
$tipo_msg_tecnico = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (isset($_POST['acao']) && $_POST['acao'] === 'salvar_config') {
        $msg_config = 'Configurações do sistema salvas com sucesso!';
    }

   
    if (isset($_POST['acao']) && $_POST['acao'] === 'cadastrar_tecnico') {
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
                    $msg_tecnico = 'Este e-mail já está cadastrado no sistema.';
                    $tipo_msg_tecnico = 'error';
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

                    $msg_tecnico = 'Técnico cadastrado com sucesso!';
                    $tipo_msg_tecnico = 'success';
                }
            } catch (PDOException $e) {
                $msg_tecnico = 'Erro no banco de dados: ' . $e->getMessage();
                $tipo_msg_tecnico = 'error';
            }
        } else {
            $msg_tecnico = 'Preencha todos os campos para cadastrar o técnico.';
            $tipo_msg_tecnico = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações do Sistema - Suporte</title>
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
        <a href="configuracoes.php" class="active"><span class="material-symbols-rounded">settings</span> Configurações</a>
        <a href="logout.php"><span class="material-symbols-rounded">logout</span> Sair</a>
    </aside>

    <main class="main-content">
        <header class="top-header">
            <h1>Configurações do Sistema</h1>
        </header>

        <!-- Bloco 1: Parâmetros Gerais -->
        <div class="table-container" style="max-width: 650px; margin-bottom: 30px;">
            <div class="table-header">
                <h3>Parâmetros Gerais</h3>
            </div>

            <?php if (!empty($msg_config)): ?>
                <div class="alert-success"><?php echo htmlspecialchars($msg_config); ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="acao" value="salvar_config">

                <div class="form-group">
                    <label for="nome_sistema">Nome da Organização / Empresa</label>
                    <input type="text" id="nome_sistema" name="nome_sistema" class="form-control" value="Suporte Técnico Interno">
                </div>

                <div class="form-group">
                    <label for="email_suporte">E-mail para Alertas de Novos Chamados</label>
                    <input type="email" id="email_suporte" name="email_suporte" class="form-control" value="suporte@empresa.com">
                </div>

                <button type="submit" class="btn-submit">Salvar Alterações</button>
            </form>
        </div>

        <!-- Bloco 2: Cadastrar Novo Técnico -->
        <div class="table-container" style="max-width: 650px;">
            <div class="table-header">
                <h3>Cadastrar Novo Técnico</h3>
            </div>

            <?php if (!empty($msg_tecnico)): ?>
                <div class="alert-<?php echo $tipo_msg_tecnico === 'success' ? 'success' : 'error'; ?>">
                    <?php echo htmlspecialchars($msg_tecnico); ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="acao" value="cadastrar_tecnico">

                <div class="form-group">
                    <label for="nome">Nome do Técnico</label>
                    <input type="text" id="nome" name="nome" class="form-control" placeholder="Ex: Carlos Silva" required>
                </div>

                <div class="form-group">
                    <label for="email">E-mail de Acesso</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="carlos@empresa.com" required>
                </div>

                <div class="form-group">
                    <label for="senha">Senha Inicial</label>
                    <input type="password" id="senha" name="senha" class="form-control" placeholder="••••••••" required>
                </div>

                <div class="form-group">
                    <label for="nivel">Nível de Acesso</label>
                    <select id="nivel" name="nivel" class="form-control">
                        <option value="tecnico">Técnico</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>

                <button type="submit" class="btn-submit">Cadastrar Técnico</button>
            </form>
        </div>
    </main>

</body>
</html>
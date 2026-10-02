
<?php
use App\Config\Conexao;

require_once '../app/Config/Conexao.php';

$pdo = Conexao::getConexao();

$chamadosClientes = [];
$erro = false;
$mensagemErro = '';

$porPagina = 10;
$paginaAtual = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;

if ($paginaAtual < 1) {
    $paginaAtual = 1;
}

$totalChamados = 0;
$totalPaginas = 1;

try {
    $totalChamados = (int) $pdo->query(
        "SELECT COUNT(*) FROM chamados"
    )->fetchColumn();

    $totalPaginas = max(
        1,
        (int) ceil($totalChamados / $porPagina)
    );

    if ($paginaAtual > $totalPaginas) {
        $paginaAtual = $totalPaginas;
    }

    $offset = ($paginaAtual - 1) * $porPagina;

    $stmt = $pdo->prepare("
        SELECT
            id,
            solicitante,
            email,
            assunto,
            descricao,
            status,
            criado_em
        FROM chamados
        ORDER BY id DESC
        LIMIT :limite OFFSET :offset
    ");

    $stmt->bindValue(':limite', $porPagina, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $chamadosClientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log($e->getMessage());
    $erro = true;
    $mensagemErro = $e->getMessage();
}

// Mostra no máximo 5 números de página.
$janela = 5;
$inicio = max(1, $paginaAtual - 2);
$fim = min($totalPaginas, $inicio + $janela - 1);
$inicio = max(1, $fim - $janela + 1);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Histórico de Atendimentos - Sistema de Chamados</title>

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="css/painel.css">

    <style>
        .paginacao {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            padding: 20px;
            flex-wrap: wrap;
        }

        .paginacao a,
        .paginacao span {
            min-width: 38px;
            padding: 8px 12px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 6px;
            color: #0d1b2a;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            background: #fff;
        }

        .paginacao a:hover {
            background: #e8f0fe;
            border-color: #1a73e8;
        }

        .paginacao .ativa {
            background: #1a73e8;
            border-color: #1a73e8;
            color: #fff;
        }

        .paginacao .desativada {
            color: #aaa;
            background: #f5f5f5;
            cursor: not-allowed;
        }

        .info-paginacao {
            text-align: center;
            color: #777;
            font-size: 0.85rem;
            padding-bottom: 15px;
        }

        .erro-detalhado {
            color: #b91c1c;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 6px;
            padding: 15px;
            margin: 20px;
            overflow-wrap: anywhere;
        }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-brand">
            <span class="material-symbols-rounded">headset_mic</span>
            Suporte Técnico
        </div>

        <a href="painel.php">
            <span class="material-symbols-rounded">home</span>
            Painel
        </a>

        <a href="chamados.php">
            <span class="material-symbols-rounded">confirmation_number</span>
            Chamados
        </a>

        <a href="clientes.php" class="active">
            <span class="material-symbols-rounded">person</span>
            Clientes
        </a>

        <a href="relatorios.php">
            <span class="material-symbols-rounded">bar_chart</span>
            Relatórios
        </a>

        <a href="configuracoes.php">
            <span class="material-symbols-rounded">settings</span>
            Configurações
        </a>

        <a href="logout.php">
            <span class="material-symbols-rounded">logout</span>
            Sair
        </a>
    </aside>

    <main class="main-content">
        <header class="top-header">
            <h1>Histórico de Atendimentos</h1>
        </header>

        <div class="table-container">
            <div class="table-header">
                <h3>
                    Histórico Geral de Solicitações
                    (<?= $totalChamados ?>)
                </h3>
            </div>

            <?php if ($erro): ?>

                <div class="erro-detalhado">
                    <strong>Erro ao consultar os chamados:</strong>
                    <pre><?= htmlspecialchars(
                        $mensagemErro,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?></pre>
                </div>

            <?php else: ?>

                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Solicitante</th>
                            <th>E-mail</th>
                            <th>Assunto</th>
                            <th>Status</th>
                            <th>Data</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!empty($chamadosClientes)): ?>

                            <?php foreach ($chamadosClientes as $chamado): ?>
                                <?php
                                $status = trim($chamado['status'] ?? '');
                                $statusStr = mb_strtolower($status);

                                $statusClass = 'abertos';

                                if (str_contains($statusStr, 'andamento')) {
                                    $statusClass = 'andamento';
                                } elseif (str_contains($statusStr, 'aguardando')) {
                                    $statusClass = 'aguardando';
                                } elseif (
                                    str_contains($statusStr, 'resolvido') ||
                                    str_contains($statusStr, 'fechado')
                                ) {
                                    $statusClass = 'resolvido';
                                }

                                $statusTexto = ucfirst(
                                    str_replace('_', ' ', $status)
                                );
                                ?>

                                <tr>
                                    <td>#<?= (int) $chamado['id'] ?></td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $chamado['solicitante'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $chamado['email'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        <strong>
                                            <?= htmlspecialchars(
                                                $chamado['assunto'] ?? '',
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </strong>
                                        <br>
                                        <small>
                                            <?= htmlspecialchars(
                                                $chamado['descricao'] ?? '',
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </small>
                                    </td>

                                    <td>
                                        <span class="badge <?= htmlspecialchars(
                                            $statusClass,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>">
                                            <?= htmlspecialchars(
                                                $statusTexto,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?= !empty($chamado['criado_em'])
                                            ? date(
                                                'd/m/Y H:i',
                                                strtotime($chamado['criado_em'])
                                            )
                                            : '—' ?>
                                    </td>
                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td
                                    colspan="6"
                                    style="text-align: center; color: #777; padding: 20px;"
                                >
                                    Nenhum chamado de cliente registrado até o momento.
                                </td>
                            </tr>

                        <?php endif; ?>
                    </tbody>
                </table>

                <?php if ($totalPaginas > 1): ?>

                    <nav class="paginacao" aria-label="Paginação">

                        <?php if ($paginaAtual > 1): ?>
                            <a href="?pagina=<?= $paginaAtual - 1 ?>">
                                &laquo; Anterior
                            </a>
                        <?php else: ?>
                            <span class="desativada">&laquo; Anterior</span>
                        <?php endif; ?>

                        <?php if ($inicio > 1): ?>
                            <a href="?pagina=1">1</a>
                            <?php if ($inicio > 2): ?>
                                <span class="desativada">...</span>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php for ($i = $inicio; $i <= $fim; $i++): ?>
                            <?php if ($i === $paginaAtual): ?>
                                <span class="ativa"><?= $i ?></span>
                            <?php else: ?>
                                <a href="?pagina=<?= $i ?>"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($fim < $totalPaginas): ?>
                            <?php if ($fim < $totalPaginas - 1): ?>
                                <span class="desativada">...</span>
                            <?php endif; ?>
                            <a href="?pagina=<?= $totalPaginas ?>">
                                <?= $totalPaginas ?>
                            </a>
                        <?php endif; ?>

                        <?php if ($paginaAtual < $totalPaginas): ?>
                            <a href="?pagina=<?= $paginaAtual + 1 ?>">
                                Próxima &raquo;
                            </a>
                        <?php else: ?>
                            <span class="desativada">Próxima &raquo;</span>
                        <?php endif; ?>

                    </nav>

                    <p class="info-paginacao">
                        Página <?= $paginaAtual ?> de <?= $totalPaginas ?>
                        &middot; <?= $porPagina ?> por página
                    </p>

                <?php endif; ?>

            <?php endif; ?>
        </div>
    </main>

</body>
</html>

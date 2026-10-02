
<?php
use App\Config\Conexao;

require_once '../app/Config/Conexao.php';

$pdo = Conexao::getConexao();

$chamadosClientes = [];
$erro = false;
$mensagemErro = '';

$porPagina = 10;
$paginaAtual = max(1, (int) ($_GET['pagina'] ?? 1));
$busca = trim($_GET['busca'] ?? '');

$totalChamados = 0;
$totalPaginas = 1;

try {
    // Conta os chamados de acordo com o nome pesquisado.
    $stmtContagem = $pdo->prepare("
        SELECT COUNT(*)
        FROM chamados
        WHERE solicitante LIKE :busca
    ");

    $stmtContagem->execute([
        ':busca' => '%' . $busca . '%'
    ]);

    $totalChamados = (int) $stmtContagem->fetchColumn();

    $totalPaginas = max(
        1,
        (int) ceil($totalChamados / $porPagina)
    );

    if ($paginaAtual > $totalPaginas) {
        $paginaAtual = $totalPaginas;
    }

    $offset = ($paginaAtual - 1) * $porPagina;

    // Consulta os chamados com o filtro de nome.
    $stmt = $pdo->prepare("
        SELECT *
        FROM chamados
        WHERE solicitante LIKE :busca
        ORDER BY id DESC
        LIMIT :limite OFFSET :offset
    ");

    $stmt->bindValue(
        ':busca',
        '%' . $busca . '%',
        PDO::PARAM_STR
    );
    $stmt->bindValue(':limite', $porPagina, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $chamadosClientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Lista os nomes cadastrados para as sugestões.
    $stmtNomes = $pdo->query("
        SELECT DISTINCT solicitante
        FROM chamados
        WHERE solicitante IS NOT NULL
          AND solicitante <> ''
        ORDER BY solicitante
    ");

    $nomes = $stmtNomes->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    error_log($e->getMessage());
    $erro = true;
    $mensagemErro = 'Não foi possível consultar os atendimentos.';
}

// Exibe no máximo cinco números de página.
$janela = 5;
$inicio = max(1, $paginaAtual - 2);
$fim = min($totalPaginas, $inicio + $janela - 1);
$inicio = max(1, $fim - $janela + 1);

// Mantém o termo de pesquisa nos links.
function linkPagina(int $pagina, string $busca): string
{
    return '?' . http_build_query([
        'pagina' => $pagina,
        'busca' => $busca
    ]);
}
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

        .pesquisa {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            padding: 15px 20px;
        }

        .pesquisa input {
            flex: 1;
            min-width: 200px;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
            outline: none;
        }

        .pesquisa input:focus {
            border-color: #1a73e8;
            box-shadow: 0 0 0 2px rgba(26, 115, 232, 0.12);
        }

        .pesquisa button {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            background: #1a73e8;
            color: #fff;
            font-family: inherit;
            font-weight: 600;
            cursor: pointer;
        }

        .pesquisa button:hover {
            background: #1557b0;
        }

        .limpar-pesquisa {
            padding: 10px;
            color: #1a73e8;
            text-decoration: none;
            font-size: 14px;
        }

        .limpar-pesquisa:hover {
            text-decoration: underline;
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

            <!-- Pesquisa de pessoas -->
            <form method="GET" action="clientes.php" class="pesquisa">
                <input
                    type="search"
                    name="busca"
                    list="sugestoes-nomes"
                    value="<?= htmlspecialchars(
                        $busca,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    placeholder="Digite o nome da pessoa..."
                    autocomplete="off"
                    aria-label="Pesquisar pelo nome da pessoa"
                >

                <datalist id="sugestoes-nomes">
                    <?php foreach (($nomes ?? []) as $nome): ?>
                        <option value="<?= htmlspecialchars(
                            $nome,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>">
                    <?php endforeach; ?>
                </datalist>

                <button type="submit">
                    <span class="material-symbols-rounded"
                          style="font-size: 18px; vertical-align: middle;">
                        search
                    </span>
                    Pesquisar
                </button>

                <?php if ($busca !== ''): ?>
                    <a href="clientes.php" class="limpar-pesquisa">
                        Limpar
                    </a>
                <?php endif; ?>
            </form>

            <?php if ($erro): ?>

                <div class="erro-detalhado">
                    <strong>Erro ao consultar os atendimentos:</strong>
                    <p><?= htmlspecialchars(
                        $mensagemErro,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?></p>
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

                                $data = $chamado['criado_em'] ?? '';
                                ?>

                                <tr>
                                    <td>
                                        #<?= (int) ($chamado['id'] ?? 0) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $chamado['solicitante'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $chamado['email'] ?? '—',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        <strong>
                                            <?= htmlspecialchars(
                                                $chamado['assunto']
                                                ?? $chamado['titulo']
                                                ?? '—',
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
                                        <?php if ($data !== ''): ?>
                                            <?= htmlspecialchars(
                                                date(
                                                    'd/m/Y H:i',
                                                    strtotime($data)
                                                ),
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        <?php else: ?>
                                            —
                                        <?php endif; ?>
                                    </td>
                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td
                                    colspan="6"
                                    style="text-align: center; color: #777; padding: 20px;"
                                >
                                    <?php if ($busca !== ''): ?>
                                        Nenhum atendimento encontrado para
                                        "<?= htmlspecialchars(
                                            $busca,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>".
                                    <?php else: ?>
                                        Nenhum chamado de cliente registrado
                                        até o momento.
                                    <?php endif; ?>
                                </td>
                            </tr>

                        <?php endif; ?>
                    </tbody>
                </table>

                <?php if ($totalPaginas > 1): ?>

                    <nav class="paginacao" aria-label="Paginação">

                        <?php if ($paginaAtual > 1): ?>
                            <a href="<?= htmlspecialchars(
                                linkPagina($paginaAtual - 1, $busca),
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>">
                                &laquo; Anterior
                            </a>
                        <?php else: ?>
                            <span class="desativada">
                                &laquo; Anterior
                            </span>
                        <?php endif; ?>

                        <?php if ($inicio > 1): ?>
                            <a href="<?= htmlspecialchars(
                                linkPagina(1, $busca),
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>">1</a>

                            <?php if ($inicio > 2): ?>
                                <span class="desativada">...</span>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php for ($i = $inicio; $i <= $fim; $i++): ?>
                            <?php if ($i === $paginaAtual): ?>
                                <span class="ativa"><?= $i ?></span>
                            <?php else: ?>
                                <a href="<?= htmlspecialchars(
                                    linkPagina($i, $busca),
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>">
                                    <?= $i ?>
                                </a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($fim < $totalPaginas): ?>
                            <?php if ($fim < $totalPaginas - 1): ?>
                                <span class="desativada">...</span>
                            <?php endif; ?>

                            <a href="<?= htmlspecialchars(
                                linkPagina($totalPaginas, $busca),
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>">
                                <?= $totalPaginas ?>
                            </a>
                        <?php endif; ?>

                        <?php if ($paginaAtual < $totalPaginas): ?>
                            <a href="<?= htmlspecialchars(
                                linkPagina($paginaAtual + 1, $busca),
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>">
                                Próxima &raquo;
                            </a>
                        <?php else: ?>
                            <span class="desativada">
                                Próxima &raquo;
                            </span>
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


<?php
use App\Config\Conexao;

require_once '../app/Config/Conexao.php';

$pdo = Conexao::getConexao();

$chamadosClientes = [];
$nomesPessoas = [];
$erro = false;

$busca = trim($_GET['busca'] ?? '');
$porPagina = 10;
$paginaAtual = max(1, (int) ($_GET['pagina'] ?? 1));
$totalChamados = 0;
$totalPaginas = 1;

try {
    // Identifica as colunas existentes no banco.
    $colunas = $pdo->query("SHOW COLUMNS FROM chamados")
                   ->fetchAll(PDO::FETCH_COLUMN);

    $tem = static fn($coluna) => in_array($coluna, $colunas, true);

    // Campos utilizados na pesquisa.
    $camposPesquisa = [];
    foreach (['nome', 'solicitante'] as $campo) {
        if ($tem($campo)) {
            $camposPesquisa[] = $campo;
        }
    }

    // Monta a pesquisa sem alterar os dados.
    $condicoes = [];
    $parametros = [];

    if ($busca !== '' && count($camposPesquisa) > 0) {
        foreach ($camposPesquisa as $i => $campo) {
            $param = ':busca' . $i;
            $condicoes[] = "`$campo` LIKE $param";
            $parametros[$param] = '%' . $busca . '%';
        }
    }

    $where = count($condicoes) > 0
        ? ' WHERE (' . implode(' OR ', $condicoes) . ')'
        : '';

    // Total de chamados encontrados.
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM chamados" . $where
    );

    foreach ($parametros as $param => $valor) {
        $stmt->bindValue($param, $valor, PDO::PARAM_STR);
    }

    $stmt->execute();
    $totalChamados = (int) $stmt->fetchColumn();

    $totalPaginas = max(
        1,
        (int) ceil($totalChamados / $porPagina)
    );

    if ($paginaAtual > $totalPaginas) {
        $paginaAtual = $totalPaginas;
    }

    $offset = ($paginaAtual - 1) * $porPagina;

    // Busca os chamados da página atual.
    $stmt = $pdo->prepare(
        "SELECT * FROM chamados" . $where .
        " ORDER BY id DESC LIMIT :limite OFFSET :offset"
    );

    foreach ($parametros as $param => $valor) {
        $stmt->bindValue($param, $valor, PDO::PARAM_STR);
    }

    $stmt->bindValue(':limite', $porPagina, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $chamadosClientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Lista os nomes para as sugestões da pesquisa.
    $consultasNomes = [];

    foreach ($camposPesquisa as $campo) {
        $consultasNomes[] =
            "SELECT DISTINCT `$campo` AS pessoa
             FROM chamados
             WHERE `$campo` IS NOT NULL
               AND TRIM(`$campo`) <> ''";
    }

    if (count($consultasNomes) > 0) {
        $sqlNomes = implode(' UNION ', $consultasNomes)
                  . ' ORDER BY pessoa';

        $nomesPessoas = $pdo->query($sqlNomes)
                            ->fetchAll(PDO::FETCH_COLUMN);
    }

} catch (PDOException $e) {
    error_log('Erro no histórico: ' . $e->getMessage());
    $erro = true;
}

// Paginação com até cinco números.
$janela = 5;
$inicio = max(1, $paginaAtual - 2);
$fim = min($totalPaginas, $inicio + $janela - 1);
$inicio = max(1, $fim - $janela + 1);

// Preserva a pesquisa nos links da paginação.
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

        .pesquisa-historico {
            display: flex;
            gap: 8px;
            align-items: center;
            padding: 15px 20px;
        }

        .pesquisa-historico input {
            flex: 1;
            min-width: 0;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 7px;
            font: inherit;
        }

        .pesquisa-historico button {
            padding: 10px 18px;
            border: 0;
            border-radius: 7px;
            background: #1a73e8;
            color: white;
            cursor: pointer;
            font: inherit;
            font-weight: 600;
        }

        .pesquisa-historico button:hover {
            background: #155fc0;
        }

        .pesquisa-historico .limpar {
            padding: 10px 12px;
            color: #1a73e8;
            text-decoration: none;
            white-space: nowrap;
        }

        .mensagem-vazia {
            text-align: center;
            color: #777;
            padding: 20px;
        }

        @media (max-width: 600px) {
            .pesquisa-historico {
                flex-wrap: wrap;
            }

            .pesquisa-historico input {
                flex-basis: 100%;
            }
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

                <div class="mensagem-vazia">
                    Ocorreu um erro ao consultar os chamados.
                    Verifique os registros de erro do sistema.
                </div>

            <?php else: ?>

                <form method="GET" action="clientes.php"
                      class="pesquisa-historico">

                    <input
                        type="search"
                        name="busca"
                        list="lista-pessoas"
                        placeholder="Digite o nome da pessoa..."
                        value="<?= htmlspecialchars(
                            $busca,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        autocomplete="off"
                    >

                    <datalist id="lista-pessoas">
                        <?php foreach ($nomesPessoas as $nome): ?>
                            <option value="<?= htmlspecialchars(
                                $nome,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>">
                        <?php endforeach; ?>
                    </datalist>

                    <button type="submit">
                        <span class="material-symbols-rounded"
                              style="font-size:16px; vertical-align:middle;">
                            search
                        </span>
                        Pesquisar
                    </button>

                    <?php if ($busca !== ''): ?>
                        <a class="limpar" href="clientes.php">Limpar</a>
                    <?php endif; ?>
                </form>

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
                                $solicitante = trim(
                                    $chamado['solicitante'] ?? ''
                                );

                                if ($solicitante === '') {
                                    $solicitante = trim(
                                        $chamado['nome'] ?? ''
                                    );
                                }

                                $email = $chamado['email'] ?? '';

                                $assunto = $chamado['assunto']
                                    ?? $chamado['titulo']
                                    ?? $chamado['categoria']
                                    ?? '—';

                                $status = trim($chamado['status'] ?? '');
                                $statusStr = strtolower($status);
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

                                $data = '—';
                                if (!empty($chamado['criado_em'])) {
                                    $timestamp = strtotime(
                                        $chamado['criado_em']
                                    );

                                    if ($timestamp !== false) {
                                        $data = date(
                                            'd/m/Y H:i',
                                            $timestamp
                                        );
                                    }
                                }
                                ?>

                                <tr>
                                    <td>
                                        #<?= (int) ($chamado['id'] ?? 0) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $solicitante,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $email,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        <strong>
                                            <?= htmlspecialchars(
                                                $assunto,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </strong>
                                        <?php if (!empty($chamado['descricao'])): ?>
                                            <br>
                                            <small>
                                                <?= htmlspecialchars(
                                                    $chamado['descricao'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>
                                            </small>
                                        <?php endif; ?>
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

                                    <td><?= htmlspecialchars(
                                        $data,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?></td>
                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="6" class="mensagem-vazia">
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
                            <span class="desativada">&laquo; Anterior</span>
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
                                ) ?>"><?= $i ?></a>
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
                            ) ?>"><?= $totalPaginas ?></a>
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

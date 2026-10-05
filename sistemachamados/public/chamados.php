<?php

use App\Config\Conexao;

require_once '../app/Config/Conexao.php';

$pdo = Conexao::getConexao();

/*
|--------------------------------------------------------------------------
| AÇÕES DOS CHAMADOS
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $acao = $_POST['acao'] ?? '';

    if ($id > 0) {

        try {

            if ($acao === 'aceitar') {

                $stmt = $pdo->prepare("
                    UPDATE chamados
                    SET status = 'Em Andamento'
                    WHERE id = ?
                ");

                $stmt->execute([$id]);

            } elseif ($acao === 'resolver') {

                $stmt = $pdo->prepare("
                    UPDATE chamados
                    SET status = 'Resolvido'
                    WHERE id = ?
                ");

                $stmt->execute([$id]);

            } elseif ($acao === 'encerrar') {

                $stmt = $pdo->prepare("
                    UPDATE chamados
                    SET status = 'Fechado'
                    WHERE id = ?
                ");

                $stmt->execute([$id]);
            }

        } catch (Exception $e) {

            error_log("Erro ao atualizar chamado: " . $e->getMessage());

        }
    }
}


/*
|--------------------------------------------------------------------------
| BUSCAR CHAMADOS
|--------------------------------------------------------------------------
*/

$chamados = [];

try {

    $stmt = $pdo->query("
        SELECT
            id,
            nome,
            matricula,
            setor,
            categoria,
            descricao,
            prioridade,
            status,
            cpf,
            criado_em,
            solicitante,
            email
        FROM chamados
        ORDER BY criado_em DESC
    ");

    $chamados = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {

    error_log("Erro ao buscar chamados: " . $e->getMessage());

}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Chamados - Suporte Técnico</title>

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="css/painel.css">

    <link rel="stylesheet" href="css/chamados.css">

</head>

<body>

    <aside class="sidebar">

        <div class="sidebar-brand">

            <span class="material-symbols-rounded">
                headset_mic
            </span>

            Suporte Técnico

        </div>


        <a href="painel.php">

            <span class="material-symbols-rounded">
                home
            </span>

            Painel

        </a>


        <a href="chamados.php" class="active">

            <span class="material-symbols-rounded">
                confirmation_number
            </span>

            Chamados

        </a>


        <a href="clientes.php">

            <span class="material-symbols-rounded">
                person
            </span>

            Histórico de atendimentos

        </a>


        <a href="relatorios.php">

            <span class="material-symbols-rounded">
                bar_chart
            </span>

            Relatórios

        </a>


        <a href="configuracoes.php">

            <span class="material-symbols-rounded">
                settings
            </span>

            Configurações

        </a>


        <a href="logout.php">

            <span class="material-symbols-rounded">
                logout
            </span>

            Sair

        </a>

    </aside>


    <main class="main-content">

        <header class="top-header">

            <div>

                <h1>Chamados</h1>

                <p class="page-subtitle">
                    Gerencie os chamados recebidos pelo sistema.
                </p>

            </div>

        </header>


        <div class="table-container">

            <div class="table-header">

                <h3>Todos os chamados</h3>

                <span class="material-symbols-rounded close-icon">
                    confirmation_number
                </span>

            </div>


            <div class="table-scroll">

                <table>

                    <thead>

                        <tr>

                            <th>ID</th>

                            <th>Solicitante</th>

                            <th>Matrícula</th>

                            <th>Setor</th>

                            <th>Categoria</th>

                            <th>Prioridade</th>

                            <th>Status</th>

                            <th>Data</th>

                            <th>Ações</th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php if (!empty($chamados)): ?>

                            <?php foreach ($chamados as $chamado): ?>

                                <?php

                                $status = $chamado['status'] ?? '';

                                $statusStr = strtolower($status);

                                $statusClass = 'abertos';


                                if (strpos($statusStr, 'andamento') !== false) {

                                    $statusClass = 'andamento';

                                } elseif (strpos($statusStr, 'aguardando') !== false) {

                                    $statusClass = 'aguardando';

                                } elseif (strpos($statusStr, 'resolvido') !== false) {

                                    $statusClass = 'resolvido';

                                } elseif (strpos($statusStr, 'fechado') !== false) {

                                    $statusClass = 'fechado';

                                }


                                $prioridade = $chamado['prioridade'] ?? '';

                                $prioridadeStr = strtolower($prioridade);

                                $prioridadeClass = 'media';


                                if (strpos($prioridadeStr, 'alta') !== false) {

                                    $prioridadeClass = 'alta';

                                } elseif (strpos($prioridadeStr, 'baixa') !== false) {

                                    $prioridadeClass = 'baixa';

                                }

                                ?>


                                <tr>

                                    <td>
                                        #<?php echo htmlspecialchars($chamado['id']); ?>
                                    </td>


                                    <td>

                                        <strong>

                                            <?php

                                            echo htmlspecialchars(

                                                $chamado['solicitante']

                                                ?: $chamado['nome']

                                            );

                                            ?>

                                        </strong>

                                    </td>


                                    <td>

                                        <?php

                                        echo htmlspecialchars(

                                            $chamado['matricula'] ?? ''

                                        );

                                        ?>

                                    </td>


                                    <td>

                                        <?php

                                        echo htmlspecialchars(

                                            $chamado['setor'] ?? ''

                                        );

                                        ?>

                                    </td>


                                    <td>

                                        <?php

                                        echo htmlspecialchars(

                                            $chamado['categoria'] ?? ''

                                        );

                                        ?>

                                    </td>


                                    <td>

                                        <span class="badge prioridade <?php echo $prioridadeClass; ?>">

                                            <?php

                                            echo htmlspecialchars(

                                                $prioridade ?: 'Não informada'

                                            );

                                            ?>

                                        </span>

                                    </td>


                                    <td>

                                        <span class="badge <?php echo $statusClass; ?>">

                                            <?php

                                            echo htmlspecialchars(

                                                $status ?: 'Não informado'

                                            );

                                            ?>

                                        </span>

                                    </td>


                                    <td class="data-chamado">

                                        <?php

                                        if (!empty($chamado['criado_em'])) {

                                            echo date(

                                                'd/m/Y H:i',

                                                strtotime($chamado['criado_em'])

                                            );

                                        } else {

                                            echo 'Não informado';

                                        }

                                        ?>

                                    </td>


                                    <td>

                                        <div class="action-buttons">


                                            <!-- VER DETALHES -->

                                            <a

                                                href="chamado.php?id=<?php echo $chamado['id']; ?>"

                                                class="btn-action-sm btn-details"

                                                title="Ver detalhes"

                                            >

                                                <span class="material-symbols-rounded">

                                                    visibility

                                                </span>

                                            </a>


                                            <!-- ACEITAR -->

                                            <?php if ($status === 'Aberto'): ?>

                                                <form method="POST">

                                                    <input

                                                        type="hidden"

                                                        name="id"

                                                        value="<?php echo $chamado['id']; ?>"

                                                    >

                                                    <input

                                                        type="hidden"

                                                        name="acao"

                                                        value="aceitar"

                                                    >

                                                    <button

                                                        type="submit"

                                                        class="btn-action-sm btn-accept"

                                                        title="Aceitar chamado"

                                                    >

                                                        <span class="material-symbols-rounded">

                                                            check

                                                        </span>

                                                        Aceitar

                                                    </button>

                                                </form>

                                            <?php endif; ?>


                                            <!-- RESOLVER -->

                                            <?php if ($status === 'Em Andamento'): ?>

                                                <form method="POST">

                                                    <input

                                                        type="hidden"

                                                        name="id"

                                                        value="<?php echo $chamado['id']; ?>"

                                                    >

                                                    <input

                                                        type="hidden"

                                                        name="acao"

                                                        value="resolver"

                                                    >

                                                    <button

                                                        type="submit"

                                                        class="btn-action-sm btn-resolve"

                                                        title="Resolver chamado"

                                                    >

                                                        <span class="material-symbols-rounded">

                                                            done_all

                                                        </span>

                                                        Resolver

                                                    </button>

                                                </form>

                                            <?php endif; ?>


                                            <!-- ENCERRAR -->

                                            <?php if ($status === 'Resolvido'): ?>

                                                <form method="POST">

                                                    <input

                                                        type="hidden"

                                                        name="id"

                                                        value="<?php echo $chamado['id']; ?>"

                                                    >

                                                    <input

                                                        type="hidden"

                                                        name="acao"

                                                        value="encerrar"

                                                    >

                                                    <button

                                                        type="submit"

                                                        class="btn-action-sm btn-close"

                                                        title="Encerrar chamado"

                                                    >

                                                        <span class="material-symbols-rounded">

                                                            close

                                                        </span>

                                                        Encerrar

                                                    </button>

                                                </form>

                                            <?php endif; ?>


                                        </div>

                                    </td>

                                </tr>


                            <?php endforeach; ?>


                        <?php else: ?>

                            <tr>

                                <td

                                    colspan="9"

                                    class="empty-state"

                                >

                                    <span class="material-symbols-rounded">

                                        inbox

                                    </span>

                                    <strong>

                                        Nenhum chamado encontrado

                                    </strong>

                                    <span>

                                        Os chamados recebidos aparecerão aqui.

                                    </span>

                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </main>

</body>

</html>

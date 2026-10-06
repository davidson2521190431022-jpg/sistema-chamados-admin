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

    $id = $_POST['id'] ?? null;
    $acao = $_POST['acao'] ?? null;

    if ($id && $acao) {

        if ($acao === 'aceitar') {

            $sql = "UPDATE chamados
                    SET status = 'Em Andamento'
                    WHERE id = ?";

        } elseif ($acao === 'resolver') {

            $sql = "UPDATE chamados
                    SET status = 'Resolvido'
                    WHERE id = ?";

        } elseif ($acao === 'encerrar') {

            $sql = "UPDATE chamados
                    SET status = 'Fechado'
                    WHERE id = ?";
        }

        if (isset($sql)) {

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);

        }
    }
}


/*
|--------------------------------------------------------------------------
| BUSCAR CHAMADOS
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            id,
            nome,
            matricula,
            setor,
            categoria,
            descricao,
            prioridade,
            status,
            cpf,
            solicitante,
            email
        FROM chamados
        ORDER BY id DESC";

$stmt = $pdo->query($sql);

$chamados = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

                            <th>Ações</th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php if (!empty($chamados)): ?>

                            <?php foreach ($chamados as $chamado): ?>

                                <tr>

                                    <td>

                                        #<?php
                                        echo htmlspecialchars(
                                            $chamado['id']
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <strong>

                                            <?php

                                            echo htmlspecialchars(

                                                $chamado['solicitante']
                                                ?: $chamado['nome']
                                                ?: 'Não informado'

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

                                        <span class="badge">

                                            <?php

                                            echo htmlspecialchars(
                                                $chamado['prioridade']
                                                ?? 'Não informada'
                                            );

                                            ?>

                                        </span>

                                    </td>


                                    <td>

                                        <span class="badge">

                                            <?php

                                            echo htmlspecialchars(
                                                $chamado['status']
                                                ?? 'Não informado'
                                            );

                                            ?>

                                        </span>

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

                                            <?php if (
                                                $chamado['status'] === 'Aberto'
                                            ): ?>

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
                                                    >

                                                        Aceitar

                                                    </button>

                                                </form>

                                            <?php endif; ?>


                                            <!-- RESOLVER -->

                                            <?php if (
                                                $chamado['status'] === 'Em Andamento'
                                            ): ?>

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
                                                    >

                                                        Resolver

                                                    </button>

                                                </form>

                                            <?php endif; ?>


                                            <!-- ENCERRAR -->

                                            <?php if (
                                                $chamado['status'] === 'Resolvido'
                                            ): ?>

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
                                                    >

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
                                    colspan="8"
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

<?php
ob_start();

require_once '../app/Config/Conexao.php';

$pdo = \App\Config\Conexao::getConexao();


// ========================================
// AÇÕES DOS CHAMADOS
// ========================================

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['chamado_id'], $_POST['acao'])
) {

    $chamadoId = (int) $_POST['chamado_id'];
    $acao = $_POST['acao'];


    // ACEITAR
    if ($acao === 'aceitar') {

        $stmt = $pdo->prepare("
            UPDATE chamados
            SET status = 'Em Andamento'
            WHERE id = ?
        ");

        $stmt->execute([$chamadoId]);
    }


    // RESOLVER
    elseif ($acao === 'resolver') {

        $stmt = $pdo->prepare("
            UPDATE chamados
            SET status = 'Resolvido'
            WHERE id = ?
        ");

        $stmt->execute([$chamadoId]);
    }


    // ENCERRAR / BAIXAR
    elseif ($acao === 'encerrar') {

        $stmt = $pdo->prepare("
            UPDATE chamados
            SET status = 'Fechado'
            WHERE id = ?
        ");

        $stmt->execute([$chamadoId]);
    }

    /*
     * NÃO usamos header() aqui.
     * Depois da atualização, a página continua
     * normalmente e mostra os dados atualizados.
     */
}


// ========================================
// BUSCAR CHAMADOS
// ========================================

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

    $chamados = [];

    $erroBanco = $e->getMessage();
}

?>


<!DOCTYPE html>

<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Gerenciar Chamados - Sistema de Chamados
    </title>


    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >


    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0"
        rel="stylesheet"
    >


    <link
        rel="stylesheet"
        href="css/painel.css"
    >

</head>


<body>


<!-- ========================================
     MENU LATERAL
     ======================================== -->

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


    <a
        href="chamados.php"
        class="active"
    >

        <span class="material-symbols-rounded">
            confirmation_number
        </span>

        Chamados

    </a>


    <a href="clientes.php">

        <span class="material-symbols-rounded">
            person
        </span>

        Clientes

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


<!-- ========================================
     CONTEÚDO
     ======================================== -->

<main class="main-content">


    <header class="top-header">

        <h1>
            Gerenciamento de Chamados
        </h1>

    </header>


    <div class="table-container">


        <div class="table-header">

            <h3>
                Todos os Chamados
            </h3>

        </div>


        <table>


            <thead>

                <tr>

                    <th>ID</th>

                    <th>Solicitante</th>

                    <th>Matrícula</th>

                    <th>Setor</th>

                    <th>Categoria</th>

                    <th>Status</th>

                    <th>Data do Chamado</th>

                    <th>Ações</th>

                </tr>

            </thead>


            <tbody>


                <?php if (!empty($chamados)): ?>


                    <?php foreach ($chamados as $chamado): ?>


                        <tr>


                            <!-- ID -->

                            <td>

                                #

                                <?php
                                echo htmlspecialchars(
                                    $chamado['id']
                                );
                                ?>

                            </td>


                            <!-- NOME -->

                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $chamado['nome'] ?? 'Não informado'
                                );
                                ?>

                            </td>


                            <!-- MATRÍCULA -->

                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $chamado['matricula'] ?? 'Não informado'
                                );
                                ?>

                            </td>


                            <!-- SETOR -->

                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $chamado['setor'] ?? 'Não informado'
                                );
                                ?>

                            </td>


                            <!-- CATEGORIA -->

                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $chamado['categoria'] ?? 'Não informado'
                                );
                                ?>

                            </td>


                            <!-- STATUS -->

                            <td>

                                <?php

                                $status = $chamado['status'] ?? 'Aberto';

                                $statusStr = strtolower($status);

                                $statusClass = 'abertos';


                                if (
                                    strpos(
                                        $statusStr,
                                        'andamento'
                                    ) !== false
                                ) {

                                    $statusClass = 'andamento';

                                } elseif (
                                    strpos(
                                        $statusStr,
                                        'aguardando'
                                    ) !== false
                                ) {

                                    $statusClass = 'aguardando';

                                } elseif (
                                    strpos(
                                        $statusStr,
                                        'resolvido'
                                    ) !== false
                                ) {

                                    $statusClass = 'resolvido';

                                } elseif (
                                    strpos(
                                        $statusStr,
                                        'fechado'
                                    ) !== false
                                ) {

                                    $statusClass = 'resolvido';
                                }

                                ?>


                                <span
                                    class="badge <?php echo $statusClass; ?>"
                                >

                                    <?php
                                    echo htmlspecialchars($status);
                                    ?>

                                </span>

                            </td>


                            <!-- DATA -->

                            <td>

                                <?php

                                if (
                                    !empty(
                                        $chamado['criado_em']
                                    )
                                ) {

                                    echo date(
                                        'd/m/Y H:i',
                                        strtotime(
                                            $chamado['criado_em']
                                        )
                                    );

                                } else {

                                    echo 'Não informado';

                                }

                                ?>

                            </td>


                            <!-- AÇÕES -->

                            <td>

                                <div class="action-buttons">


                                    <!-- ACEITAR -->

                                    <?php if ($status === 'Aberto'): ?>

                                        <form
                                            method="POST"
                                            style="display:inline;"
                                        >

                                            <input
                                                type="hidden"
                                                name="chamado_id"
                                                value="<?php
                                                echo htmlspecialchars(
                                                    $chamado['id']
                                                );
                                                ?>"
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

                                                <span
                                                    class="material-symbols-rounded"
                                                    style="font-size:16px;"
                                                >
                                                    play_arrow
                                                </span>

                                                Aceitar

                                            </button>

                                        </form>

                                    <?php endif; ?>


                                    <!-- RESOLVER -->

                                    <?php if (
                                        $status !== 'Resolvido' &&
                                        $status !== 'Fechado'
                                    ): ?>

                                        <form
                                            method="POST"
                                            style="display:inline;"
                                        >

                                            <input
                                                type="hidden"
                                                name="chamado_id"
                                                value="<?php
                                                echo htmlspecialchars(
                                                    $chamado['id']
                                                );
                                                ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="acao"
                                                value="resolver"
                                            >

                                            <button
                                                type="submit"
                                                class="btn-action-sm btn-resolve"
                                                title="Marcar como Resolvido"
                                            >

                                                <span
                                                    class="material-symbols-rounded"
                                                    style="font-size:16px;"
                                                >
                                                    check
                                                </span>

                                                Resolver

                                            </button>

                                        </form>

                                    <?php endif; ?>


                                    <!-- BAIXAR -->

                                    <?php if ($status !== 'Fechado'): ?>

                                        <form
                                            method="POST"
                                            style="display:inline;"
                                        >

                                            <input
                                                type="hidden"
                                                name="chamado_id"
                                                value="<?php
                                                echo htmlspecialchars(
                                                    $chamado['id']
                                                );
                                                ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="acao"
                                                value="encerrar"
                                            >

                                            <button
                                                type="submit"
                                                class="btn-action-sm btn-close"
                                                title="Baixar / Encerrar"
                                            >

                                                <span
                                                    class="material-symbols-rounded"
                                                    style="font-size:16px;"
                                                >
                                                    archive
                                                </span>

                                                Baixar

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
                            style="
                                text-align: center;
                                color: #777;
                                padding: 20px;
                            "
                        >

                            Nenhum chamado encontrado.

                        </td>

                    </tr>


                <?php endif; ?>


            </tbody>

        </table>


    </div>


</main>


</body>

</html>

<?php
ob_end_flush();
?>

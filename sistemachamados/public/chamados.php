<?php

use App\Config\Conexao;

require_once '../app/Config/Conexao.php';

$pdo = Conexao::getConexao();

$idChamado = isset($_GET['id']) ? intval($_GET['id']) : 0;

$chamado = null;

if ($idChamado > 0) {

    $stmt = $pdo->prepare("
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
        WHERE id = ?
    ");

    $stmt->execute([$idChamado]);

    $chamado = $stmt->fetch(PDO::FETCH_ASSOC);
}

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Chamado #<?php echo $idChamado; ?> - Suporte Técnico
    </title>

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

        <a href="chamados.php">

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

                <a
                    href="chamados.php"
                    class="back-link"
                >

                    <span class="material-symbols-rounded">
                        arrow_back
                    </span>

                    Voltar aos chamados

                </a>

                <h1>
                    Detalhes do Chamado
                </h1>

            </div>

        </header>


        <?php if ($chamado): ?>

            <div class="detail-card">

                <div class="detail-header">

                    <div>

                        <span class="detail-id">

                            Chamado #<?php echo htmlspecialchars($chamado['id']); ?>

                        </span>

                        <h2>

                            <?php

                            echo htmlspecialchars(
                                $chamado['categoria'] ?? 'Chamado'
                            );

                            ?>

                        </h2>

                    </div>


                    <span class="badge">

                        <?php
                        echo htmlspecialchars($chamado['status']);
                        ?>

                    </span>

                </div>


                <div class="detail-grid">

                    <div class="detail-item">

                        <span>Solicitante</span>

                        <strong>

                            <?php

                            echo htmlspecialchars(
                                $chamado['solicitante']
                                ?: $chamado['nome']
                                ?: 'Não informado'
                            );

                            ?>

                        </strong>

                    </div>


                    <div class="detail-item">

                        <span>Matrícula</span>

                        <strong>

                            <?php
                            echo htmlspecialchars(
                                $chamado['matricula'] ?? 'Não informado'
                            );
                            ?>

                        </strong>

                    </div>


                    <div class="detail-item">

                        <span>Setor</span>

                        <strong>

                            <?php
                            echo htmlspecialchars(
                                $chamado['setor'] ?? 'Não informado'
                            );
                            ?>

                        </strong>

                    </div>


                    <div class="detail-item">

                        <span>Prioridade</span>

                        <strong>

                            <?php
                            echo htmlspecialchars(
                                $chamado['prioridade'] ?? 'Não informada'
                            );
                            ?>

                        </strong>

                    </div>


                    <div class="detail-item">

                        <span>Data do chamado</span>

                        <strong>

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

                        </strong>

                    </div>


                    <div class="detail-item">

                        <span>E-mail</span>

                        <strong>

                            <?php
                            echo htmlspecialchars(
                                $chamado['email'] ?? 'Não informado'
                            );
                            ?>

                        </strong>

                    </div>

                </div>


                <div class="description-box">

                    <h3>

                        <span class="material-symbols-rounded">
                            description
                        </span>

                        Descrição do problema

                    </h3>


                    <p>

                        <?php

                        echo nl2br(
                            htmlspecialchars(
                                $chamado['descricao']
                                ?? 'Nenhuma descrição informada.'
                            )
                        );

                        ?>

                    </p>

                </div>


                <div class="detail-actions">

                    <a
                        href="chamados.php"
                        class="btn-back"
                    >

                        Voltar

                    </a>

                </div>

            </div>

        <?php else: ?>

            <div class="not-found">

                <span class="material-symbols-rounded">

                    error

                </span>

                <h2>

                    Chamado não encontrado

                </h2>

                <p>

                    O chamado #<?php echo $idChamado; ?>
                    não existe ou foi removido.

                </p>

                <a
                    href="chamados.php"
                    class="btn-back"
                >

                    Voltar aos chamados

                </a>

            </div>

        <?php endif; ?>

    </main>

</body>

</html>

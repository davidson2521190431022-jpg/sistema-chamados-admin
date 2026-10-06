<?php

require_once '../app/Config/Conexao.php';

$pdo = \App\Config\Conexao::getConexao();

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
        }

        elseif ($acao === 'resolver') {
            $sql = "UPDATE chamados 
                    SET status = 'Resolvido' 
                    WHERE id = ?";
        }

        elseif ($acao === 'encerrar') {
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
            criado_em,
            solicitante,
            email
        FROM chamados
        ORDER BY criado_em DESC";

$stmt = $pdo->query($sql);
$chamados = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Chamados</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 30px;
        }

        h1 {
            margin-bottom: 25px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        th,
        td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #222;
            color: white;
        }

        tr:nth-child(even) {
            background: #f9f9f9;
        }

        .status {
            font-weight: bold;
        }

        .acoes {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        button {
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            color: white;
        }

        .aceitar {
            background: #007bff;
        }

        .resolver {
            background: #28a745;
        }

        .encerrar {
            background: #dc3545;
        }

        button:hover {
            opacity: 0.85;
        }

    </style>

</head>

<body>

    <h1>Chamados</h1>

    <?php if (empty($chamados)): ?>

        <p>Nenhum chamado encontrado.</p>

    <?php else: ?>

        <table>

            <thead>

                <tr>

                    <th>ID</th>

                    <th>Solicitante</th>

                    <th>Matrícula</th>

                    <th>Setor</th>

                    <th>Categoria</th>

                    <th>Descrição</th>

                    <th>Prioridade</th>

                    <th>Status</th>

                    <th>Data do Chamado</th>

                    <th>Ações</th>

                </tr>

            </thead>

            <tbody>

                <?php foreach ($chamados as $chamado): ?>

                    <tr>

                        <td>
                            <?= htmlspecialchars($chamado['id']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $chamado['solicitante'] ?: $chamado['nome']
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($chamado['matricula']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($chamado['setor']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($chamado['categoria']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($chamado['descricao']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($chamado['prioridade']) ?>
                        </td>

                        <td class="status">
                            <?= htmlspecialchars($chamado['status']) ?>
                        </td>

                        <td>

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

                            <div class="acoes">

                                <?php if ($chamado['status'] === 'Aberto'): ?>

                                    <form method="POST">

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= $chamado['id'] ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="acao"
                                            value="aceitar"
                                        >

                                        <button
                                            type="submit"
                                            class="aceitar"
                                        >
                                            Aceitar
                                        </button>

                                    </form>

                                <?php endif; ?>


                                <?php if (
                                    $chamado['status'] === 'Em Andamento'
                                ): ?>

                                    <form method="POST">

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= $chamado['id'] ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="acao"
                                            value="resolver"
                                        >

                                        <button
                                            type="submit"
                                            class="resolver"
                                        >
                                            Resolver
                                        </button>

                                    </form>

                                <?php endif; ?>


                                <?php if (
                                    $chamado['status'] === 'Resolvido'
                                ): ?>

                                    <form method="POST">

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= $chamado['id'] ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="acao"
                                            value="encerrar"
                                        >

                                        <button
                                            type="submit"
                                            class="encerrar"
                                        >
                                            Encerrar
                                        </button>

                                    </form>

                                <?php endif; ?>

                            </div>

                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    <?php endif; ?>

</body>

</html>

<?php

use App\Config\Conexao;
use App\Models\Cliente;
use App\Models\Chamado;

require_once '../app/Config/Conexao.php';
require_once '../app/Models/Cliente.php';
require_once '../app/Models/Chamado.php';

header('Content-Type: application/json; charset=utf-8');

$inputData = file_get_contents('php://input');
$data = json_decode($inputData, true);

if (empty($data)) {
    $data = $_POST;
}

/*
 * Dados recebidos pelo WhatsApp
 */
$nome = trim($data['nome'] ?? '');
$telefone = trim($data['telefone'] ?? '');
$email = trim($data['email'] ?? '');
$cpf = trim($data['cpf'] ?? '');
$assunto = trim($data['assunto'] ?? '');
$descricao = trim($data['descricao'] ?? '');

/*
 * Verifica os dados mínimos
 */
if (empty($nome) || empty($telefone) || empty($assunto) || empty($descricao)) {
    http_response_code(400);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Dados incompletos. Envie nome, telefone, assunto e descricao.'
    ]);

    exit;
}

try {

    /*
     * 1. Procura o cliente pelo telefone
     */
    $cliente = Cliente::porTelefone($telefone);

    /*
     * 2. Se o cliente ainda não existir,
     *    cadastra um novo cliente
     */
    if (!$cliente) {

        /*
         * CPF e e-mail são obrigatórios no banco.
         * Caso a integração ainda não envie esses dados,
         * o cadastro não poderá ser criado corretamente.
         */
        if (empty($cpf) || empty($email)) {

            http_response_code(400);

            echo json_encode([
                'sucesso' => false,
                'mensagem' => 'Cliente novo precisa informar CPF e e-mail para o cadastro.'
            ]);

            exit;
        }

        Cliente::criar([
            'nome' => $nome,
            'cpf' => $cpf,
            'email' => $email,
            'telefone' => $telefone
        ]);

        /*
         * Busca novamente para obter o id_cliente
         */
        $cliente = Cliente::porTelefone($telefone);
    }

    /*
     * 3. Cria o chamado relacionado ao cliente
     */
    $criado = Chamado::criar([
        'id_cliente' => $cliente['id_cliente'],
        'assunto' => $assunto,
        'descricao' => $descricao,
        'status' => 'aberto',
        'prioridade' => 'media'
    ]);

    if (!$criado) {
        throw new Exception('Não foi possível criar o chamado.');
    }

    /*
     * 4. Retorna uma resposta de sucesso
     */
    http_response_code(201);

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Cliente e chamado processados com sucesso.',
        'id_cliente' => $cliente['id_cliente'],
        'id_chamado' => 'criado'
    ]);

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => $e->getMessage()
    ]);
}
<?php

use App\Config\Conexao;

require_once '../app/Config/Conexao.php';


header('Content-Type: application/json; charset=utf-8');


$inputData = file_get_contents('php://input');
$data = json_decode($inputData, true);


if (empty($data)) {
    $data = $_POST;
}


$solicitante = trim($data['solicitante'] ?? '');
$email = trim($data['email'] ?? '');
$assunto = trim($data['assunto'] ?? '');
$descricao = trim($data['descricao'] ?? '');

if (empty($solicitante) || empty($email) || empty($assunto) || empty($descricao)) {
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Dados incompletos. Certifique-se de enviar: solicitante, email, assunto e descricao.'
    ]);
    exit;
}

try {
    $pdo = Conexao::getConexao();
    
    $stmt = $pdo->prepare("
        INSERT INTO chamados (solicitante, email, assunto, descricao, status, data_criacao) 
        VALUES (?, ?, ?, ?, 'Aberto', NOW())
    ");
    
    $stmt->execute([$solicitante, $email, $assunto, $descricao]);
    $novoId = $pdo->lastInsertId();

    http_response_code(201);
    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Chamado criado com sucesso via webhook.',
        'id_chamado' => $novoId
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'sucesso' => false, 
        'mensagem' => 'Erro interno ao processar o webhook no banco de dados, por favor tente novamente.'
    ]);
} 
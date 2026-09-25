<?php
namespace App\Controllers;

require_once __DIR__ . '/../Helpers/funcoes.php';
require_once __DIR__ . '/../Helpers/Auth.php';
require_once __DIR__ . '/../Services/ClienteService.php';

use App\Services\ClienteService;
use App\Helpers\Auth;

class ClienteController {
    public function listar() {
        Auth::proteger();
        $clientes = ClienteService::listarTodos() ?? [];
        require_once __DIR__ . '/../Views/clientes/listar.php';
    }

    public function criar() {
        Auth::proteger();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dados = [
                'nome' => $_POST['nome'] ?? '',
                'email' => $_POST['email'] ?? '',
                'telefone' => $_POST['telefone'] ?? ''
            ];

            $resultado = ClienteService::criarCliente($dados);

            if ($resultado['sucesso']) {
                redirecionar('clientes?sucesso=1');
            } else {
                $erro = $resultado['mensagem'];
                require_once __DIR__ . '/../Views/clientes/criar.php';
            }
        } else {
            require_once __DIR__ . '/../Views/clientes/criar.php';
        }
    }
}
<?php
namespace App\Controllers;

require_once __DIR__ . '/../Helpers/funcoes.php';
require_once __DIR__ . '/../Helpers/Auth.php';
require_once __DIR__ . '/../Services/ChamadoService.php';

use App\Services\ChamadoService;
use App\Helpers\Auth;

class ChamadoController {
    public function listar() {
        Auth::proteger();
        $chamados = ChamadoService::listarTodos() ?? [];
        
        
        require_once __DIR__ . '/../../public/chamados.php';
    }

    public function criar() {
        Auth::proteger();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dados = [
                'solicitante' => $_POST['solicitante'] ?? '',
                'email' => $_POST['email'] ?? '',
                'assunto' => $_POST['assunto'] ?? '',
                'descricao' => $_POST['descricao'] ?? ''
            ];

            $resultado = ChamadoService::criarChamado($dados);

            if ($resultado['sucesso']) {
                redirecionar('chamados?sucesso=1');
            } else {
                $erro = $resultado['mensagem'];
                
                require_once __DIR__ . '/../../public/novo_chamado.php';
            }
        } else {
            
            require_once __DIR__ . '/../../public/novo_chamado.php';
        }
    }
}
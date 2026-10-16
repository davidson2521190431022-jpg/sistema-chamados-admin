<?php
namespace App\Controllers;

require_once __DIR__ . '/../Helpers/funcoes.php';
require_once __DIR__ . '/../Helpers/Auth.php';
require_once __DIR__ . '/../Services/ConhecimentoService.php';

use App\Services\ConhecimentoService;
use App\Helpers\Auth;

class ConhecimentoController {
    public function listar() {
        Auth::proteger();
        $conhecimentos = ConhecimentoService::listarTodos() ?? [];
        require_once __DIR__ . '/../Views/conhecimento/listar.php';
    }

    public function criar() {
        Auth::proteger();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dados = [
                'titulo' => $_POST['titulo'] ?? '',
                'conteudo' => $_POST['conteudo'] ?? '',
                'categoria' => $_POST['categoria'] ?? ''
            ];

            $resultado = ConhecimentoService::criarConhecimento($dados);

            if ($resultado['sucesso']) {
                redirecionar('conhecimento?sucesso=1');
            } else {
                $erro = $resultado['mensagem'];
                require_once __DIR__ . '/../Views/conhecimento/criar.php';
            }
        } else {
            require_once __DIR__ . '/../Views/conhecimento/criar.php';
        }
    }
}
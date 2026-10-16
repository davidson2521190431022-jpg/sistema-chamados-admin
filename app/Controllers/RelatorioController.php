<?php
namespace App\Controllers;

require_once __DIR__ . '/../Helpers/funcoes.php';
require_once __DIR__ . '/../Helpers/Auth.php';
require_once __DIR__ . '/../Services/RelatorioService.php';

use App\Services\RelatorioService;
use App\Helpers\Auth;

class RelatorioController {
    public function index() {
        Auth::proteger();
        
        require_once __DIR__ . '/../Views/relatorios/index.php';
    }

    public function gerar() {
        Auth::proteger();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $tipo = $_POST['tipo'] ?? '';
            $dataInicio = $_POST['data_inicio'] ?? '';
            $dataFim = $_POST['data_fim'] ?? '';

            $dadosRelatorio = RelatorioService::gerarRelatorio($tipo, $dataInicio, $dataFim);
            
            require_once __DIR__ . '/../Views/relatorios/resultado.php';
        } else {
            redirecionar('relatorios');
        }
    }
}
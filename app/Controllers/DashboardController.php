<?php
namespace App\Controllers;

require_once __DIR__ . '/../Helpers/funcoes.php';
require_once __DIR__ . '/../Helpers/Auth.php';

use App\Helpers\Auth;

class DashboardController {
    public function index() {
        Auth::proteger();
        
        // Dados iniciais ou chamadas de services para o painel principal
        $estatisticas = [
            'total_chamados' => 0,
            'total_clientes' => 0,
            'total_conhecimentos' => 0
        ];

        require_once __DIR__ . '/../Views/dashboard/index.php';
    }
}
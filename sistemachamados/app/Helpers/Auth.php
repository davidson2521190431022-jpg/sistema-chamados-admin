<?php
namespace App\Helpers;

use App\Helpers\Session;

class Auth {
    
    // Método chamado no topo do chamados.php
    public static function proteger() {
        Session::iniciar();
        
        // Exemplo: verifica se o usuário está logado
        // Adapte a chave 'usuario_id' para o que você usa no seu login
        if (!Session::obter('usuario_id')) {
            header('Location: login.php');
            exit;
        }
    }
}

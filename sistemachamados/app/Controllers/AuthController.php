<?php
namespace App\Controllers;

require_once __DIR__ . '/../Helpers/funcoes.php';
require_once __DIR__ . '/../Helpers/Session.php';
require_once __DIR__ . '/../Helpers/Auth.php';

use App\Helpers\Session;
use App\Helpers\Auth;

class AuthController {
    public function exibirLogin() {
        if (Auth::estaLogado()) {
            redirecionar('dashboard');
        }
        require_once __DIR__ . '/../Views/auth/login.php';
    }

    public function autenticar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirecionar('login');
        }

        $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $senha = $_POST['senha'] ?? '';

        if (empty($email) || empty($senha)) {
            $erro = 'Preencha todos os campos.';
            require_once __DIR__ . '/../Views/auth/login.php';
            return;
        }

        if ($email === 'admin@admin.com' && $senha === '123456') {
            Session::definir('usuario_id', 1);
            Session::definir('usuario_nome', 'Administrador');
            Session::definir('usuario_perfil', 'admin');
            redirecionar('dashboard');
        } else {
            $erro = 'E-mail ou senha inválidos.';
            require_once __DIR__ . '/../Views/auth/login.php';
        }
    }

    public function logout() {
        Session::destruir();
        redirecionar('login');
    }
}
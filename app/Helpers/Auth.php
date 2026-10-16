<?php
namespace App\Helpers;

class Auth
{
    public static function iniciar()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function estaLogado()
    {
        self::iniciar();
        return !empty($_SESSION['usuario_id']);
    }

    public static function proteger()
    {
        self::iniciar();

        if (empty($_SESSION['usuario_id'])) {
            header('Location: login.php');
            exit;
        }
    }

    public static function exigirAdmin()
    {
        self::proteger();

        if (($_SESSION['usuario_perfil'] ?? '') !== 'admin') {
            http_response_code(403);
            exit('Acesso negado. Esta área é exclusiva para administradores.');
        }
    }
}
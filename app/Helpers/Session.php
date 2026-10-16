<?php
namespace App\Helpers;

class Session {
    public static function iniciar() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function definir($chave, $valor) {
        self::iniciar();
        $_SESSION[$chave] = $valor;
    }

    public static function obter($chave, $padrao = null) {
        self::iniciar();
        return $_SESSION[$chave] ?? $padrao;
    }

    public static function deletar($chave) {
        self::iniciar();
        if (isset($_SESSION[$chave])) {
            unset($_SESSION[$chave]);
        }
    }

    public static function destruir() {
        self::iniciar();
        session_unset();
        session_destroy();
    }
}
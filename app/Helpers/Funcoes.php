<?php

if (!function_exists('base_url')) {
    function base_url($caminho = '') {
        $protocolo = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        $pastaBase = rtrim(str_replace('\\', '/', $scriptName), '/');
        $pastaBase = preg_replace('/\/public$/', '', $pastaBase);

        $baseUrl = "{$protocolo}://{$host}{$pastaBase}/public";
        
        return rtrim($baseUrl, '/') . '/' . ltrim($caminho, '/');
    }
}

if (!function_exists('redirecionar')) {
    function redirecionar($caminho) {
        $urlDestino = base_url($caminho);
        
        if (!headers_sent()) {
            header("Location: " . $urlDestino);
            exit;
        }
        
        echo "<script>window.location.href = '" . $urlDestino . "';</script>";
        echo "<noscript><meta http-equiv='refresh' content='0;url=" . $urlDestino . "'></noscript>";
        exit;
    }
}

if (!function_exists('verificar_autenticacao')) {
    function verificar_autenticacao() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario_id'])) {
            redirecionar('login');
        }
    }
}

if (!function_exists('sanitizar')) {
    function sanitizar($dado) {
        return htmlspecialchars(trim($dado), ENT_QUOTES, 'UTF-8');
    }
}
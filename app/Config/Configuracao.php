<?php

define('BASE_URL', 'http://localhost/sistemachamados/public/');
define('NOME_SISTEMA', 'Suporte Técnico - TCC');
date_default_timezone_set('America/Sao_Paulo');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
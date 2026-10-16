<?php

use App\Config\Conexao;
use App\Helpers\Auth;
use App\Models\Notificacao;

require_once __DIR__ . '/../app/Config/Conexao.php';
require_once __DIR__ . '/../app/Helpers/Auth.php';
require_once __DIR__ . '/../app/Models/Notificacao.php';

Auth::proteger();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: painel.php');
    exit;
}

$usuarioId = (int) $_SESSION['usuario_id'];
$idNotificacao = filter_input(
    INPUT_POST,
    'id_notificacao',
    FILTER_VALIDATE_INT
);

if (!$idNotificacao || $idNotificacao < 1) {
    header('Location: painel.php');
    exit;
}

try {
    Notificacao::marcarComoLida($idNotificacao, $usuarioId);
} catch (Throwable $e) {
    error_log('Erro ao marcar notificação como lida: ' . $e->getMessage());
}

header('Location: painel.php');
exit;
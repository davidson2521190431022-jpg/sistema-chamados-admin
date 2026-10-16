<?php
namespace App\Services;

use App\Models\Notificacao;

class NotificacaoService
{
    public static function enviar($usuarioId, $mensagem)
    {
        if (empty($usuarioId) || empty(trim($mensagem))) {
            return false;
        }

        return Notificacao::criar([
            'id_usuario' => $usuarioId,
            'mensagem' => trim($mensagem)
        ]);
    }
}

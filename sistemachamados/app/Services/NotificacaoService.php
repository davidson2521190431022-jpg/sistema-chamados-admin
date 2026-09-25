<?php
namespace App\Services;

use App\Models\Notificacao;

class NotificacaoService {
    public static function enviar($usuarioId, $mensagem) {
        if (empty($usuarioId) || empty($mensagem)) {
            return false;
        }

        return Notificacao::criar([
            'usuario_id' => $usuarioId,
            'mensagem' => htmlspecialchars(trim($mensagem))
        ]);
    }
}
<?php
namespace App\Services;

class WhatsAppService {
    public static function enviarMensagem($telefone, $mensagem) {
        $telefoneLimpo = preg_replace('/\D/', '', $telefone);
        
        if (empty($telefoneLimpo) || empty($mensagem)) {
            return [
                'sucesso' => false,
                'mensagem' => 'Telefone ou mensagem inválidos.'
            ];
        }

        $mensagemCodificada = urlencode($mensagem);
        $url = "https://api.whatsapp.com/send?phone={$telefoneLimpo}&text={$mensagemCodificada}";

        return [
            'sucesso' => true,
            'url' => $url,
            'mensagem' => 'Link do WhatsApp gerado com sucesso.'
        ];
    }
}
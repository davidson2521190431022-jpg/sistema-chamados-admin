
<?php
namespace App\Services;

class WhatsAppService
{
    public static function notificarStatus(
        int $id,
        string $status,
        string $responsavel = ''
    ): array
    {
        $url = getenv('SCT_API_NOTIFY_URL');
        $segredo = getenv('SCT_INTERNAL_TOKEN');

        if (
            !$url ||
            !$segredo ||
            strlen($segredo) < 32 ||
            !function_exists('curl_init') ||
            !filter_var($url, FILTER_VALIDATE_URL) ||
            !str_starts_with($url, 'https://')
        ) {
            return [
                'sucesso' => false,
                'mensagem' => 'Integração não configurada no Admin.'
            ];
        }

        $json = json_encode([
            'id_chamado' => $id,
            'status' => $status,
            'responsavel' => $responsavel
        ], JSON_UNESCAPED_UNICODE);

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $segredo
            ],
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 20
        ]);

        $body = curl_exec($ch);
        $erro = curl_error($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        $data = is_string($body)
            ? json_decode($body, true)
            : null;

        if (
            $erro ||
            $http < 200 ||
            $http >= 300 ||
            empty($data['sucesso'])
        ) {
            error_log(
                'SCT notificação: HTTP ' .
                $http . ' curl ' . $erro
            );

            return [
                'sucesso' => false,
                'mensagem' => 'Status salvo, mas a notificação não foi aceita pela API.'
            ];
        }

        return [
            'sucesso' => true,
            'mensagem' => 'Status salvo e mensagem aceita pela Meta (entrega ainda não confirmada).'
        ];
    }
}

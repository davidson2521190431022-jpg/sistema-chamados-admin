<?php
namespace App\Services;
use App\Models\Chamado;
class ChamadoService {
    public static function criarChamado($dados) {
        if (empty($dados['solicitante']) || empty($dados['email']) || empty($dados['assunto']) || empty($dados['descricao'])) {
            return [
                'sucesso' => false,
                'mensagem' => 'Todos os campos obrigatórios devem ser preenchidos.'
            ];
        }
        $criado = Chamado::criar([
            'solicitante' => htmlspecialchars(trim($dados['solicitante'])),
            'email' => filter_var($dados['email'], FILTER_SANITIZE_EMAIL),
            'assunto' => htmlspecialchars(trim($dados['assunto'])),
            'descricao' => htmlspecialchars(trim($dados['descricao']))
        ]);
        if ($criado) {
            return [
                'sucesso' => true,
                'mensagem' => 'Chamado criado com sucesso!'
            ];
        }
        return [
            'sucesso' => false,
            'mensagem' => 'Erro ao salvar o chamado no banco de dados.'
        ];
    }
}
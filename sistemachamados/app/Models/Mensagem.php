<?php
namespace App\Models;

use App\Config\Conexao;
use PDO;

class Mensagem {
    public static function porChamado($chamadoId) {
        $pdo = Conexao::getConexao();
        $stmt = $pdo->prepare("SELECT * FROM mensagens WHERE chamado_id = ? ORDER BY data_envio ASC");
        $stmt->execute([$chamadoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function criar($dados) {
        $pdo = Conexao::getConexao();
        $stmt = $pdo->prepare("
            INSERT INTO mensagens (chamado_id, remetente, mensagem, data_envio) 
            VALUES (?, ?, ?, NOW())
        ");
        return $stmt->execute([
            $dados['chamado_id'],
            $dados['remetente'],
            $dados['mensagem']
        ]);
    }
}
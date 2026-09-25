<?php
namespace App\Models;

use App\Config\Conexao;
use PDO;

class Notificacao {
    public static function naoLidas($usuarioId) {
        $pdo = Conexao::getConexao();
        $stmt = $pdo->prepare("SELECT * FROM notificacoes WHERE usuario_id = ? AND lida = 0 ORDER BY data_criacao DESC");
        $stmt->execute([$usuarioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function marcarComoLida($id) {
        $pdo = Conexao::getConexao();
        $stmt = $pdo->prepare("UPDATE notificacoes SET lida = 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function criar($dados) {
        $pdo = Conexao::getConexao();
        $stmt = $pdo->prepare("
            INSERT INTO notificacoes (usuario_id, mensagem, lida, data_criacao) 
            VALUES (?, ?, 0, NOW())
        ");
        return $stmt->execute([
            $dados['usuario_id'],
            $dados['mensagem']
        ]);
    }
}
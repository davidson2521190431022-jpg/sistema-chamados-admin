<?php
namespace App\Models;

use App\Config\Conexao;
use PDO;

class Notificacao {
    public static function naoLidas($usuarioId) {
        $pdo = Conexao::getConexao();

        $stmt = $pdo->prepare("
            SELECT *
            FROM notificacoes
            WHERE id_usuario = ? AND lida = 0
            ORDER BY data_criacao DESC
        ");

        $stmt->execute([$usuarioId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function marcarComoLida($id, $usuarioId) {
        $pdo = Conexao::getConexao();

        $stmt = $pdo->prepare("
            UPDATE notificacoes
            SET lida = 1
            WHERE id_notificacao = ? AND id_usuario = ?
        ");

        return $stmt->execute([$id, $usuarioId]);
    }

    public static function criar($dados) {
        $pdo = Conexao::getConexao();

        $stmt = $pdo->prepare("
            INSERT INTO notificacoes (id_usuario, mensagem, lida)
            VALUES (?, ?, 0)
        ");

        return $stmt->execute([
            $dados['id_usuario'],
            $dados['mensagem']
        ]);
    }
}

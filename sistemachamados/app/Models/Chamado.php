<?php
namespace App\Models;

use App\Config\Conexao;
use PDO;

class Chamado {
    public static function todos() {
        $pdo = Conexao::getConexao();
        $stmt = $pdo->query("SELECT * FROM chamados ORDER BY data_criacao DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function porId($id) {
        $pdo = Conexao::getConexao();
        $stmt = $pdo->prepare("SELECT * FROM chamados WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function criar($dados) {
        $pdo = Conexao::getConexao();
        $stmt = $pdo->prepare("
            INSERT INTO chamados (solicitante, email, assunto, descricao, status, data_criacao) 
            VALUES (?, ?, ?, ?, 'Aberto', NOW())
        ");
        return $stmt->execute([
            $dados['solicitante'],
            $dados['email'],
            $dados['assunto'],
            $dados['descricao']
        ]);
    }
}
<?php
namespace App\Models;

use App\Config\Conexao;
use PDO;

class Cliente {
    public static function todos() {
        $pdo = Conexao::getConexao();
        $stmt = $pdo->query("SELECT * FROM clientes ORDER BY nome ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function porId($id) {
        $pdo = Conexao::getConexao();
        $stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function criar($dados) {
        $pdo = Conexao::getConexao();
        $stmt = $pdo->prepare("
            INSERT INTO clientes (nome, email, telefone, data_cadastro) 
            VALUES (?, ?, ?, NOW())
        ");
        return $stmt->execute([
            $dados['nome'],
            $dados['email'],
            $dados['telefone']
        ]);
    }
}
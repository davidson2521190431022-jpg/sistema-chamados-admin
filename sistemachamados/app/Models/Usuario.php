<?php
namespace App\Models;

use App\Config\Conexao;
use PDO;

class Usuario {
    public static function porEmail($email) {
        $pdo = Conexao::getConexao();
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function porId($id) {
        $pdo = Conexao::getConexao();
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function criar($dados) {
        $pdo = Conexao::getConexao();
        $senhaHash = password_hash($dados['senha'], PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (nome, email, senha, perfil, data_cadastro) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        return $stmt->execute([
            $dados['nome'],
            $dados['email'],
            $senhaHash,
            $dados['perfil'] ?? 'atendente'
        ]);
    }
}
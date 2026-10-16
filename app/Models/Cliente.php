<?php

namespace App\Models;

use App\Config\Conexao;
use PDO;

class Cliente
{
    public static function todos()
    {
        $pdo = Conexao::getConexao();

        $stmt = $pdo->query("
            SELECT *
            FROM clientes
            ORDER BY nome ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function porId($id)
    {
        $pdo = Conexao::getConexao();

        $stmt = $pdo->prepare("
            SELECT *
            FROM clientes
            WHERE id_cliente = ?
        ");

        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function porTelefone($telefone)
    {
        $pdo = Conexao::getConexao();

        $stmt = $pdo->prepare("
            SELECT *
            FROM clientes
            WHERE telefone = ?
        ");

        $stmt->execute([$telefone]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function porEmail($email)
    {
        $pdo = Conexao::getConexao();

        $stmt = $pdo->prepare("
            SELECT *
            FROM clientes
            WHERE email = ?
        ");

        $stmt->execute([$email]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function criar($dados)
    {
        $pdo = Conexao::getConexao();

        $stmt = $pdo->prepare("
            INSERT INTO clientes
                (nome, cpf, email, telefone, data_cadastro)
            VALUES
                (?, ?, ?, ?, NOW())
        ");

        return $stmt->execute([
            $dados['nome'],
            $dados['cpf'],
            $dados['email'],
            $dados['telefone']
        ]);
    }
}
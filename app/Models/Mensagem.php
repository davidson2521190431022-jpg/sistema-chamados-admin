<?php

namespace App\Models;

use App\Config\Conexao;
use PDO;

class Mensagem
{
    public static function porChamado($chamadoId)
    {
        $pdo = Conexao::getConexao();

        $stmt = $pdo->prepare("
            SELECT *
            FROM mensagens
            WHERE id_chamado = ?
            ORDER BY data_envio ASC
        ");

        $stmt->execute([$chamadoId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function criar($dados)
    {
        $pdo = Conexao::getConexao();

        $stmt = $pdo->prepare("
            INSERT INTO mensagens
                (
                    id_chamado,
                    id_usuario,
                    id_cliente,
                    mensagem
                )
            VALUES
                (?, ?, ?, ?)
        ");

        return $stmt->execute([
            $dados['id_chamado'],
            $dados['id_usuario'] ?? null,
            $dados['id_cliente'] ?? null,
            $dados['mensagem']
        ]);
    }
}
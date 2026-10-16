<?php

namespace App\Models;

use App\Config\Conexao;
use PDO;

class Chamado
{
    public static function todos()
    {
        $pdo = Conexao::getConexao();

        $stmt = $pdo->query("
            SELECT *
            FROM chamados
            ORDER BY data_criacao DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function porId($id)
    {
        $pdo = Conexao::getConexao();

        $stmt = $pdo->prepare("
            SELECT *
            FROM chamados
            WHERE id_chamado = ?
        ");

        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
public static function criar($dados)
{
    $pdo = Conexao::getConexao();

    $stmt = $pdo->prepare("
        INSERT INTO chamados
            (
                id_cliente,
                id_usuario,
                id_categoria,
                assunto,
                descricao,
                status,
                prioridade
            )
        VALUES
            (?, ?, ?, ?, ?, ?, ?)
    ");

    $sucesso = $stmt->execute([
        $dados['id_cliente'],
        $dados['id_usuario'] ?? null,
        $dados['id_categoria'] ?? null,
        $dados['assunto'],
        $dados['descricao'],
        $dados['status'] ?? 'aberto',
        $dados['prioridade'] ?? 'media'
    ]);

    if (!$sucesso) {
        return false;
    }

    try {
        $administradores = $pdo->query("
            SELECT id_usuario
            FROM usuarios
            WHERE nivel_acesso = 'admin'
        ")->fetchAll(PDO::FETCH_COLUMN);

        $mensagem = 'Novo chamado aberto: '
            . $dados['assunto'] . '.';

        $notificacao = $pdo->prepare("
            INSERT INTO notificacoes
                (id_usuario, mensagem, lida)
            VALUES (?, ?, 0)
        ");

        foreach ($administradores as $idAdministrador) {
            $notificacao->execute([
                $idAdministrador,
                $mensagem
            ]);
        }
    } catch (\Throwable $e) {
        error_log(
            'Erro ao criar notificação do chamado: '
            . $e->getMessage()
        );
    }

    return true;
}
}
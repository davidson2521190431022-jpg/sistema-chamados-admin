
CREATE DATABASE IF NOT EXISTS sistema_chamados
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE sistema_chamados;

CREATE TABLE usuarios (
    id_usuario INT NOT NULL AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    nivel_acesso ENUM('admin', 'tecnico') NOT NULL DEFAULT 'tecnico',
    data_cadastro TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_usuario),
    UNIQUE KEY email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE clientes (
    id_cliente INT NOT NULL AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    cpf VARCHAR(11) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefone VARCHAR(20) DEFAULT NULL,
    data_cadastro TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_cliente),
    UNIQUE KEY cpf (cpf),
    UNIQUE KEY email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categorias (
    id_categoria INT NOT NULL AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    data_cadastro TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_categoria),
    UNIQUE KEY nome (nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE chamados (
    id_chamado INT NOT NULL AUTO_INCREMENT,
    id_cliente INT DEFAULT NULL,
    id_usuario INT DEFAULT NULL,
    id_categoria INT DEFAULT NULL,
    assunto VARCHAR(200) NOT NULL,
    descricao TEXT NOT NULL,
    status ENUM(
        'aberto',
        'em_andamento',
        'aguardando_cliente',
        'resolvido',
        'fechado'
    ) NOT NULL DEFAULT 'aberto',
    prioridade ENUM('baixa', 'media', 'alta', 'urgente')
        NOT NULL DEFAULT 'media',
    data_criacao TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_chamado),
    KEY fk_chamado_cliente (id_cliente),
    KEY fk_chamado_usuario (id_usuario),
    KEY fk_chamado_categoria (id_categoria),
    CONSTRAINT fk_chamado_categoria
        FOREIGN KEY (id_categoria)
        REFERENCES categorias (id_categoria)
        ON DELETE SET NULL,
    CONSTRAINT fk_chamado_cliente
        FOREIGN KEY (id_cliente)
        REFERENCES clientes (id_cliente)
        ON DELETE SET NULL,
    CONSTRAINT fk_chamado_usuario
        FOREIGN KEY (id_usuario)
        REFERENCES usuarios (id_usuario)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE mensagens (
    id_mensagem INT NOT NULL AUTO_INCREMENT,
    id_chamado INT NOT NULL,
    id_usuario INT DEFAULT NULL,
    id_cliente INT DEFAULT NULL,
    mensagem TEXT NOT NULL,
    data_envio TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_mensagem),
    KEY fk_mensagem_chamado (id_chamado),
    KEY fk_mensagem_usuario (id_usuario),
    KEY fk_mensagem_cliente (id_cliente),
    CONSTRAINT fk_mensagem_chamado
        FOREIGN KEY (id_chamado)
        REFERENCES chamados (id_chamado)
        ON DELETE CASCADE,
    CONSTRAINT fk_mensagem_cliente
        FOREIGN KEY (id_cliente)
        REFERENCES clientes (id_cliente)
        ON DELETE SET NULL,
    CONSTRAINT fk_mensagem_usuario
        FOREIGN KEY (id_usuario)
        REFERENCES usuarios (id_usuario)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE notificacoes (
    id_notificacao INT NOT NULL AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    mensagem TEXT NOT NULL,
    lida TINYINT(1) NOT NULL DEFAULT 0,
    data_criacao TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_notificacao),
    KEY fk_notificacao_usuario (id_usuario),
    CONSTRAINT fk_notificacao_usuario
        FOREIGN KEY (id_usuario)
        REFERENCES usuarios (id_usuario)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE recuperacao_senha (
    id_recuperacao INT NOT NULL AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    data_expiracao DATETIME NOT NULL,
    PRIMARY KEY (id_recuperacao),
    UNIQUE KEY token (token),
    KEY fk_recuperacao_usuario (id_usuario),
    CONSTRAINT fk_recuperacao_usuario
        FOREIGN KEY (id_usuario)
        REFERENCES usuarios (id_usuario)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

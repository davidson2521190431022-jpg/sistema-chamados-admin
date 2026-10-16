USE sistema_chamados;

INSERT INTO usuarios (nome, email, senha, nivel_acesso)
VALUES (
    'Administrador',
    'admin@admin.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin');
INSERT INTO categorias (nome, descricao)
VALUES
('Dúvida', 'Dúvidas gerais sobre o sistema ou serviço'),
('Problema Técnico', 'Falhas, erros ou problemas de funcionamento'),
('Financeiro', 'Questões relacionadas a pagamentos e faturas');

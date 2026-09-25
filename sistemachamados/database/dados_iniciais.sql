USE sistemachamados;

 Insere o usuário administrador (Email: admin@admin.com / Senha: 123456)
INSERT INTO usuarios (nome, email, senha, perfil) 
VALUES ('Administrador', 'admin@admin.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
INSERT INTO categorias (nome, descricao) VALUES 
('Dúvida', 'Dúvidas gerais sobre o sistema ou serviço'),
('Problema Técnico', 'Falhas, erros ou problemas de funcionamento'),
('Financeiro', 'Questões relacionadas a pagamentos e faturas');
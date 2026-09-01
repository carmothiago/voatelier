-- =====================================================================
-- VITÓRIA OLIVER ATELIER - Dados iniciais (seed)
-- Execute DEPOIS do schema.sql
-- =====================================================================

USE voatelier;

-- ---------------------------------------------------------------------
-- Perfis
-- ---------------------------------------------------------------------
INSERT INTO perfis (nome, slug, descricao) VALUES
('Administrador', 'administrador', 'Acesso completo ao sistema'),
('Gerente', 'gerente', 'Gestão, clientes, agenda, vestidos, produção e financeiro'),
('Atendimento', 'atendimento', 'Clientes, CRM e agenda'),
('Costureira', 'costureira', 'Medidas, provas e produção'),
('Financeiro', 'financeiro', 'Contas a pagar, contas a receber e relatórios financeiros');

-- ---------------------------------------------------------------------
-- Permissões (módulo + ação)
-- Módulos previstos para todo o sistema (etapas futuras incluídas
-- desde já para não exigir alteração de schema depois)
-- ---------------------------------------------------------------------
INSERT INTO permissoes (modulo, acao, descricao) VALUES
('dashboard', 'visualizar', 'Ver o painel principal'),
('usuarios', 'visualizar', 'Ver usuários do sistema'),
('usuarios', 'criar', 'Criar usuários'),
('usuarios', 'editar', 'Editar usuários'),
('usuarios', 'excluir', 'Excluir/inativar usuários'),
('clientes', 'visualizar', 'Ver clientes'),
('clientes', 'criar', 'Cadastrar clientes'),
('clientes', 'editar', 'Editar clientes'),
('clientes', 'excluir', 'Excluir clientes'),
('crm', 'visualizar', 'Ver pipeline comercial'),
('crm', 'editar', 'Mover clientes entre etapas do CRM'),
('agenda', 'visualizar', 'Ver agenda'),
('agenda', 'criar', 'Criar agendamentos'),
('agenda', 'editar', 'Editar agendamentos'),
('agenda', 'excluir', 'Excluir agendamentos'),
('vestidos', 'visualizar', 'Ver vestidos'),
('vestidos', 'criar', 'Cadastrar vestidos'),
('vestidos', 'editar', 'Editar vestidos'),
('vestidos', 'excluir', 'Excluir vestidos'),
('medidas', 'visualizar', 'Ver medidas'),
('medidas', 'criar', 'Cadastrar medidas'),
('provas', 'visualizar', 'Ver provas'),
('provas', 'criar', 'Cadastrar provas'),
('provas', 'editar', 'Editar provas'),
('producao', 'visualizar', 'Ver produção'),
('producao', 'editar', 'Atualizar etapas de produção'),
('estoque', 'visualizar', 'Ver estoque'),
('estoque', 'editar', 'Movimentar estoque'),
('fornecedores', 'visualizar', 'Ver fornecedores'),
('fornecedores', 'editar', 'Editar fornecedores'),
('financeiro', 'visualizar', 'Ver financeiro'),
('financeiro', 'editar', 'Lançar/editar contas'),
('contratos', 'visualizar', 'Ver contratos'),
('contratos', 'criar', 'Gerar contratos'),
('documentos', 'visualizar', 'Ver documentos e fotos'),
('documentos', 'criar', 'Anexar documentos e fotos'),
('relatorios', 'visualizar', 'Ver relatórios'),
('auditoria', 'visualizar', 'Ver logs de auditoria');

-- ---------------------------------------------------------------------
-- Vínculo perfis x permissões
-- ---------------------------------------------------------------------

-- Administrador: todas as permissões
INSERT INTO perfis_permissoes (perfil_id, permissao_id)
SELECT (SELECT id FROM perfis WHERE slug = 'administrador'), id FROM permissoes;

-- Gerente: tudo, exceto gestão de usuários e auditoria
INSERT INTO perfis_permissoes (perfil_id, permissao_id)
SELECT (SELECT id FROM perfis WHERE slug = 'gerente'), id
FROM permissoes
WHERE modulo NOT IN ('usuarios', 'auditoria');

-- Atendimento: clientes, crm, agenda, dashboard
INSERT INTO perfis_permissoes (perfil_id, permissao_id)
SELECT (SELECT id FROM perfis WHERE slug = 'atendimento'), id
FROM permissoes
WHERE modulo IN ('dashboard', 'clientes', 'crm', 'agenda', 'documentos');

-- Costureira: medidas, provas, produção, dashboard
INSERT INTO perfis_permissoes (perfil_id, permissao_id)
SELECT (SELECT id FROM perfis WHERE slug = 'costureira'), id
FROM permissoes
WHERE modulo IN ('dashboard', 'medidas', 'provas', 'producao', 'vestidos')
  AND acao = 'visualizar'
   OR (modulo IN ('medidas', 'provas', 'producao') AND acao IN ('criar', 'editar'));

-- Financeiro: financeiro, contratos (visualizar), relatorios, dashboard
INSERT INTO perfis_permissoes (perfil_id, permissao_id)
SELECT (SELECT id FROM perfis WHERE slug = 'financeiro'), id
FROM permissoes
WHERE modulo IN ('dashboard', 'financeiro', 'relatorios')
   OR (modulo = 'contratos' AND acao = 'visualizar');

-- ---------------------------------------------------------------------
-- Usuário administrador inicial
-- Usuário: admin
-- Senha:   admin123   (ALTERE NO PRIMEIRO LOGIN - o sistema irá exigir)
-- Hash gerado com password_hash() / algoritmo bcrypt
-- ---------------------------------------------------------------------
INSERT INTO usuarios (perfil_id, nome, usuario, email, senha_hash, status, precisa_trocar_senha)
VALUES (
    (SELECT id FROM perfis WHERE slug = 'administrador'),
    'Administrador',
    'admin',
    'admin@voatelier.local',
    '$2b$10$0p8T7Qh6JMf1g0eVKEc.auQoRGB5arf/YziTnoSgT7oKVUlNgTEie',
    'ativo',
    1
);

-- =====================================================================
-- IMPORTANTE: os dados acima (usuário admin) são apenas para a primeira
-- configuração do sistema. Troque a senha imediatamente após o primeiro
-- acesso. Dados fictícios de clientes/vestidos/agenda/financeiro serão
-- adicionados nas próximas etapas, junto com as respectivas tabelas.
-- =====================================================================

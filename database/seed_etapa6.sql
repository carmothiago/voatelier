-- =====================================================================
-- VITÓRIA OLIVER ATELIER - ETAPA 6
-- Não há novas tabelas nesta etapa (a tabela "auditoria" já existe desde
-- a Etapa 1 — aqui só ganhamos uma tela para consultá-la). Este arquivo
-- apenas adiciona a permissão de "Configurações" (backup do sistema),
-- reservada ao Administrador.
-- Execute DEPOIS das Etapas 1 a 5.
-- =====================================================================

USE voatelier;

INSERT IGNORE INTO permissoes (modulo, acao, descricao) VALUES
    ('configuracoes', 'visualizar', 'Ver configurações e gerar backup do sistema');

INSERT IGNORE INTO perfis_permissoes (perfil_id, permissao_id)
SELECT (SELECT id FROM perfis WHERE slug = 'administrador'), id
FROM permissoes
WHERE modulo = 'configuracoes';

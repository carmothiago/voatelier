-- =====================================================================
-- Permissão medidas.configurar
-- Concedida a: administrador, gerente e costureira.
-- Execute DEPOIS de schema_medidas_campos.sql.
-- =====================================================================

USE voatelier;

-- Adiciona a permissão ao catálogo
INSERT IGNORE INTO permissoes (modulo, acao, descricao) VALUES
    ('medidas', 'configurar', 'Adicionar, remover e reordenar campos do formulário de medidas');

-- Administrador
INSERT IGNORE INTO perfis_permissoes (perfil_id, permissao_id)
SELECT (SELECT id FROM perfis WHERE slug = 'administrador'), id
FROM permissoes WHERE modulo = 'medidas' AND acao = 'configurar';

-- Gerente
INSERT IGNORE INTO perfis_permissoes (perfil_id, permissao_id)
SELECT (SELECT id FROM perfis WHERE slug = 'gerente'), id
FROM permissoes WHERE modulo = 'medidas' AND acao = 'configurar';

-- Costureira
INSERT IGNORE INTO perfis_permissoes (perfil_id, permissao_id)
SELECT (SELECT id FROM perfis WHERE slug = 'costureira'), id
FROM permissoes WHERE modulo = 'medidas' AND acao = 'configurar';

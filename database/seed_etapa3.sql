-- =====================================================================
-- VITÓRIA OLIVER ATELIER - Dados de demonstração (Etapa 3)
-- ATENÇÃO: dados FICTÍCIOS apenas para testar o sistema.
-- Execute DEPOIS de database/schema_etapa3.sql
-- =====================================================================

USE voatelier;

INSERT INTO vestidos (codigo, nome, categoria, tipo, tamanho, cor, valor, descricao, status)
VALUES
    ('VO-001', 'Vestido Serena', 'Sereia', 'sob_medida', '38', 'Off-white', 8500.00, 'Renda francesa com cauda destacável', 'em_producao'),
    ('VO-002', 'Vestido Aurora', 'Princesa', 'venda', '40', 'Branco', 6200.00, 'Saia volumosa com bordado de pedrarias', 'disponivel'),
    ('VO-003', 'Vestido Bela', 'Evasê', 'sob_medida', '36', 'Champagne', 7300.00, 'Decote coração com renda chantilly', 'reservado'),
    ('VO-004', 'Vestido Luna', 'Sereia', 'venda', '42', 'Off-white', 5800.00, NULL, 'disponivel');

UPDATE vestidos SET cliente_id = (SELECT id FROM clientes WHERE nome_completo = 'Vitória Oliveira')
WHERE codigo = 'VO-001';

UPDATE vestidos SET cliente_id = (SELECT id FROM clientes WHERE nome_completo = 'Ana Eloise Souza')
WHERE codigo = 'VO-003';

INSERT INTO historico_vestidos (vestido_id, status_anterior, status_novo, cliente_id, observacao)
SELECT id, NULL, status, cliente_id, 'Cadastro de demonstração' FROM vestidos;

-- Medidas (histórico) da Vitória Oliveira
INSERT INTO medidas (cliente_id, busto, cintura, quadril, altura, ombro, observacoes)
SELECT id, 88.0, 68.0, 96.0, 168.0, 38.0, 'Primeira medição'
FROM clientes WHERE nome_completo = 'Vitória Oliveira';

INSERT INTO medidas (cliente_id, busto, cintura, quadril, altura, ombro, observacoes)
SELECT id, 87.0, 67.0, 95.5, 168.0, 38.0, 'Reavaliação após ajuste de dieta da cliente'
FROM clientes WHERE nome_completo = 'Vitória Oliveira';

-- Prova da Vitória (vinculada ao agendamento de hoje já criado na Etapa 2)
INSERT INTO provas (numero, cliente_id, vestido_id, data_prova, responsavel_id, status, observacoes)
SELECT 1,
       (SELECT id FROM clientes WHERE nome_completo = 'Vitória Oliveira'),
       (SELECT id FROM vestidos WHERE codigo = 'VO-001'),
       CURDATE(), 1, 'em_execucao', 'Segunda prova — ajustar cintura';

INSERT INTO ajustes (prova_id, descricao, parte_vestido, medida_atual, medida_desejada, status)
SELECT id, 'Apertar cintura', 'Cintura', '68 cm', '67 cm', 'pendente'
FROM provas WHERE numero = 1 AND cliente_id = (SELECT id FROM clientes WHERE nome_completo = 'Vitória Oliveira');

INSERT INTO ajustes (prova_id, descricao, parte_vestido, medida_atual, medida_desejada, status)
SELECT id, 'Ajustar comprimento da bainha', 'Bainha', '-', '-2 cm', 'concluido'
FROM provas WHERE numero = 1 AND cliente_id = (SELECT id FROM clientes WHERE nome_completo = 'Vitória Oliveira');

-- Produção
INSERT INTO producao (cliente_id, vestido_id, responsavel_id, etapa, data_inicio, prazo, observacoes)
VALUES
    ((SELECT id FROM clientes WHERE nome_completo = 'Vitória Oliveira'), (SELECT id FROM vestidos WHERE codigo = 'VO-001'), 1, 'ajustes', DATE_SUB(CURDATE(), INTERVAL 30 DAY), DATE_ADD(CURDATE(), INTERVAL 10 DAY), 'Prioridade alta — casamento em breve'),
    ((SELECT id FROM clientes WHERE nome_completo = 'Ana Eloise Souza'), (SELECT id FROM vestidos WHERE codigo = 'VO-003'), 1, 'costura', DATE_SUB(CURDATE(), INTERVAL 15 DAY), DATE_ADD(CURDATE(), INTERVAL 3 DAY), NULL),
    ((SELECT id FROM clientes WHERE nome_completo = 'Camila Fernandes'), NULL, 1, 'projeto', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 60 DAY), 'Aguardando aprovação do desenho'),
    ((SELECT id FROM clientes WHERE nome_completo = 'Juliana Costa'), NULL, 1, 'modelagem', DATE_SUB(CURDATE(), INTERVAL 45 DAY), DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'ATRASADO — cobrar fornecedor de tecido');

INSERT INTO producao_historico (producao_id, etapa_anterior, etapa_nova, observacao)
SELECT id, NULL, etapa, 'Cadastro de demonstração' FROM producao;

-- =====================================================================
-- Lembrete: estes dados são apenas para demonstração/teste.
-- =====================================================================

-- =====================================================================
-- VITÓRIA OLIVER ATELIER - Dados de demonstração (Etapa 4)
-- ATENÇÃO: dados FICTÍCIOS apenas para testar o sistema.
-- Execute DEPOIS de database/schema_etapa4.sql
-- =====================================================================

USE voatelier;

INSERT INTO fornecedores (nome, cnpj_cpf, telefone, whatsapp, email, endereco)
VALUES
    ('Tecidos Bella Noiva Ltda', '12.345.678/0001-90', '(11) 3333-1111', '(11) 93333-1111', 'contato@bellanoivatecidos.com.br', 'Rua Barão de Tatuí, 200 — Bom Retiro, São Paulo/SP'),
    ('Aviamentos Prata Fina', '23.456.789/0001-11', '(11) 3333-2222', '(11) 93333-2222', 'vendas@pratafina.com.br', 'Rua Aimorés, 450 — Bom Retiro, São Paulo/SP'),
    ('Rendas & Cia', '34.567.890/0001-22', '(11) 3333-3333', NULL, 'contato@rendasecia.com.br', NULL);

INSERT INTO materiais (codigo, nome, categoria, unidade, quantidade, estoque_minimo, custo_unitario, fornecedor_id)
VALUES
    ('MT-001', 'Renda francesa branca', 'Rendas', 'm', 12.5, 5.0, 85.00, (SELECT id FROM fornecedores WHERE nome = 'Rendas & Cia')),
    ('MT-002', 'Tule ilusão off-white', 'Tule', 'm', 3.0, 8.0, 22.50, (SELECT id FROM fornecedores WHERE nome = 'Tecidos Bella Noiva Ltda')),
    ('MT-003', 'Zíper invisível 60cm', 'Zíperes', 'un', 40, 15, 4.90, (SELECT id FROM fornecedores WHERE nome = 'Aviamentos Prata Fina')),
    ('MT-004', 'Pedraria strass 4mm', 'Pedrarias', 'kg', 0.8, 1.0, 180.00, (SELECT id FROM fornecedores WHERE nome = 'Aviamentos Prata Fina')),
    ('MT-005', 'Cetim champagne', 'Tecidos', 'm', 25.0, 10.0, 34.00, (SELECT id FROM fornecedores WHERE nome = 'Tecidos Bella Noiva Ltda'));

-- Movimentações de exemplo (entradas iniciais + uma saída)
INSERT INTO movimentacoes_estoque (material_id, tipo, quantidade, quantidade_resultante, motivo)
SELECT id, 'entrada', quantidade, quantidade, 'Estoque inicial (cadastro de demonstração)'
FROM materiais;

INSERT INTO movimentacoes_estoque (material_id, tipo, quantidade, quantidade_resultante, motivo)
SELECT id, 'saida', 2.0, quantidade - 2.0, 'Uso no vestido VO-001 (Vitória Oliveira)'
FROM materiais WHERE codigo = 'MT-002';

UPDATE materiais SET quantidade = quantidade - 2.0 WHERE codigo = 'MT-002';

-- =====================================================================
-- Lembrete: MT-002 (Tule ilusão) e MT-004 (Pedraria) já ficam abaixo do
-- estoque mínimo propositalmente, para você testar o alerta de estoque baixo.
-- =====================================================================

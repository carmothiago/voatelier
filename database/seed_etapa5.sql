-- =====================================================================
-- VITÓRIA OLIVER ATELIER - Dados de demonstração (Etapa 5)
-- ATENÇÃO: dados FICTÍCIOS apenas para testar o sistema.
-- Execute DEPOIS de database/schema_etapa5.sql
-- =====================================================================

USE voatelier;

-- Contas a receber (uma paga, uma pendente futura, uma VENCIDA para testar alerta)
INSERT INTO contas_receber (cliente_id, descricao, valor, vencimento, forma_pagamento, status, data_pagamento)
VALUES
    ((SELECT id FROM clientes WHERE nome_completo = 'Vitória Oliveira'), 'Entrada - 50% do vestido', 4250.00, DATE_SUB(CURDATE(), INTERVAL 60 DAY), 'PIX', 'pago', DATE_SUB(CURDATE(), INTERVAL 60 DAY)),
    ((SELECT id FROM clientes WHERE nome_completo = 'Vitória Oliveira'), 'Parcela final - 50% do vestido', 4250.00, DATE_ADD(CURDATE(), INTERVAL 15 DAY), 'Cartão de crédito', 'pendente', NULL),
    ((SELECT id FROM clientes WHERE nome_completo = 'Ana Eloise Souza'), 'Sinal do contrato', 2000.00, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'PIX', 'pendente', NULL);

-- Contas a pagar (uma paga, uma pendente futura, uma VENCIDA)
INSERT INTO contas_pagar (fornecedor_id, categoria, descricao, valor, vencimento, status, data_pagamento)
VALUES
    ((SELECT id FROM fornecedores WHERE nome = 'Tecidos Bella Noiva Ltda'), 'Matéria-prima', 'Compra de cetim e renda', 1800.00, DATE_SUB(CURDATE(), INTERVAL 20 DAY), 'pago', DATE_SUB(CURDATE(), INTERVAL 20 DAY)),
    ((SELECT id FROM fornecedores WHERE nome = 'Aviamentos Prata Fina'), 'Aviamentos', 'Zíperes e pedrarias', 450.00, DATE_ADD(CURDATE(), INTERVAL 10 DAY), 'pendente', NULL),
    (NULL, 'Aluguel', 'Aluguel do ateliê - mês corrente', 3200.00, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'pendente', NULL);

-- Contrato de exemplo (sem PDF gerado ainda — o PDF é gerado sob demanda ao clicar em "Gerar PDF")
INSERT INTO contratos (cliente_id, vestido_id, data_contrato, valor, forma_pagamento, data_entrega, data_devolucao, clausulas, observacoes)
SELECT
    c.id,
    (SELECT id FROM vestidos WHERE codigo = 'VO-001'),
    DATE_SUB(CURDATE(), INTERVAL 60 DAY),
    8500.00,
    '50% na assinatura + 50% até 15 dias antes da entrega',
    DATE_SUB((SELECT data_casamento FROM clientes WHERE nome_completo = 'Vitória Oliveira'), INTERVAL 7 DAY),
    DATE_ADD((SELECT data_casamento FROM clientes WHERE nome_completo = 'Vitória Oliveira'), INTERVAL 5 DAY),
    'O vestido deve ser retirado em até 5 dias após a data do evento. Ajustes adicionais após a entrega serão cobrados à parte. Cancelamentos com menos de 30 dias do evento não têm direito a reembolso do sinal.',
    'Contrato de demonstração'
FROM clientes c WHERE c.nome_completo = 'Vitória Oliveira';

-- =====================================================================
-- Lembrete: dados fictícios apenas para demonstração/teste.
-- O sinal de Ana Eloise Souza (contas_receber) e o aluguel do ateliê
-- (contas_pagar) estão propositalmente com vencimento passado e status
-- "pendente", para você testar os alertas de inadimplência/vencidos.
-- =====================================================================

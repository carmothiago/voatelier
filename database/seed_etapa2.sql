-- =====================================================================
-- VITÓRIA OLIVER ATELIER - Dados de demonstração (Etapa 2)
-- ATENÇÃO: estes são dados FICTÍCIOS apenas para testar o sistema.
-- Execute DEPOIS de database/schema_etapa2.sql
-- =====================================================================

USE voatelier;

INSERT INTO clientes
    (nome_completo, cpf, telefone, whatsapp, email, cidade, estado, instagram,
     data_casamento, local_casamento, nome_noivo, tipo_casamento, etapa_crm, ativo)
VALUES
    ('Vitória Oliveira', '111.111.111-11', '(11) 91111-1111', '(11) 91111-1111', 'Vitória.oliveira@exemplo.com', 'São Paulo', 'SP', '@Vitória.oliveira', DATE_ADD(CURDATE(), INTERVAL 45 DAY), 'Espaço Villa Bianca', 'João Pedro', 'Casamento civil e religioso', 'contrato_fechado', 1),
    ('Ana Eloise Souza', '222.222.222-22', '(11) 92222-2222', '(11) 92222-2222', 'ana.Eloise@exemplo.com', 'São Paulo', 'SP', '@ana.Eloise', DATE_ADD(CURDATE(), INTERVAL 20 DAY), 'Sítio das Rosas', 'Carlos Eduardo', 'Casamento ao ar livre', 'negociacao', 1),
    ('Camila Fernandes', '333.333.333-33', '(11) 93333-3333', '(11) 93333-3333', 'camila.fernandes@exemplo.com', 'Guarulhos', 'SP', '@camila.fernandes', DATE_ADD(CURDATE(), INTERVAL 120 DAY), NULL, 'Rafael', 'Casamento civil', 'orcamento_enviado', 1),
    ('Juliana Costa', '444.444.444-44', '(11) 94444-4444', '(11) 94444-4444', 'juliana.costa@exemplo.com', 'São Bernardo do Campo', 'SP', '@juliana.costa', NULL, NULL, 'Fernando', NULL, 'atendimento_agendado', 1),
    ('Eloise Lima', '555.555.555-55', '(11) 95555-5555', '(11) 95555-5555', 'Eloise.lima@exemplo.com', 'São Paulo', 'SP', '@Eloise.lima', NULL, NULL, NULL, NULL, 'novo_contato', 1),
    ('Fernanda Rocha', '666.666.666-66', '(11) 96666-6666', '(11) 96666-6666', 'fernanda.rocha@exemplo.com', 'Santo André', 'SP', '@fernanda.rocha', NULL, NULL, 'Marcelo', NULL, 'perdido', 1);

UPDATE clientes SET motivo_perda = 'Optou por outro atelier' WHERE nome_completo = 'Fernanda Rocha';

-- Histórico de CRM inicial (opcional, apenas ilustrativo)
INSERT INTO crm_historico (cliente_id, etapa_anterior, etapa_nova, observacao)
SELECT id, NULL, etapa_crm, 'Cadastro de demonstração' FROM clientes;

-- Agendamentos de demonstração (hoje e nos próximos dias)
-- Responsável: usuário admin (id 1, criado na Etapa 1)
INSERT INTO agendamentos (cliente_id, tipo, data_agendamento, hora_inicio, hora_fim, responsavel_id, observacoes, criado_por)
VALUES
    ((SELECT id FROM clientes WHERE nome_completo = 'Vitória Oliveira'), 'prova', CURDATE(), '10:00:00', '11:00:00', 1, 'Segunda prova do vestido principal', 1),
    ((SELECT id FROM clientes WHERE nome_completo = 'Ana Eloise Souza'), 'atendimento', CURDATE(), '14:00:00', '15:00:00', 1, 'Apresentação de proposta comercial', 1),
    ((SELECT id FROM clientes WHERE nome_completo = 'Juliana Costa'), 'atendimento', DATE_ADD(CURDATE(), INTERVAL 1 DAY), '09:30:00', '10:30:00', 1, 'Primeira visita ao atelier', 1),
    ((SELECT id FROM clientes WHERE nome_completo = 'Camila Fernandes'), 'medicao', DATE_ADD(CURDATE(), INTERVAL 2 DAY), '16:00:00', '16:45:00', 1, NULL, 1);

-- =====================================================================
-- Lembrete: estes dados são apenas para demonstração/teste.
-- Remova-os antes de colocar o sistema em uso real no atelier.
-- =====================================================================

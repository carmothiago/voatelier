-- =====================================================================
-- VITÓRIA OLIVER ATELIER - ETAPA 2
-- Clientes, histórico de alterações, CRM (pipeline) e Agenda
-- Execute DEPOIS de database/schema.sql e database/seed.sql (Etapa 1)
-- =====================================================================

USE voatelier;

SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- Tabela: clientes
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS clientes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    -- Dados pessoais
    nome_completo VARCHAR(150) NOT NULL,
    cpf VARCHAR(14) DEFAULT NULL,
    data_nascimento DATE DEFAULT NULL,
    telefone VARCHAR(20) DEFAULT NULL,
    whatsapp VARCHAR(20) DEFAULT NULL,
    email VARCHAR(150) DEFAULT NULL,
    endereco VARCHAR(255) DEFAULT NULL,
    cidade VARCHAR(100) DEFAULT NULL,
    estado CHAR(2) DEFAULT NULL,
    instagram VARCHAR(100) DEFAULT NULL,
    observacoes TEXT DEFAULT NULL,

    -- Dados do casamento
    data_casamento DATE DEFAULT NULL,
    horario_casamento TIME DEFAULT NULL,
    local_casamento VARCHAR(255) DEFAULT NULL,
    nome_noivo VARCHAR(150) DEFAULT NULL,
    tipo_casamento VARCHAR(50) DEFAULT NULL,
    observacoes_casamento TEXT DEFAULT NULL,

    -- CRM / pipeline comercial
    etapa_crm ENUM(
        'novo_contato',
        'atendimento_agendado',
        'atendimento_realizado',
        'orcamento_enviado',
        'negociacao',
        'contrato_fechado',
        'perdido'
    ) NOT NULL DEFAULT 'novo_contato',
    motivo_perda VARCHAR(255) DEFAULT NULL,

    -- Controle
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_por INT UNSIGNED DEFAULT NULL,
    atualizado_por INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    KEY idx_clientes_nome (nome_completo),
    KEY idx_clientes_cpf (cpf),
    KEY idx_clientes_telefone (telefone),
    KEY idx_clientes_etapa (etapa_crm),
    KEY idx_clientes_data_casamento (data_casamento),
    KEY idx_clientes_ativo (ativo),
    CONSTRAINT fk_clientes_criado_por FOREIGN KEY (criado_por) REFERENCES usuarios(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_clientes_atualizado_por FOREIGN KEY (atualizado_por) REFERENCES usuarios(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabela: historico_clientes
-- Guarda cada alteração de campo relevante feita na ficha da cliente
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS historico_clientes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT UNSIGNED NOT NULL,
    usuario_id INT UNSIGNED DEFAULT NULL,
    campo VARCHAR(60) NOT NULL,
    valor_anterior TEXT DEFAULT NULL,
    valor_novo TEXT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    KEY idx_historico_cliente (cliente_id),
    CONSTRAINT fk_historico_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_historico_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabela: crm_historico
-- Histórico específico de movimentação no funil comercial (Kanban)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS crm_historico (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT UNSIGNED NOT NULL,
    usuario_id INT UNSIGNED DEFAULT NULL,
    etapa_anterior VARCHAR(30) DEFAULT NULL,
    etapa_nova VARCHAR(30) NOT NULL,
    observacao VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    KEY idx_crm_historico_cliente (cliente_id),
    CONSTRAINT fk_crm_historico_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_crm_historico_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabela: agendamentos
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS agendamentos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT UNSIGNED DEFAULT NULL,
    tipo ENUM('atendimento', 'medicao', 'prova', 'ajuste', 'entrega', 'devolucao', 'reuniao') NOT NULL,
    data_agendamento DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fim TIME NOT NULL,
    responsavel_id INT UNSIGNED DEFAULT NULL,
    observacoes TEXT DEFAULT NULL,
    status ENUM('agendado', 'concluido', 'cancelado') NOT NULL DEFAULT 'agendado',
    criado_por INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    KEY idx_agendamentos_data (data_agendamento),
    KEY idx_agendamentos_cliente (cliente_id),
    KEY idx_agendamentos_responsavel (responsavel_id),
    KEY idx_agendamentos_tipo (tipo),
    CONSTRAINT fk_agendamentos_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_agendamentos_responsavel FOREIGN KEY (responsavel_id) REFERENCES usuarios(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_agendamentos_criado_por FOREIGN KEY (criado_por) REFERENCES usuarios(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

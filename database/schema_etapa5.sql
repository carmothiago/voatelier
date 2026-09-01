-- =====================================================================
-- VITÓRIA OLIVER ATELIER - ETAPA 5
-- Financeiro (contas a receber/pagar) e Contratos
-- Execute DEPOIS das Etapas 1, 2, 3 e 4
-- =====================================================================

USE voatelier;

SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- Tabela: contas_receber
--
-- OBS: o valor "vencido" não é um status gravado no banco — é calculado
-- na consulta (status = 'pendente' AND vencimento < CURDATE()). Isso evita
-- depender de uma tarefa agendada (cron) só para manter o status em dia,
-- o que seria mais uma peça para quebrar em um servidor local no XAMPP.
-- O status gravado é sempre 'pendente', 'pago' ou 'cancelado'.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS contas_receber (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT UNSIGNED NOT NULL,
    descricao VARCHAR(255) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    vencimento DATE NOT NULL,
    forma_pagamento VARCHAR(50) DEFAULT NULL,
    status ENUM('pendente', 'pago', 'cancelado') NOT NULL DEFAULT 'pendente',
    data_pagamento DATE DEFAULT NULL,
    observacoes TEXT DEFAULT NULL,
    criado_por INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    KEY idx_contas_receber_cliente (cliente_id),
    KEY idx_contas_receber_status (status),
    KEY idx_contas_receber_vencimento (vencimento),
    CONSTRAINT fk_contas_receber_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_contas_receber_criado_por FOREIGN KEY (criado_por) REFERENCES usuarios(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabela: contas_pagar
-- (mesma lógica de "vencido calculado" da tabela acima)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS contas_pagar (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fornecedor_id INT UNSIGNED DEFAULT NULL,
    categoria VARCHAR(60) DEFAULT NULL,
    descricao VARCHAR(255) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    vencimento DATE NOT NULL,
    status ENUM('pendente', 'pago', 'cancelado') NOT NULL DEFAULT 'pendente',
    data_pagamento DATE DEFAULT NULL,
    observacoes TEXT DEFAULT NULL,
    criado_por INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    KEY idx_contas_pagar_fornecedor (fornecedor_id),
    KEY idx_contas_pagar_status (status),
    KEY idx_contas_pagar_vencimento (vencimento),
    CONSTRAINT fk_contas_pagar_fornecedor FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_contas_pagar_criado_por FOREIGN KEY (criado_por) REFERENCES usuarios(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabela: contratos
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS contratos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT UNSIGNED NOT NULL,
    vestido_id INT UNSIGNED DEFAULT NULL,
    data_contrato DATE NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    forma_pagamento VARCHAR(100) DEFAULT NULL,
    data_entrega DATE DEFAULT NULL,
    data_devolucao DATE DEFAULT NULL,
    clausulas TEXT DEFAULT NULL,
    observacoes TEXT DEFAULT NULL,
    arquivo_pdf VARCHAR(255) DEFAULT NULL COMMENT 'Nome do PDF gerado, salvo em uploads/contratos/',
    criado_por INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    KEY idx_contratos_cliente (cliente_id),
    KEY idx_contratos_vestido (vestido_id),
    CONSTRAINT fk_contratos_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_contratos_vestido FOREIGN KEY (vestido_id) REFERENCES vestidos(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_contratos_criado_por FOREIGN KEY (criado_por) REFERENCES usuarios(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

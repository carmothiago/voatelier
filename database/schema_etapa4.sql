-- =====================================================================
-- VITÓRIA OLIVER ATELIER - ETAPA 4
-- Estoque (materiais + movimentações), Fornecedores e Documentos
-- Execute DEPOIS das Etapas 1, 2 e 3 (schema.sql + seeds anteriores)
-- =====================================================================

USE voatelier;

SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- Tabela: fornecedores
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS fornecedores (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    cnpj_cpf VARCHAR(20) DEFAULT NULL,
    telefone VARCHAR(20) DEFAULT NULL,
    whatsapp VARCHAR(20) DEFAULT NULL,
    email VARCHAR(150) DEFAULT NULL,
    endereco VARCHAR(255) DEFAULT NULL,
    observacoes TEXT DEFAULT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_por INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    KEY idx_fornecedores_nome (nome),
    KEY idx_fornecedores_ativo (ativo),
    CONSTRAINT fk_fornecedores_criado_por FOREIGN KEY (criado_por) REFERENCES usuarios(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabela: materiais
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS materiais (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL,
    nome VARCHAR(150) NOT NULL,
    categoria VARCHAR(60) DEFAULT NULL COMMENT 'Tecidos, Rendas, Tule, Linhas, Zíperes, Botões, Pedrarias, Aviamentos...',
    unidade VARCHAR(10) NOT NULL DEFAULT 'un' COMMENT 'un, m, kg, cx...',
    quantidade DECIMAL(10,2) NOT NULL DEFAULT 0,
    estoque_minimo DECIMAL(10,2) NOT NULL DEFAULT 0,
    custo_unitario DECIMAL(10,2) DEFAULT NULL,
    fornecedor_id INT UNSIGNED DEFAULT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_por INT UNSIGNED DEFAULT NULL,
    atualizado_por INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_materiais_codigo (codigo),
    KEY idx_materiais_categoria (categoria),
    KEY idx_materiais_fornecedor (fornecedor_id),
    KEY idx_materiais_ativo (ativo),
    CONSTRAINT fk_materiais_fornecedor FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_materiais_criado_por FOREIGN KEY (criado_por) REFERENCES usuarios(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_materiais_atualizado_por FOREIGN KEY (atualizado_por) REFERENCES usuarios(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabela: movimentacoes_estoque
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS movimentacoes_estoque (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    material_id INT UNSIGNED NOT NULL,
    tipo ENUM('entrada', 'saida', 'ajuste') NOT NULL,
    quantidade DECIMAL(10,2) NOT NULL COMMENT 'Quantidade movimentada (sempre positiva); o efeito depende do tipo',
    quantidade_resultante DECIMAL(10,2) NOT NULL COMMENT 'Estoque após a movimentação, para consulta rápida',
    motivo VARCHAR(255) DEFAULT NULL,
    usuario_id INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    KEY idx_movimentacoes_material (material_id),
    KEY idx_movimentacoes_tipo (tipo),
    CONSTRAINT fk_movimentacoes_material FOREIGN KEY (material_id) REFERENCES materiais(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_movimentacoes_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabela: anexos
-- Documentos e fotos gerais anexados à ficha da cliente
-- (distinto de anexos_prova, que é específico das provas — Etapa 3)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS anexos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT UNSIGNED NOT NULL,
    nome_arquivo VARCHAR(255) NOT NULL COMMENT 'Nome gerado pelo sistema, salvo em uploads/documentos/',
    nome_original VARCHAR(255) NOT NULL,
    tamanho_bytes INT UNSIGNED DEFAULT NULL,
    descricao VARCHAR(255) DEFAULT NULL,
    criado_por INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    KEY idx_anexos_cliente (cliente_id),
    CONSTRAINT fk_anexos_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_anexos_criado_por FOREIGN KEY (criado_por) REFERENCES usuarios(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

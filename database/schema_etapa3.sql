-- =====================================================================
-- VITÓRIA OLIVER ATELIER - ETAPA 3
-- Vestidos, Medidas, Provas (com ajustes e fotos) e Produção
-- Execute DEPOIS de database/schema.sql + seed.sql (Etapa 1)
-- e database/schema_etapa2.sql + seed_etapa2.sql (Etapa 2)
-- =====================================================================

USE voatelier;

SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- Tabela: vestidos
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS vestidos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL,
    nome VARCHAR(150) NOT NULL,
    categoria VARCHAR(80) DEFAULT NULL,
    tipo ENUM('venda', 'sob_medida') NOT NULL DEFAULT 'sob_medida',
    tamanho VARCHAR(20) DEFAULT NULL,
    cor VARCHAR(50) DEFAULT NULL,
    valor DECIMAL(10,2) DEFAULT NULL,
    descricao TEXT DEFAULT NULL,
    status ENUM('disponivel', 'reservado', 'em_producao', 'indisponivel') NOT NULL DEFAULT 'disponivel',
    cliente_id INT UNSIGNED DEFAULT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_por INT UNSIGNED DEFAULT NULL,
    atualizado_por INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_vestidos_codigo (codigo),
    KEY idx_vestidos_status (status),
    KEY idx_vestidos_cliente (cliente_id),
    KEY idx_vestidos_ativo (ativo),
    CONSTRAINT fk_vestidos_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_vestidos_criado_por FOREIGN KEY (criado_por) REFERENCES usuarios(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_vestidos_atualizado_por FOREIGN KEY (atualizado_por) REFERENCES usuarios(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabela: historico_vestidos
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS historico_vestidos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vestido_id INT UNSIGNED NOT NULL,
    usuario_id INT UNSIGNED DEFAULT NULL,
    cliente_id INT UNSIGNED DEFAULT NULL,
    status_anterior VARCHAR(20) DEFAULT NULL,
    status_novo VARCHAR(20) NOT NULL,
    observacao VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    KEY idx_historico_vestido (vestido_id),
    CONSTRAINT fk_historico_vestido FOREIGN KEY (vestido_id) REFERENCES vestidos(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_historico_vestido_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_historico_vestido_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabela: medidas
-- Cada registro é um "retrato" das medidas da cliente em uma data.
-- Nunca é atualizado/sobrescrito — apenas novos registros são inseridos.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS medidas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT UNSIGNED NOT NULL,
    busto DECIMAL(6,2) DEFAULT NULL,
    cintura DECIMAL(6,2) DEFAULT NULL,
    quadril DECIMAL(6,2) DEFAULT NULL,
    altura DECIMAL(6,2) DEFAULT NULL,
    ombro DECIMAL(6,2) DEFAULT NULL,
    braco DECIMAL(6,2) DEFAULT NULL,
    biceps DECIMAL(6,2) DEFAULT NULL,
    punho DECIMAL(6,2) DEFAULT NULL,
    comprimento_frente DECIMAL(6,2) DEFAULT NULL,
    comprimento_costas DECIMAL(6,2) DEFAULT NULL,
    decote DECIMAL(6,2) DEFAULT NULL,
    observacoes TEXT DEFAULT NULL,
    usuario_id INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    KEY idx_medidas_cliente (cliente_id),
    CONSTRAINT fk_medidas_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_medidas_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabela: provas
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS provas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    numero INT UNSIGNED NOT NULL,
    cliente_id INT UNSIGNED NOT NULL,
    vestido_id INT UNSIGNED DEFAULT NULL,
    data_prova DATE NOT NULL,
    responsavel_id INT UNSIGNED DEFAULT NULL,
    status ENUM('pendente', 'em_execucao', 'concluido') NOT NULL DEFAULT 'pendente',
    observacoes TEXT DEFAULT NULL,
    criado_por INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    KEY idx_provas_cliente (cliente_id),
    KEY idx_provas_vestido (vestido_id),
    KEY idx_provas_status (status),
    KEY idx_provas_data (data_prova),
    CONSTRAINT fk_provas_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_provas_vestido FOREIGN KEY (vestido_id) REFERENCES vestidos(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_provas_responsavel FOREIGN KEY (responsavel_id) REFERENCES usuarios(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabela: ajustes (itens de ajuste dentro de uma prova)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS ajustes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    prova_id INT UNSIGNED NOT NULL,
    descricao VARCHAR(255) NOT NULL,
    parte_vestido VARCHAR(100) DEFAULT NULL,
    medida_atual VARCHAR(50) DEFAULT NULL,
    medida_desejada VARCHAR(50) DEFAULT NULL,
    observacao TEXT DEFAULT NULL,
    status ENUM('pendente', 'em_execucao', 'concluido') NOT NULL DEFAULT 'pendente',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    KEY idx_ajustes_prova (prova_id),
    CONSTRAINT fk_ajustes_prova FOREIGN KEY (prova_id) REFERENCES provas(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabela: anexos_prova (fotos da prova)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS anexos_prova (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    prova_id INT UNSIGNED NOT NULL,
    nome_arquivo VARCHAR(255) NOT NULL COMMENT 'Nome gerado pelo sistema, salvo em uploads/provas/',
    nome_original VARCHAR(255) NOT NULL,
    tamanho_bytes INT UNSIGNED DEFAULT NULL,
    criado_por INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    KEY idx_anexos_prova (prova_id),
    CONSTRAINT fk_anexos_prova FOREIGN KEY (prova_id) REFERENCES provas(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabela: producao (projetos de produção, um por vestido/cliente)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS producao (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT UNSIGNED NOT NULL,
    vestido_id INT UNSIGNED DEFAULT NULL,
    responsavel_id INT UNSIGNED DEFAULT NULL,
    etapa ENUM(
        'projeto', 'desenho', 'aprovacao', 'modelagem', 'corte',
        'costura', 'bordado', 'ajustes', 'acabamento', 'finalizacao', 'entrega'
    ) NOT NULL DEFAULT 'projeto',
    data_inicio DATE DEFAULT NULL,
    prazo DATE DEFAULT NULL,
    observacoes TEXT DEFAULT NULL,
    criado_por INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    KEY idx_producao_cliente (cliente_id),
    KEY idx_producao_vestido (vestido_id),
    KEY idx_producao_etapa (etapa),
    KEY idx_producao_prazo (prazo),
    CONSTRAINT fk_producao_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_producao_vestido FOREIGN KEY (vestido_id) REFERENCES vestidos(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_producao_responsavel FOREIGN KEY (responsavel_id) REFERENCES usuarios(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabela: producao_historico
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS producao_historico (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    producao_id INT UNSIGNED NOT NULL,
    usuario_id INT UNSIGNED DEFAULT NULL,
    etapa_anterior VARCHAR(20) DEFAULT NULL,
    etapa_nova VARCHAR(20) NOT NULL,
    observacao VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    KEY idx_producao_historico (producao_id),
    CONSTRAINT fk_producao_historico FOREIGN KEY (producao_id) REFERENCES producao(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_producao_historico_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

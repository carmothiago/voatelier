-- =====================================================================
-- Campos de medidas configuráveis pela interface
--
-- Problema anterior: os campos de medida eram colunas fixas na tabela
-- `medidas` (busto, cintura, etc.) definidas como constantes PHP.
-- Isso impedia adicionar/remover campos sem alterar o banco e o código.
--
-- Solução: duas novas tabelas desvinculam o schema dos campos concretos.
--   medidas_campos  → catálogo dos campos disponíveis (configurável)
--   medidas_valores → valores registrados por campo, por ficha
--
-- Os registros históricos nas colunas antigas de `medidas` continuam
-- válidos e são lidos normalmente pelo sistema (retrocompatibilidade).
-- =====================================================================

-- ---------------------------------------------------------------------
-- Tabela: medidas_campos
-- Catálogo de campos de medida configuráveis pela interface.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS medidas_campos (
    id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug      VARCHAR(60)  NOT NULL UNIQUE,  -- nome técnico, ex: "colo", "busto_alto"
    label     VARCHAR(100) NOT NULL,          -- nome exibido, ex: "Colo", "Busto alto"
    ordem     SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    ativo     TINYINT(1) NOT NULL DEFAULT 1,
    criado_por INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    KEY idx_mc_ativo_ordem (ativo, ordem),
    CONSTRAINT fk_mc_criado_por FOREIGN KEY (criado_por) REFERENCES usuarios(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabela: medidas_valores
-- Valores de medidas dinâmicas, vinculados a uma ficha (medidas.id)
-- e a um campo (medidas_campos.id).
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS medidas_valores (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    medida_id   INT UNSIGNED NOT NULL,
    campo_id    INT UNSIGNED NOT NULL,
    valor       DECIMAL(7,2) DEFAULT NULL,

    UNIQUE KEY uk_mv_medida_campo (medida_id, campo_id),
    KEY idx_mv_campo (campo_id),
    CONSTRAINT fk_mv_medida FOREIGN KEY (medida_id) REFERENCES medidas(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_mv_campo  FOREIGN KEY (campo_id)  REFERENCES medidas_campos(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Seed: migra os campos históricos fixos para o catálogo dinâmico
-- (ordem espelha a sequência de Medida::CAMPOS)
-- ---------------------------------------------------------------------
INSERT IGNORE INTO medidas_campos (slug, label, ordem) VALUES
    ('busto',               'Busto',               1),
    ('cintura',             'Cintura',              2),
    ('quadril',             'Quadril',              3),
    ('altura',              'Altura',               4),
    ('ombro',               'Ombro',                5),
    ('braco',               'Braço',                6),
    ('biceps',              'Bíceps',               7),
    ('punho',               'Punho',                8),
    ('comprimento_frente',  'Comprimento frente',   9),
    ('comprimento_costas',  'Comprimento costas',  10),
    ('decote',              'Decote',              11);

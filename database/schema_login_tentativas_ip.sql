-- =====================================================================
-- Rate limiting por IP no formulário de login
--
-- Problema: o bloqueio anterior era apenas por conta de usuário.
-- Um atacante podia fazer N×5 tentativas atacando N usuários distintos
-- sem nunca ser bloqueado.
--
-- Solução: esta tabela conta tentativas falhas por IP de forma
-- independente do usuário tentado. Após LOGIN_IP_MAX_TENTATIVAS falhas
-- o IP fica bloqueado por LOGIN_IP_BLOQUEIO_MINUTOS minutos.
-- O bloqueio por usuário (tabela usuarios.bloqueado_ate) é mantido —
-- as duas camadas coexistem.
--
-- Execute DEPOIS de schema.sql (precisa do banco criado).
-- =====================================================================

USE voatelier;

CREATE TABLE IF NOT EXISTS login_tentativas_ip (
    id            INT UNSIGNED     AUTO_INCREMENT PRIMARY KEY,
    ip            VARCHAR(45)      NOT NULL,
    tentativas    SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    bloqueado_ate DATETIME         DEFAULT NULL,
    ultima_tentativa DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_ip (ip),
    KEY idx_bloqueado_ate (bloqueado_ate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Contador de tentativas de login falhas por endereço IP.';

-- =====================================================================
-- VITÓRIA OLIVER ATELIER - Sistema de Gestão
-- Banco de dados: voatelier
-- ETAPA 1: Estrutura base, autenticação, perfis e permissões
-- MySQL 8+ / InnoDB / utf8mb4
-- =====================================================================

CREATE DATABASE IF NOT EXISTS voatelier
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE voatelier;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- Tabela: perfis
-- Perfis de acesso do sistema (Administrador, Gerente, Atendimento,
-- Costureira, Financeiro)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS perfis (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    slug VARCHAR(50) NOT NULL,
    descricao VARCHAR(255) DEFAULT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_perfis_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabela: permissoes
-- Permissões granulares por módulo + ação
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS permissoes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    modulo VARCHAR(50) NOT NULL,
    acao VARCHAR(50) NOT NULL,
    descricao VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_permissoes_modulo_acao (modulo, acao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabela: perfis_permissoes
-- Relação N:N entre perfis e permissões
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS perfis_permissoes (
    perfil_id INT UNSIGNED NOT NULL,
    permissao_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (perfil_id, permissao_id),
    CONSTRAINT fk_pp_perfil FOREIGN KEY (perfil_id) REFERENCES perfis(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_pp_permissao FOREIGN KEY (permissao_id) REFERENCES permissoes(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabela: usuarios
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    perfil_id INT UNSIGNED NOT NULL,
    nome VARCHAR(150) NOT NULL,
    usuario VARCHAR(50) NOT NULL,
    email VARCHAR(150) DEFAULT NULL,
    senha_hash VARCHAR(255) NOT NULL,
    status ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    precisa_trocar_senha TINYINT(1) NOT NULL DEFAULT 0,
    tentativas_login TINYINT UNSIGNED NOT NULL DEFAULT 0,
    bloqueado_ate DATETIME DEFAULT NULL,
    ultimo_login DATETIME DEFAULT NULL,
    ultimo_ip VARCHAR(45) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_usuarios_usuario (usuario),
    UNIQUE KEY uk_usuarios_email (email),
    KEY idx_usuarios_perfil (perfil_id),
    KEY idx_usuarios_status (status),
    CONSTRAINT fk_usuarios_perfil FOREIGN KEY (perfil_id) REFERENCES perfis(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabela: auditoria
-- Log de ações do sistema (login, logout, alterações relevantes)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS auditoria (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED DEFAULT NULL,
    data_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ip VARCHAR(45) DEFAULT NULL,
    modulo VARCHAR(50) NOT NULL,
    acao VARCHAR(50) NOT NULL,
    registro_afetado VARCHAR(100) DEFAULT NULL,
    dados_anteriores TEXT DEFAULT NULL,
    dados_novos TEXT DEFAULT NULL,
    KEY idx_auditoria_usuario (usuario_id),
    KEY idx_auditoria_modulo (modulo),
    KEY idx_auditoria_data (data_hora),
    CONSTRAINT fk_auditoria_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================
-- Observação sobre as demais tabelas do sistema completo
-- (clientes, crm, agendamentos, vestidos, medidas, provas, producao,
-- materiais, fornecedores, contratos, contas_receber, contas_pagar,
-- anexos, etc.) serão criadas nas próximas etapas, mantendo o mesmo
-- padrão de nomenclatura e as referências de chave estrangeira para
-- "usuarios" e "auditoria" definidas aqui.
-- =====================================================================

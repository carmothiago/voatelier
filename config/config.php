<?php
/**
 * Vitória Oliver Atelier - Configuração geral do sistema
 *
 * Este arquivo concentra todas as configurações não sensíveis do sistema.
 * As credenciais de banco de dados ficam em config/database.php.
 *
 * NÃO deixe senhas ou segredos neste arquivo.
 */

// ---------------------------------------------------------------------
// Identidade do sistema
// ---------------------------------------------------------------------
define('APP_NAME', 'Vitória Oliver Atelier');
define('APP_SHORT_NAME', 'VO Atelier');
define('APP_VERSION', '1.0.0');

// ---------------------------------------------------------------------
// Ambiente
// Troque para 'production' quando o sistema estiver em uso real.
// Em 'production', erros técnicos não são exibidos na tela.
// ---------------------------------------------------------------------
define('APP_ENV', 'development'); // 'development' | 'production'

// ---------------------------------------------------------------------
// Fuso horário
// ---------------------------------------------------------------------
date_default_timezone_set('America/Sao_Paulo');

// ---------------------------------------------------------------------
// Caminhos (baseados na raiz do projeto, não no computador específico,
// para facilitar migração futura de servidor)
// ---------------------------------------------------------------------
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('UPLOADS_PATH', BASE_PATH . '/uploads');
define('STORAGE_PATH', BASE_PATH . '/storage');

// URL base do sistema dentro da rede local.
// Se o sistema estiver em uma subpasta (ex: /voatelier), mantenha assim.
// Se estiver na raiz do site, deixe como ''.
define('BASE_URL', '/voatelier');

// ---------------------------------------------------------------------
// Upload de arquivos
// ---------------------------------------------------------------------
define('UPLOAD_MAX_SIZE', 8 * 1024 * 1024); // 8 MB
define('UPLOAD_ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf']);
define('UPLOAD_ALLOWED_MIME', [
    'image/jpeg',
    'image/png',
    'application/pdf',
]);

// ---------------------------------------------------------------------
// Segurança de sessão / login
// ---------------------------------------------------------------------
define('SESSION_NAME', 'voatelier_session');
define('SESSION_LIFETIME', 60 * 60 * 8); // 8 horas
define('LOGIN_MAX_TENTATIVAS', 5);
define('LOGIN_BLOQUEIO_MINUTOS', 15);

// ---------------------------------------------------------------------
// Exibição de erros conforme o ambiente
// ---------------------------------------------------------------------
if (APP_ENV === 'development') {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(E_ALL);
}

// Log de erros sempre ativo, independente do ambiente
ini_set('log_errors', '1');
ini_set('error_log', STORAGE_PATH . '/logs/php-errors.log');

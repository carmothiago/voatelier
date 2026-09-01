<?php

use App\Core\Auth;

/**
 * Escapa dados para saída segura em HTML (proteção contra XSS).
 * Use sempre que exibir dados vindos do usuário ou do banco nas views.
 */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Monta uma URL absoluta dentro do sistema, prefixando com BASE_URL.
 */
function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

/**
 * Formata valores monetários no padrão brasileiro (R$ 1.234,56).
 */
function formatarMoeda(float $valor): string
{
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

/**
 * Formata datas do formato do banco (Y-m-d ou Y-m-d H:i:s) para dd/mm/aaaa.
 */
function formatarData(?string $data, bool $comHora = false): string
{
    if (empty($data)) {
        return '-';
    }

    $timestamp = strtotime($data);
    return $comHora ? date('d/m/Y H:i', $timestamp) : date('d/m/Y', $timestamp);
}

/**
 * Retorna uma mensagem flash gravada na sessão e a remove em seguida.
 * Uso típico: definir com $_SESSION['flash'] = ['tipo' => 'sucesso', 'mensagem' => '...'];
 */
function flash(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

function setFlash(string $tipo, string $mensagem): void
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'mensagem' => $mensagem];
}

/**
 * Atalho para verificação de permissão nas views (ex: esconder botões).
 */
function podeAcessar(string $permissao): bool
{
    return Auth::can($permissao);
}

/**
 * Valida e armazena um upload de arquivo (foto ou PDF) com segurança:
 * checa extensão, MIME real e tamanho, e sempre renomeia o arquivo
 * (nunca confia no nome original enviado pelo navegador).
 *
 * @param array $arquivo Um item de $_FILES, ex: $_FILES['foto']
 * @param string $subpastaUploads Nome da subpasta dentro de uploads/ (ex: 'provas')
 * @return array{ok: bool, erro?: string, nome_arquivo?: string, nome_original?: string, tamanho?: int}
 */
function armazenarUpload(array $arquivo, string $subpastaUploads): array
{
    if (empty($arquivo['name']) || $arquivo['error'] === UPLOAD_ERR_NO_FILE) {
        return ['ok' => false, 'erro' => 'Nenhum arquivo selecionado.'];
    }

    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'erro' => 'Falha ao enviar o arquivo. Tente novamente.'];
    }

    if ($arquivo['size'] > UPLOAD_MAX_SIZE) {
        $limiteMb = round(UPLOAD_MAX_SIZE / 1024 / 1024, 1);
        return ['ok' => false, 'erro' => "Arquivo maior que o limite permitido ({$limiteMb} MB)."];
    }

    $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
    if (!in_array($extensao, UPLOAD_ALLOWED_EXTENSIONS, true)) {
        return ['ok' => false, 'erro' => 'Tipo de arquivo não permitido. Use JPG, PNG ou PDF.'];
    }

    // Verifica o MIME real do conteúdo (não confia na extensão nem no Content-Type enviado)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeReal = finfo_file($finfo, $arquivo['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeReal, UPLOAD_ALLOWED_MIME, true)) {
        return ['ok' => false, 'erro' => 'O conteúdo do arquivo não corresponde a um tipo permitido.'];
    }

    $pastaDestino = UPLOADS_PATH . '/' . trim($subpastaUploads, '/');
    if (!is_dir($pastaDestino)) {
        mkdir($pastaDestino, 0755, true);
    }

    // Nome sempre gerado pelo sistema — nunca reaproveita o nome original enviado.
    $nomeGerado = bin2hex(random_bytes(16)) . '.' . $extensao;
    $caminhoCompleto = $pastaDestino . '/' . $nomeGerado;

    if (!move_uploaded_file($arquivo['tmp_name'], $caminhoCompleto)) {
        return ['ok' => false, 'erro' => 'Não foi possível salvar o arquivo no servidor.'];
    }

    return [
        'ok'            => true,
        'nome_arquivo'  => $nomeGerado,
        'nome_original' => basename($arquivo['name']),
        'tamanho'       => (int) $arquivo['size'],
    ];
}
/**
 * Formata um tamanho em bytes para uma string legível (KB/MB).
 */
function formatarTamanho(int $bytes): string
{
    if ($bytes < 1024) {
        return $bytes . ' B';
    }
    if ($bytes < 1024 * 1024) {
        return round($bytes / 1024, 1) . ' KB';
    }
    return round($bytes / 1024 / 1024, 1) . ' MB';
}

function registrarAuditoria(string $modulo, string $acao, ?string $registroAfetado = null, ?array $dadosAnteriores = null, ?array $dadosNovos = null): void
{
    try {
        $db = \App\Core\Database::getConnection();
        $stmt = $db->prepare(
            'INSERT INTO auditoria (usuario_id, ip, modulo, acao, registro_afetado, dados_anteriores, dados_novos)
             VALUES (:usuario_id, :ip, :modulo, :acao, :registro_afetado, :dados_anteriores, :dados_novos)'
        );

        $stmt->execute([
            'usuario_id'        => Auth::id(),
            'ip'                => Auth::clientIp(),
            'modulo'            => $modulo,
            'acao'              => $acao,
            'registro_afetado'  => $registroAfetado,
            'dados_anteriores'  => $dadosAnteriores ? json_encode($dadosAnteriores, JSON_UNESCAPED_UNICODE) : null,
            'dados_novos'       => $dadosNovos ? json_encode($dadosNovos, JSON_UNESCAPED_UNICODE) : null,
        ]);
    } catch (\Throwable $e) {
        // Falha ao auditar nunca deve interromper a operação principal.
        error_log('Falha ao registrar auditoria: ' . $e->getMessage());
    }
}

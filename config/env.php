<?php

/**
 * Carrega as variáveis do arquivo .env na raiz do projeto.
 *
 * Parser mínimo, sem dependências externas:
 * - ignora linhas em branco e comentários (#)
 * - suporta valores com espaços se delimitados por aspas simples ou duplas
 * - define as variáveis via putenv() e $_ENV para compatibilidade máxima
 *
 * Deve ser chamado UMA única vez, no início do bootstrap (public/index.php),
 * antes de qualquer outro require.
 */
function carregarEnv(string $caminho): void
{
    if (!file_exists($caminho)) {
        // Em produção, .env ausente é erro crítico — não deixa o sistema subir.
        http_response_code(500);
        error_log('.env não encontrado em: ' . $caminho);
        exit('Erro de configuração do servidor. Contate o administrador.');
    }

    $linhas = file($caminho, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($linhas as $linha) {
        // Ignora comentários
        if (str_starts_with(ltrim($linha), '#')) {
            continue;
        }

        // Espera o formato CHAVE=VALOR
        if (!str_contains($linha, '=')) {
            continue;
        }

        [$chave, $valor] = explode('=', $linha, 2);

        $chave = trim($chave);
        $valor = trim($valor);

        // Remove aspas simples ou duplas que envolvam o valor inteiro
        if (
            strlen($valor) >= 2 &&
            (
                (str_starts_with($valor, '"') && str_ends_with($valor, '"')) ||
                (str_starts_with($valor, "'") && str_ends_with($valor, "'"))
            )
        ) {
            $valor = substr($valor, 1, -1);
        }

        // Só define se ainda não estiver no ambiente (evita sobrescrever vars do sistema)
        if (!array_key_exists($chave, $_ENV) && getenv($chave) === false) {
            putenv("{$chave}={$valor}");
            $_ENV[$chave] = $valor;
        }
    }
}

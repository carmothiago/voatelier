<?php

namespace App\Core;

/**
 * Rate limiting de tentativas de login por endereço IP.
 *
 * Usa a tabela `login_tentativas_ip` no banco de dados — sem dependências
 * externas, sem arquivos de estado, compatível com XAMPP.
 *
 * Fluxo de uso no login:
 *   1. RateLimiter::verificar($ip)  → se bloqueado, retorna mensagem de erro
 *   2. Auth::attempt(...)           → valida usuário/senha
 *   3a. Falha  → RateLimiter::registrarFalha($ip)
 *   3b. Sucesso → RateLimiter::liberar($ip)
 */
class RateLimiter
{
    /**
     * Verifica se o IP está bloqueado no momento.
     *
     * @return array{bloqueado: bool, erro: string|null, minutosRestantes: int}
     */
    public static function verificar(string $ip): array
    {
        $registro = self::buscar($ip);

        if (!$registro) {
            return ['bloqueado' => false, 'erro' => null, 'minutosRestantes' => 0];
        }

        if (!empty($registro['bloqueado_ate']) && strtotime($registro['bloqueado_ate']) > time()) {
            $minutos = (int) ceil((strtotime($registro['bloqueado_ate']) - time()) / 60);
            return [
                'bloqueado'       => true,
                'erro'            => "Muitas tentativas de login a partir deste endereço. Tente novamente em {$minutos} minuto(s).",
                'minutosRestantes'=> $minutos,
            ];
        }

        return ['bloqueado' => false, 'erro' => null, 'minutosRestantes' => 0];
    }

    /**
     * Registra uma tentativa de login falha para o IP.
     * Se atingir o limite, define bloqueado_ate.
     */
    public static function registrarFalha(string $ip): void
    {
        $db = Database::getConnection();

        // Upsert: cria o registro ou incrementa o contador existente
        $stmt = $db->prepare(
            'INSERT INTO login_tentativas_ip (ip, tentativas, bloqueado_ate)
             VALUES (:ip, 1, NULL)
             ON DUPLICATE KEY UPDATE
                 tentativas    = tentativas + 1,
                 bloqueado_ate = bloqueado_ate'  // mantém valor atual por enquanto
        );
        $stmt->execute(['ip' => $ip]);

        // Lê o contador atualizado
        $registro = self::buscar($ip);
        if (!$registro) {
            return;
        }

        $tentativas = (int) $registro['tentativas'];

        // Se atingiu o limite e ainda não está bloqueado, aplica o bloqueio
        if ($tentativas >= LOGIN_IP_MAX_TENTATIVAS && empty($registro['bloqueado_ate'])) {
            $bloqueadoAte = date('Y-m-d H:i:s', time() + LOGIN_IP_BLOQUEIO_MINUTOS * 60);
            $db->prepare(
                'UPDATE login_tentativas_ip
                 SET bloqueado_ate = :bloqueado_ate, tentativas = 0
                 WHERE ip = :ip'
            )->execute(['bloqueado_ate' => $bloqueadoAte, 'ip' => $ip]);
        }
    }

    /**
     * Reseta o contador do IP após um login bem-sucedido.
     */
    public static function liberar(string $ip): void
    {
        Database::getConnection()
            ->prepare('DELETE FROM login_tentativas_ip WHERE ip = :ip')
            ->execute(['ip' => $ip]);
    }

    /**
     * Retorna o registro de tentativas do IP ou null se não existir.
     */
    private static function buscar(string $ip): array|null
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT * FROM login_tentativas_ip WHERE ip = :ip LIMIT 1'
        );
        $stmt->execute(['ip' => $ip]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}

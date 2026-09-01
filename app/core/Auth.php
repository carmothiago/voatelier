<?php

namespace App\Core;

use App\Models\Usuario;

/**
 * Autenticação e controle de acesso baseado em sessão PHP.
 *
 * Guarda em sessão apenas o essencial (id, nome, usuário, perfil e o
 * conjunto de permissões "modulo.acao" já resolvido), evitando consultas
 * repetidas ao banco em cada requisição.
 */
class Auth
{
    public static function check(): bool
    {
        return !empty($_SESSION['usuario_id']);
    }

    public static function id(): ?int
    {
        return $_SESSION['usuario_id'] ?? null;
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        return [
            'id'      => $_SESSION['usuario_id'],
            'nome'    => $_SESSION['usuario_nome'],
            'usuario' => $_SESSION['usuario_login'],
            'perfil'  => $_SESSION['usuario_perfil'],
        ];
    }

    /**
     * Verifica se o usuário logado possui a permissão informada.
     * Formato esperado: "modulo.acao", ex: "clientes.criar".
     * Administradores possuem acesso irrestrito.
     */
    public static function can(string $permissao): bool
    {
        if (!self::check()) {
            return false;
        }

        if (($_SESSION['usuario_perfil'] ?? null) === 'administrador') {
            return true;
        }

        $permissoes = $_SESSION['usuario_permissoes'] ?? [];
        return in_array($permissao, $permissoes, true);
    }

    /**
     * Efetua o login: valida usuário/senha, checa bloqueio por tentativas,
     * regenera o ID de sessão e carrega as permissões do perfil.
     *
     * Retorna um array ['ok' => bool, 'erro' => string|null, 'usuario' => array|null]
     */
    public static function attempt(string $login, string $senha): array
    {
        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->findByLogin($login);

        if (!$usuario) {
            // Mensagem genérica: nunca revelar se o usuário existe ou não.
            return ['ok' => false, 'erro' => 'Usuário ou senha inválidos.'];
        }

        if ($usuario['status'] !== 'ativo') {
            return ['ok' => false, 'erro' => 'Este usuário está inativo. Procure o administrador.'];
        }

        if (!empty($usuario['bloqueado_ate']) && strtotime($usuario['bloqueado_ate']) > time()) {
            $minutos = ceil((strtotime($usuario['bloqueado_ate']) - time()) / 60);
            return ['ok' => false, 'erro' => "Usuário temporariamente bloqueado. Tente novamente em {$minutos} minuto(s)."];
        }

        if (!password_verify($senha, $usuario['senha_hash'])) {
            $usuarioModel->registrarTentativaFalha((int) $usuario['id']);
            return ['ok' => false, 'erro' => 'Usuário ou senha inválidos.'];
        }

        // Login correto: reseta tentativas, atualiza último acesso
        $usuarioModel->registrarLoginSucesso((int) $usuario['id'], self::clientIp());

        // Regenera o ID de sessão para prevenir fixation após autenticação
        session_regenerate_id(true);

        $permissoes = $usuarioModel->listarPermissoesDoPerfil((int) $usuario['perfil_id']);

        $_SESSION['usuario_id']          = (int) $usuario['id'];
        $_SESSION['usuario_nome']        = $usuario['nome'];
        $_SESSION['usuario_login']       = $usuario['usuario'];
        $_SESSION['usuario_perfil']      = $usuario['perfil_slug'];
        $_SESSION['usuario_permissoes']  = $permissoes;
        $_SESSION['precisa_trocar_senha'] = (bool) $usuario['precisa_trocar_senha'];

        return ['ok' => true, 'erro' => null, 'usuario' => $usuario];
    }

    public static function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    public static function precisaTrocarSenha(): bool
    {
        return !empty($_SESSION['precisa_trocar_senha']);
    }

    public static function clientIp(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? 'desconhecido';
    }
}

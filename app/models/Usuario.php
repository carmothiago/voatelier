<?php

namespace App\Models;

use App\Core\Model;

class Usuario extends Model
{
    protected string $table = 'usuarios';

    /**
     * Campos que podem ser escritos via formulário de criação/edição.
     * senha_hash é tratada separadamente — nunca passa por aqui.
     */
    public const CAMPOS_FORMULARIO = [
        'perfil_id', 'nome', 'usuario', 'email', 'status', 'precisa_trocar_senha',
    ];

    // ------------------------------------------------------------------
    // Consultas para a interface de gerenciamento
    // ------------------------------------------------------------------

    /**
     * Lista todos os usuários com o nome do perfil, com filtro opcional.
     */
    public function listarTodos(?string $busca = null): array
    {
        $sql = 'SELECT u.*, p.nome AS perfil_nome, p.slug AS perfil_slug
                FROM usuarios u
                INNER JOIN perfis p ON p.id = u.perfil_id';
        $params = [];

        if (!empty($busca)) {
            $sql .= ' WHERE u.nome LIKE :busca1 OR u.usuario LIKE :busca2 OR u.email LIKE :busca3';
            $termo = '%' . $busca . '%';
            $params = ['busca1' => $termo, 'busca2' => $termo, 'busca3' => $termo];
        }

        $sql .= ' ORDER BY u.nome ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Busca um usuário pelo id, trazendo nome e slug do perfil.
     */
    public function findComPerfil(int $id): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT u.*, p.nome AS perfil_nome, p.slug AS perfil_slug
             FROM usuarios u
             INNER JOIN perfis p ON p.id = u.perfil_id
             WHERE u.id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Marca o usuário como inativo (exclusão lógica).
     * Usa a coluna status pois usuarios não tem coluna ativo.
     */
    public function excluirLogicamente(int $id): bool
    {
        return $this->update($id, ['status' => 'inativo']);
    }

    /**
     * Verifica se o login já está em uso por outro usuário.
     */
    public function loginEmUso(string $login, ?int $ignorarId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM usuarios WHERE usuario = :login';
        $params = ['login' => $login];

        if ($ignorarId !== null) {
            $sql .= ' AND id != :id';
            $params['id'] = $ignorarId;
        }

        return (int) $this->db->prepare($sql)->execute($params) &&
               (int) $this->db->query("SELECT COUNT(*) FROM usuarios WHERE usuario = " . $this->db->quote($login) . ($ignorarId ? " AND id != {$ignorarId}" : ''))->fetchColumn() > 0;
    }

    /**
     * Verifica se o e-mail já está em uso por outro usuário.
     */
    public function emailEmUso(string $email, ?int $ignorarId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM usuarios WHERE email = :email';
        $params = ['email' => $email];

        if ($ignorarId !== null) {
            $sql .= ' AND id != :id';
            $params['id'] = $ignorarId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Verifica se o login já está em uso — versão correta com prepared statement.
     */
    public function loginJaExiste(string $login, ?int $ignorarId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM usuarios WHERE usuario = :login';
        $params = ['login' => $login];

        if ($ignorarId !== null) {
            $sql .= ' AND id != :id';
            $params['id'] = $ignorarId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    // ------------------------------------------------------------------
    // Métodos usados pelo sistema de autenticação (Auth.php)
    // ------------------------------------------------------------------

    /**
     * Busca um usuário pelo login, já trazendo o slug do perfil.
     */
    public function findByLogin(string $login): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT u.*, p.slug AS perfil_slug, p.nome AS perfil_nome
             FROM usuarios u
             INNER JOIN perfis p ON p.id = u.perfil_id
             WHERE u.usuario = :login
             LIMIT 1'
        );
        $stmt->execute(['login' => $login]);
        return $stmt->fetch();
    }

    /**
     * Retorna a lista de permissões ("modulo.acao") associadas ao perfil.
     */
    public function listarPermissoesDoPerfil(int $perfilId): array
    {
        $stmt = $this->db->prepare(
            "SELECT CONCAT(perm.modulo, '.', perm.acao) AS permissao
             FROM perfis_permissoes pp
             INNER JOIN permissoes perm ON perm.id = pp.permissao_id
             WHERE pp.perfil_id = :perfil_id"
        );
        $stmt->execute(['perfil_id' => $perfilId]);

        return array_column($stmt->fetchAll(), 'permissao');
    }

    public function registrarTentativaFalha(int $usuarioId): void
    {
        $usuario = $this->find($usuarioId);
        if (!$usuario) {
            return;
        }

        $tentativas = (int) $usuario['tentativas_login'] + 1;
        $dados = ['tentativas_login' => $tentativas];

        if ($tentativas >= LOGIN_MAX_TENTATIVAS) {
            $dados['bloqueado_ate'] = date('Y-m-d H:i:s', time() + (LOGIN_BLOQUEIO_MINUTOS * 60));
            $dados['tentativas_login'] = 0;
        }

        $this->update($usuarioId, $dados);
    }

    public function registrarLoginSucesso(int $usuarioId, string $ip): void
    {
        $this->update($usuarioId, [
            'tentativas_login' => 0,
            'bloqueado_ate'    => null,
            'ultimo_login'     => date('Y-m-d H:i:s'),
            'ultimo_ip'        => $ip,
        ]);
    }

    public function trocarSenha(int $usuarioId, string $novaSenha): void
    {
        $hash = password_hash($novaSenha, PASSWORD_DEFAULT);
        $this->update($usuarioId, [
            'senha_hash'           => $hash,
            'precisa_trocar_senha' => 0,
        ]);
    }
}

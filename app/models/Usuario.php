<?php

namespace App\Models;

use App\Core\Model;

class Usuario extends Model
{
    protected string $table = 'usuarios';

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

<?php

namespace App\Models;

use App\Core\Model;

class Producao extends Model
{
    protected string $table = 'producao';

    public const ETAPAS = [
        'projeto'      => 'Projeto',
        'desenho'      => 'Desenho',
        'aprovacao'    => 'Aprovação',
        'modelagem'    => 'Modelagem',
        'corte'        => 'Corte',
        'costura'      => 'Costura',
        'bordado'      => 'Bordado',
        'ajustes'      => 'Ajustes',
        'acabamento'   => 'Acabamento',
        'finalizacao'  => 'Finalização',
        'entrega'      => 'Entrega',
    ];

    public function listarParaKanban(): array
    {
        $stmt = $this->db->query(
            "SELECT pr.*, c.nome_completo AS cliente_nome, v.nome AS vestido_nome, v.codigo AS vestido_codigo, u.nome AS responsavel_nome
             FROM producao pr
             INNER JOIN clientes c ON c.id = pr.cliente_id
             LEFT JOIN vestidos v ON v.id = pr.vestido_id
             LEFT JOIN usuarios u ON u.id = pr.responsavel_id
             WHERE pr.etapa != 'entrega'
             ORDER BY pr.prazo IS NULL, pr.prazo ASC"
        );
        return $stmt->fetchAll();
    }

    public function buscarCompleta(int $id): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT pr.*, c.nome_completo AS cliente_nome, v.nome AS vestido_nome, v.codigo AS vestido_codigo, u.nome AS responsavel_nome
             FROM producao pr
             INNER JOIN clientes c ON c.id = pr.cliente_id
             LEFT JOIN vestidos v ON v.id = pr.vestido_id
             LEFT JOIN usuarios u ON u.id = pr.responsavel_id
             WHERE pr.id = :id"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function moverEtapa(int $producaoId, string $novaEtapa, ?int $usuarioId, ?string $observacao = null): bool
    {
        $producao = $this->find($producaoId);
        if (!$producao) {
            return false;
        }

        $ok = $this->update($producaoId, ['etapa' => $novaEtapa]);

        if ($ok) {
            $stmt = $this->db->prepare(
                'INSERT INTO producao_historico (producao_id, usuario_id, etapa_anterior, etapa_nova, observacao)
                 VALUES (:producao_id, :usuario_id, :etapa_anterior, :etapa_nova, :observacao)'
            );
            $stmt->execute([
                'producao_id'    => $producaoId,
                'usuario_id'     => $usuarioId,
                'etapa_anterior' => $producao['etapa'],
                'etapa_nova'     => $novaEtapa,
                'observacao'     => $observacao,
            ]);
        }

        return $ok;
    }

    public function historico(int $producaoId): array
    {
        $stmt = $this->db->prepare(
            'SELECT ph.*, u.nome AS usuario_nome
             FROM producao_historico ph
             LEFT JOIN usuarios u ON u.id = ph.usuario_id
             WHERE ph.producao_id = :producao_id
             ORDER BY ph.created_at DESC'
        );
        $stmt->execute(['producao_id' => $producaoId]);
        return $stmt->fetchAll();
    }

    public function contarEmProducao(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM producao WHERE etapa != 'entrega'");
        return (int) $stmt->fetch()['total'];
    }

    public function contarAtrasados(): int
    {
        $stmt = $this->db->query(
            "SELECT COUNT(*) AS total FROM producao WHERE etapa != 'entrega' AND prazo IS NOT NULL AND prazo < CURDATE()"
        );
        return (int) $stmt->fetch()['total'];
    }

    public function contarProximosDoPrazo(int $dias = 7): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS total FROM producao
             WHERE etapa != 'entrega' AND prazo IS NOT NULL
               AND prazo BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :dias DAY)"
        );
        $stmt->execute(['dias' => $dias]);
        return (int) $stmt->fetch()['total'];
    }

    public function listarAtrasados(): array
    {
        $stmt = $this->db->query(
            "SELECT pr.*, c.nome_completo AS cliente_nome
             FROM producao pr
             INNER JOIN clientes c ON c.id = pr.cliente_id
             WHERE pr.etapa != 'entrega' AND pr.prazo IS NOT NULL AND pr.prazo < CURDATE()
             ORDER BY pr.prazo ASC"
        );
        return $stmt->fetchAll();
    }
}

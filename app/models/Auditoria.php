<?php

namespace App\Models;

use App\Core\Model;

class Auditoria extends Model
{
    protected string $table = 'auditoria';

    /**
     * Lista registros de auditoria com filtros opcionais, mais recentes primeiro.
     * Sempre limitado para não sobrecarregar a tela em bancos muito grandes.
     */
    public function listarComFiltros(array $filtros = [], int $limite = 200): array
    {
        $sql = "SELECT a.*, u.nome AS usuario_nome
                FROM auditoria a
                LEFT JOIN usuarios u ON u.id = a.usuario_id
                WHERE 1 = 1";
        $params = [];

        if (!empty($filtros['modulo'])) {
            $sql .= ' AND a.modulo = :modulo';
            $params['modulo'] = $filtros['modulo'];
        }

        if (!empty($filtros['usuario_id'])) {
            $sql .= ' AND a.usuario_id = :usuario_id';
            $params['usuario_id'] = $filtros['usuario_id'];
        }

        if (!empty($filtros['data_de'])) {
            $sql .= ' AND a.data_hora >= :data_de';
            $params['data_de'] = $filtros['data_de'] . ' 00:00:00';
        }

        if (!empty($filtros['data_ate'])) {
            $sql .= ' AND a.data_hora <= :data_ate';
            $params['data_ate'] = $filtros['data_ate'] . ' 23:59:59';
        }

        if (!empty($filtros['busca'])) {
            $sql .= ' AND (a.registro_afetado LIKE :busca OR a.acao LIKE :busca2)';
            $params['busca'] = '%' . $filtros['busca'] . '%';
            $params['busca2'] = '%' . $filtros['busca'] . '%';
        }

        $sql .= ' ORDER BY a.data_hora DESC LIMIT ' . (int) $limite;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Lista os módulos distintos já registrados, para alimentar o filtro.
     */
    public function listarModulosDistintos(): array
    {
        $stmt = $this->db->query('SELECT DISTINCT modulo FROM auditoria ORDER BY modulo ASC');
        return array_column($stmt->fetchAll(), 'modulo');
    }

    public function contarTotal(): int
    {
        $stmt = $this->db->query('SELECT COUNT(*) AS total FROM auditoria');
        return (int) $stmt->fetch()['total'];
    }
}

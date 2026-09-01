<?php

namespace App\Models;

use App\Core\Model;

class ContaReceber extends Model
{
    protected string $table = 'contas_receber';

    public const STATUS = [
        'pendente'  => 'Pendente',
        'pago'      => 'Pago',
        'cancelado' => 'Cancelado',
    ];

    public function listarComCliente(?string $filtro = null): array
    {
        $sql = "SELECT cr.*, c.nome_completo AS cliente_nome,
                       (cr.status = 'pendente' AND cr.vencimento < CURDATE()) AS vencido
                FROM contas_receber cr
                INNER JOIN clientes c ON c.id = cr.cliente_id";
        $params = [];

        if ($filtro === 'pendente') {
            $sql .= " WHERE cr.status = 'pendente'";
        } elseif ($filtro === 'vencido') {
            $sql .= " WHERE cr.status = 'pendente' AND cr.vencimento < CURDATE()";
        } elseif ($filtro === 'pago') {
            $sql .= " WHERE cr.status = 'pago'";
        }

        $sql .= ' ORDER BY cr.vencimento ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function listarPorCliente(int $clienteId): array
    {
        $stmt = $this->db->prepare(
            "SELECT *, (status = 'pendente' AND vencimento < CURDATE()) AS vencido
             FROM contas_receber WHERE cliente_id = :cliente_id ORDER BY vencimento ASC"
        );
        $stmt->execute(['cliente_id' => $clienteId]);
        return $stmt->fetchAll();
    }

    public function marcarComoPago(int $id, string $dataPagamento): bool
    {
        return $this->update($id, ['status' => 'pago', 'data_pagamento' => $dataPagamento]);
    }

    public function totalReceitasNoMes(): float
    {
        $stmt = $this->db->query(
            "SELECT COALESCE(SUM(valor), 0) AS total FROM contas_receber
             WHERE status = 'pago' AND MONTH(data_pagamento) = MONTH(CURDATE()) AND YEAR(data_pagamento) = YEAR(CURDATE())"
        );
        return (float) $stmt->fetch()['total'];
    }

    public function totalAReceber(): float
    {
        $stmt = $this->db->query("SELECT COALESCE(SUM(valor), 0) AS total FROM contas_receber WHERE status = 'pendente'");
        return (float) $stmt->fetch()['total'];
    }

    public function totalVencido(): float
    {
        $stmt = $this->db->query(
            "SELECT COALESCE(SUM(valor), 0) AS total FROM contas_receber WHERE status = 'pendente' AND vencimento < CURDATE()"
        );
        return (float) $stmt->fetch()['total'];
    }

    public function contarVencidas(): int
    {
        $stmt = $this->db->query(
            "SELECT COUNT(*) AS total FROM contas_receber WHERE status = 'pendente' AND vencimento < CURDATE()"
        );
        return (int) $stmt->fetch()['total'];
    }
}

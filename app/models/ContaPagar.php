<?php

namespace App\Models;

use App\Core\Model;

class ContaPagar extends Model
{
    protected string $table = 'contas_pagar';

    public const STATUS = [
        'pendente'  => 'Pendente',
        'pago'      => 'Pago',
        'cancelado' => 'Cancelado',
    ];

    public function listarComFornecedor(?string $filtro = null): array
    {
        $sql = "SELECT cp.*, f.nome AS fornecedor_nome,
                       (cp.status = 'pendente' AND cp.vencimento < CURDATE()) AS vencido
                FROM contas_pagar cp
                LEFT JOIN fornecedores f ON f.id = cp.fornecedor_id";

        if ($filtro === 'pendente') {
            $sql .= " WHERE cp.status = 'pendente'";
        } elseif ($filtro === 'vencido') {
            $sql .= " WHERE cp.status = 'pendente' AND cp.vencimento < CURDATE()";
        } elseif ($filtro === 'pago') {
            $sql .= " WHERE cp.status = 'pago'";
        }

        $sql .= ' ORDER BY cp.vencimento ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function marcarComoPago(int $id, string $dataPagamento): bool
    {
        return $this->update($id, ['status' => 'pago', 'data_pagamento' => $dataPagamento]);
    }

    public function totalDespesasNoMes(): float
    {
        $stmt = $this->db->query(
            "SELECT COALESCE(SUM(valor), 0) AS total FROM contas_pagar
             WHERE status = 'pago' AND MONTH(data_pagamento) = MONTH(CURDATE()) AND YEAR(data_pagamento) = YEAR(CURDATE())"
        );
        return (float) $stmt->fetch()['total'];
    }

    public function totalAPagar(): float
    {
        $stmt = $this->db->query("SELECT COALESCE(SUM(valor), 0) AS total FROM contas_pagar WHERE status = 'pendente'");
        return (float) $stmt->fetch()['total'];
    }

    public function totalVencido(): float
    {
        $stmt = $this->db->query(
            "SELECT COALESCE(SUM(valor), 0) AS total FROM contas_pagar WHERE status = 'pendente' AND vencimento < CURDATE()"
        );
        return (float) $stmt->fetch()['total'];
    }

    public function contarVencidas(): int
    {
        $stmt = $this->db->query(
            "SELECT COUNT(*) AS total FROM contas_pagar WHERE status = 'pendente' AND vencimento < CURDATE()"
        );
        return (int) $stmt->fetch()['total'];
    }
}

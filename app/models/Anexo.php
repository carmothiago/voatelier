<?php

namespace App\Models;

use App\Core\Model;

class Anexo extends Model
{
    protected string $table = 'anexos';

    public function listarPorCliente(int $clienteId): array
    {
        $stmt = $this->db->prepare(
            'SELECT a.*, u.nome AS usuario_nome
             FROM anexos a
             LEFT JOIN usuarios u ON u.id = a.criado_por
             WHERE a.cliente_id = :cliente_id
             ORDER BY a.created_at DESC'
        );
        $stmt->execute(['cliente_id' => $clienteId]);
        return $stmt->fetchAll();
    }
}

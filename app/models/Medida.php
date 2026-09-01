<?php

namespace App\Models;

use App\Core\Model;

class Medida extends Model
{
    protected string $table = 'medidas';

    public const CAMPOS = [
        'busto', 'cintura', 'quadril', 'altura', 'ombro', 'braco',
        'biceps', 'punho', 'comprimento_frente', 'comprimento_costas', 'decote',
    ];

    public const LABELS = [
        'busto'               => 'Busto',
        'cintura'             => 'Cintura',
        'quadril'             => 'Quadril',
        'altura'              => 'Altura',
        'ombro'               => 'Ombro',
        'braco'               => 'Braço',
        'biceps'              => 'Bíceps',
        'punho'               => 'Punho',
        'comprimento_frente'  => 'Comprimento frente',
        'comprimento_costas'  => 'Comprimento costas',
        'decote'              => 'Decote',
    ];

    /**
     * Lista o histórico de medidas de uma cliente, mais recente primeiro.
     */
    public function historicoDaCliente(int $clienteId): array
    {
        $stmt = $this->db->prepare(
            'SELECT m.*, u.nome AS usuario_nome
             FROM medidas m
             LEFT JOIN usuarios u ON u.id = m.usuario_id
             WHERE m.cliente_id = :cliente_id
             ORDER BY m.created_at DESC'
        );
        $stmt->execute(['cliente_id' => $clienteId]);
        return $stmt->fetchAll();
    }

    public function ultimaDaCliente(int $clienteId): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM medidas WHERE cliente_id = :cliente_id ORDER BY created_at DESC LIMIT 1'
        );
        $stmt->execute(['cliente_id' => $clienteId]);
        return $stmt->fetch();
    }

    // Nenhum método update()/delete() é exposto propositalmente:
    // medidas nunca são sobrescritas, apenas novos registros são inseridos.
}

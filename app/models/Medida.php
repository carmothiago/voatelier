<?php

namespace App\Models;

use App\Core\Model;

class Medida extends Model
{
    protected string $table = 'medidas';

    /**
     * Campos fixos originais — mantidos para retrocompatibilidade com
     * registros históricos gravados antes do sistema de campos dinâmicos.
     * Novos registros usam medidas_valores.
     */
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
     * Lista o histórico de medidas de uma cliente, enriquecendo cada
     * registro com os valores dinâmicos de medidas_valores.
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
        $medidas = $stmt->fetchAll();

        if (empty($medidas)) {
            return [];
        }

        // Carrega valores dinâmicos de todas as fichas de uma vez (1 query)
        $ids = array_column($medidas, 'id');
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $stmt = $this->db->prepare(
            "SELECT mv.medida_id, mc.slug, mc.label, mv.valor
             FROM medidas_valores mv
             INNER JOIN medidas_campos mc ON mc.id = mv.campo_id
             WHERE mv.medida_id IN ({$placeholders})
             ORDER BY mc.ordem ASC"
        );
        $stmt->execute($ids);
        $valoresDinamicos = $stmt->fetchAll();

        // Agrupa por medida_id para merge eficiente
        $porMedida = [];
        foreach ($valoresDinamicos as $v) {
            $porMedida[$v['medida_id']][$v['slug']] = [
                'label' => $v['label'],
                'valor' => $v['valor'],
            ];
        }

        foreach ($medidas as &$m) {
            $m['_dinamicos'] = $porMedida[$m['id']] ?? [];
        }
        unset($m);

        return $medidas;
    }

    public function ultimaDaCliente(int $clienteId): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM medidas WHERE cliente_id = :cliente_id ORDER BY created_at DESC LIMIT 1'
        );
        $stmt->execute(['cliente_id' => $clienteId]);
        return $stmt->fetch();
    }

    /**
     * Insere uma nova ficha de medidas e, em seguida, persiste os valores
     * dinâmicos na tabela medidas_valores.
     *
     * @param array $dadosFixos   Colunas da tabela medidas (cliente_id, usuario_id, campos legados, observacoes)
     * @param array $dadosDinamicos  ['slug' => valor_decimal, ...] — apenas slugs ativos
     * @param array $camposAtivos    Resultado de MedidaCampo::camposAtivos() passado pelo controller
     */
    public function inserirComDinamicos(array $dadosFixos, array $dadosDinamicos, array $camposAtivos): int
    {
        $medidaId = $this->insert($dadosFixos);

        if (empty($dadosDinamicos) || empty($camposAtivos)) {
            return $medidaId;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO medidas_valores (medida_id, campo_id, valor) VALUES (:medida_id, :campo_id, :valor)
             ON DUPLICATE KEY UPDATE valor = VALUES(valor)'
        );

        foreach ($camposAtivos as $slug => $campo) {
            if (!array_key_exists($slug, $dadosDinamicos)) {
                continue;
            }
            $valor = $dadosDinamicos[$slug];
            if ($valor === null || $valor === '') {
                continue;
            }
            $stmt->execute([
                'medida_id' => $medidaId,
                'campo_id'  => (int) $campo['id'],
                'valor'     => $valor,
            ]);
        }

        return $medidaId;
    }

    // Nenhum método update()/delete() é exposto propositalmente:
    // medidas nunca são sobrescritas, apenas novos registros são inseridos.
}

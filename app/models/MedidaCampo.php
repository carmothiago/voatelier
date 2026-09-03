<?php

namespace App\Models;

use App\Core\Model;

/**
 * Gerencia o catálogo de campos de medida configuráveis pela interface.
 * Cada campo tem um slug técnico (ex: "colo"), um label exibido e uma ordem.
 */
class MedidaCampo extends Model
{
    protected string $table = 'medidas_campos';

    /**
     * Retorna todos os campos ativos, ordenados para exibição no formulário.
     * Retorna array indexado por slug para facilitar lookup nas views.
     */
    public function camposAtivos(): array
    {
        $stmt = $this->db->query(
            'SELECT * FROM medidas_campos WHERE ativo = 1 ORDER BY ordem ASC, id ASC'
        );
        $rows = $stmt->fetchAll();

        $indexado = [];
        foreach ($rows as $row) {
            $indexado[$row['slug']] = $row;
        }
        return $indexado;
    }

    /**
     * Retorna todos os campos (ativos e inativos) para a tela de configuração.
     */
    public function todos(): array
    {
        $stmt = $this->db->query(
            'SELECT mc.*, u.nome AS criador_nome
             FROM medidas_campos mc
             LEFT JOIN usuarios u ON u.id = mc.criado_por
             ORDER BY mc.ordem ASC, mc.id ASC'
        );
        return $stmt->fetchAll();
    }

    /**
     * Verifica se já existe um campo com este slug.
     */
    public function slugEmUso(string $slug, ?int $ignorarId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM medidas_campos WHERE slug = :slug';
        $params = ['slug' => $slug];

        if ($ignorarId !== null) {
            $sql .= ' AND id != :id';
            $params['id'] = $ignorarId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Inverte o estado ativo/inativo de um campo.
     */
    public function toggleAtivo(int $id): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE medidas_campos SET ativo = 1 - ativo WHERE id = :id'
        );
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Reordena os campos a partir de um array de ids na nova sequência.
     */
    public function reordenar(array $ids): void
    {
        $stmt = $this->db->prepare(
            'UPDATE medidas_campos SET ordem = :ordem WHERE id = :id'
        );
        foreach ($ids as $posicao => $id) {
            $stmt->execute(['ordem' => $posicao + 1, 'id' => (int) $id]);
        }
    }

    /**
     * Gera um slug válido a partir de um label (letras, números e _).
     * Ex: "Busto alto" → "busto_alto"
     */
    public static function gerarSlug(string $label): string
    {
        $slug = mb_strtolower(trim($label));
        // Transliteration básica para caracteres comuns do português
        $slug = strtr($slug, [
            'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'ä' => 'a',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'ó' => 'o', 'ò' => 'o', 'õ' => 'o', 'ô' => 'o', 'ö' => 'o',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ç' => 'c', 'ñ' => 'n',
        ]);
        $slug = preg_replace('/[^a-z0-9]+/', '_', $slug);
        return trim($slug, '_');
    }
}

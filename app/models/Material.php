<?php

namespace App\Models;

use App\Core\Model;

class Material extends Model
{
    protected string $table = 'materiais';

    public const CAMPOS_FORMULARIO = [
        'codigo', 'nome', 'categoria', 'unidade', 'estoque_minimo', 'custo_unitario', 'fornecedor_id',
    ];

    public const TIPOS_MOVIMENTACAO = [
        'entrada' => 'Entrada',
        'saida'   => 'Saída',
        'ajuste'  => 'Ajuste',
    ];

    public function listarAtivos(?string $busca = null, bool $apenasAbaixoMinimo = false, int $limite = 0, int $offset = 0): array
    {
        $sql = "SELECT m.*, f.nome AS fornecedor_nome
                FROM materiais m
                LEFT JOIN fornecedores f ON f.id = m.fornecedor_id
                WHERE m.ativo = 1";
        $params = [];

        if (!empty($busca)) {
            $sql .= ' AND (m.nome LIKE :busca1 OR m.codigo LIKE :busca2)';
            $termo = '%' . $busca . '%';
            $params['busca1'] = $termo;
            $params['busca2'] = $termo;
        }

        if ($apenasAbaixoMinimo) {
            $sql .= ' AND m.quantidade <= m.estoque_minimo';
        }

        $sql .= ' ORDER BY m.nome ASC';

        if ($limite > 0) {
            $sql .= ' LIMIT :limite OFFSET :offset';
            $params['limite'] = $limite;
            $params['offset'] = $offset;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function contarAtivos(?string $busca = null, bool $apenasAbaixoMinimo = false): int
    {
        $sql = 'SELECT COUNT(*) FROM materiais m WHERE m.ativo = 1';
        $params = [];

        if (!empty($busca)) {
            $sql .= ' AND (m.nome LIKE :busca1 OR m.codigo LIKE :busca2)';
            $termo = '%' . $busca . '%';
            $params['busca1'] = $termo;
            $params['busca2'] = $termo;
        }

        if ($apenasAbaixoMinimo) {
            $sql .= ' AND m.quantidade <= m.estoque_minimo';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function buscarComFornecedor(int $id): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT m.*, f.nome AS fornecedor_nome
             FROM materiais m
             LEFT JOIN fornecedores f ON f.id = m.fornecedor_id
             WHERE m.id = :id"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function codigoExiste(string $codigo, ?int $ignorarId = null): bool
    {
        $sql = 'SELECT id FROM materiais WHERE codigo = :codigo';
        $params = ['codigo' => $codigo];

        if ($ignorarId !== null) {
            $sql .= ' AND id != :id';
            $params['id'] = $ignorarId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetch();
    }

    public function listarAbaixoDoMinimo(): array
    {
        return $this->listarAtivos(null, true);
    }

    public function contarAbaixoDoMinimo(): int
    {
        $stmt = $this->db->query('SELECT COUNT(*) AS total FROM materiais WHERE ativo = 1 AND quantidade <= estoque_minimo');
        return (int) $stmt->fetch()['total'];
    }

    /**
     * Registra uma movimentação de estoque (entrada, saída ou ajuste) e
     * atualiza a quantidade atual do material de forma atômica.
     *
     * @param string $tipo 'entrada' | 'saida' | 'ajuste'
     * @param float $quantidade Para 'ajuste', é a nova quantidade final; para os demais, é o delta.
     */
    public function movimentar(int $materialId, string $tipo, float $quantidade, ?int $usuarioId, ?string $motivo = null): array
    {
        $material = $this->find($materialId);
        if (!$material) {
            return ['ok' => false, 'erro' => 'Material não encontrado.'];
        }

        $quantidadeAtual = (float) $material['quantidade'];

        switch ($tipo) {
            case 'entrada':
                $novaQuantidade = $quantidadeAtual + $quantidade;
                break;
            case 'saida':
                if ($quantidade > $quantidadeAtual) {
                    return ['ok' => false, 'erro' => 'Quantidade de saída maior que o estoque disponível.'];
                }
                $novaQuantidade = $quantidadeAtual - $quantidade;
                break;
            case 'ajuste':
                $novaQuantidade = $quantidade;
                break;
            default:
                return ['ok' => false, 'erro' => 'Tipo de movimentação inválido.'];
        }

        $this->db->beginTransaction();
        try {
            $this->update($materialId, ['quantidade' => $novaQuantidade]);

            $stmt = $this->db->prepare(
                'INSERT INTO movimentacoes_estoque (material_id, tipo, quantidade, quantidade_resultante, motivo, usuario_id)
                 VALUES (:material_id, :tipo, :quantidade, :quantidade_resultante, :motivo, :usuario_id)'
            );
            $stmt->execute([
                'material_id'           => $materialId,
                'tipo'                  => $tipo,
                'quantidade'            => $quantidade,
                'quantidade_resultante' => $novaQuantidade,
                'motivo'                => $motivo,
                'usuario_id'            => $usuarioId,
            ]);

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            error_log('Falha ao movimentar estoque: ' . $e->getMessage());
            return ['ok' => false, 'erro' => 'Não foi possível registrar a movimentação.'];
        }

        return ['ok' => true, 'nova_quantidade' => $novaQuantidade];
    }

    public function historicoMovimentacoes(int $materialId): array
    {
        $stmt = $this->db->prepare(
            'SELECT mv.*, u.nome AS usuario_nome
             FROM movimentacoes_estoque mv
             LEFT JOIN usuarios u ON u.id = mv.usuario_id
             WHERE mv.material_id = :material_id
             ORDER BY mv.created_at DESC'
        );
        $stmt->execute(['material_id' => $materialId]);
        return $stmt->fetchAll();
    }

    public function excluirLogicamente(int $id): bool
    {
        return $this->update($id, ['ativo' => 0]);
    }
}

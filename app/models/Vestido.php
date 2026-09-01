<?php

namespace App\Models;

use App\Core\Model;

class Vestido extends Model
{
    protected string $table = 'vestidos';

    public const CAMPOS_FORMULARIO = [
        'codigo', 'nome', 'categoria', 'tipo', 'tamanho', 'cor', 'valor', 'descricao',
    ];

    public const STATUS = [
        'disponivel'   => 'Disponível',
        'reservado'    => 'Reservado',
        'em_producao'  => 'Em produção',
        'indisponivel' => 'Indisponível',
    ];

    public const TIPOS = [
        'venda'       => 'Venda',
        'sob_medida'  => 'Sob medida',
    ];

    public function listarAtivos(?string $busca = null, ?string $status = null, ?int $clienteId = null): array
    {
        $sql = "SELECT v.*, c.nome_completo AS cliente_nome
                FROM vestidos v
                LEFT JOIN clientes c ON c.id = v.cliente_id
                WHERE v.ativo = 1";
        $params = [];

        if (!empty($busca)) {
            $sql .= ' AND (v.nome LIKE :busca1 OR v.codigo LIKE :busca2)';
            $termo = '%' . $busca . '%';
            $params['busca1'] = $termo;
            $params['busca2'] = $termo;
        }

        if (!empty($status)) {
            $sql .= ' AND v.status = :status';
            $params['status'] = $status;
        }

        if ($clienteId !== null) {
            $sql .= ' AND v.cliente_id = :cliente_id';
            $params['cliente_id'] = $clienteId;
        }

        $sql .= ' ORDER BY v.codigo ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function buscarComCliente(int $id): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT v.*, c.nome_completo AS cliente_nome
             FROM vestidos v
             LEFT JOIN clientes c ON c.id = v.cliente_id
             WHERE v.id = :id"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function codigoExiste(string $codigo, ?int $ignorarId = null): bool
    {
        $sql = 'SELECT id FROM vestidos WHERE codigo = :codigo';
        $params = ['codigo' => $codigo];

        if ($ignorarId !== null) {
            $sql .= ' AND id != :id';
            $params['id'] = $ignorarId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetch();
    }

    public function alterarStatus(int $vestidoId, string $novoStatus, ?int $usuarioId, ?int $clienteId = null, ?string $observacao = null): bool
    {
        $vestido = $this->find($vestidoId);
        if (!$vestido) {
            return false;
        }

        $dados = ['status' => $novoStatus, 'atualizado_por' => $usuarioId];
        if ($clienteId !== null || in_array($novoStatus, ['disponivel', 'indisponivel'], true)) {
            $dados['cliente_id'] = in_array($novoStatus, ['disponivel', 'indisponivel'], true) ? null : $clienteId;
        }

        $ok = $this->update($vestidoId, $dados);

        if ($ok) {
            $stmt = $this->db->prepare(
                'INSERT INTO historico_vestidos (vestido_id, usuario_id, cliente_id, status_anterior, status_novo, observacao)
                 VALUES (:vestido_id, :usuario_id, :cliente_id, :status_anterior, :status_novo, :observacao)'
            );
            $stmt->execute([
                'vestido_id'      => $vestidoId,
                'usuario_id'      => $usuarioId,
                'cliente_id'      => $dados['cliente_id'] ?? $vestido['cliente_id'],
                'status_anterior' => $vestido['status'],
                'status_novo'     => $novoStatus,
                'observacao'      => $observacao,
            ]);
        }

        return $ok;
    }

    public function historico(int $vestidoId): array
    {
        $stmt = $this->db->prepare(
            'SELECT h.*, u.nome AS usuario_nome, c.nome_completo AS cliente_nome
             FROM historico_vestidos h
             LEFT JOIN usuarios u ON u.id = h.usuario_id
             LEFT JOIN clientes c ON c.id = h.cliente_id
             WHERE h.vestido_id = :vestido_id
             ORDER BY h.created_at DESC'
        );
        $stmt->execute(['vestido_id' => $vestidoId]);
        return $stmt->fetchAll();
    }

    public function contarPorStatus(): array
    {
        $stmt = $this->db->query(
            "SELECT status, COUNT(*) AS total FROM vestidos WHERE ativo = 1 GROUP BY status"
        );
        $linhas = $stmt->fetchAll();

        $resultado = array_fill_keys(array_keys(self::STATUS), 0);
        foreach ($linhas as $linha) {
            $resultado[$linha['status']] = (int) $linha['total'];
        }

        return $resultado;
    }

    public function excluirLogicamente(int $id): bool
    {
        return $this->update($id, ['ativo' => 0]);
    }
}

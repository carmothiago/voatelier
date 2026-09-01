<?php

namespace App\Models;

use App\Core\Model;

class Prova extends Model
{
    protected string $table = 'provas';

    public const STATUS = [
        'pendente'     => 'Pendente',
        'em_execucao'  => 'Em execução',
        'concluido'    => 'Concluído',
    ];

    public function proximoNumero(int $clienteId): int
    {
        $stmt = $this->db->prepare('SELECT COALESCE(MAX(numero), 0) + 1 AS proximo FROM provas WHERE cliente_id = :cliente_id');
        $stmt->execute(['cliente_id' => $clienteId]);
        return (int) $stmt->fetch()['proximo'];
    }

    public function listarPorCliente(int $clienteId): array
    {
        $stmt = $this->db->prepare(
            "SELECT p.*, v.nome AS vestido_nome, v.codigo AS vestido_codigo, u.nome AS responsavel_nome
             FROM provas p
             LEFT JOIN vestidos v ON v.id = p.vestido_id
             LEFT JOIN usuarios u ON u.id = p.responsavel_id
             WHERE p.cliente_id = :cliente_id
             ORDER BY p.data_prova DESC, p.numero DESC"
        );
        $stmt->execute(['cliente_id' => $clienteId]);
        return $stmt->fetchAll();
    }

    public function listarTodas(?string $status = null): array
    {
        $sql = "SELECT p.*, c.nome_completo AS cliente_nome, v.nome AS vestido_nome, u.nome AS responsavel_nome
                FROM provas p
                INNER JOIN clientes c ON c.id = p.cliente_id
                LEFT JOIN vestidos v ON v.id = p.vestido_id
                LEFT JOIN usuarios u ON u.id = p.responsavel_id";
        $params = [];

        if (!empty($status)) {
            $sql .= ' WHERE p.status = :status';
            $params['status'] = $status;
        }

        $sql .= ' ORDER BY p.data_prova DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function buscarCompleta(int $id): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT p.*, c.nome_completo AS cliente_nome, v.nome AS vestido_nome, v.codigo AS vestido_codigo, u.nome AS responsavel_nome
             FROM provas p
             INNER JOIN clientes c ON c.id = p.cliente_id
             LEFT JOIN vestidos v ON v.id = p.vestido_id
             LEFT JOIN usuarios u ON u.id = p.responsavel_id
             WHERE p.id = :id"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    // -------------------------------------------------------------
    // Ajustes
    // -------------------------------------------------------------

    public function listarAjustes(int $provaId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM ajustes WHERE prova_id = :prova_id ORDER BY id ASC');
        $stmt->execute(['prova_id' => $provaId]);
        return $stmt->fetchAll();
    }

    public function inserirAjuste(array $dados): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO ajustes (prova_id, descricao, parte_vestido, medida_atual, medida_desejada, observacao, status)
             VALUES (:prova_id, :descricao, :parte_vestido, :medida_atual, :medida_desejada, :observacao, :status)'
        );
        $stmt->execute($dados);
        return (int) $this->db->lastInsertId();
    }

    public function atualizarStatusAjuste(int $ajusteId, string $status): bool
    {
        $stmt = $this->db->prepare('UPDATE ajustes SET status = :status WHERE id = :id');
        return $stmt->execute(['status' => $status, 'id' => $ajusteId]);
    }

    // -------------------------------------------------------------
    // Anexos (fotos)
    // -------------------------------------------------------------

    public function listarAnexos(int $provaId): array
    {
        $stmt = $this->db->prepare(
            'SELECT a.*, u.nome AS usuario_nome
             FROM anexos_prova a
             LEFT JOIN usuarios u ON u.id = a.criado_por
             WHERE a.prova_id = :prova_id
             ORDER BY a.created_at DESC'
        );
        $stmt->execute(['prova_id' => $provaId]);
        return $stmt->fetchAll();
    }

    public function inserirAnexo(int $provaId, string $nomeArquivo, string $nomeOriginal, int $tamanho, ?int $usuarioId): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO anexos_prova (prova_id, nome_arquivo, nome_original, tamanho_bytes, criado_por)
             VALUES (:prova_id, :nome_arquivo, :nome_original, :tamanho, :criado_por)'
        );
        $stmt->execute([
            'prova_id'      => $provaId,
            'nome_arquivo'  => $nomeArquivo,
            'nome_original' => $nomeOriginal,
            'tamanho'       => $tamanho,
            'criado_por'    => $usuarioId,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function buscarAnexo(int $anexoId): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM anexos_prova WHERE id = :id');
        $stmt->execute(['id' => $anexoId]);
        return $stmt->fetch();
    }

    public function excluirAnexo(int $anexoId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM anexos_prova WHERE id = :id');
        return $stmt->execute(['id' => $anexoId]);
    }
}

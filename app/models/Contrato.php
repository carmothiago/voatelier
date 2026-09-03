<?php

namespace App\Models;

use App\Core\Model;

class Contrato extends Model
{
    protected string $table = 'contratos';

    public function listarTodos(int $limite = 0, int $offset = 0): array
    {
        $sql = "SELECT ct.*, c.nome_completo AS cliente_nome, v.codigo AS vestido_codigo, v.nome AS vestido_nome
                FROM contratos ct
                INNER JOIN clientes c ON c.id = ct.cliente_id
                LEFT JOIN vestidos v ON v.id = ct.vestido_id
                ORDER BY ct.data_contrato DESC";

        if ($limite > 0) {
            $sql .= ' LIMIT :limite OFFSET :offset';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limite', $limite, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $this->db->query($sql);
        }

        return $stmt->fetchAll();
    }

    public function contarTodos(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM contratos')->fetchColumn();
    }

    public function listarPorCliente(int $clienteId): array
    {
        $stmt = $this->db->prepare(
            "SELECT ct.*, v.codigo AS vestido_codigo, v.nome AS vestido_nome
             FROM contratos ct
             LEFT JOIN vestidos v ON v.id = ct.vestido_id
             WHERE ct.cliente_id = :cliente_id
             ORDER BY ct.data_contrato DESC"
        );
        $stmt->execute(['cliente_id' => $clienteId]);
        return $stmt->fetchAll();
    }

    public function buscarCompleto(int $id): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT ct.*, c.nome_completo AS cliente_nome, c.cpf AS cliente_cpf, c.endereco AS cliente_endereco,
                    c.cidade AS cliente_cidade, c.estado AS cliente_estado,
                    v.codigo AS vestido_codigo, v.nome AS vestido_nome
             FROM contratos ct
             INNER JOIN clientes c ON c.id = ct.cliente_id
             LEFT JOIN vestidos v ON v.id = ct.vestido_id
             WHERE ct.id = :id"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function salvarNomeArquivoPdf(int $id, string $nomeArquivo): bool
    {
        return $this->update($id, ['arquivo_pdf' => $nomeArquivo]);
    }

    public function totalContratadoNoMes(): float
    {
        $stmt = $this->db->query(
            "SELECT COALESCE(SUM(valor), 0) AS total FROM contratos
             WHERE MONTH(data_contrato) = MONTH(CURDATE()) AND YEAR(data_contrato) = YEAR(CURDATE())"
        );
        return (float) $stmt->fetch()['total'];
    }
}

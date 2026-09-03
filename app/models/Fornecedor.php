<?php

namespace App\Models;

use App\Core\Model;

class Fornecedor extends Model
{
    protected string $table = 'fornecedores';

    public const CAMPOS_FORMULARIO = [
        'nome', 'cnpj_cpf', 'telefone', 'whatsapp', 'email', 'endereco', 'observacoes',
    ];

    public function listarAtivos(?string $busca = null, int $limite = 0, int $offset = 0): array
    {
        $sql = 'SELECT * FROM fornecedores WHERE ativo = 1';
        $params = [];

        if (!empty($busca)) {
            $sql .= ' AND (nome LIKE :busca1 OR cnpj_cpf LIKE :busca2)';
            $termo = '%' . $busca . '%';
            $params['busca1'] = $termo;
            $params['busca2'] = $termo;
        }

        $sql .= ' ORDER BY nome ASC';

        if ($limite > 0) {
            $sql .= ' LIMIT :limite OFFSET :offset';
            $params['limite'] = $limite;
            $params['offset'] = $offset;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function contarAtivos(?string $busca = null): int
    {
        $sql = 'SELECT COUNT(*) FROM fornecedores WHERE ativo = 1';
        $params = [];

        if (!empty($busca)) {
            $sql .= ' AND (nome LIKE :busca1 OR cnpj_cpf LIKE :busca2)';
            $termo = '%' . $busca . '%';
            $params['busca1'] = $termo;
            $params['busca2'] = $termo;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function listarMateriais(int $fornecedorId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM materiais WHERE fornecedor_id = :fornecedor_id AND ativo = 1 ORDER BY nome ASC'
        );
        $stmt->execute(['fornecedor_id' => $fornecedorId]);
        return $stmt->fetchAll();
    }

    public function excluirLogicamente(int $id): bool
    {
        return $this->update($id, ['ativo' => 0]);
    }
}

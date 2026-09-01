<?php

namespace App\Core;

use PDO;

/**
 * Model base com operações genéricas via PDO (prepared statements).
 * Os models específicos devem definir a propriedade $table.
 */
abstract class Model
{
    protected string $table;
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function find(int $id): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function all(string $orderBy = 'id', string $direction = 'ASC'): array
    {
        // Whitelist simples para evitar injeção via ORDER BY dinâmico
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $orderBy = preg_replace('/[^a-zA-Z0-9_]/', '', $orderBy);

        $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY {$orderBy} {$direction}");
        return $stmt->fetchAll();
    }

    public function findBy(string $column, $value): array|false
    {
        $column = preg_replace('/[^a-zA-Z0-9_]/', '', $column);
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$column} = :value LIMIT 1");
        $stmt->execute(['value' => $value]);
        return $stmt->fetch();
    }

    public function insert(array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn($c) => ':' . $c, $columns);

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $assignments = implode(', ', array_map(fn($c) => "{$c} = :{$c}", array_keys($data)));

        $sql = "UPDATE {$this->table} SET {$assignments} WHERE id = :id";
        $data['id'] = $id;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}

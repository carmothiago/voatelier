<?php

namespace App\Models;

use App\Core\Model;

class Perfil extends Model
{
    protected string $table = 'perfis';

    public function todosAtivos(): array
    {
        $stmt = $this->db->query("SELECT * FROM perfis WHERE ativo = 1 ORDER BY nome ASC");
        return $stmt->fetchAll();
    }
}

<?php

namespace App\Models;

use App\Core\Model;

class Agendamento extends Model
{
    protected string $table = 'agendamentos';

    public const TIPOS = [
        'atendimento' => 'Atendimento',
        'medicao'     => 'Medição',
        'prova'       => 'Prova',
        'ajuste'      => 'Ajuste',
        'entrega'     => 'Entrega',
        'devolucao'   => 'Devolução',
        'reuniao'     => 'Reunião',
    ];

    public const STATUS = [
        'agendado'  => 'Agendado',
        'concluido' => 'Concluído',
        'cancelado' => 'Cancelado',
    ];

    /**
     * Lista os agendamentos de um mês inteiro (para a visão de calendário),
     * já com o nome do cliente e do responsável.
     */
    public function listarPorMes(int $ano, int $mes): array
    {
        $stmt = $this->db->prepare(
            "SELECT a.*, c.nome_completo AS cliente_nome, u.nome AS responsavel_nome
             FROM agendamentos a
             LEFT JOIN clientes c ON c.id = a.cliente_id
             LEFT JOIN usuarios u ON u.id = a.responsavel_id
             WHERE YEAR(a.data_agendamento) = :ano AND MONTH(a.data_agendamento) = :mes
             ORDER BY a.data_agendamento ASC, a.hora_inicio ASC"
        );
        $stmt->execute(['ano' => $ano, 'mes' => $mes]);
        return $stmt->fetchAll();
    }

    public function listarPorData(string $data): array
    {
        $stmt = $this->db->prepare(
            "SELECT a.*, c.nome_completo AS cliente_nome, u.nome AS responsavel_nome
             FROM agendamentos a
             LEFT JOIN clientes c ON c.id = a.cliente_id
             LEFT JOIN usuarios u ON u.id = a.responsavel_id
             WHERE a.data_agendamento = :data
             ORDER BY a.hora_inicio ASC"
        );
        $stmt->execute(['data' => $data]);
        return $stmt->fetchAll();
    }

    public function listarPorCliente(int $clienteId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM agendamentos WHERE cliente_id = :cliente_id ORDER BY data_agendamento DESC, hora_inicio DESC"
        );
        $stmt->execute(['cliente_id' => $clienteId]);
        return $stmt->fetchAll();
    }

    public function resumoHoje(): array
    {
        $stmt = $this->db->query(
            "SELECT tipo, COUNT(*) AS total
             FROM agendamentos
             WHERE data_agendamento = CURDATE() AND status = 'agendado'
             GROUP BY tipo"
        );
        $linhas = $stmt->fetchAll();

        $resultado = array_fill_keys(array_keys(self::TIPOS), 0);
        foreach ($linhas as $linha) {
            $resultado[$linha['tipo']] = (int) $linha['total'];
        }

        return $resultado;
    }

    public function contarHoje(): int
    {
        $stmt = $this->db->query(
            "SELECT COUNT(*) AS total FROM agendamentos WHERE data_agendamento = CURDATE() AND status = 'agendado'"
        );
        return (int) $stmt->fetch()['total'];
    }

    /**
     * Verifica se existe conflito de horário para o mesmo responsável no
     * mesmo dia. Dois intervalos conflitam quando (inicioA < fimB) e (inicioB < fimA).
     *
     * @param int|null $ignorarId Ignora este agendamento na checagem (útil ao editar)
     */
    public function existeConflito(
        ?int $responsavelId,
        string $data,
        string $horaInicio,
        string $horaFim,
        ?int $ignorarId = null
    ): array|false {
        if (empty($responsavelId)) {
            return false;
        }

        $sql = "SELECT a.*, c.nome_completo AS cliente_nome
                FROM agendamentos a
                LEFT JOIN clientes c ON c.id = a.cliente_id
                WHERE a.responsavel_id = :responsavel_id
                  AND a.data_agendamento = :data
                  AND a.status = 'agendado'
                  AND a.hora_inicio < :hora_fim
                  AND a.hora_fim > :hora_inicio";

        $params = [
            'responsavel_id' => $responsavelId,
            'data'           => $data,
            'hora_inicio'    => $horaInicio,
            'hora_fim'       => $horaFim,
        ];

        if ($ignorarId !== null) {
            $sql .= ' AND a.id != :ignorar_id';
            $params['ignorar_id'] = $ignorarId;
        }

        $sql .= ' LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetch();
    }
}

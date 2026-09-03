<?php

namespace App\Models;

use App\Core\Model;

class Cliente extends Model
{
    protected string $table = 'clientes';

    /**
     * Campos editáveis via formulário. Mantido em um único lugar para
     * reaproveitar entre store(), update() e o registro de histórico.
     */
    public const CAMPOS_FORMULARIO = [
        'nome_completo', 'cpf', 'data_nascimento', 'telefone', 'whatsapp',
        'email', 'endereco', 'cidade', 'estado', 'instagram', 'observacoes',
        'data_casamento', 'horario_casamento', 'local_casamento', 'nome_noivo',
        'tipo_casamento', 'observacoes_casamento',
    ];

    public const ETAPAS_CRM = [
        'novo_contato'           => 'Novo contato',
        'atendimento_agendado'   => 'Atendimento agendado',
        'atendimento_realizado'  => 'Atendimento realizado',
        'orcamento_enviado'      => 'Orçamento enviado',
        'negociacao'             => 'Negociação',
        'contrato_fechado'       => 'Contrato fechado',
        'perdido'                => 'Perdido',
    ];

    public function listarAtivos(?string $busca = null, int $limite = 0, int $offset = 0): array
    {
        $sql = 'SELECT * FROM clientes WHERE ativo = 1';
        $params = [];

        if (!empty($busca)) {
            $sql .= ' AND (nome_completo LIKE :busca1 OR cpf LIKE :busca2 OR telefone LIKE :busca3 OR whatsapp LIKE :busca4)';
            $termo = '%' . $busca . '%';
            $params = ['busca1' => $termo, 'busca2' => $termo, 'busca3' => $termo, 'busca4' => $termo];
        }

        $sql .= ' ORDER BY nome_completo ASC';

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
        $sql = 'SELECT COUNT(*) FROM clientes WHERE ativo = 1';
        $params = [];

        if (!empty($busca)) {
            $sql .= ' AND (nome_completo LIKE :busca1 OR cpf LIKE :busca2 OR telefone LIKE :busca3 OR whatsapp LIKE :busca4)';
            $termo = '%' . $busca . '%';
            $params = ['busca1' => $termo, 'busca2' => $termo, 'busca3' => $termo, 'busca4' => $termo];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function listarParaKanban(): array
    {
        $stmt = $this->db->query(
            "SELECT id, nome_completo, nome_noivo, data_casamento, etapa_crm, motivo_perda
             FROM clientes
             WHERE ativo = 1
             ORDER BY updated_at DESC"
        );
        return $stmt->fetchAll();
    }

    public function casamentosProximos(int $dias = 30): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, nome_completo, data_casamento
             FROM clientes
             WHERE ativo = 1
               AND data_casamento IS NOT NULL
               AND data_casamento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :dias DAY)
             ORDER BY data_casamento ASC"
        );
        $stmt->execute(['dias' => $dias]);
        return $stmt->fetchAll();
    }

    public function contarPorEtapa(): array
    {
        $stmt = $this->db->query(
            "SELECT etapa_crm, COUNT(*) AS total
             FROM clientes
             WHERE ativo = 1
             GROUP BY etapa_crm"
        );
        $linhas = $stmt->fetchAll();

        $resultado = array_fill_keys(array_keys(self::ETAPAS_CRM), 0);
        foreach ($linhas as $linha) {
            $resultado[$linha['etapa_crm']] = (int) $linha['total'];
        }

        return $resultado;
    }

    public function contarNovosContatosNoMes(): int
    {
        $stmt = $this->db->query(
            "SELECT COUNT(*) AS total FROM clientes
             WHERE ativo = 1 AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())"
        );
        return (int) $stmt->fetch()['total'];
    }

    /**
     * Atualiza a etapa do CRM e grava o histórico de movimentação.
     */
    public function moverEtapa(int $clienteId, string $novaEtapa, ?int $usuarioId, ?string $observacao = null): bool
    {
        $cliente = $this->find($clienteId);
        if (!$cliente) {
            return false;
        }

        $etapaAnterior = $cliente['etapa_crm'];

        $dados = ['etapa_crm' => $novaEtapa, 'atualizado_por' => $usuarioId];
        if ($novaEtapa !== 'perdido') {
            $dados['motivo_perda'] = null;
        }

        $ok = $this->update($clienteId, $dados);

        if ($ok) {
            $stmt = $this->db->prepare(
                'INSERT INTO crm_historico (cliente_id, usuario_id, etapa_anterior, etapa_nova, observacao)
                 VALUES (:cliente_id, :usuario_id, :etapa_anterior, :etapa_nova, :observacao)'
            );
            $stmt->execute([
                'cliente_id'     => $clienteId,
                'usuario_id'     => $usuarioId,
                'etapa_anterior' => $etapaAnterior,
                'etapa_nova'     => $novaEtapa,
                'observacao'     => $observacao,
            ]);
        }

        return $ok;
    }

    public function historicoCrm(int $clienteId): array
    {
        $stmt = $this->db->prepare(
            'SELECT ch.*, u.nome AS usuario_nome
             FROM crm_historico ch
             LEFT JOIN usuarios u ON u.id = ch.usuario_id
             WHERE ch.cliente_id = :cliente_id
             ORDER BY ch.created_at DESC'
        );
        $stmt->execute(['cliente_id' => $clienteId]);
        return $stmt->fetchAll();
    }

    public function historicoAlteracoes(int $clienteId): array
    {
        $stmt = $this->db->prepare(
            'SELECT h.*, u.nome AS usuario_nome
             FROM historico_clientes h
             LEFT JOIN usuarios u ON u.id = h.usuario_id
             WHERE h.cliente_id = :cliente_id
             ORDER BY h.created_at DESC'
        );
        $stmt->execute(['cliente_id' => $clienteId]);
        return $stmt->fetchAll();
    }

    /**
     * Compara os dados antigos com os novos e grava uma linha de
     * histórico para cada campo que realmente mudou.
     */
    public function registrarHistoricoDiff(int $clienteId, array $dadosAntigos, array $dadosNovos, ?int $usuarioId): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO historico_clientes (cliente_id, usuario_id, campo, valor_anterior, valor_novo)
             VALUES (:cliente_id, :usuario_id, :campo, :valor_anterior, :valor_novo)'
        );

        foreach (self::CAMPOS_FORMULARIO as $campo) {
            $antigo = $dadosAntigos[$campo] ?? null;
            $novo = $dadosNovos[$campo] ?? null;

            if ((string) $antigo !== (string) $novo) {
                $stmt->execute([
                    'cliente_id'     => $clienteId,
                    'usuario_id'     => $usuarioId,
                    'campo'          => $campo,
                    'valor_anterior' => $antigo,
                    'valor_novo'     => $novo,
                ]);
            }
        }
    }

    public function excluirLogicamente(int $clienteId): bool
    {
        return $this->update($clienteId, ['ativo' => 0]);
    }
}

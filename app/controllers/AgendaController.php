<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Agendamento;
use App\Models\Cliente;
use App\Models\Usuario;

class AgendaController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $this->requirePermission('agenda.visualizar');

        $ano = (int) $this->input('ano', date('Y'));
        $mes = (int) $this->input('mes', date('n'));

        if ($mes < 1 || $mes > 12) {
            $mes = (int) date('n');
        }

        $agendamentoModel = new Agendamento();
        $eventos = $agendamentoModel->listarPorMes($ano, $mes);

        // Agrupa os eventos por dia (1..31) para facilitar a montagem do grid no calendário
        $eventosPorDia = [];
        foreach ($eventos as $evento) {
            $dia = (int) date('j', strtotime($evento['data_agendamento']));
            $eventosPorDia[$dia][] = $evento;
        }

        $timestampMes = mktime(0, 0, 0, $mes, 1, $ano);
        $mesAnterior = $mes === 1 ? 12 : $mes - 1;
        $anoMesAnterior = $mes === 1 ? $ano - 1 : $ano;
        $mesSeguinte = $mes === 12 ? 1 : $mes + 1;
        $anoMesSeguinte = $mes === 12 ? $ano + 1 : $ano;

        $this->view('agenda/index', [
            'titulo'          => 'Agenda',
            'ano'             => $ano,
            'mes'             => $mes,
            'nomeMes'         => $this->nomeMes($mes),
            'diasNoMes'       => (int) date('t', $timestampMes),
            'diaSemanaInicio' => (int) date('w', $timestampMes), // 0 = domingo
            'eventosPorDia'   => $eventosPorDia,
            'mesAnterior'     => $mesAnterior,
            'anoMesAnterior'  => $anoMesAnterior,
            'mesSeguinte'     => $mesSeguinte,
            'anoMesSeguinte'  => $anoMesSeguinte,
            'tipos'           => Agendamento::TIPOS,
        ]);
    }

    public function novoForm(): void
    {
        $this->requireLogin();
        $this->requirePermission('agenda.criar');

        $this->carregarFormulario(null, [
            'cliente_id'       => $this->input('cliente_id', ''),
            'data_agendamento' => $this->input('data', date('Y-m-d')),
        ]);
    }

    public function store(): void
    {
        $this->requireLogin();
        $this->requirePermission('agenda.criar');

        if (!Csrf::validate($this->input('csrf_token'))) {
            setFlash('erro', 'Sessão expirada. Tente novamente.');
            $this->redirect('/agenda/novo');
            return;
        }

        $dados = $this->dadosDoFormulario();
        $erro = $this->validar($dados);

        if ($erro) {
            setFlash('erro', $erro);
            $this->carregarFormulario(null, $dados);
            return;
        }

        $agendamentoModel = new Agendamento();
        $forcar = $this->input('forcar') === '1';

        if (!$forcar) {
            $conflito = $agendamentoModel->existeConflito(
                $dados['responsavel_id'],
                $dados['data_agendamento'],
                $dados['hora_inicio'],
                $dados['hora_fim']
            );

            if ($conflito) {
                $this->carregarFormulario(null, $dados, $this->mensagemConflito($conflito));
                return;
            }
        }

        $dados['criado_por'] = Auth::id();
        $id = $agendamentoModel->insert($dados);

        registrarAuditoria('agenda', 'criar', (string) $id, null, $dados);

        setFlash('sucesso', 'Agendamento criado com sucesso.');
        $this->redirect('/agenda?ano=' . date('Y', strtotime($dados['data_agendamento'])) . '&mes=' . date('n', strtotime($dados['data_agendamento'])));
    }

    public function editarForm(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('agenda.editar');

        $agendamentoModel = new Agendamento();
        $agendamento = $agendamentoModel->find((int) $id);

        if (!$agendamento) {
            http_response_code(404);
            require APP_PATH . '/views/errors/404.php';
            return;
        }

        $this->carregarFormulario((int) $id, $agendamento);
    }

    public function update(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('agenda.editar');

        if (!Csrf::validate($this->input('csrf_token'))) {
            setFlash('erro', 'Sessão expirada. Tente novamente.');
            $this->redirect('/agenda/' . $id . '/editar');
            return;
        }

        $agendamentoModel = new Agendamento();
        $agendamentoAntigo = $agendamentoModel->find((int) $id);

        if (!$agendamentoAntigo) {
            http_response_code(404);
            require APP_PATH . '/views/errors/404.php';
            return;
        }

        $dados = $this->dadosDoFormulario();
        $erro = $this->validar($dados);

        if ($erro) {
            setFlash('erro', $erro);
            $this->carregarFormulario((int) $id, $dados);
            return;
        }

        $forcar = $this->input('forcar') === '1';

        if (!$forcar) {
            $conflito = $agendamentoModel->existeConflito(
                $dados['responsavel_id'],
                $dados['data_agendamento'],
                $dados['hora_inicio'],
                $dados['hora_fim'],
                (int) $id
            );

            if ($conflito) {
                $this->carregarFormulario((int) $id, $dados, $this->mensagemConflito($conflito));
                return;
            }
        }

        $agendamentoModel->update((int) $id, $dados);

        registrarAuditoria('agenda', 'editar', $id, $agendamentoAntigo, $dados);

        setFlash('sucesso', 'Agendamento atualizado.');
        $this->redirect('/agenda?ano=' . date('Y', strtotime($dados['data_agendamento'])) . '&mes=' . date('n', strtotime($dados['data_agendamento'])));
    }

    public function excluir(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('agenda.excluir');

        if (!Csrf::validate($this->input('csrf_token'))) {
            setFlash('erro', 'Sessão expirada. Tente novamente.');
            $this->redirect('/agenda');
            return;
        }

        $agendamentoModel = new Agendamento();
        $agendamento = $agendamentoModel->find((int) $id);

        if ($agendamento) {
            $agendamentoModel->delete((int) $id);
            registrarAuditoria('agenda', 'excluir', $id, $agendamento, null);
        }

        setFlash('sucesso', 'Agendamento excluído.');
        $this->redirect('/agenda');
    }

    /**
     * Renderiza o formulário de agendamento (criação ou edição), carregando
     * as listas de clientes e usuários necessárias para os selects.
     */
    private function carregarFormulario(?int $id, array $agendamento, ?string $avisoConflito = null): void
    {
        $clienteModel = new Cliente();
        $usuarioModel = new Usuario();

        $this->view('agenda/form', [
            'titulo'         => $id ? 'Editar agendamento' : 'Novo agendamento',
            'agendamento'    => $agendamento,
            'id'             => $id,
            'acao'           => $id ? url('/agenda/' . $id) : url('/agenda'),
            'clientes'       => $clienteModel->listarAtivos(),
            'usuarios'       => $usuarioModel->all('nome'),
            'tipos'          => Agendamento::TIPOS,
            'avisoConflito'  => $avisoConflito,
        ]);
    }

    private function dadosDoFormulario(): array
    {
        return [
            'cliente_id'       => $this->input('cliente_id') ?: null,
            'tipo'             => $this->input('tipo'),
            'data_agendamento' => $this->input('data_agendamento'),
            'hora_inicio'      => $this->input('hora_inicio'),
            'hora_fim'         => $this->input('hora_fim'),
            'responsavel_id'   => $this->input('responsavel_id') ?: null,
            'observacoes'      => trim((string) $this->input('observacoes', '')) ?: null,
            'status'           => $this->input('status', 'agendado'),
        ];
    }

    private function validar(array $dados): ?string
    {
        if (empty($dados['tipo']) || !array_key_exists($dados['tipo'], Agendamento::TIPOS)) {
            return 'Selecione um tipo de agendamento válido.';
        }

        if (empty($dados['data_agendamento']) || empty($dados['hora_inicio']) || empty($dados['hora_fim'])) {
            return 'Informe data, horário de início e horário de término.';
        }

        if ($dados['hora_fim'] <= $dados['hora_inicio']) {
            return 'O horário de término deve ser depois do horário de início.';
        }

        return null;
    }

    private function mensagemConflito(array $conflito): string
    {
        $nomeCliente = $conflito['cliente_nome'] ?? 'outro compromisso';
        return sprintf(
            'Conflito de horário: já existe "%s" com %s das %s às %s. Ajuste o horário ou confirme abaixo para salvar mesmo assim.',
            Agendamento::TIPOS[$conflito['tipo']] ?? $conflito['tipo'],
            $nomeCliente,
            substr($conflito['hora_inicio'], 0, 5),
            substr($conflito['hora_fim'], 0, 5)
        );
    }

    private function nomeMes(int $mes): string
    {
        $nomes = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
        ];

        return $nomes[$mes] ?? '';
    }
}

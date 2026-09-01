<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Agendamento;
use App\Models\Cliente;

class ClienteController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $this->requirePermission('clientes.visualizar');

        $busca = trim((string) $this->input('q', ''));
        $clienteModel = new Cliente();

        $this->view('clientes/index', [
            'titulo'   => 'Clientes',
            'clientes' => $clienteModel->listarAtivos($busca ?: null),
            'busca'    => $busca,
        ]);
    }

    public function novoForm(): void
    {
        $this->requireLogin();
        $this->requirePermission('clientes.criar');

        $this->view('clientes/form', [
            'titulo'  => 'Nova cliente',
            'cliente' => null,
            'acao'    => url('/clientes'),
        ]);
    }

    public function store(): void
    {
        $this->requireLogin();
        $this->requirePermission('clientes.criar');

        if (!Csrf::validate($this->input('csrf_token'))) {
            setFlash('erro', 'Sessão expirada. Tente novamente.');
            $this->redirect('/clientes/novo');
            return;
        }

        $dados = $this->dadosDoFormulario();

        if (empty($dados['nome_completo'])) {
            setFlash('erro', 'O nome completo da cliente é obrigatório.');
            $this->redirect('/clientes/novo');
            return;
        }

        $clienteModel = new Cliente();
        $dados['criado_por'] = Auth::id();
        $dados['atualizado_por'] = Auth::id();

        $id = $clienteModel->insert($dados);

        registrarAuditoria('clientes', 'criar', (string) $id, null, $dados);

        setFlash('sucesso', 'Cliente cadastrada com sucesso.');
        $this->redirect('/clientes/' . $id);
    }

    public function show(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('clientes.visualizar');

        $clienteModel = new Cliente();
        $cliente = $clienteModel->find((int) $id);

        if (!$cliente) {
            http_response_code(404);
            require APP_PATH . '/views/errors/404.php';
            return;
        }

        $agendamentoModel = new Agendamento();
        $medidaModel = new \App\Models\Medida();
        $provaModel = new \App\Models\Prova();
        $vestidoModel = new \App\Models\Vestido();
        $anexoModel = new \App\Models\Anexo();
        $contratoModel = new \App\Models\Contrato();
        $contaReceberModel = new \App\Models\ContaReceber();

        $this->view('clientes/show', [
            'titulo'            => $cliente['nome_completo'],
            'cliente'           => $cliente,
            'agendamentos'      => $agendamentoModel->listarPorCliente((int) $id),
            'historicoCrm'      => $clienteModel->historicoCrm((int) $id),
            'historicoCampos'   => $clienteModel->historicoAlteracoes((int) $id),
            'etapasCrm'         => Cliente::ETAPAS_CRM,
            'ultimaMedida'      => $medidaModel->ultimaDaCliente((int) $id),
            'labelsMedidas'     => \App\Models\Medida::LABELS,
            'provas'            => $provaModel->listarPorCliente((int) $id),
            'vestidosVinculados'=> $vestidoModel->listarAtivos(null, null, (int) $id),
            'documentos'        => $anexoModel->listarPorCliente((int) $id),
            'contratos'         => $contratoModel->listarPorCliente((int) $id),
            'contasReceber'     => $contaReceberModel->listarPorCliente((int) $id),
        ]);
    }

    public function editarForm(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('clientes.editar');

        $clienteModel = new Cliente();
        $cliente = $clienteModel->find((int) $id);

        if (!$cliente) {
            http_response_code(404);
            require APP_PATH . '/views/errors/404.php';
            return;
        }

        $this->view('clientes/form', [
            'titulo'  => 'Editar cliente',
            'cliente' => $cliente,
            'acao'    => url('/clientes/' . $id),
        ]);
    }

    public function update(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('clientes.editar');

        if (!Csrf::validate($this->input('csrf_token'))) {
            setFlash('erro', 'Sessão expirada. Tente novamente.');
            $this->redirect('/clientes/' . $id . '/editar');
            return;
        }

        $clienteModel = new Cliente();
        $clienteAntigo = $clienteModel->find((int) $id);

        if (!$clienteAntigo) {
            http_response_code(404);
            require APP_PATH . '/views/errors/404.php';
            return;
        }

        $dados = $this->dadosDoFormulario();

        if (empty($dados['nome_completo'])) {
            setFlash('erro', 'O nome completo da cliente é obrigatório.');
            $this->redirect('/clientes/' . $id . '/editar');
            return;
        }

        $dados['atualizado_por'] = Auth::id();

        $clienteModel->update((int) $id, $dados);
        $clienteModel->registrarHistoricoDiff((int) $id, $clienteAntigo, $dados, Auth::id());

        registrarAuditoria('clientes', 'editar', $id, $clienteAntigo, $dados);

        setFlash('sucesso', 'Dados da cliente atualizados.');
        $this->redirect('/clientes/' . $id);
    }

    public function excluir(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('clientes.excluir');

        if (!Csrf::validate($this->input('csrf_token'))) {
            setFlash('erro', 'Sessão expirada. Tente novamente.');
            $this->redirect('/clientes/' . $id);
            return;
        }

        $clienteModel = new Cliente();
        $cliente = $clienteModel->find((int) $id);

        if ($cliente) {
            $clienteModel->excluirLogicamente((int) $id);
            registrarAuditoria('clientes', 'excluir', $id, $cliente, null);
        }

        setFlash('sucesso', 'Cliente removida da listagem.');
        $this->redirect('/clientes');
    }

    /**
     * Extrai e normaliza os campos do formulário de cliente a partir do $_POST.
     */
    private function dadosDoFormulario(): array
    {
        $dados = [];

        foreach (Cliente::CAMPOS_FORMULARIO as $campo) {
            $valor = trim((string) $this->input($campo, ''));
            $dados[$campo] = $valor === '' ? null : $valor;
        }

        return $dados;
    }
}

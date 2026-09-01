<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Cliente;
use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Models\Fornecedor;

class FinanceiroController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $this->requirePermission('financeiro.visualizar');

        $receberModel = new ContaReceber();
        $pagarModel = new ContaPagar();

        $this->view('financeiro/index', [
            'titulo'           => 'Financeiro · Fluxo de caixa',
            'receitasMes'      => $receberModel->totalReceitasNoMes(),
            'despesasMes'      => $pagarModel->totalDespesasNoMes(),
            'aReceber'         => $receberModel->totalAReceber(),
            'aPagar'           => $pagarModel->totalAPagar(),
            'vencidoReceber'   => $receberModel->totalVencido(),
            'vencidoPagar'     => $pagarModel->totalVencido(),
            'qtdVencidasReceber' => $receberModel->contarVencidas(),
            'qtdVencidasPagar' => $pagarModel->contarVencidas(),
        ]);
    }

    // -------------------------------------------------------------
    // Contas a receber
    // -------------------------------------------------------------

    public function receber(): void
    {
        $this->requireLogin();
        $this->requirePermission('financeiro.visualizar');

        $filtro = (string) $this->input('filtro', '');
        $receberModel = new ContaReceber();

        $this->view('financeiro/receber_index', [
            'titulo' => 'Contas a receber',
            'contas' => $receberModel->listarComCliente($filtro ?: null),
            'filtro' => $filtro,
            'statusLista' => ContaReceber::STATUS,
        ]);
    }

    public function novoReceberForm(): void
    {
        $this->requireLogin();
        $this->requirePermission('financeiro.editar');

        $clienteModel = new Cliente();
        $this->view('financeiro/receber_form', [
            'titulo'   => 'Nova conta a receber',
            'conta'    => ['cliente_id' => $this->input('cliente_id', '')],
            'clientes' => $clienteModel->listarAtivos(),
        ]);
    }

    public function storeReceber(): void
    {
        $this->requireLogin();
        $this->requirePermission('financeiro.editar');

        if (!Csrf::validate($this->input('csrf_token'))) {
            setFlash('erro', 'Sessão expirada. Tente novamente.');
            $this->redirect('/financeiro/receber/novo');
            return;
        }

        $dados = [
            'cliente_id'      => (int) $this->input('cliente_id'),
            'descricao'       => trim((string) $this->input('descricao', '')),
            'valor'           => str_replace(',', '.', (string) $this->input('valor', '0')),
            'vencimento'      => $this->input('vencimento'),
            'forma_pagamento' => trim((string) $this->input('forma_pagamento', '')) ?: null,
            'observacoes'     => trim((string) $this->input('observacoes', '')) ?: null,
            'criado_por'      => Auth::id(),
        ];

        if (!$dados['cliente_id'] || empty($dados['descricao']) || empty($dados['vencimento'])) {
            setFlash('erro', 'Preencha cliente, descrição e vencimento.');
            $this->redirect('/financeiro/receber/novo');
            return;
        }

        $receberModel = new ContaReceber();
        $id = $receberModel->insert($dados);

        registrarAuditoria('financeiro', 'criar_receber', (string) $id, null, $dados);

        setFlash('sucesso', 'Conta a receber registrada.');
        $this->redirect('/financeiro/receber');
    }

    public function marcarReceberPago(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('financeiro.editar');

        if (!Csrf::validate($this->input('csrf_token'))) {
            setFlash('erro', 'Sessão expirada. Tente novamente.');
            $this->redirect('/financeiro/receber');
            return;
        }

        $receberModel = new ContaReceber();
        $receberModel->marcarComoPago((int) $id, date('Y-m-d'));

        registrarAuditoria('financeiro', 'marcar_pago_receber', $id, null, ['data_pagamento' => date('Y-m-d')]);

        setFlash('sucesso', 'Recebimento confirmado.');
        $this->redirect('/financeiro/receber');
    }

    // -------------------------------------------------------------
    // Contas a pagar
    // -------------------------------------------------------------

    public function pagar(): void
    {
        $this->requireLogin();
        $this->requirePermission('financeiro.visualizar');

        $filtro = (string) $this->input('filtro', '');
        $pagarModel = new ContaPagar();

        $this->view('financeiro/pagar_index', [
            'titulo' => 'Contas a pagar',
            'contas' => $pagarModel->listarComFornecedor($filtro ?: null),
            'filtro' => $filtro,
            'statusLista' => ContaPagar::STATUS,
        ]);
    }

    public function novoPagarForm(): void
    {
        $this->requireLogin();
        $this->requirePermission('financeiro.editar');

        $fornecedorModel = new Fornecedor();
        $this->view('financeiro/pagar_form', [
            'titulo'       => 'Nova conta a pagar',
            'conta'        => [],
            'fornecedores' => $fornecedorModel->listarAtivos(),
        ]);
    }

    public function storePagar(): void
    {
        $this->requireLogin();
        $this->requirePermission('financeiro.editar');

        if (!Csrf::validate($this->input('csrf_token'))) {
            setFlash('erro', 'Sessão expirada. Tente novamente.');
            $this->redirect('/financeiro/pagar/novo');
            return;
        }

        $dados = [
            'fornecedor_id' => $this->input('fornecedor_id') ?: null,
            'categoria'     => trim((string) $this->input('categoria', '')) ?: null,
            'descricao'     => trim((string) $this->input('descricao', '')),
            'valor'         => str_replace(',', '.', (string) $this->input('valor', '0')),
            'vencimento'    => $this->input('vencimento'),
            'observacoes'   => trim((string) $this->input('observacoes', '')) ?: null,
            'criado_por'    => Auth::id(),
        ];

        if (empty($dados['descricao']) || empty($dados['vencimento'])) {
            setFlash('erro', 'Preencha descrição e vencimento.');
            $this->redirect('/financeiro/pagar/novo');
            return;
        }

        $pagarModel = new ContaPagar();
        $id = $pagarModel->insert($dados);

        registrarAuditoria('financeiro', 'criar_pagar', (string) $id, null, $dados);

        setFlash('sucesso', 'Conta a pagar registrada.');
        $this->redirect('/financeiro/pagar');
    }

    public function marcarPagarPago(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('financeiro.editar');

        if (!Csrf::validate($this->input('csrf_token'))) {
            setFlash('erro', 'Sessão expirada. Tente novamente.');
            $this->redirect('/financeiro/pagar');
            return;
        }

        $pagarModel = new ContaPagar();
        $pagarModel->marcarComoPago((int) $id, date('Y-m-d'));

        registrarAuditoria('financeiro', 'marcar_pago_pagar', $id, null, ['data_pagamento' => date('Y-m-d')]);

        setFlash('sucesso', 'Pagamento confirmado.');
        $this->redirect('/financeiro/pagar');
    }
}

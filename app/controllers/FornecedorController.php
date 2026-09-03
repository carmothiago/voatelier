<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Validador;
use App\Models\Fornecedor;

class FornecedorController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $this->requirePermission('fornecedores.visualizar');

        $busca  = trim((string) $this->input('q', ''));
        $pagina = max(1, (int) $this->input('pagina', 1));

        $fornecedorModel = new Fornecedor();
        $paginador       = new \App\Core\Paginador(
            $fornecedorModel->contarAtivos($busca ?: null),
            PAGINA_TAMANHO,
            $pagina
        );

        $this->view('fornecedores/index', [
            'titulo'      => 'Fornecedores',
            'fornecedores'=> $fornecedorModel->listarAtivos($busca ?: null, PAGINA_TAMANHO, $paginador->offset()),
            'busca'       => $busca,
            'paginador'   => $paginador,
            'urlBase'     => url('/fornecedores') . ($busca !== '' ? '?q=' . urlencode($busca) : ''),
        ]);
    }

    public function novoForm(): void
    {
        $this->requireLogin();
        $this->requirePermission('fornecedores.editar');

        $this->view('fornecedores/form', [
            'titulo'     => 'Novo fornecedor',
            'fornecedor' => null,
            'acao'       => url('/fornecedores'),
        ]);
    }

    public function store(): void
    {
        $this->requireLogin();
        $this->requirePermission('fornecedores.editar');

        if (!Csrf::validate($this->input('csrf_token'))) {
            setFlash('erro', 'Sessão expirada. Tente novamente.');
            $this->redirect('/fornecedores/novo');
            return;
        }

        $dados = $this->dadosDoFormulario();
        $erro  = $this->validar($dados);

        if ($erro) {
            setFlash('erro', $erro);
            $this->redirect('/fornecedores/novo');
            return;
        }

        $fornecedorModel = new Fornecedor();
        $dados['criado_por'] = Auth::id();
        $id = $fornecedorModel->insert($dados);

        registrarAuditoria('fornecedores', 'criar', (string) $id, null, $dados);

        setFlash('sucesso', 'Fornecedor cadastrado com sucesso.');
        $this->redirect('/fornecedores/' . $id);
    }

    public function show(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('fornecedores.visualizar');

        $fornecedorModel = new Fornecedor();
        $fornecedor = $fornecedorModel->find((int) $id);

        if (!$fornecedor) {
            http_response_code(404);
            require APP_PATH . '/views/errors/404.php';
            return;
        }

        $this->view('fornecedores/show', [
            'titulo'     => $fornecedor['nome'],
            'fornecedor' => $fornecedor,
            'materiais'  => $fornecedorModel->listarMateriais((int) $id),
        ]);
    }

    public function editarForm(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('fornecedores.editar');

        $fornecedorModel = new Fornecedor();
        $fornecedor = $fornecedorModel->find((int) $id);

        if (!$fornecedor) {
            http_response_code(404);
            require APP_PATH . '/views/errors/404.php';
            return;
        }

        $this->view('fornecedores/form', [
            'titulo'     => 'Editar fornecedor',
            'fornecedor' => $fornecedor,
            'acao'       => url('/fornecedores/' . $id),
        ]);
    }

    public function update(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('fornecedores.editar');

        if (!Csrf::validate($this->input('csrf_token'))) {
            setFlash('erro', 'Sessão expirada. Tente novamente.');
            $this->redirect('/fornecedores/' . $id . '/editar');
            return;
        }

        $fornecedorModel = new Fornecedor();
        $fornecedorAntigo = $fornecedorModel->find((int) $id);

        if (!$fornecedorAntigo) {
            http_response_code(404);
            require APP_PATH . '/views/errors/404.php';
            return;
        }

        $dados = $this->dadosDoFormulario();
        $erro  = $this->validar($dados);

        if ($erro) {
            setFlash('erro', $erro);
            $this->redirect('/fornecedores/' . $id . '/editar');
            return;
        }

        $fornecedorModel->update((int) $id, $dados);

        registrarAuditoria('fornecedores', 'editar', $id, $fornecedorAntigo, $dados);

        setFlash('sucesso', 'Fornecedor atualizado.');
        $this->redirect('/fornecedores/' . $id);
    }

    public function excluir(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('fornecedores.editar');

        if (!Csrf::validate($this->input('csrf_token'))) {
            setFlash('erro', 'Sessão expirada. Tente novamente.');
            $this->redirect('/fornecedores');
            return;
        }

        $fornecedorModel = new Fornecedor();
        $fornecedor = $fornecedorModel->find((int) $id);

        if ($fornecedor) {
            $fornecedorModel->excluirLogicamente((int) $id);
            registrarAuditoria('fornecedores', 'excluir', $id, $fornecedor, null);
        }

        setFlash('sucesso', 'Fornecedor removido da listagem.');
        $this->redirect('/fornecedores');
    }

    private function dadosDoFormulario(): array
    {
        $dados = [];
        foreach (Fornecedor::CAMPOS_FORMULARIO as $campo) {
            $valor = trim((string) $this->input($campo, ''));
            $dados[$campo] = $valor === '' ? null : $valor;
        }

        return $dados;
    }

    private function validar(array $dados): ?string
    {
        $v = new Validador($dados);

        $v->obrigatorio('nome', 'Nome do fornecedor')
          ->tamanhoMaximo('nome', 150, 'Nome')
          ->cpfOuCnpj('cnpj_cpf')
          ->email('email')
          ->tamanhoMaximo('telefone', 20, 'Telefone')
          ->tamanhoMaximo('whatsapp', 20, 'WhatsApp');

        return $v->falhou() ? $v->primeiroErro() : null;
    }
}

<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Cliente;
use App\Models\Vestido;

class VestidoController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $this->requirePermission('vestidos.visualizar');

        $vestidoModel = new Vestido();
        $busca = trim((string) $this->input('q', ''));
        $status = (string) $this->input('status', '');

        $this->view('vestidos/index', [
            'titulo'   => 'Vestidos',
            'vestidos' => $vestidoModel->listarAtivos($busca ?: null, $status ?: null),
            'busca'    => $busca,
            'statusFiltro' => $status,
            'statusLista'  => Vestido::STATUS,
        ]);
    }

    public function novoForm(): void
    {
        $this->requireLogin();
        $this->requirePermission('vestidos.criar');

        $this->view('vestidos/form', [
            'titulo'  => 'Novo vestido',
            'vestido' => null,
            'acao'    => url('/vestidos'),
        ]);
    }

    public function store(): void
    {
        $this->requireLogin();
        $this->requirePermission('vestidos.criar');

        if (!Csrf::validate($this->input('csrf_token'))) {
            setFlash('erro', 'Sessão expirada. Tente novamente.');
            $this->redirect('/vestidos/novo');
            return;
        }

        $dados = $this->dadosDoFormulario();
        $erro = $this->validar($dados, null);

        if ($erro) {
            setFlash('erro', $erro);
            $this->redirect('/vestidos/novo');
            return;
        }

        $vestidoModel = new Vestido();
        $dados['criado_por'] = Auth::id();
        $dados['atualizado_por'] = Auth::id();

        $id = $vestidoModel->insert($dados);

        // Registra o primeiro estado no histórico
        $this->registrarHistoricoInicial($id, $dados['status'] ?? 'disponivel');

        registrarAuditoria('vestidos', 'criar', (string) $id, null, $dados);

        setFlash('sucesso', 'Vestido cadastrado com sucesso.');
        $this->redirect('/vestidos/' . $id);
    }

    public function show(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('vestidos.visualizar');

        $vestidoModel = new Vestido();
        $vestido = $vestidoModel->buscarComCliente((int) $id);

        if (!$vestido) {
            http_response_code(404);
            require APP_PATH . '/views/errors/404.php';
            return;
        }

        $clienteModel = new Cliente();

        $this->view('vestidos/show', [
            'titulo'    => $vestido['nome'] . ' (' . $vestido['codigo'] . ')',
            'vestido'   => $vestido,
            'historico' => $vestidoModel->historico((int) $id),
            'clientes'  => $clienteModel->listarAtivos(),
            'statusLista' => Vestido::STATUS,
        ]);
    }

    public function editarForm(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('vestidos.editar');

        $vestidoModel = new Vestido();
        $vestido = $vestidoModel->find((int) $id);

        if (!$vestido) {
            http_response_code(404);
            require APP_PATH . '/views/errors/404.php';
            return;
        }

        $this->view('vestidos/form', [
            'titulo'  => 'Editar vestido',
            'vestido' => $vestido,
            'acao'    => url('/vestidos/' . $id),
        ]);
    }

    public function update(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('vestidos.editar');

        if (!Csrf::validate($this->input('csrf_token'))) {
            setFlash('erro', 'Sessão expirada. Tente novamente.');
            $this->redirect('/vestidos/' . $id . '/editar');
            return;
        }

        $vestidoModel = new Vestido();
        $vestidoAntigo = $vestidoModel->find((int) $id);

        if (!$vestidoAntigo) {
            http_response_code(404);
            require APP_PATH . '/views/errors/404.php';
            return;
        }

        $dados = $this->dadosDoFormulario();
        unset($dados['status']); // status é alterado só pela ação dedicada (com histórico)
        $erro = $this->validar($dados, (int) $id);

        if ($erro) {
            setFlash('erro', $erro);
            $this->redirect('/vestidos/' . $id . '/editar');
            return;
        }

        $dados['atualizado_por'] = Auth::id();
        $vestidoModel->update((int) $id, $dados);

        registrarAuditoria('vestidos', 'editar', $id, $vestidoAntigo, $dados);

        setFlash('sucesso', 'Vestido atualizado.');
        $this->redirect('/vestidos/' . $id);
    }

    public function mudarStatus(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('vestidos.editar');

        if (!Csrf::validate($this->input('csrf_token'))) {
            setFlash('erro', 'Sessão expirada. Tente novamente.');
            $this->redirect('/vestidos/' . $id);
            return;
        }

        $novoStatus = (string) $this->input('status');
        $clienteId = $this->input('cliente_id') ?: null;
        $observacao = trim((string) $this->input('observacao', '')) ?: null;

        if (!array_key_exists($novoStatus, Vestido::STATUS)) {
            setFlash('erro', 'Status inválido.');
            $this->redirect('/vestidos/' . $id);
            return;
        }

        $vestidoModel = new Vestido();
        $ok = $vestidoModel->alterarStatus((int) $id, $novoStatus, Auth::id(), $clienteId ? (int) $clienteId : null, $observacao);

        if ($ok) {
            registrarAuditoria('vestidos', 'mudar_status', $id, null, ['status' => $novoStatus]);
            setFlash('sucesso', 'Status do vestido atualizado.');
        } else {
            setFlash('erro', 'Não foi possível atualizar o status.');
        }

        $this->redirect('/vestidos/' . $id);
    }

    public function excluir(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('vestidos.excluir');

        if (!Csrf::validate($this->input('csrf_token'))) {
            setFlash('erro', 'Sessão expirada. Tente novamente.');
            $this->redirect('/vestidos/' . $id);
            return;
        }

        $vestidoModel = new Vestido();
        $vestido = $vestidoModel->find((int) $id);

        if ($vestido) {
            $vestidoModel->excluirLogicamente((int) $id);
            registrarAuditoria('vestidos', 'excluir', $id, $vestido, null);
        }

        setFlash('sucesso', 'Vestido removido da listagem.');
        $this->redirect('/vestidos');
    }

    private function dadosDoFormulario(): array
    {
        $dados = [];
        foreach (Vestido::CAMPOS_FORMULARIO as $campo) {
            $valor = trim((string) $this->input($campo, ''));
            $dados[$campo] = $valor === '' ? null : $valor;
        }

        if ($dados['valor'] !== null) {
            $dados['valor'] = str_replace(',', '.', preg_replace('/[^\d,\.]/', '', $dados['valor']));
        }

        $dados['status'] = $this->input('status', 'disponivel');

        return $dados;
    }

    private function validar(array $dados, ?int $ignorarId): ?string
    {
        if (empty($dados['codigo'])) {
            return 'Informe o código do vestido.';
        }

        if (empty($dados['nome'])) {
            return 'Informe o nome do vestido.';
        }

        if (!array_key_exists($dados['tipo'] ?? '', Vestido::TIPOS)) {
            return 'Selecione um tipo válido (venda ou sob medida).';
        }

        $vestidoModel = new Vestido();
        if ($vestidoModel->codigoExiste($dados['codigo'], $ignorarId)) {
            return 'Já existe um vestido cadastrado com este código.';
        }

        return null;
    }

    private function registrarHistoricoInicial(int $vestidoId, string $status): void
    {
        $vestidoModel = new Vestido();
        // Usa alterarStatus com o mesmo status apenas para gravar a primeira linha do histórico
        $vestido = $vestidoModel->find($vestidoId);
        $db = \App\Core\Database::getConnection();
        $stmt = $db->prepare(
            'INSERT INTO historico_vestidos (vestido_id, usuario_id, cliente_id, status_anterior, status_novo, observacao)
             VALUES (:vestido_id, :usuario_id, :cliente_id, NULL, :status_novo, :observacao)'
        );
        $stmt->execute([
            'vestido_id'  => $vestidoId,
            'usuario_id'  => Auth::id(),
            'cliente_id'  => $vestido['cliente_id'] ?? null,
            'status_novo' => $status,
            'observacao'  => 'Cadastro inicial',
        ]);
    }
}

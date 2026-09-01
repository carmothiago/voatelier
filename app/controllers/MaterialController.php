<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Fornecedor;
use App\Models\Material;

class MaterialController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $this->requirePermission('estoque.visualizar');

        $busca = trim((string) $this->input('q', ''));
        $apenasAbaixoMinimo = $this->input('abaixo_minimo') === '1';

        $materialModel = new Material();

        $this->view('estoque/index', [
            'titulo'    => 'Estoque',
            'materiais' => $materialModel->listarAtivos($busca ?: null, $apenasAbaixoMinimo),
            'busca'     => $busca,
            'apenasAbaixoMinimo' => $apenasAbaixoMinimo,
            'totalAbaixoMinimo'  => $materialModel->contarAbaixoDoMinimo(),
        ]);
    }

    public function novoForm(): void
    {
        $this->requireLogin();
        $this->requirePermission('estoque.editar');

        $this->carregarFormulario(['material' => null]);
    }

    public function store(): void
    {
        $this->requireLogin();
        $this->requirePermission('estoque.editar');

        if (!Csrf::validate($this->input('csrf_token'))) {
            setFlash('erro', 'Sessão expirada. Tente novamente.');
            $this->redirect('/estoque/novo');
            return;
        }

        $dados = $this->dadosDoFormulario();
        $erro = $this->validar($dados, null);

        if ($erro) {
            setFlash('erro', $erro);
            $this->redirect('/estoque/novo');
            return;
        }

        $materialModel = new Material();
        $dados['criado_por'] = Auth::id();
        $dados['atualizado_por'] = Auth::id();
        $dados['quantidade'] = (float) ($this->input('quantidade_inicial', 0) ?: 0);

        $id = $materialModel->insert($dados);

        if ($dados['quantidade'] > 0) {
            $db = \App\Core\Database::getConnection();
            $stmt = $db->prepare(
                'INSERT INTO movimentacoes_estoque (material_id, tipo, quantidade, quantidade_resultante, motivo, usuario_id)
                 VALUES (:id, "entrada", :quantidade, :quantidade, "Estoque inicial", :usuario_id)'
            );
            $stmt->execute(['id' => $id, 'quantidade' => $dados['quantidade'], 'usuario_id' => Auth::id()]);
        }

        registrarAuditoria('estoque', 'criar', (string) $id, null, $dados);

        setFlash('sucesso', 'Material cadastrado com sucesso.');
        $this->redirect('/estoque/' . $id);
    }

    public function show(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('estoque.visualizar');

        $materialModel = new Material();
        $material = $materialModel->buscarComFornecedor((int) $id);

        if (!$material) {
            http_response_code(404);
            require APP_PATH . '/views/errors/404.php';
            return;
        }

        $this->view('estoque/show', [
            'titulo'      => $material['nome'],
            'material'    => $material,
            'movimentacoes' => $materialModel->historicoMovimentacoes((int) $id),
            'tiposMovimentacao' => Material::TIPOS_MOVIMENTACAO,
        ]);
    }

    public function editarForm(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('estoque.editar');

        $materialModel = new Material();
        $material = $materialModel->find((int) $id);

        if (!$material) {
            http_response_code(404);
            require APP_PATH . '/views/errors/404.php';
            return;
        }

        $this->carregarFormulario(['material' => $material]);
    }

    public function update(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('estoque.editar');

        if (!Csrf::validate($this->input('csrf_token'))) {
            setFlash('erro', 'Sessão expirada. Tente novamente.');
            $this->redirect('/estoque/' . $id . '/editar');
            return;
        }

        $materialModel = new Material();
        $materialAntigo = $materialModel->find((int) $id);

        if (!$materialAntigo) {
            http_response_code(404);
            require APP_PATH . '/views/errors/404.php';
            return;
        }

        $dados = $this->dadosDoFormulario();
        $erro = $this->validar($dados, (int) $id);

        if ($erro) {
            setFlash('erro', $erro);
            $this->redirect('/estoque/' . $id . '/editar');
            return;
        }

        $dados['atualizado_por'] = Auth::id();
        $materialModel->update((int) $id, $dados);

        registrarAuditoria('estoque', 'editar', $id, $materialAntigo, $dados);

        setFlash('sucesso', 'Material atualizado.');
        $this->redirect('/estoque/' . $id);
    }

    public function movimentar(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('estoque.editar');

        if (!Csrf::validate($this->input('csrf_token'))) {
            setFlash('erro', 'Sessão expirada. Tente novamente.');
            $this->redirect('/estoque/' . $id);
            return;
        }

        $tipo = (string) $this->input('tipo');
        $quantidade = (float) str_replace(',', '.', (string) $this->input('quantidade', '0'));
        $motivo = trim((string) $this->input('motivo', '')) ?: null;

        if (!array_key_exists($tipo, Material::TIPOS_MOVIMENTACAO) || $quantidade < 0) {
            setFlash('erro', 'Movimentação inválida.');
            $this->redirect('/estoque/' . $id);
            return;
        }

        $materialModel = new Material();
        $resultado = $materialModel->movimentar((int) $id, $tipo, $quantidade, Auth::id(), $motivo);

        if (!$resultado['ok']) {
            setFlash('erro', $resultado['erro']);
        } else {
            registrarAuditoria('estoque', 'movimentar', $id, null, ['tipo' => $tipo, 'quantidade' => $quantidade]);
            setFlash('sucesso', 'Movimentação registrada.');
        }

        $this->redirect('/estoque/' . $id);
    }

    public function excluir(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('estoque.editar');

        if (!Csrf::validate($this->input('csrf_token'))) {
            setFlash('erro', 'Sessão expirada. Tente novamente.');
            $this->redirect('/estoque');
            return;
        }

        $materialModel = new Material();
        $material = $materialModel->find((int) $id);

        if ($material) {
            $materialModel->excluirLogicamente((int) $id);
            registrarAuditoria('estoque', 'excluir', $id, $material, null);
        }

        setFlash('sucesso', 'Material removido da listagem.');
        $this->redirect('/estoque');
    }

    private function carregarFormulario(array $dados): void
    {
        $fornecedorModel = new Fornecedor();

        $this->view('estoque/form', array_merge([
            'titulo'      => isset($dados['material']['id']) ? 'Editar material' : 'Novo material',
            'fornecedores'=> $fornecedorModel->listarAtivos(),
        ], $dados));
    }

    private function dadosDoFormulario(): array
    {
        $dados = [];
        foreach (Material::CAMPOS_FORMULARIO as $campo) {
            $valor = trim((string) $this->input($campo, ''));
            $dados[$campo] = $valor === '' ? null : $valor;
        }

        foreach (['estoque_minimo', 'custo_unitario'] as $campoNumerico) {
            if ($dados[$campoNumerico] !== null) {
                $dados[$campoNumerico] = str_replace(',', '.', $dados[$campoNumerico]);
            }
        }

        if (empty($dados['unidade'])) {
            $dados['unidade'] = 'un';
        }

        return $dados;
    }

    private function validar(array $dados, ?int $ignorarId): ?string
    {
        if (empty($dados['codigo'])) {
            return 'Informe o código do material.';
        }

        if (empty($dados['nome'])) {
            return 'Informe o nome do material.';
        }

        $materialModel = new Material();
        if ($materialModel->codigoExiste($dados['codigo'], $ignorarId)) {
            return 'Já existe um material cadastrado com este código.';
        }

        return null;
    }
}

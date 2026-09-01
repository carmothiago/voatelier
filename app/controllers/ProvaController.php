<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Cliente;
use App\Models\Prova;
use App\Models\Usuario;
use App\Models\Vestido;

class ProvaController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $this->requirePermission('provas.visualizar');

        $status = (string) $this->input('status', '');
        $provaModel = new Prova();

        $this->view('provas/index', [
            'titulo'  => 'Provas',
            'provas'  => $provaModel->listarTodas($status ?: null),
            'statusFiltro' => $status,
            'statusLista'  => Prova::STATUS,
        ]);
    }

    public function novoForm(): void
    {
        $this->requireLogin();
        $this->requirePermission('provas.criar');

        $this->carregarFormulario(['cliente_id' => $this->input('cliente_id', '')]);
    }

    public function store(): void
    {
        $this->requireLogin();
        $this->requirePermission('provas.criar');

        if (!Csrf::validate($this->input('csrf_token'))) {
            setFlash('erro', 'Sessão expirada. Tente novamente.');
            $this->redirect('/provas/novo');
            return;
        }

        $clienteId = (int) $this->input('cliente_id');
        if (!$clienteId) {
            setFlash('erro', 'Selecione a cliente.');
            $this->redirect('/provas/novo');
            return;
        }

        $provaModel = new Prova();
        $dados = [
            'numero'         => $provaModel->proximoNumero($clienteId),
            'cliente_id'     => $clienteId,
            'vestido_id'     => $this->input('vestido_id') ?: null,
            'data_prova'     => $this->input('data_prova'),
            'responsavel_id' => $this->input('responsavel_id') ?: null,
            'status'         => $this->input('status', 'pendente'),
            'observacoes'    => trim((string) $this->input('observacoes', '')) ?: null,
            'criado_por'     => Auth::id(),
        ];

        if (empty($dados['data_prova'])) {
            setFlash('erro', 'Informe a data da prova.');
            $this->carregarFormulario($dados);
            return;
        }

        $id = $provaModel->insert($dados);

        registrarAuditoria('provas', 'criar', (string) $id, null, $dados);

        setFlash('sucesso', 'Prova nº ' . $dados['numero'] . ' registrada.');
        $this->redirect('/provas/' . $id);
    }

    public function show(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('provas.visualizar');

        $provaModel = new Prova();
        $prova = $provaModel->buscarCompleta((int) $id);

        if (!$prova) {
            http_response_code(404);
            require APP_PATH . '/views/errors/404.php';
            return;
        }

        $this->view('provas/show', [
            'titulo'   => 'Prova nº ' . $prova['numero'] . ' · ' . $prova['cliente_nome'],
            'prova'    => $prova,
            'ajustes'  => $provaModel->listarAjustes((int) $id),
            'anexos'   => $provaModel->listarAnexos((int) $id),
            'statusLista' => Prova::STATUS,
        ]);
    }

    public function mudarStatus(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('provas.editar');

        if (!Csrf::validate($this->input('csrf_token'))) {
            setFlash('erro', 'Sessão expirada. Tente novamente.');
            $this->redirect('/provas/' . $id);
            return;
        }

        $novoStatus = (string) $this->input('status');
        if (!array_key_exists($novoStatus, Prova::STATUS)) {
            setFlash('erro', 'Status inválido.');
            $this->redirect('/provas/' . $id);
            return;
        }

        $provaModel = new Prova();
        $provaModel->update((int) $id, ['status' => $novoStatus]);

        registrarAuditoria('provas', 'mudar_status', $id, null, ['status' => $novoStatus]);

        setFlash('sucesso', 'Status da prova atualizado.');
        $this->redirect('/provas/' . $id);
    }

    // -------------------------------------------------------------
    // Ajustes
    // -------------------------------------------------------------

    public function adicionarAjuste(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('provas.editar');

        if (!Csrf::validate($this->input('csrf_token'))) {
            setFlash('erro', 'Sessão expirada. Tente novamente.');
            $this->redirect('/provas/' . $id);
            return;
        }

        $descricao = trim((string) $this->input('descricao', ''));
        if ($descricao === '') {
            setFlash('erro', 'Descreva o ajuste necessário.');
            $this->redirect('/provas/' . $id);
            return;
        }

        $provaModel = new Prova();
        $provaModel->inserirAjuste([
            'prova_id'         => (int) $id,
            'descricao'        => $descricao,
            'parte_vestido'    => trim((string) $this->input('parte_vestido', '')) ?: null,
            'medida_atual'     => trim((string) $this->input('medida_atual', '')) ?: null,
            'medida_desejada'  => trim((string) $this->input('medida_desejada', '')) ?: null,
            'observacao'       => trim((string) $this->input('observacao', '')) ?: null,
            'status'           => 'pendente',
        ]);

        registrarAuditoria('provas', 'adicionar_ajuste', $id, null, ['descricao' => $descricao]);

        setFlash('sucesso', 'Ajuste adicionado.');
        $this->redirect('/provas/' . $id);
    }

    public function mudarStatusAjuste(string $id, string $ajusteId): void
    {
        $this->requireLogin();
        $this->requirePermission('provas.editar');

        if (!Csrf::validate($this->input('csrf_token'))) {
            setFlash('erro', 'Sessão expirada. Tente novamente.');
            $this->redirect('/provas/' . $id);
            return;
        }

        $novoStatus = (string) $this->input('status');
        if (!array_key_exists($novoStatus, Prova::STATUS)) {
            setFlash('erro', 'Status inválido.');
            $this->redirect('/provas/' . $id);
            return;
        }

        $provaModel = new Prova();
        $provaModel->atualizarStatusAjuste((int) $ajusteId, $novoStatus);

        setFlash('sucesso', 'Status do ajuste atualizado.');
        $this->redirect('/provas/' . $id);
    }

    // -------------------------------------------------------------
    // Fotos (anexos)
    // -------------------------------------------------------------

    public function enviarFoto(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('documentos.criar');

        if (!Csrf::validate($this->input('csrf_token'))) {
            setFlash('erro', 'Sessão expirada. Tente novamente.');
            $this->redirect('/provas/' . $id);
            return;
        }

        $provaModel = new Prova();
        if (!$provaModel->find((int) $id)) {
            http_response_code(404);
            require APP_PATH . '/views/errors/404.php';
            return;
        }

        $resultado = armazenarUpload($_FILES['foto'] ?? [], 'provas');

        if (!$resultado['ok']) {
            setFlash('erro', $resultado['erro']);
            $this->redirect('/provas/' . $id);
            return;
        }

        $provaModel->inserirAnexo(
            (int) $id,
            $resultado['nome_arquivo'],
            $resultado['nome_original'],
            $resultado['tamanho'],
            Auth::id()
        );

        registrarAuditoria('documentos', 'enviar_foto_prova', $id, null, ['arquivo' => $resultado['nome_arquivo']]);

        setFlash('sucesso', 'Foto anexada com sucesso.');
        $this->redirect('/provas/' . $id);
    }

    public function excluirFoto(string $id, string $anexoId): void
    {
        $this->requireLogin();
        $this->requirePermission('documentos.criar');

        if (!Csrf::validate($this->input('csrf_token'))) {
            setFlash('erro', 'Sessão expirada. Tente novamente.');
            $this->redirect('/provas/' . $id);
            return;
        }

        $provaModel = new Prova();
        $anexo = $provaModel->buscarAnexo((int) $anexoId);

        if ($anexo && (int) $anexo['prova_id'] === (int) $id) {
            $caminho = UPLOADS_PATH . '/provas/' . $anexo['nome_arquivo'];
            if (file_exists($caminho)) {
                unlink($caminho);
            }
            $provaModel->excluirAnexo((int) $anexoId);
            registrarAuditoria('documentos', 'excluir_foto_prova', $id, $anexo, null);
        }

        setFlash('sucesso', 'Foto removida.');
        $this->redirect('/provas/' . $id);
    }

    private function carregarFormulario(array $dados): void
    {
        $clienteModel = new Cliente();
        $vestidoModel = new Vestido();
        $usuarioModel = new Usuario();

        $this->view('provas/form', [
            'titulo'   => 'Nova prova',
            'prova'    => $dados,
            'clientes' => $clienteModel->listarAtivos(),
            'vestidos' => $vestidoModel->listarAtivos(),
            'usuarios' => $usuarioModel->all('nome'),
        ]);
    }
}

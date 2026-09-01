<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Cliente;
use App\Models\Producao;
use App\Models\Usuario;
use App\Models\Vestido;

class ProducaoController extends Controller
{
    public function kanban(): void
    {
        $this->requireLogin();
        $this->requirePermission('producao.visualizar');

        $producaoModel = new Producao();

        $this->view('producao/kanban', [
            'titulo'   => 'Produção',
            'projetos' => $producaoModel->listarParaKanban(),
            'etapas'   => Producao::ETAPAS,
            'atrasados'=> $producaoModel->listarAtrasados(),
        ]);
    }

    public function novoForm(): void
    {
        $this->requireLogin();
        $this->requirePermission('producao.editar');

        $this->carregarFormulario(['cliente_id' => $this->input('cliente_id', '')]);
    }

    public function store(): void
    {
        $this->requireLogin();
        $this->requirePermission('producao.editar');

        if (!Csrf::validate($this->input('csrf_token'))) {
            setFlash('erro', 'Sessão expirada. Tente novamente.');
            $this->redirect('/producao/novo');
            return;
        }

        $clienteId = (int) $this->input('cliente_id');
        if (!$clienteId) {
            setFlash('erro', 'Selecione a cliente.');
            $this->redirect('/producao/novo');
            return;
        }

        $dados = [
            'cliente_id'     => $clienteId,
            'vestido_id'     => $this->input('vestido_id') ?: null,
            'responsavel_id' => $this->input('responsavel_id') ?: null,
            'etapa'          => $this->input('etapa', 'projeto'),
            'data_inicio'    => $this->input('data_inicio') ?: null,
            'prazo'          => $this->input('prazo') ?: null,
            'observacoes'    => trim((string) $this->input('observacoes', '')) ?: null,
            'criado_por'     => Auth::id(),
        ];

        $producaoModel = new Producao();
        $id = $producaoModel->insert($dados);

        $db = \App\Core\Database::getConnection();
        $stmt = $db->prepare(
            'INSERT INTO producao_historico (producao_id, usuario_id, etapa_anterior, etapa_nova, observacao)
             VALUES (:id, :usuario_id, NULL, :etapa, :observacao)'
        );
        $stmt->execute([
            'id'         => $id,
            'usuario_id' => Auth::id(),
            'etapa'      => $dados['etapa'],
            'observacao' => 'Projeto criado',
        ]);

        registrarAuditoria('producao', 'criar', (string) $id, null, $dados);

        setFlash('sucesso', 'Projeto de produção criado.');
        $this->redirect('/producao');
    }

    public function show(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('producao.visualizar');

        $producaoModel = new Producao();
        $producao = $producaoModel->buscarCompleta((int) $id);

        if (!$producao) {
            http_response_code(404);
            require APP_PATH . '/views/errors/404.php';
            return;
        }

        $this->view('producao/show', [
            'titulo'    => 'Produção · ' . $producao['cliente_nome'],
            'producao'  => $producao,
            'historico' => $producaoModel->historico((int) $id),
            'etapas'    => Producao::ETAPAS,
        ]);
    }

    /**
     * Move um projeto entre etapas via AJAX (drag-and-drop), no mesmo
     * padrão usado no Kanban do CRM.
     */
    public function mover(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!Auth::check()) {
            http_response_code(401);
            echo json_encode(['ok' => false, 'erro' => 'Não autenticado.']);
            return;
        }

        if (!Auth::can('producao.editar')) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'erro' => 'Sem permissão para mover projetos de produção.']);
            return;
        }

        if (!Csrf::validate($this->input('csrf_token'))) {
            http_response_code(419);
            echo json_encode(['ok' => false, 'erro' => 'Sessão expirada. Recarregue a página.']);
            return;
        }

        $producaoId = (int) $this->input('producao_id');
        $novaEtapa = (string) $this->input('nova_etapa');

        if (!array_key_exists($novaEtapa, Producao::ETAPAS)) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'erro' => 'Etapa inválida.']);
            return;
        }

        $producaoModel = new Producao();
        $ok = $producaoModel->moverEtapa($producaoId, $novaEtapa, Auth::id());

        if ($ok) {
            registrarAuditoria('producao', 'mover_etapa', (string) $producaoId, null, ['etapa' => $novaEtapa]);
        }

        echo json_encode(['ok' => $ok]);
    }

    private function carregarFormulario(array $dados): void
    {
        $clienteModel = new Cliente();
        $vestidoModel = new Vestido();
        $usuarioModel = new Usuario();

        $this->view('producao/form', [
            'titulo'   => 'Novo projeto de produção',
            'producao' => $dados,
            'clientes' => $clienteModel->listarAtivos(),
            'vestidos' => $vestidoModel->listarAtivos(),
            'usuarios' => $usuarioModel->all('nome'),
            'etapas'   => Producao::ETAPAS,
        ]);
    }
}

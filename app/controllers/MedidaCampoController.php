<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\MedidaCampo;

/**
 * Gerencia o catálogo de campos de medida configuráveis.
 * Acesso restrito a: costureira, gerente e administrador
 * (qualquer perfil com a permissão 'medidas.configurar').
 */
class MedidaCampoController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $this->requirePermission('medidas.configurar');

        $campoModel = new MedidaCampo();

        $this->view('medidas/campos', [
            'titulo' => 'Configurar campos de medida',
            'campos' => $campoModel->todos(),
        ]);
    }

    /**
     * Adiciona um novo campo ao catálogo.
     */
    public function store(): void
    {
        $this->requireLogin();
        $this->requirePermission('medidas.configurar');

        if (!Csrf::validate($this->input('csrf_token'))) {
            setFlash('erro', 'Sessão expirada. Tente novamente.');
            $this->redirect('/medidas/campos');
            return;
        }

        $label = trim((string) $this->input('label', ''));

        if (empty($label)) {
            setFlash('erro', 'Informe o nome do campo.');
            $this->redirect('/medidas/campos');
            return;
        }

        if (mb_strlen($label) > 100) {
            setFlash('erro', 'O nome do campo deve ter no máximo 100 caracteres.');
            $this->redirect('/medidas/campos');
            return;
        }

        $campoModel = new MedidaCampo();
        $slug = MedidaCampo::gerarSlug($label);

        if (empty($slug)) {
            setFlash('erro', 'Nome inválido. Use letras e números.');
            $this->redirect('/medidas/campos');
            return;
        }

        if ($campoModel->slugEmUso($slug)) {
            setFlash('erro', "Já existe um campo com o nome equivalente a \"{$label}\" (slug: {$slug}).");
            $this->redirect('/medidas/campos');
            return;
        }

        // Posiciona o novo campo ao final da lista
        $todos = $campoModel->todos();
        $proximaOrdem = empty($todos) ? 1 : max(array_column($todos, 'ordem')) + 1;

        $id = $campoModel->insert([
            'slug'      => $slug,
            'label'     => $label,
            'ordem'     => $proximaOrdem,
            'ativo'     => 1,
            'criado_por' => Auth::id(),
        ]);

        registrarAuditoria('medidas', 'campo_criado', (string) $id, null, ['label' => $label, 'slug' => $slug]);

        setFlash('sucesso', "Campo \"{$label}\" adicionado com sucesso.");
        $this->redirect('/medidas/campos');
    }

    /**
     * Ativa ou desativa um campo (não exclui — preserva histórico).
     */
    public function toggle(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('medidas.configurar');

        if (!Csrf::validate($this->input('csrf_token'))) {
            setFlash('erro', 'Sessão expirada. Tente novamente.');
            $this->redirect('/medidas/campos');
            return;
        }

        $campoModel = new MedidaCampo();
        $campo = $campoModel->find((int) $id);

        if (!$campo) {
            setFlash('erro', 'Campo não encontrado.');
            $this->redirect('/medidas/campos');
            return;
        }

        $campoModel->toggleAtivo((int) $id);

        $novoEstado = $campo['ativo'] ? 'desativado' : 'ativado';
        registrarAuditoria('medidas', "campo_{$novoEstado}", $id, $campo, null);

        setFlash('sucesso', "Campo \"{$campo['label']}\" {$novoEstado}.");
        $this->redirect('/medidas/campos');
    }

    /**
     * Salva a nova ordem dos campos via drag-and-drop.
     * Recebe POST com campo 'ordem[]' = array de ids na sequência desejada.
     */
    public function reordenar(): void
    {
        $this->requireLogin();
        $this->requirePermission('medidas.configurar');

        if (!Csrf::validate($this->input('csrf_token'))) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'erro' => 'Token inválido.']);
            return;
        }

        $ids = $_POST['ordem'] ?? [];

        if (!is_array($ids) || empty($ids)) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'erro' => 'Nenhuma ordem recebida.']);
            return;
        }

        // Garante que só inteiros passam
        $ids = array_map('intval', $ids);
        $ids = array_filter($ids, fn($v) => $v > 0);

        $campoModel = new MedidaCampo();
        $campoModel->reordenar(array_values($ids));

        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
    }
}

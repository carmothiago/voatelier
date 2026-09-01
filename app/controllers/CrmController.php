<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Cliente;

class CrmController extends Controller
{
    public function kanban(): void
    {
        $this->requireLogin();
        $this->requirePermission('crm.visualizar');

        $clienteModel = new Cliente();

        $this->view('crm/kanban', [
            'titulo'   => 'CRM · Pipeline comercial',
            'clientes' => $clienteModel->listarParaKanban(),
            'etapas'   => Cliente::ETAPAS_CRM,
        ]);
    }

    /**
     * Recebe a movimentação de um card via fetch/AJAX (JSON) e retorna JSON.
     * Mantém a mesma validação de permissão e CSRF do restante do sistema.
     */
    public function mover(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!Auth::check()) {
            http_response_code(401);
            echo json_encode(['ok' => false, 'erro' => 'Não autenticado.']);
            return;
        }

        if (!Auth::can('crm.editar')) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'erro' => 'Sem permissão para mover cards do CRM.']);
            return;
        }

        if (!Csrf::validate($this->input('csrf_token'))) {
            http_response_code(419);
            echo json_encode(['ok' => false, 'erro' => 'Sessão expirada. Recarregue a página.']);
            return;
        }

        $clienteId = (int) $this->input('cliente_id');
        $novaEtapa = (string) $this->input('nova_etapa');
        $motivoPerda = trim((string) $this->input('motivo_perda', ''));

        if (!array_key_exists($novaEtapa, Cliente::ETAPAS_CRM)) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'erro' => 'Etapa inválida.']);
            return;
        }

        $clienteModel = new Cliente();
        $cliente = $clienteModel->find($clienteId);

        if (!$cliente) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'erro' => 'Cliente não encontrada.']);
            return;
        }

        $ok = $clienteModel->moverEtapa($clienteId, $novaEtapa, Auth::id());

        if ($ok && $novaEtapa === 'perdido' && $motivoPerda !== '') {
            $clienteModel->update($clienteId, ['motivo_perda' => $motivoPerda]);
        }

        if ($ok) {
            registrarAuditoria('crm', 'mover_etapa', (string) $clienteId, ['etapa_crm' => $cliente['etapa_crm']], ['etapa_crm' => $novaEtapa]);
        }

        echo json_encode(['ok' => $ok]);
    }
}

<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Anexo;
use App\Models\Cliente;

class DocumentoController extends Controller
{
    public function enviar(string $clienteId): void
    {
        $this->requireLogin();
        $this->requirePermission('documentos.criar');

        if (!Csrf::validate($this->input('csrf_token'))) {
            setFlash('erro', 'Sessão expirada. Tente novamente.');
            $this->redirect('/clientes/' . $clienteId);
            return;
        }

        $clienteModel = new Cliente();
        if (!$clienteModel->find((int) $clienteId)) {
            http_response_code(404);
            require APP_PATH . '/views/errors/404.php';
            return;
        }

        $resultado = armazenarUpload($_FILES['arquivo'] ?? [], 'documentos');

        if (!$resultado['ok']) {
            setFlash('erro', $resultado['erro']);
            $this->redirect('/clientes/' . $clienteId);
            return;
        }

        $anexoModel = new Anexo();
        $anexoModel->insert([
            'cliente_id'    => (int) $clienteId,
            'nome_arquivo'  => $resultado['nome_arquivo'],
            'nome_original' => $resultado['nome_original'],
            'tamanho_bytes' => $resultado['tamanho'],
            'descricao'     => trim((string) $this->input('descricao', '')) ?: null,
            'criado_por'    => Auth::id(),
        ]);

        registrarAuditoria('documentos', 'enviar', $clienteId, null, ['arquivo' => $resultado['nome_arquivo']]);

        setFlash('sucesso', 'Documento anexado com sucesso.');
        $this->redirect('/clientes/' . $clienteId);
    }

    public function excluir(string $clienteId, string $anexoId): void
    {
        $this->requireLogin();
        $this->requirePermission('documentos.criar');

        if (!Csrf::validate($this->input('csrf_token'))) {
            setFlash('erro', 'Sessão expirada. Tente novamente.');
            $this->redirect('/clientes/' . $clienteId);
            return;
        }

        $anexoModel = new Anexo();
        $anexo = $anexoModel->find((int) $anexoId);

        if ($anexo && (int) $anexo['cliente_id'] === (int) $clienteId) {
            $caminho = UPLOADS_PATH . '/documentos/' . $anexo['nome_arquivo'];
            if (file_exists($caminho)) {
                unlink($caminho);
            }
            $anexoModel->delete((int) $anexoId);
            registrarAuditoria('documentos', 'excluir', $clienteId, $anexo, null);
        }

        setFlash('sucesso', 'Documento removido.');
        $this->redirect('/clientes/' . $clienteId);
    }
}

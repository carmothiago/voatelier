<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Cliente;
use App\Models\Medida;

class MedidaController extends Controller
{
    public function index(string $clienteId): void
    {
        $this->requireLogin();
        $this->requirePermission('medidas.visualizar');

        $clienteModel = new Cliente();
        $cliente = $clienteModel->find((int) $clienteId);

        if (!$cliente) {
            http_response_code(404);
            require APP_PATH . '/views/errors/404.php';
            return;
        }

        $medidaModel = new Medida();

        $this->view('medidas/index', [
            'titulo'   => 'Medidas · ' . $cliente['nome_completo'],
            'cliente'  => $cliente,
            'medidas'  => $medidaModel->historicoDaCliente((int) $clienteId),
            'labels'   => Medida::LABELS,
        ]);
    }

    public function store(string $clienteId): void
    {
        $this->requireLogin();
        $this->requirePermission('medidas.criar');

        if (!Csrf::validate($this->input('csrf_token'))) {
            setFlash('erro', 'Sessão expirada. Tente novamente.');
            $this->redirect('/clientes/' . $clienteId . '/medidas');
            return;
        }

        $clienteModel = new Cliente();
        if (!$clienteModel->find((int) $clienteId)) {
            http_response_code(404);
            require APP_PATH . '/views/errors/404.php';
            return;
        }

        $dados = ['cliente_id' => (int) $clienteId, 'usuario_id' => Auth::id()];

        foreach (Medida::CAMPOS as $campo) {
            $valor = trim((string) $this->input($campo, ''));
            $dados[$campo] = $valor === '' ? null : str_replace(',', '.', $valor);
        }

        $dados['observacoes'] = trim((string) $this->input('observacoes', '')) ?: null;

        $medidaModel = new Medida();
        $id = $medidaModel->insert($dados);

        registrarAuditoria('medidas', 'criar', (string) $id, null, $dados);

        setFlash('sucesso', 'Nova ficha de medidas registrada.');
        $this->redirect('/clientes/' . $clienteId . '/medidas');
    }
}

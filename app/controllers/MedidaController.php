<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Cliente;
use App\Models\Medida;
use App\Models\MedidaCampo;

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
        $campoModel  = new MedidaCampo();

        // Campos ativos do catálogo dinâmico, indexados por slug
        $camposAtivos = $campoModel->camposAtivos();

        // Se o catálogo dinâmico ainda estiver vazio (instalação nova antes
        // do seed), cai de volta para os labels fixos como segurança.
        $labelsExibicao = empty($camposAtivos)
            ? Medida::LABELS
            : array_map(fn($c) => $c['label'], $camposAtivos);

        $this->view('medidas/index', [
            'titulo'       => 'Medidas · ' . $cliente['nome_completo'],
            'cliente'      => $cliente,
            'medidas'      => $medidaModel->historicoDaCliente((int) $clienteId),
            'labels'       => $labelsExibicao,   // label exibido por slug
            'camposAtivos' => $camposAtivos,      // metadados completos (id, slug, label, ordem)
            'podeCofigurar'=> \App\Core\Auth::can('medidas.configurar'),
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

        $campoModel   = new MedidaCampo();
        $camposAtivos = $campoModel->camposAtivos();

        // Dados base da ficha (colunas fixas da tabela medidas)
        $dadosFixos = [
            'cliente_id'  => (int) $clienteId,
            'usuario_id'  => Auth::id(),
            'observacoes' => trim((string) $this->input('observacoes', '')) ?: null,
        ];

        // Campos fixos legados: presentes na tabela medidas como colunas DECIMAL
        // Continuam sendo gravados diretamente para manter retrocompatibilidade
        foreach (Medida::CAMPOS as $campo) {
            $valor = trim((string) $this->input($campo, ''));
            $dadosFixos[$campo] = $valor === '' ? null : str_replace(',', '.', $valor);
        }

        // Campos dinâmicos: slugs que NÃO existem como coluna fixa
        $slugsFixos     = array_flip(Medida::CAMPOS);
        $dadosDinamicos = [];

        foreach ($camposAtivos as $slug => $campo) {
            if (isset($slugsFixos[$slug])) {
                // Já foi tratado acima como campo fixo
                continue;
            }
            $valor = trim((string) $this->input($slug, ''));
            if ($valor !== '') {
                $dadosDinamicos[$slug] = str_replace(',', '.', $valor);
            }
        }

        $medidaModel = new Medida();
        $id = $medidaModel->inserirComDinamicos($dadosFixos, $dadosDinamicos, $camposAtivos);

        registrarAuditoria('medidas', 'criar', (string) $id, null, array_merge($dadosFixos, $dadosDinamicos));

        setFlash('sucesso', 'Nova ficha de medidas registrada.');
        $this->redirect('/clientes/' . $clienteId . '/medidas');
    }
}

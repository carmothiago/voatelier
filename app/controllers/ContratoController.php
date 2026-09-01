<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\PdfGerador;
use App\Models\Cliente;
use App\Models\Contrato;
use App\Models\Vestido;

class ContratoController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $this->requirePermission('contratos.visualizar');

        $contratoModel = new Contrato();

        $this->view('contratos/index', [
            'titulo'    => 'Contratos',
            'contratos' => $contratoModel->listarTodos(),
        ]);
    }

    public function novoForm(): void
    {
        $this->requireLogin();
        $this->requirePermission('contratos.criar');

        $clienteModel = new Cliente();
        $vestidoModel = new Vestido();

        $this->view('contratos/form', [
            'titulo'   => 'Novo contrato',
            'contrato' => ['cliente_id' => $this->input('cliente_id', '')],
            'clientes' => $clienteModel->listarAtivos(),
            'vestidos' => $vestidoModel->listarAtivos(),
        ]);
    }

    public function store(): void
    {
        $this->requireLogin();
        $this->requirePermission('contratos.criar');

        if (!Csrf::validate($this->input('csrf_token'))) {
            setFlash('erro', 'Sessão expirada. Tente novamente.');
            $this->redirect('/contratos/novo');
            return;
        }

        $clienteId = (int) $this->input('cliente_id');
        if (!$clienteId) {
            setFlash('erro', 'Selecione a cliente.');
            $this->redirect('/contratos/novo');
            return;
        }

        $dados = [
            'cliente_id'      => $clienteId,
            'vestido_id'      => $this->input('vestido_id') ?: null,
            'data_contrato'   => $this->input('data_contrato', date('Y-m-d')),
            'valor'           => str_replace(',', '.', (string) $this->input('valor', '0')),
            'forma_pagamento' => trim((string) $this->input('forma_pagamento', '')) ?: null,
            'data_entrega'    => $this->input('data_entrega') ?: null,
            'data_devolucao'  => $this->input('data_devolucao') ?: null,
            'clausulas'       => trim((string) $this->input('clausulas', '')) ?: null,
            'observacoes'     => trim((string) $this->input('observacoes', '')) ?: null,
            'criado_por'      => Auth::id(),
        ];

        $contratoModel = new Contrato();
        $id = $contratoModel->insert($dados);

        registrarAuditoria('contratos', 'criar', (string) $id, null, $dados);

        setFlash('sucesso', 'Contrato registrado. Gere o PDF quando quiser.');
        $this->redirect('/contratos/' . $id);
    }

    public function show(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('contratos.visualizar');

        $contratoModel = new Contrato();
        $contrato = $contratoModel->buscarCompleto((int) $id);

        if (!$contrato) {
            http_response_code(404);
            require APP_PATH . '/views/errors/404.php';
            return;
        }

        $this->view('contratos/show', [
            'titulo'   => 'Contrato · ' . $contrato['cliente_nome'],
            'contrato' => $contrato,
        ]);
    }

    /**
     * Gera (ou regenera) o PDF do contrato a partir dos dados atuais e
     * o disponibiliza para download.
     */
    public function gerarPdf(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('contratos.visualizar');

        $contratoModel = new Contrato();
        $contrato = $contratoModel->buscarCompleto((int) $id);

        if (!$contrato) {
            http_response_code(404);
            require APP_PATH . '/views/errors/404.php';
            return;
        }

        $pdf = new PdfGerador();
        $pdf->titulo('Contrato de Prestação de Serviços');
        $pdf->subtitulo(APP_NAME);
        $pdf->espaco(6);

        $pdf->campo('Cliente', $contrato['cliente_nome']);
        if (!empty($contrato['cliente_cpf'])) {
            $pdf->campo('CPF', $contrato['cliente_cpf']);
        }
        if (!empty($contrato['cliente_endereco'])) {
            $endereco = $contrato['cliente_endereco'];
            if (!empty($contrato['cliente_cidade'])) {
                $endereco .= ' - ' . $contrato['cliente_cidade'] . '/' . $contrato['cliente_estado'];
            }
            $pdf->campo('Endereço', $endereco);
        }

        $pdf->espaco(6);
        $pdf->campo('Data do contrato', formatarData($contrato['data_contrato']));
        if (!empty($contrato['vestido_nome'])) {
            $pdf->campo('Vestido', $contrato['vestido_codigo'] . ' — ' . $contrato['vestido_nome']);
        }
        $pdf->campo('Valor', formatarMoeda((float) $contrato['valor']));
        if (!empty($contrato['forma_pagamento'])) {
            $pdf->campo('Forma de pagamento', $contrato['forma_pagamento']);
        }
        if (!empty($contrato['data_entrega'])) {
            $pdf->campo('Data de entrega prevista', formatarData($contrato['data_entrega']));
        }
        if (!empty($contrato['data_devolucao'])) {
            $pdf->campo('Data de devolução prevista', formatarData($contrato['data_devolucao']));
        }

        if (!empty($contrato['clausulas'])) {
            $pdf->espaco(10);
            $pdf->subtitulo('Cláusulas');
            $pdf->paragrafo($contrato['clausulas']);
        }

        if (!empty($contrato['observacoes'])) {
            $pdf->espaco(6);
            $pdf->subtitulo('Observações');
            $pdf->paragrafo($contrato['observacoes']);
        }

        $pdf->espaco(24);
        $pdf->linhaDivisoria();
        $pdf->espaco(30);
        $pdf->campo('Assinatura da cliente', '_______________________________________________');
        $pdf->espaco(20);
        $pdf->campo('Assinatura do ateliê', '_______________________________________________');

        $pastaContratos = UPLOADS_PATH . '/contratos';
        if (!is_dir($pastaContratos)) {
            mkdir($pastaContratos, 0755, true);
        }

        $nomeArquivo = 'contrato-' . $contrato['id'] . '-' . bin2hex(random_bytes(6)) . '.pdf';
        file_put_contents($pastaContratos . '/' . $nomeArquivo, $pdf->gerar());

        // Remove o PDF anterior, se existir, para não acumular arquivos órfãos
        if (!empty($contrato['arquivo_pdf'])) {
            $antigo = $pastaContratos . '/' . $contrato['arquivo_pdf'];
            if (file_exists($antigo)) {
                unlink($antigo);
            }
        }

        $contratoModel->salvarNomeArquivoPdf((int) $id, $nomeArquivo);

        registrarAuditoria('contratos', 'gerar_pdf', $id, null, ['arquivo' => $nomeArquivo]);

        $this->redirect('/uploads/contratos/' . $nomeArquivo);
    }
}

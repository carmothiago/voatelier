<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Cliente;
use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Models\Material;
use App\Models\Producao;

class RelatorioController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $this->requirePermission('relatorios.visualizar');

        $this->view('relatorios/index', [
            'titulo' => 'Relatórios',
        ]);
    }

    public function comercial(): void
    {
        $this->requireLogin();
        $this->requirePermission('relatorios.visualizar');

        $clienteModel = new Cliente();
        $etapas = $clienteModel->contarPorEtapa();
        $total = array_sum($etapas);
        $fechados = $etapas['contrato_fechado'] ?? 0;
        $perdidos = $etapas['perdido'] ?? 0;
        $taxaConversao = $total > 0 ? round(($fechados / $total) * 100, 1) : 0;

        if ($this->input('exportar') === 'csv') {
            $this->exportarCsv('relatorio_comercial', ['Etapa', 'Quantidade'], array_map(
                fn($slug, $qtd) => [Cliente::ETAPAS_CRM[$slug] ?? $slug, $qtd],
                array_keys($etapas),
                array_values($etapas)
            ));
            return;
        }

        $this->view('relatorios/comercial', [
            'titulo'         => 'Relatório Comercial',
            'etapas'         => $etapas,
            'nomesEtapas'    => Cliente::ETAPAS_CRM,
            'total'          => $total,
            'taxaConversao'  => $taxaConversao,
            'novosNoMes'     => $clienteModel->contarNovosContatosNoMes(),
        ]);
    }

    public function financeiro(): void
    {
        $this->requireLogin();
        $this->requirePermission('relatorios.visualizar');

        $receberModel = new ContaReceber();
        $pagarModel = new ContaPagar();

        $dados = [
            'receitasMes'  => $receberModel->totalReceitasNoMes(),
            'despesasMes'  => $pagarModel->totalDespesasNoMes(),
            'aReceber'     => $receberModel->totalAReceber(),
            'aPagar'       => $pagarModel->totalAPagar(),
            'vencidoReceber' => $receberModel->totalVencido(),
            'vencidoPagar' => $pagarModel->totalVencido(),
        ];
        $dados['saldoMes'] = $dados['receitasMes'] - $dados['despesasMes'];

        if ($this->input('exportar') === 'csv') {
            $this->exportarCsv('relatorio_financeiro', ['Indicador', 'Valor (R$)'], [
                ['Receitas do mês (pagas)', number_format($dados['receitasMes'], 2, ',', '.')],
                ['Despesas do mês (pagas)', number_format($dados['despesasMes'], 2, ',', '.')],
                ['Saldo do mês', number_format($dados['saldoMes'], 2, ',', '.')],
                ['A receber (pendente)', number_format($dados['aReceber'], 2, ',', '.')],
                ['A pagar (pendente)', number_format($dados['aPagar'], 2, ',', '.')],
                ['Vencido a receber', number_format($dados['vencidoReceber'], 2, ',', '.')],
                ['Vencido a pagar', number_format($dados['vencidoPagar'], 2, ',', '.')],
            ]);
            return;
        }

        $this->view('relatorios/financeiro', array_merge(['titulo' => 'Relatório Financeiro'], $dados));
    }

    public function producao(): void
    {
        $this->requireLogin();
        $this->requirePermission('relatorios.visualizar');

        $producaoModel = new Producao();
        $projetos = $producaoModel->listarParaKanban();
        $atrasados = $producaoModel->listarAtrasados();

        $porEtapa = array_fill_keys(array_keys(Producao::ETAPAS), 0);
        foreach ($projetos as $p) {
            $porEtapa[$p['etapa']]++;
        }

        if ($this->input('exportar') === 'csv') {
            $this->exportarCsv('relatorio_producao', ['Etapa', 'Quantidade'], array_map(
                fn($slug, $qtd) => [Producao::ETAPAS[$slug] ?? $slug, $qtd],
                array_keys($porEtapa),
                array_values($porEtapa)
            ));
            return;
        }

        $this->view('relatorios/producao', [
            'titulo'    => 'Relatório de Produção',
            'porEtapa'  => $porEtapa,
            'etapas'    => Producao::ETAPAS,
            'atrasados' => $atrasados,
            'total'     => count($projetos),
        ]);
    }

    public function estoque(): void
    {
        $this->requireLogin();
        $this->requirePermission('relatorios.visualizar');

        $materialModel = new Material();
        $materiais = $materialModel->listarAtivos();
        $abaixoMinimo = $materialModel->listarAbaixoDoMinimo();

        $valorTotalEstoque = array_sum(array_map(
            fn($m) => (float) $m['quantidade'] * (float) ($m['custo_unitario'] ?? 0),
            $materiais
        ));

        if ($this->input('exportar') === 'csv') {
            $this->exportarCsv('relatorio_estoque', ['Código', 'Nome', 'Quantidade', 'Mínimo', 'Fornecedor'], array_map(
                fn($m) => [$m['codigo'], $m['nome'], $m['quantidade'], $m['estoque_minimo'], $m['fornecedor_nome'] ?? ''],
                $materiais
            ));
            return;
        }

        $this->view('relatorios/estoque', [
            'titulo'            => 'Relatório de Estoque',
            'materiais'         => $materiais,
            'abaixoMinimo'      => $abaixoMinimo,
            'valorTotalEstoque' => $valorTotalEstoque,
        ]);
    }

    /**
     * Gera e envia um arquivo CSV para download com os dados informados.
     */
    private function exportarCsv(string $nomeArquivo, array $cabecalho, array $linhas): void
    {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $nomeArquivo . '_' . date('Y-m-d') . '.csv"');

        $saida = fopen('php://output', 'w');
        // BOM UTF-8 para o Excel reconhecer acentuação corretamente
        fwrite($saida, "\xEF\xBB\xBF");
        fputcsv($saida, $cabecalho, ';');
        foreach ($linhas as $linha) {
            fputcsv($saida, $linha, ';');
        }
        fclose($saida);
    }
}

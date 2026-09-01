<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Agendamento;
use App\Models\Cliente;
use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Models\Material;
use App\Models\Producao;
use App\Models\Vestido;

class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();

        if (Auth::precisaTrocarSenha()) {
            $this->redirect('/trocar-senha');
            return;
        }

        $clienteModel = new Cliente();
        $agendamentoModel = new Agendamento();
        $vestidoModel = new Vestido();
        $producaoModel = new Producao();

        $agendaHoje = $agendamentoModel->listarPorData(date('Y-m-d'));
        $resumoHoje = $agendamentoModel->resumoHoje();
        $etapasCrm = $clienteModel->contarPorEtapa();
        $casamentosProximos = $clienteModel->casamentosProximos(30);
        $vestidosPorStatus = $vestidoModel->contarPorStatus();
        $projetosAtrasados = $producaoModel->listarAtrasados();
        $materialModel = new Material();
        $materiaisAbaixoMinimo = $materialModel->listarAbaixoDoMinimo();
        $receberModel = new ContaReceber();
        $pagarModel = new ContaPagar();

        $this->view('dashboard/index', [
            'titulo'              => 'Painel',
            'usuario'             => Auth::user(),
            'agendaHoje'          => $agendaHoje,
            'totalAgendaHoje'     => count($agendaHoje),
            'resumoHoje'          => $resumoHoje,
            'novosContatosMes'    => $clienteModel->contarNovosContatosNoMes(),
            'etapasCrm'           => $etapasCrm,
            'nomesEtapasCrm'      => Cliente::ETAPAS_CRM,
            'casamentosProximos'  => $casamentosProximos,
            'tiposAgendamento'    => Agendamento::TIPOS,
            'vestidosEmProducao'  => $vestidosPorStatus['em_producao'] ?? 0,
            'totalEmProducao'     => $producaoModel->contarEmProducao(),
            'projetosAtrasados'   => $projetosAtrasados,
            'projetosProximoPrazo'=> $producaoModel->contarProximosDoPrazo(7),
            'materiaisAbaixoMinimo' => $materiaisAbaixoMinimo,
            'receitasMes'         => $receberModel->totalReceitasNoMes(),
            'despesasMes'         => $pagarModel->totalDespesasNoMes(),
            'aReceber'            => $receberModel->totalAReceber(),
            'vencidoReceber'      => $receberModel->totalVencido(),
            'vencidoPagar'        => $pagarModel->totalVencido(),
        ]);
    }
}

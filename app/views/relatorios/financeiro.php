<p style="margin-top:-8px;"><a href="<?= url('/relatorios') ?>">← Voltar aos relatórios</a></p>

<div class="cartoes-resumo">
    <div class="cartao"><h3>Receitas do mês</h3><div class="valor"><?= formatarMoeda($receitasMes) ?></div></div>
    <div class="cartao"><h3>Despesas do mês</h3><div class="valor"><?= formatarMoeda($despesasMes) ?></div></div>
    <div class="cartao"><h3>Saldo do mês</h3><div class="valor" style="<?= $saldoMes < 0 ? 'color:var(--cor-erro);' : '' ?>"><?= formatarMoeda($saldoMes) ?></div></div>
</div>

<div class="painel">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
        <h3 style="margin:0;">Indicadores detalhados</h3>
        <a href="<?= url('/relatorios/financeiro?exportar=csv') ?>" class="botao botao-secundario" style="width:auto;padding:8px 16px;text-decoration:none;">Exportar CSV</a>
    </div>
    <table class="tabela-elegante">
        <tr><td>A receber (pendente)</td><td style="text-align:right;font-weight:600;"><?= formatarMoeda($aReceber) ?></td></tr>
        <tr><td>A pagar (pendente)</td><td style="text-align:right;font-weight:600;"><?= formatarMoeda($aPagar) ?></td></tr>
        <tr><td>Vencido a receber</td><td style="text-align:right;font-weight:600;color:var(--cor-erro);"><?= formatarMoeda($vencidoReceber) ?></td></tr>
        <tr><td>Vencido a pagar</td><td style="text-align:right;font-weight:600;color:var(--cor-erro);"><?= formatarMoeda($vencidoPagar) ?></td></tr>
    </table>
</div>

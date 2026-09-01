<div class="cartoes-resumo">
    <div class="cartao">
        <h3>Receitas do mês</h3>
        <div class="valor"><?= formatarMoeda($receitasMes) ?></div>
    </div>
    <div class="cartao">
        <h3>Despesas do mês</h3>
        <div class="valor"><?= formatarMoeda($despesasMes) ?></div>
    </div>
    <div class="cartao">
        <h3>Saldo do mês</h3>
        <div class="valor" style="<?= ($receitasMes - $despesasMes) < 0 ? 'color:var(--cor-erro);' : '' ?>">
            <?= formatarMoeda($receitasMes - $despesasMes) ?>
        </div>
    </div>
</div>

<?php if ($qtdVencidasReceber > 0 || $qtdVencidasPagar > 0): ?>
    <div class="alerta alerta-erro">
        ⚠️
        <?php if ($qtdVencidasReceber > 0): ?>
            <?= $qtdVencidasReceber ?> conta(s) a receber vencida(s) (<?= formatarMoeda($vencidoReceber) ?>)
        <?php endif; ?>
        <?php if ($qtdVencidasReceber > 0 && $qtdVencidasPagar > 0): ?> · <?php endif; ?>
        <?php if ($qtdVencidasPagar > 0): ?>
            <?= $qtdVencidasPagar ?> conta(s) a pagar vencida(s) (<?= formatarMoeda($vencidoPagar) ?>)
        <?php endif; ?>
    </div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start;">
    <div class="painel">
        <h3 style="margin-top:0;">Contas a receber</h3>
        <table class="tabela-elegante">
            <tr><td>Pendente (a receber)</td><td style="text-align:right;font-weight:600;"><?= formatarMoeda($aReceber) ?></td></tr>
            <tr><td>Vencido</td><td style="text-align:right;font-weight:600;color:var(--cor-erro);"><?= formatarMoeda($vencidoReceber) ?></td></tr>
        </table>
        <p style="margin-top:14px;"><a href="<?= url('/financeiro/receber') ?>">Ver contas a receber →</a></p>
    </div>

    <div class="painel">
        <h3 style="margin-top:0;">Contas a pagar</h3>
        <table class="tabela-elegante">
            <tr><td>Pendente (a pagar)</td><td style="text-align:right;font-weight:600;"><?= formatarMoeda($aPagar) ?></td></tr>
            <tr><td>Vencido</td><td style="text-align:right;font-weight:600;color:var(--cor-erro);"><?= formatarMoeda($vencidoPagar) ?></td></tr>
        </table>
        <p style="margin-top:14px;"><a href="<?= url('/financeiro/pagar') ?>">Ver contas a pagar →</a></p>
    </div>
</div>

<div class="painel">
    <p style="color:var(--cor-texto-suave);margin:0;">
        Contratos com geração de PDF disponíveis em <a href="<?= url('/contratos') ?>">Contratos</a>.
        Relatórios detalhados (comercial, financeiro, produção e estoque) disponíveis em
        <a href="<?= url('/relatorios') ?>">Relatórios</a>.
    </p>
</div>

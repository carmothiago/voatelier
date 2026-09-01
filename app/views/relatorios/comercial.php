<p style="margin-top:-8px;"><a href="<?= url('/relatorios') ?>">← Voltar aos relatórios</a></p>

<div class="cartoes-resumo">
    <div class="cartao"><h3>Total de clientes ativas</h3><div class="valor"><?= (int) $total ?></div></div>
    <div class="cartao"><h3>Novos contatos (mês)</h3><div class="valor"><?= (int) $novosNoMes ?></div></div>
    <div class="cartao"><h3>Taxa de conversão</h3><div class="valor"><?= $taxaConversao ?>%</div></div>
</div>

<div class="painel">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
        <h3 style="margin:0;">Clientes por etapa do funil</h3>
        <a href="<?= url('/relatorios/comercial?exportar=csv') ?>" class="botao botao-secundario" style="width:auto;padding:8px 16px;text-decoration:none;">Exportar CSV</a>
    </div>
    <table class="tabela-elegante">
        <thead><tr><th>Etapa</th><th style="text-align:right;">Quantidade</th></tr></thead>
        <tbody>
            <?php foreach ($nomesEtapas as $slug => $nome): ?>
                <tr>
                    <td><?= e($nome) ?></td>
                    <td style="text-align:right;font-weight:600;"><?= (int) ($etapas[$slug] ?? 0) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

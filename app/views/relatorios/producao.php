<p style="margin-top:-8px;"><a href="<?= url('/relatorios') ?>">← Voltar aos relatórios</a></p>

<div class="cartoes-resumo">
    <div class="cartao"><h3>Total em produção</h3><div class="valor"><?= (int) $total ?></div></div>
    <div class="cartao"><h3>Atrasados</h3><div class="valor" style="<?= count($atrasados) > 0 ? 'color:var(--cor-erro);' : '' ?>"><?= count($atrasados) ?></div></div>
</div>

<div class="painel">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
        <h3 style="margin:0;">Projetos por etapa</h3>
        <a href="<?= url('/relatorios/producao?exportar=csv') ?>" class="botao botao-secundario" style="width:auto;padding:8px 16px;text-decoration:none;">Exportar CSV</a>
    </div>
    <table class="tabela-elegante">
        <thead><tr><th>Etapa</th><th style="text-align:right;">Quantidade</th></tr></thead>
        <tbody>
            <?php foreach ($etapas as $slug => $nome): ?>
                <tr>
                    <td><?= e($nome) ?></td>
                    <td style="text-align:right;font-weight:600;"><?= (int) ($porEtapa[$slug] ?? 0) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if (!empty($atrasados)): ?>
    <div class="painel">
        <h3 style="margin-top:0;">Projetos atrasados</h3>
        <table class="tabela-elegante">
            <thead><tr><th>Cliente</th><th>Prazo</th></tr></thead>
            <tbody>
                <?php foreach ($atrasados as $a): ?>
                    <tr>
                        <td><a href="<?= url('/producao/' . $a['id']) ?>"><?= e($a['cliente_nome']) ?></a></td>
                        <td style="color:var(--cor-erro);"><?= formatarData($a['prazo']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

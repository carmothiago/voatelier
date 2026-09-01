<p style="margin-top:-8px;"><a href="<?= url('/relatorios') ?>">← Voltar aos relatórios</a></p>

<div class="cartoes-resumo">
    <div class="cartao"><h3>Materiais cadastrados</h3><div class="valor"><?= count($materiais) ?></div></div>
    <div class="cartao"><h3>Abaixo do mínimo</h3><div class="valor" style="<?= count($abaixoMinimo) > 0 ? 'color:var(--cor-erro);' : '' ?>"><?= count($abaixoMinimo) ?></div></div>
    <div class="cartao"><h3>Valor total em estoque</h3><div class="valor"><?= formatarMoeda($valorTotalEstoque) ?></div></div>
</div>

<div class="painel">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
        <h3 style="margin:0;">Materiais</h3>
        <a href="<?= url('/relatorios/estoque?exportar=csv') ?>" class="botao botao-secundario" style="width:auto;padding:8px 16px;text-decoration:none;">Exportar CSV</a>
    </div>
    <table class="tabela-elegante">
        <thead><tr><th>Código</th><th>Nome</th><th>Quantidade</th><th>Mínimo</th><th>Fornecedor</th></tr></thead>
        <tbody>
            <?php foreach ($materiais as $m): ?>
                <tr style="<?= $m['quantidade'] <= $m['estoque_minimo'] ? 'background:#fbeceb;' : '' ?>">
                    <td><a href="<?= url('/estoque/' . $m['id']) ?>"><?= e($m['codigo']) ?></a></td>
                    <td><?= e($m['nome']) ?></td>
                    <td><?= e(rtrim(rtrim($m['quantidade'], '0'), '.')) ?></td>
                    <td><?= e(rtrim(rtrim($m['estoque_minimo'], '0'), '.')) ?></td>
                    <td><?= e($m['fornecedor_nome'] ?: '-') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

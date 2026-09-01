<div class="painel">
    <?php if ($totalAbaixoMinimo > 0): ?>
        <div class="alerta alerta-aviso">
            ⚠️ <?= (int) $totalAbaixoMinimo ?> material(is) abaixo do estoque mínimo.
            <a href="<?= url('/estoque?abaixo_minimo=1') ?>" style="color:inherit;text-decoration:underline;">Ver quais</a>
        </div>
    <?php endif; ?>

    <div style="display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:20px;flex-wrap:wrap;">
        <form method="get" action="<?= url('/estoque') ?>" style="display:flex;gap:8px;flex:1;min-width:260px;align-items:center;">
            <input type="text" name="q" value="<?= e($busca) ?>" placeholder="Buscar por nome ou código..."
                   style="flex:1;padding:10px 13px;border:1px solid var(--cor-borda);border-radius:6px;">
            <label style="font-size:13px;color:var(--cor-texto-suave);display:flex;align-items:center;gap:6px;white-space:nowrap;">
                <input type="checkbox" name="abaixo_minimo" value="1" <?= $apenasAbaixoMinimo ? 'checked' : '' ?>>
                Só abaixo do mínimo
            </label>
            <button type="submit" class="botao botao-secundario" style="width:auto;padding:10px 18px;">Filtrar</button>
        </form>

        <?php if (podeAcessar('estoque.editar')): ?>
            <a href="<?= url('/estoque/novo') ?>" class="botao" style="width:auto;text-decoration:none;padding:10px 20px;">+ Novo material</a>
        <?php endif; ?>
    </div>

    <?php if (empty($materiais)): ?>
        <p style="color:var(--cor-texto-suave);">Nenhum material encontrado.</p>
    <?php else: ?>
        <table class="tabela-elegante">
            <thead><tr><th>Código</th><th>Nome</th><th>Categoria</th><th>Quantidade</th><th>Mínimo</th><th>Fornecedor</th></tr></thead>
            <tbody>
                <?php foreach ($materiais as $m): ?>
                    <?php $abaixo = (float) $m['quantidade'] <= (float) $m['estoque_minimo']; ?>
                    <tr>
                        <td><a href="<?= url('/estoque/' . $m['id']) ?>"><?= e($m['codigo']) ?></a></td>
                        <td><?= e($m['nome']) ?></td>
                        <td><?= e($m['categoria'] ?: '-') ?></td>
                        <td style="<?= $abaixo ? 'color:var(--cor-erro);font-weight:600;' : '' ?>">
                            <?= e(rtrim(rtrim($m['quantidade'], '0'), '.')) ?> <?= e($m['unidade']) ?>
                            <?= $abaixo ? ' ⚠️' : '' ?>
                        </td>
                        <td><?= e(rtrim(rtrim($m['estoque_minimo'], '0'), '.')) ?> <?= e($m['unidade']) ?></td>
                        <td><?= e($m['fornecedor_nome'] ?: '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

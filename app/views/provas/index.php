<div class="painel">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:20px;flex-wrap:wrap;">
        <form method="get" action="<?= url('/provas') ?>" style="display:flex;gap:8px;">
            <select name="status" style="padding:10px 13px;border:1px solid var(--cor-borda);border-radius:6px;">
                <option value="">Todos os status</option>
                <?php foreach ($statusLista as $slug => $nome): ?>
                    <option value="<?= e($slug) ?>" <?= $statusFiltro === $slug ? 'selected' : '' ?>><?= e($nome) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="botao botao-secundario" style="width:auto;padding:10px 18px;">Filtrar</button>
        </form>

        <?php if (podeAcessar('provas.criar')): ?>
            <a href="<?= url('/provas/novo') ?>" class="botao" style="width:auto;text-decoration:none;padding:10px 20px;">+ Nova prova</a>
        <?php endif; ?>
    </div>

    <?php if (empty($provas)): ?>
        <p style="color:var(--cor-texto-suave);">Nenhuma prova encontrada.</p>
    <?php else: ?>
        <table class="tabela-elegante">
            <thead><tr><th>Nº</th><th>Cliente</th><th>Vestido</th><th>Data</th><th>Responsável</th><th>Status</th></tr></thead>
            <tbody>
                <?php foreach ($provas as $p): ?>
                    <tr>
                        <td><a href="<?= url('/provas/' . $p['id']) ?>">#<?= (int) $p['numero'] ?></a></td>
                        <td><?= e($p['cliente_nome']) ?></td>
                        <td><?= e($p['vestido_nome'] ?: '-') ?></td>
                        <td><?= formatarData($p['data_prova']) ?></td>
                        <td><?= e($p['responsavel_nome'] ?: '-') ?></td>
                        <td>
                            <span style="font-size:12px;background:var(--cor-dourado-suave);color:var(--cor-dourado);padding:3px 10px;border-radius:20px;">
                                <?= e($statusLista[$p['status']] ?? $p['status']) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

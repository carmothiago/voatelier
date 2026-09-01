<div class="painel">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:20px;flex-wrap:wrap;">
        <form method="get" action="<?= url('/vestidos') ?>" style="display:flex;gap:8px;flex:1;min-width:280px;">
            <input type="text" name="q" value="<?= e($busca) ?>" placeholder="Buscar por nome ou código..."
                   style="flex:1;padding:10px 13px;border:1px solid var(--cor-borda);border-radius:6px;">
            <select name="status" style="padding:10px 13px;border:1px solid var(--cor-borda);border-radius:6px;">
                <option value="">Todos os status</option>
                <?php foreach ($statusLista as $slug => $nome): ?>
                    <option value="<?= e($slug) ?>" <?= $statusFiltro === $slug ? 'selected' : '' ?>><?= e($nome) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="botao botao-secundario" style="width:auto;padding:10px 18px;">Filtrar</button>
        </form>

        <?php if (podeAcessar('vestidos.criar')): ?>
            <a href="<?= url('/vestidos/novo') ?>" class="botao" style="width:auto;text-decoration:none;padding:10px 20px;">
                + Novo vestido
            </a>
        <?php endif; ?>
    </div>

    <?php if (empty($vestidos)): ?>
        <p style="color:var(--cor-texto-suave);">Nenhum vestido encontrado.</p>
    <?php else: ?>
        <table class="tabela-elegante">
            <thead>
                <tr>
                    <th>Código</th><th>Nome</th><th>Categoria</th><th>Tamanho</th><th>Valor</th><th>Status</th><th>Cliente</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($vestidos as $v): ?>
                    <tr>
                        <td><a href="<?= url('/vestidos/' . $v['id']) ?>"><?= e($v['codigo']) ?></a></td>
                        <td><?= e($v['nome']) ?></td>
                        <td><?= e($v['categoria'] ?: '-') ?></td>
                        <td><?= e($v['tamanho'] ?: '-') ?></td>
                        <td><?= $v['valor'] !== null ? formatarMoeda((float) $v['valor']) : '-' ?></td>
                        <td>
                            <span style="font-size:12px;background:var(--cor-dourado-suave);color:var(--cor-dourado);padding:3px 10px;border-radius:20px;">
                                <?= e($statusLista[$v['status']] ?? $v['status']) ?>
                            </span>
                        </td>
                        <td><?= e($v['cliente_nome'] ?: '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

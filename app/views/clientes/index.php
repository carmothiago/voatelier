<div class="painel">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:20px;flex-wrap:wrap;">
        <form method="get" action="<?= url('/clientes') ?>" style="display:flex;gap:8px;flex:1;min-width:260px;">
            <input type="text" name="q" value="<?= e($busca) ?>" placeholder="Buscar por nome, CPF ou telefone..."
                   style="flex:1;padding:10px 13px;border:1px solid var(--cor-borda);border-radius:6px;">
            <button type="submit" class="botao botao-secundario" style="width:auto;padding:10px 18px;">Buscar</button>
        </form>

        <?php if (podeAcessar('clientes.criar')): ?>
            <a href="<?= url('/clientes/novo') ?>" class="botao" style="width:auto;text-decoration:none;padding:10px 20px;">
                + Nova cliente
            </a>
        <?php endif; ?>
    </div>

    <?php if (empty($clientes)): ?>
        <p style="color:var(--cor-texto-suave);">Nenhuma cliente encontrada.</p>
    <?php else: ?>
        <table class="tabela-elegante">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Telefone / WhatsApp</th>
                    <th>Casamento</th>
                    <th>Etapa</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clientes as $cliente): ?>
                    <tr>
                        <td>
                            <a href="<?= url('/clientes/' . $cliente['id']) ?>"><?= e($cliente['nome_completo']) ?></a>
                        </td>
                        <td><?= e($cliente['whatsapp'] ?: $cliente['telefone']) ?></td>
                        <td><?= formatarData($cliente['data_casamento']) ?></td>
                        <td>
                            <span style="font-size:12px;background:var(--cor-dourado-suave);color:var(--cor-dourado);padding:3px 10px;border-radius:20px;">
                                <?= e(\App\Models\Cliente::ETAPAS_CRM[$cliente['etapa_crm']] ?? $cliente['etapa_crm']) ?>
                            </span>
                        </td>
                        <td><a href="<?= url('/clientes/' . $cliente['id']) ?>">Ver ficha →</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

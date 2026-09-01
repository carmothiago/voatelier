<div class="painel">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:20px;flex-wrap:wrap;">
        <form method="get" action="<?= url('/fornecedores') ?>" style="display:flex;gap:8px;flex:1;min-width:260px;">
            <input type="text" name="q" value="<?= e($busca) ?>" placeholder="Buscar por nome ou CNPJ..."
                   style="flex:1;padding:10px 13px;border:1px solid var(--cor-borda);border-radius:6px;">
            <button type="submit" class="botao botao-secundario" style="width:auto;padding:10px 18px;">Buscar</button>
        </form>

        <?php if (podeAcessar('fornecedores.editar')): ?>
            <a href="<?= url('/fornecedores/novo') ?>" class="botao" style="width:auto;text-decoration:none;padding:10px 20px;">+ Novo fornecedor</a>
        <?php endif; ?>
    </div>

    <?php if (empty($fornecedores)): ?>
        <p style="color:var(--cor-texto-suave);">Nenhum fornecedor encontrado.</p>
    <?php else: ?>
        <table class="tabela-elegante">
            <thead><tr><th>Nome</th><th>CNPJ/CPF</th><th>Telefone</th><th>E-mail</th></tr></thead>
            <tbody>
                <?php foreach ($fornecedores as $f): ?>
                    <tr>
                        <td><a href="<?= url('/fornecedores/' . $f['id']) ?>"><?= e($f['nome']) ?></a></td>
                        <td><?= e($f['cnpj_cpf'] ?: '-') ?></td>
                        <td><?= e($f['telefone'] ?: '-') ?></td>
                        <td><?= e($f['email'] ?: '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

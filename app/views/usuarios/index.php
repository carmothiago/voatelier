<?php use App\Core\Csrf; ?>
<div class="painel">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:20px;flex-wrap:wrap;">
        <form method="get" action="<?= url('/usuarios') ?>" style="display:flex;gap:8px;flex:1;min-width:280px;">
            <input type="text" name="q" value="<?= e($busca) ?>" placeholder="Buscar por nome, login ou e-mail..."
                   style="flex:1;padding:10px 13px;border:1px solid var(--cor-borda);border-radius:6px;">
            <button type="submit" class="botao botao-secundario" style="width:auto;padding:10px 18px;">Buscar</button>
        </form>

        <?php if (podeAcessar('usuarios.criar')): ?>
            <a href="<?= url('/usuarios/novo') ?>" class="botao" style="width:auto;text-decoration:none;padding:10px 20px;">
                + Novo usuário
            </a>
        <?php endif; ?>
    </div>

    <?php if (empty($usuarios)): ?>
        <p style="color:var(--cor-texto-suave);">Nenhum usuário encontrado.</p>
    <?php else: ?>
        <table class="tabela-elegante">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Login</th>
                    <th>E-mail</th>
                    <th>Perfil</th>
                    <th>Status</th>
                    <th>Último acesso</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $u): ?>
                    <tr>
                        <td>
                            <a href="<?= url('/usuarios/' . $u['id']) ?>"><?= e($u['nome']) ?></a>
                        </td>
                        <td><?= e($u['usuario']) ?></td>
                        <td><?= e($u['email'] ?? '—') ?></td>
                        <td><?= e($u['perfil_nome']) ?></td>
                        <td>
                            <?php if ($u['status'] === 'ativo'): ?>
                                <span style="font-size:12px;background:#e8f5e9;color:#2e7d32;padding:3px 10px;border-radius:20px;">Ativo</span>
                            <?php else: ?>
                                <span style="font-size:12px;background:#fce4e4;color:#c62828;padding:3px 10px;border-radius:20px;">Inativo</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:13px;color:var(--cor-texto-suave);">
                            <?= $u['ultimo_login'] ? formatarData($u['ultimo_login'], true) : '—' ?>
                        </td>
                        <td style="text-align:right;white-space:nowrap;">
                            <?php if (podeAcessar('usuarios.editar')): ?>
                                <a href="<?= url('/usuarios/' . $u['id'] . '/editar') ?>"
                                   style="font-size:13px;color:var(--cor-dourado);text-decoration:none;margin-right:12px;">Editar</a>
                            <?php endif; ?>
                            <?php if (podeAcessar('usuarios.excluir') && $u['status'] === 'ativo'): ?>
                                <form method="post" action="<?= url('/usuarios/' . $u['id'] . '/excluir') ?>"
                                      style="display:inline;"
                                      onsubmit="return confirm('Inativar o usuário <?= e($u['nome']) ?>?')">
                                    <?= Csrf::field() ?>
                                    <button type="submit"
                                            style="background:none;border:none;color:#c62828;cursor:pointer;font-size:13px;padding:0;">
                                        Inativar
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

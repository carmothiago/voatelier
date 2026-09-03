<?php use App\Core\Csrf; ?>

<div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;margin-bottom:20px;flex-wrap:wrap;">
    <div>
        <div style="font-size:12px;color:var(--cor-dourado);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:4px;">
            <?= e($usuario['perfil_nome']) ?>
        </div>
        <p style="margin:0;color:var(--cor-texto-suave);font-size:14px;">
            Login: <strong><?= e($usuario['usuario']) ?></strong>
            <?php if ($usuario['status'] === 'inativo'): ?>
                &nbsp;·&nbsp;
                <span style="font-size:12px;background:#fce4e4;color:#c62828;padding:2px 10px;border-radius:20px;">Inativo</span>
            <?php endif; ?>
        </p>
    </div>

    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <?php if (podeAcessar('usuarios.editar')): ?>
            <a href="<?= url('/usuarios/' . $usuario['id'] . '/editar') ?>"
               class="botao botao-secundario" style="width:auto;text-decoration:none;padding:9px 18px;">
                Editar
            </a>

            <?php if ($usuario['id'] !== \App\Core\Auth::id()): ?>
                <form method="post" action="<?= url('/usuarios/' . $usuario['id'] . '/reset-senha') ?>"
                      onsubmit="return confirm('Gerar uma senha temporária para <?= e($usuario['nome']) ?>?\nA senha atual será invalidada.')">
                    <?= Csrf::field() ?>
                    <button type="submit" class="botao botao-secundario" style="width:auto;padding:9px 18px;">
                        Redefinir senha
                    </button>
                </form>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (podeAcessar('usuarios.excluir') && $usuario['id'] !== \App\Core\Auth::id() && $usuario['status'] === 'ativo'): ?>
            <form method="post" action="<?= url('/usuarios/' . $usuario['id'] . '/excluir') ?>"
                  onsubmit="return confirm('Inativar o usuário <?= e($usuario['nome']) ?>? Ele não conseguirá mais fazer login.')">
                <?= Csrf::field() ?>
                <button type="submit" class="botao" style="width:auto;padding:9px 18px;background:#c62828;">
                    Inativar
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start;">

    <div class="painel">
        <h3 style="margin-top:0;">Dados do usuário</h3>
        <dl style="display:grid;grid-template-columns:auto 1fr;gap:8px 20px;margin:0;font-size:14px;">

            <dt style="color:var(--cor-texto-suave);">Nome</dt>
            <dd style="margin:0;"><?= e($usuario['nome']) ?></dd>

            <dt style="color:var(--cor-texto-suave);">Login</dt>
            <dd style="margin:0;"><?= e($usuario['usuario']) ?></dd>

            <dt style="color:var(--cor-texto-suave);">E-mail</dt>
            <dd style="margin:0;"><?= e($usuario['email'] ?? '—') ?></dd>

            <dt style="color:var(--cor-texto-suave);">Perfil</dt>
            <dd style="margin:0;"><?= e($usuario['perfil_nome']) ?></dd>

            <dt style="color:var(--cor-texto-suave);">Status</dt>
            <dd style="margin:0;">
                <?php if ($usuario['status'] === 'ativo'): ?>
                    <span style="font-size:12px;background:#e8f5e9;color:#2e7d32;padding:3px 10px;border-radius:20px;">Ativo</span>
                <?php else: ?>
                    <span style="font-size:12px;background:#fce4e4;color:#c62828;padding:3px 10px;border-radius:20px;">Inativo</span>
                <?php endif; ?>
            </dd>

            <dt style="color:var(--cor-texto-suave);">Troca de senha</dt>
            <dd style="margin:0;">
                <?= $usuario['precisa_trocar_senha'] ? 'Pendente no próximo login' : 'Não exigida' ?>
            </dd>

            <dt style="color:var(--cor-texto-suave);">Cadastrado em</dt>
            <dd style="margin:0;"><?= formatarData($usuario['created_at'], true) ?></dd>
        </dl>
    </div>

    <div class="painel">
        <h3 style="margin-top:0;">Histórico de acesso</h3>
        <dl style="display:grid;grid-template-columns:auto 1fr;gap:8px 20px;margin:0;font-size:14px;">

            <dt style="color:var(--cor-texto-suave);">Último login</dt>
            <dd style="margin:0;">
                <?= $usuario['ultimo_login'] ? formatarData($usuario['ultimo_login'], true) : '—' ?>
            </dd>

            <dt style="color:var(--cor-texto-suave);">Último IP</dt>
            <dd style="margin:0;"><?= e($usuario['ultimo_ip'] ?? '—') ?></dd>

            <dt style="color:var(--cor-texto-suave);">Tentativas falhas</dt>
            <dd style="margin:0;"><?= (int) $usuario['tentativas_login'] ?></dd>

            <dt style="color:var(--cor-texto-suave);">Bloqueado até</dt>
            <dd style="margin:0;">
                <?php if (!empty($usuario['bloqueado_ate']) && strtotime($usuario['bloqueado_ate']) > time()): ?>
                    <span style="color:#c62828;"><?= formatarData($usuario['bloqueado_ate'], true) ?></span>
                <?php else: ?>
                    —
                <?php endif; ?>
            </dd>
        </dl>
    </div>

</div>

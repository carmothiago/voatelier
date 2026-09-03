<?php

use App\Core\Csrf;

$u   = $usuario ?? [];
$val = fn(string $campo) => e((string) ($u[$campo] ?? ''));
$ehEdicao = !empty($u['id']);
?>
<div class="painel" style="max-width:640px;">
    <form method="post" action="<?= $acao ?>">
        <?= Csrf::field() ?>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="campo" style="grid-column:1/-1;">
                <label for="nome">Nome completo *</label>
                <input type="text" id="nome" name="nome" value="<?= $val('nome') ?>" required>
            </div>

            <div class="campo">
                <label for="usuario">Login *</label>
                <input type="text" id="usuario" name="usuario" value="<?= $val('usuario') ?>"
                       required pattern="[a-zA-Z0-9_.\-]{3,50}"
                       title="Entre 3 e 50 caracteres: letras, números, _ . -">
            </div>

            <div class="campo">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" value="<?= $val('email') ?>">
            </div>

            <div class="campo">
                <label for="perfil_id">Perfil *</label>
                <select id="perfil_id" name="perfil_id" required>
                    <option value="">Selecione...</option>
                    <?php foreach ($perfis as $p): ?>
                        <option value="<?= e($p['id']) ?>"
                            <?= (string) ($u['perfil_id'] ?? '') === (string) $p['id'] ? 'selected' : '' ?>>
                            <?= e($p['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="campo">
                <label for="status">Status *</label>
                <select id="status" name="status">
                    <option value="ativo"   <?= ($u['status'] ?? 'ativo') === 'ativo'   ? 'selected' : '' ?>>Ativo</option>
                    <option value="inativo" <?= ($u['status'] ?? '')      === 'inativo' ? 'selected' : '' ?>>Inativo</option>
                </select>
            </div>

            <div class="campo" style="grid-column:1/-1;">
                <label for="senha">
                    <?= $ehEdicao ? 'Nova senha' : 'Senha *' ?>
                </label>
                <input type="password" id="senha" name="senha"
                       minlength="8"
                       <?= $ehEdicao ? '' : 'required' ?>
                       placeholder="<?= $ehEdicao ? 'Deixe em branco para não alterar' : 'Mínimo 8 caracteres' ?>">
                <?php if ($ehEdicao): ?>
                    <small style="color:var(--cor-texto-suave);">
                        Preencha apenas se quiser redefinir a senha deste usuário.
                    </small>
                <?php endif; ?>
            </div>

            <div class="campo" style="grid-column:1/-1;">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-weight:normal;">
                    <input type="checkbox" name="precisa_trocar_senha" value="1"
                           <?= !empty($u['precisa_trocar_senha']) ? 'checked' : '' ?>>
                    Exigir troca de senha no próximo login
                </label>
            </div>
        </div>

        <div style="display:flex;gap:12px;margin-top:24px;">
            <button type="submit" class="botao" style="width:auto;padding:12px 28px;">Salvar</button>
            <a href="<?= $ehEdicao ? url('/usuarios/' . $u['id']) : url('/usuarios') ?>"
               class="botao botao-secundario"
               style="width:auto;padding:12px 28px;text-decoration:none;text-align:center;">
                Cancelar
            </a>
        </div>
    </form>
</div>

<?php

use App\Core\Csrf;

$p = $prova ?? [];
?>
<div class="painel" style="max-width:600px;">
    <form method="post" action="<?= url('/provas') ?>">
        <?= Csrf::field() ?>

        <div class="campo">
            <label for="cliente_id">Cliente *</label>
            <select id="cliente_id" name="cliente_id" required>
                <option value="">Selecione...</option>
                <?php foreach ($clientes as $c): ?>
                    <option value="<?= (int) $c['id'] ?>" <?= (string) ($p['cliente_id'] ?? '') === (string) $c['id'] ? 'selected' : '' ?>>
                        <?= e($c['nome_completo']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="campo">
            <label for="vestido_id">Vestido</label>
            <select id="vestido_id" name="vestido_id">
                <option value="">— Não definido —</option>
                <?php foreach ($vestidos as $v): ?>
                    <option value="<?= (int) $v['id'] ?>"><?= e($v['codigo'] . ' — ' . $v['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="campo">
                <label for="data_prova">Data *</label>
                <input type="date" id="data_prova" name="data_prova" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="campo">
                <label for="responsavel_id">Responsável</label>
                <select id="responsavel_id" name="responsavel_id">
                    <option value="">— Não definido —</option>
                    <?php foreach ($usuarios as $u): ?>
                        <option value="<?= (int) $u['id'] ?>"><?= e($u['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="campo">
            <label for="observacoes">Observações</label>
            <textarea id="observacoes" name="observacoes" rows="3"></textarea>
        </div>

        <div style="display:flex;gap:12px;margin-top:20px;">
            <button type="submit" class="botao" style="width:auto;padding:12px 28px;">Registrar prova</button>
            <a href="<?= url('/provas') ?>" class="botao botao-secundario" style="width:auto;padding:12px 28px;text-decoration:none;text-align:center;">Cancelar</a>
        </div>
    </form>
</div>

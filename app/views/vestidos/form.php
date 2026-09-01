<?php

use App\Core\Csrf;
use App\Models\Vestido;

$v = $vestido ?? [];
$val = fn(string $campo) => e((string) ($v[$campo] ?? ''));
?>
<div class="painel" style="max-width:640px;">
    <form method="post" action="<?= $acao ?>">
        <?= Csrf::field() ?>

        <div style="display:grid;grid-template-columns:1fr 2fr;gap:16px;">
            <div class="campo">
                <label for="codigo">Código *</label>
                <input type="text" id="codigo" name="codigo" value="<?= $val('codigo') ?>" placeholder="VO-005" required>
            </div>
            <div class="campo">
                <label for="nome">Nome *</label>
                <input type="text" id="nome" name="nome" value="<?= $val('nome') ?>" required>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="campo">
                <label for="categoria">Categoria</label>
                <input type="text" id="categoria" name="categoria" value="<?= $val('categoria') ?>" placeholder="Sereia, Princesa, Evasê...">
            </div>
            <div class="campo">
                <label for="tipo">Tipo *</label>
                <select id="tipo" name="tipo" required>
                    <?php foreach (Vestido::TIPOS as $slug => $nome): ?>
                        <option value="<?= e($slug) ?>" <?= ($v['tipo'] ?? 'sob_medida') === $slug ? 'selected' : '' ?>><?= e($nome) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
            <div class="campo">
                <label for="tamanho">Tamanho</label>
                <input type="text" id="tamanho" name="tamanho" value="<?= $val('tamanho') ?>">
            </div>
            <div class="campo">
                <label for="cor">Cor</label>
                <input type="text" id="cor" name="cor" value="<?= $val('cor') ?>">
            </div>
            <div class="campo">
                <label for="valor">Valor (R$)</label>
                <input type="text" id="valor" name="valor" value="<?= $val('valor') ?>" placeholder="0,00">
            </div>
        </div>

        <?php if (!isset($v['id'])): ?>
            <div class="campo">
                <label for="status">Status inicial</label>
                <select id="status" name="status">
                    <?php foreach (Vestido::STATUS as $slug => $nome): ?>
                        <option value="<?= e($slug) ?>" <?= $slug === 'disponivel' ? 'selected' : '' ?>><?= e($nome) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>

        <div class="campo">
            <label for="descricao">Descrição</label>
            <textarea id="descricao" name="descricao" rows="4"><?= $val('descricao') ?></textarea>
        </div>

        <div style="display:flex;gap:12px;margin-top:20px;">
            <button type="submit" class="botao" style="width:auto;padding:12px 28px;">Salvar</button>
            <a href="<?= isset($v['id']) ? url('/vestidos/' . $v['id']) : url('/vestidos') ?>" class="botao botao-secundario" style="width:auto;padding:12px 28px;text-decoration:none;text-align:center;">
                Cancelar
            </a>
        </div>
    </form>
</div>

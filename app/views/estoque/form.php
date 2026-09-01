<?php

use App\Core\Csrf;

$m = $material ?? [];
$val = fn(string $campo) => e((string) ($m[$campo] ?? ''));
$acao = isset($m['id']) ? url('/estoque/' . $m['id']) : url('/estoque');
?>
<div class="painel" style="max-width:600px;">
    <form method="post" action="<?= $acao ?>">
        <?= Csrf::field() ?>

        <div style="display:grid;grid-template-columns:1fr 2fr;gap:16px;">
            <div class="campo">
                <label for="codigo">Código *</label>
                <input type="text" id="codigo" name="codigo" value="<?= $val('codigo') ?>" placeholder="MT-006" required>
            </div>
            <div class="campo">
                <label for="nome">Nome *</label>
                <input type="text" id="nome" name="nome" value="<?= $val('nome') ?>" required>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;">
            <div class="campo">
                <label for="categoria">Categoria</label>
                <input type="text" id="categoria" name="categoria" value="<?= $val('categoria') ?>" placeholder="Tecidos, Rendas, Zíperes...">
            </div>
            <div class="campo">
                <label for="unidade">Unidade</label>
                <input type="text" id="unidade" name="unidade" value="<?= $val('unidade') ?: 'un' ?>" placeholder="m, un, kg...">
            </div>
        </div>

        <?php if (!isset($m['id'])): ?>
            <div class="campo">
                <label for="quantidade_inicial">Quantidade inicial em estoque</label>
                <input type="text" id="quantidade_inicial" name="quantidade_inicial" placeholder="0">
            </div>
        <?php endif; ?>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="campo">
                <label for="estoque_minimo">Estoque mínimo</label>
                <input type="text" id="estoque_minimo" name="estoque_minimo" value="<?= $val('estoque_minimo') ?>" placeholder="0">
            </div>
            <div class="campo">
                <label for="custo_unitario">Custo unitário (R$)</label>
                <input type="text" id="custo_unitario" name="custo_unitario" value="<?= $val('custo_unitario') ?>" placeholder="0,00">
            </div>
        </div>

        <div class="campo">
            <label for="fornecedor_id">Fornecedor</label>
            <select id="fornecedor_id" name="fornecedor_id">
                <option value="">— Não definido —</option>
                <?php foreach ($fornecedores as $f): ?>
                    <option value="<?= (int) $f['id'] ?>" <?= (string) ($m['fornecedor_id'] ?? '') === (string) $f['id'] ? 'selected' : '' ?>>
                        <?= e($f['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="display:flex;gap:12px;margin-top:20px;">
            <button type="submit" class="botao" style="width:auto;padding:12px 28px;">Salvar</button>
            <a href="<?= isset($m['id']) ? url('/estoque/' . $m['id']) : url('/estoque') ?>" class="botao botao-secundario" style="width:auto;padding:12px 28px;text-decoration:none;text-align:center;">Cancelar</a>
        </div>
    </form>
</div>

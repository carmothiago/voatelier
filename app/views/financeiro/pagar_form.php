<?php

use App\Core\Csrf;
?>
<div class="painel" style="max-width:560px;">
    <form method="post" action="<?= url('/financeiro/pagar') ?>">
        <?= Csrf::field() ?>

        <div class="campo">
            <label for="fornecedor_id">Fornecedor</label>
            <select id="fornecedor_id" name="fornecedor_id">
                <option value="">— Não vinculado (ex: aluguel, contas gerais) —</option>
                <?php foreach ($fornecedores as $f): ?>
                    <option value="<?= (int) $f['id'] ?>"><?= e($f['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="display:grid;grid-template-columns:1fr 2fr;gap:16px;">
            <div class="campo">
                <label for="categoria">Categoria</label>
                <input type="text" id="categoria" name="categoria" placeholder="Matéria-prima, aluguel...">
            </div>
            <div class="campo">
                <label for="descricao">Descrição *</label>
                <input type="text" id="descricao" name="descricao" required>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="campo">
                <label for="valor">Valor (R$) *</label>
                <input type="text" id="valor" name="valor" required placeholder="0,00">
            </div>
            <div class="campo">
                <label for="vencimento">Vencimento *</label>
                <input type="date" id="vencimento" name="vencimento" required>
            </div>
        </div>

        <div class="campo">
            <label for="observacoes">Observações</label>
            <textarea id="observacoes" name="observacoes" rows="2"></textarea>
        </div>

        <div style="display:flex;gap:12px;margin-top:20px;">
            <button type="submit" class="botao" style="width:auto;padding:12px 28px;">Registrar</button>
            <a href="<?= url('/financeiro/pagar') ?>" class="botao botao-secundario" style="width:auto;padding:12px 28px;text-decoration:none;text-align:center;">Cancelar</a>
        </div>
    </form>
</div>

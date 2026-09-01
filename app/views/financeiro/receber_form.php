<?php

use App\Core\Csrf;

$c = $conta ?? [];
?>
<div class="painel" style="max-width:560px;">
    <form method="post" action="<?= url('/financeiro/receber') ?>">
        <?= Csrf::field() ?>

        <div class="campo">
            <label for="cliente_id">Cliente *</label>
            <select id="cliente_id" name="cliente_id" required>
                <option value="">Selecione...</option>
                <?php foreach ($clientes as $cli): ?>
                    <option value="<?= (int) $cli['id'] ?>" <?= (string) ($c['cliente_id'] ?? '') === (string) $cli['id'] ? 'selected' : '' ?>>
                        <?= e($cli['nome_completo']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="campo">
            <label for="descricao">Descrição *</label>
            <input type="text" id="descricao" name="descricao" required placeholder="Ex: Sinal do contrato, parcela final...">
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
            <label for="forma_pagamento">Forma de pagamento</label>
            <input type="text" id="forma_pagamento" name="forma_pagamento" placeholder="PIX, cartão, dinheiro...">
        </div>

        <div class="campo">
            <label for="observacoes">Observações</label>
            <textarea id="observacoes" name="observacoes" rows="2"></textarea>
        </div>

        <div style="display:flex;gap:12px;margin-top:20px;">
            <button type="submit" class="botao" style="width:auto;padding:12px 28px;">Registrar</button>
            <a href="<?= url('/financeiro/receber') ?>" class="botao botao-secundario" style="width:auto;padding:12px 28px;text-decoration:none;text-align:center;">Cancelar</a>
        </div>
    </form>
</div>

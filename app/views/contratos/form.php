<?php

use App\Core\Csrf;

$c = $contrato ?? [];
?>
<div class="painel" style="max-width:680px;">
    <form method="post" action="<?= url('/contratos') ?>">
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
                <label for="data_contrato">Data do contrato *</label>
                <input type="date" id="data_contrato" name="data_contrato" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="campo">
                <label for="valor">Valor (R$) *</label>
                <input type="text" id="valor" name="valor" required placeholder="0,00">
            </div>
        </div>

        <div class="campo">
            <label for="forma_pagamento">Forma de pagamento</label>
            <input type="text" id="forma_pagamento" name="forma_pagamento" placeholder="Ex: 50% na assinatura + 50% em até 15 dias antes da entrega">
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="campo">
                <label for="data_entrega">Data de entrega prevista</label>
                <input type="date" id="data_entrega" name="data_entrega">
            </div>
            <div class="campo">
                <label for="data_devolucao">Data de devolução prevista</label>
                <input type="date" id="data_devolucao" name="data_devolucao">
            </div>
        </div>

        <div class="campo">
            <label for="clausulas">Cláusulas</label>
            <textarea id="clausulas" name="clausulas" rows="6" placeholder="Texto livre das cláusulas do contrato. Será incluído no PDF gerado."></textarea>
        </div>

        <div class="campo">
            <label for="observacoes">Observações internas</label>
            <textarea id="observacoes" name="observacoes" rows="2"></textarea>
        </div>

        <div style="display:flex;gap:12px;margin-top:20px;">
            <button type="submit" class="botao" style="width:auto;padding:12px 28px;">Registrar contrato</button>
            <a href="<?= url('/contratos') ?>" class="botao botao-secundario" style="width:auto;padding:12px 28px;text-decoration:none;text-align:center;">Cancelar</a>
        </div>
    </form>
</div>

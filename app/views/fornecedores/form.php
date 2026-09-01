<?php

use App\Core\Csrf;

$f = $fornecedor ?? [];
$val = fn(string $campo) => e((string) ($f[$campo] ?? ''));
?>
<div class="painel" style="max-width:600px;">
    <form method="post" action="<?= $acao ?>">
        <?= Csrf::field() ?>

        <div class="campo">
            <label for="nome">Nome *</label>
            <input type="text" id="nome" name="nome" value="<?= $val('nome') ?>" required>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="campo">
                <label for="cnpj_cpf">CNPJ/CPF</label>
                <input type="text" id="cnpj_cpf" name="cnpj_cpf" value="<?= $val('cnpj_cpf') ?>">
            </div>
            <div class="campo">
                <label for="telefone">Telefone</label>
                <input type="text" id="telefone" name="telefone" value="<?= $val('telefone') ?>">
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="campo">
                <label for="whatsapp">WhatsApp</label>
                <input type="text" id="whatsapp" name="whatsapp" value="<?= $val('whatsapp') ?>">
            </div>
            <div class="campo">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" value="<?= $val('email') ?>">
            </div>
        </div>

        <div class="campo">
            <label for="endereco">Endereço</label>
            <input type="text" id="endereco" name="endereco" value="<?= $val('endereco') ?>">
        </div>

        <div class="campo">
            <label for="observacoes">Observações</label>
            <textarea id="observacoes" name="observacoes" rows="3"><?= $val('observacoes') ?></textarea>
        </div>

        <div style="display:flex;gap:12px;margin-top:20px;">
            <button type="submit" class="botao" style="width:auto;padding:12px 28px;">Salvar</button>
            <a href="<?= isset($f['id']) ? url('/fornecedores/' . $f['id']) : url('/fornecedores') ?>" class="botao botao-secundario" style="width:auto;padding:12px 28px;text-decoration:none;text-align:center;">Cancelar</a>
        </div>
    </form>
</div>

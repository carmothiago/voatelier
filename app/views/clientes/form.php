<?php

use App\Core\Csrf;

$c = $cliente ?? [];
$val = fn(string $campo) => e($c[$campo] ?? '');
?>
<div class="painel">
    <form method="post" action="<?= $acao ?>">
        <?= Csrf::field() ?>

        <h3 style="margin-top:0;">Dados pessoais</h3>
        <div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;">
            <div class="campo">
                <label for="nome_completo">Nome completo *</label>
                <input type="text" id="nome_completo" name="nome_completo" value="<?= $val('nome_completo') ?>" required>
            </div>
            <div class="campo">
                <label for="cpf">CPF</label>
                <input type="text" id="cpf" name="cpf" value="<?= $val('cpf') ?>" placeholder="000.000.000-00">
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
            <div class="campo">
                <label for="data_nascimento">Data de nascimento</label>
                <input type="date" id="data_nascimento" name="data_nascimento" value="<?= $val('data_nascimento') ?>">
            </div>
            <div class="campo">
                <label for="telefone">Telefone</label>
                <input type="text" id="telefone" name="telefone" value="<?= $val('telefone') ?>">
            </div>
            <div class="campo">
                <label for="whatsapp">WhatsApp</label>
                <input type="text" id="whatsapp" name="whatsapp" value="<?= $val('whatsapp') ?>">
            </div>
        </div>

        <div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;">
            <div class="campo">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" value="<?= $val('email') ?>">
            </div>
            <div class="campo">
                <label for="instagram">Instagram</label>
                <input type="text" id="instagram" name="instagram" value="<?= $val('instagram') ?>" placeholder="@usuario">
            </div>
        </div>

        <div class="campo">
            <label for="endereco">Endereço</label>
            <input type="text" id="endereco" name="endereco" value="<?= $val('endereco') ?>">
        </div>

        <div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;">
            <div class="campo">
                <label for="cidade">Cidade</label>
                <input type="text" id="cidade" name="cidade" value="<?= $val('cidade') ?>">
            </div>
            <div class="campo">
                <label for="estado">Estado</label>
                <input type="text" id="estado" name="estado" value="<?= $val('estado') ?>" maxlength="2" placeholder="SP">
            </div>
        </div>

        <div class="campo">
            <label for="observacoes">Observações</label>
            <textarea id="observacoes" name="observacoes" rows="3"><?= $val('observacoes') ?></textarea>
        </div>

        <h3>Dados do casamento</h3>
        <div style="display:grid;grid-template-columns:1fr 1fr 2fr;gap:16px;">
            <div class="campo">
                <label for="data_casamento">Data do casamento</label>
                <input type="date" id="data_casamento" name="data_casamento" value="<?= $val('data_casamento') ?>">
            </div>
            <div class="campo">
                <label for="horario_casamento">Horário</label>
                <input type="time" id="horario_casamento" name="horario_casamento" value="<?= $val('horario_casamento') ?>">
            </div>
            <div class="campo">
                <label for="local_casamento">Local</label>
                <input type="text" id="local_casamento" name="local_casamento" value="<?= $val('local_casamento') ?>">
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="campo">
                <label for="nome_noivo">Nome do(a) noivo(a)</label>
                <input type="text" id="nome_noivo" name="nome_noivo" value="<?= $val('nome_noivo') ?>">
            </div>
            <div class="campo">
                <label for="tipo_casamento">Tipo de casamento</label>
                <input type="text" id="tipo_casamento" name="tipo_casamento" value="<?= $val('tipo_casamento') ?>" placeholder="Civil, religioso, ao ar livre...">
            </div>
        </div>

        <div class="campo">
            <label for="observacoes_casamento">Observações do casamento</label>
            <textarea id="observacoes_casamento" name="observacoes_casamento" rows="3"><?= $val('observacoes_casamento') ?></textarea>
        </div>

        <div style="display:flex;gap:12px;margin-top:20px;">
            <button type="submit" class="botao" style="width:auto;padding:12px 28px;">Salvar</button>
            <a href="<?= isset($c['id']) ? url('/clientes/' . $c['id']) : url('/clientes') ?>" class="botao botao-secundario" style="width:auto;padding:12px 28px;text-decoration:none;text-align:center;">
                Cancelar
            </a>
        </div>
    </form>
</div>

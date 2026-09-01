<?php

use App\Core\Csrf;

$a = $agendamento ?? [];
$val = fn(string $campo) => e((string) ($a[$campo] ?? ''));
?>
<div class="painel" style="max-width:640px;">
    <?php if (!empty($avisoConflito)): ?>
        <div class="alerta alerta-aviso">⚠️ <?= e($avisoConflito) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= $acao ?>">
        <?= Csrf::field() ?>
        <?php if (!empty($avisoConflito)): ?>
            <input type="hidden" name="forcar" value="1">
        <?php endif; ?>

        <div class="campo">
            <label for="tipo">Tipo *</label>
            <select id="tipo" name="tipo" required>
                <option value="">Selecione...</option>
                <?php foreach ($tipos as $slug => $nome): ?>
                    <option value="<?= e($slug) ?>" <?= ($a['tipo'] ?? '') === $slug ? 'selected' : '' ?>><?= e($nome) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="campo">
            <label for="cliente_id">Cliente</label>
            <select id="cliente_id" name="cliente_id">
                <option value="">— Sem cliente vinculada (reunião interna) —</option>
                <?php foreach ($clientes as $cliente): ?>
                    <option value="<?= (int) $cliente['id'] ?>" <?= (string) ($a['cliente_id'] ?? '') === (string) $cliente['id'] ? 'selected' : '' ?>>
                        <?= e($cliente['nome_completo']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
            <div class="campo">
                <label for="data_agendamento">Data *</label>
                <input type="date" id="data_agendamento" name="data_agendamento" value="<?= $val('data_agendamento') ?>" required>
            </div>
            <div class="campo">
                <label for="hora_inicio">Início *</label>
                <input type="time" id="hora_inicio" name="hora_inicio" value="<?= $val('hora_inicio') ?>" required>
            </div>
            <div class="campo">
                <label for="hora_fim">Término *</label>
                <input type="time" id="hora_fim" name="hora_fim" value="<?= $val('hora_fim') ?>" required>
            </div>
        </div>

        <div class="campo">
            <label for="responsavel_id">Responsável</label>
            <select id="responsavel_id" name="responsavel_id">
                <option value="">— Não definido —</option>
                <?php foreach ($usuarios as $usuario): ?>
                    <option value="<?= (int) $usuario['id'] ?>" <?= (string) ($a['responsavel_id'] ?? '') === (string) $usuario['id'] ? 'selected' : '' ?>>
                        <?= e($usuario['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php if ($id): ?>
            <div class="campo">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <?php foreach (\App\Models\Agendamento::STATUS as $slug => $nome): ?>
                        <option value="<?= e($slug) ?>" <?= ($a['status'] ?? 'agendado') === $slug ? 'selected' : '' ?>><?= e($nome) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>

        <div class="campo">
            <label for="observacoes">Observações</label>
            <textarea id="observacoes" name="observacoes" rows="3"><?= $val('observacoes') ?></textarea>
        </div>

        <div style="display:flex;gap:12px;margin-top:20px;">
            <button type="submit" class="botao" style="width:auto;padding:12px 28px;">
                <?= !empty($avisoConflito) ? 'Salvar mesmo assim' : 'Salvar' ?>
            </button>
            <a href="<?= url('/agenda') ?>" class="botao botao-secundario" style="width:auto;padding:12px 28px;text-decoration:none;text-align:center;">
                Cancelar
            </a>
        </div>
    </form>
</div>

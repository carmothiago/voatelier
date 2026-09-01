<?php

use App\Core\Csrf;
?>
<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;">
    <div style="font-size:12px;color:var(--cor-dourado);text-transform:uppercase;letter-spacing:0.1em;">
        <?= e($statusLista[$vestido['status']] ?? $vestido['status']) ?>
    </div>
    <div style="display:flex;gap:10px;">
        <?php if (podeAcessar('vestidos.editar')): ?>
            <a href="<?= url('/vestidos/' . $vestido['id'] . '/editar') ?>" class="botao botao-secundario" style="width:auto;padding:9px 18px;text-decoration:none;">Editar</a>
        <?php endif; ?>
        <?php if (podeAcessar('vestidos.excluir')): ?>
            <form method="post" action="<?= url('/vestidos/' . $vestido['id'] . '/excluir') ?>" onsubmit="return confirm('Remover este vestido da listagem?');">
                <?= Csrf::field() ?>
                <button type="submit" class="botao botao-secundario" style="width:auto;padding:9px 18px;color:var(--cor-erro);border-color:#f0cfcb;">Excluir</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start;">
    <div>
        <div class="painel">
            <h3 style="margin-top:0;">Detalhes</h3>
            <table class="tabela-elegante">
                <tr><th style="width:180px;">Categoria</th><td><?= e($vestido['categoria'] ?: '-') ?></td></tr>
                <tr><th>Tipo</th><td><?= e(\App\Models\Vestido::TIPOS[$vestido['tipo']] ?? $vestido['tipo']) ?></td></tr>
                <tr><th>Tamanho</th><td><?= e($vestido['tamanho'] ?: '-') ?></td></tr>
                <tr><th>Cor</th><td><?= e($vestido['cor'] ?: '-') ?></td></tr>
                <tr><th>Valor</th><td><?= $vestido['valor'] !== null ? formatarMoeda((float) $vestido['valor']) : '-' ?></td></tr>
                <tr><th>Cliente atual</th><td><?= e($vestido['cliente_nome'] ?: '-') ?></td></tr>
                <tr><th>Descrição</th><td><?= nl2br(e($vestido['descricao'] ?: '-')) ?></td></tr>
            </table>
        </div>

        <div class="painel">
            <h3 style="margin-top:0;">Histórico</h3>
            <?php if (empty($historico)): ?>
                <p style="color:var(--cor-texto-suave);">Sem movimentações.</p>
            <?php else: ?>
                <ul style="list-style:none;padding:0;margin:0;">
                    <?php foreach ($historico as $h): ?>
                        <li style="padding:10px 0;border-bottom:1px solid #f0ece4;font-size:13px;">
                            <strong><?= e($statusLista[$h['status_novo']] ?? $h['status_novo']) ?></strong>
                            <?= $h['cliente_nome'] ? ' — ' . e($h['cliente_nome']) : '' ?><br>
                            <span style="color:var(--cor-texto-suave);">
                                <?= formatarData($h['created_at'], true) ?>
                                <?= $h['usuario_nome'] ? ' · ' . e($h['usuario_nome']) : '' ?>
                                <?= $h['observacao'] ? ' · ' . e($h['observacao']) : '' ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <div>
        <?php if (podeAcessar('vestidos.editar')): ?>
            <div class="painel">
                <h3 style="margin-top:0;">Alterar status</h3>
                <form method="post" action="<?= url('/vestidos/' . $vestido['id'] . '/status') ?>">
                    <?= Csrf::field() ?>
                    <div class="campo">
                        <label for="status">Novo status</label>
                        <select id="status" name="status">
                            <?php foreach ($statusLista as $slug => $nome): ?>
                                <option value="<?= e($slug) ?>" <?= $vestido['status'] === $slug ? 'selected' : '' ?>><?= e($nome) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="campo">
                        <label for="cliente_id">Cliente (se reservado/em produção)</label>
                        <select id="cliente_id" name="cliente_id">
                            <option value="">— Nenhuma —</option>
                            <?php foreach ($clientes as $c): ?>
                                <option value="<?= (int) $c['id'] ?>" <?= (int) ($vestido['cliente_id'] ?? 0) === (int) $c['id'] ? 'selected' : '' ?>>
                                    <?= e($c['nome_completo']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="campo">
                        <label for="observacao">Observação</label>
                        <input type="text" id="observacao" name="observacao" placeholder="Opcional">
                    </div>
                    <button type="submit" class="botao" style="width:auto;padding:10px 20px;">Salvar status</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

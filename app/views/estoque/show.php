<?php

use App\Core\Csrf;
use App\Models\Material;

$abaixo = (float) $material['quantidade'] <= (float) $material['estoque_minimo'];
?>
<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;">
    <div>
        <div style="font-size:12px;color:var(--cor-dourado);text-transform:uppercase;letter-spacing:0.1em;">
            <?= e($material['codigo']) ?>
        </div>
        <?php if ($abaixo): ?>
            <div class="alerta alerta-erro" style="margin-top:8px;display:inline-block;">⚠️ Abaixo do estoque mínimo</div>
        <?php endif; ?>
    </div>
    <?php if (podeAcessar('estoque.editar')): ?>
        <div style="display:flex;gap:10px;">
            <a href="<?= url('/estoque/' . $material['id'] . '/editar') ?>" class="botao botao-secundario" style="width:auto;padding:9px 18px;text-decoration:none;">Editar</a>
            <form method="post" action="<?= url('/estoque/' . $material['id'] . '/excluir') ?>" onsubmit="return confirm('Remover este material da listagem?');">
                <?= Csrf::field() ?>
                <button type="submit" class="botao botao-secundario" style="width:auto;padding:9px 18px;color:var(--cor-erro);border-color:#f0cfcb;">Excluir</button>
            </form>
        </div>
    <?php endif; ?>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start;">
    <div>
        <div class="painel">
            <h3 style="margin-top:0;">Detalhes</h3>
            <table class="tabela-elegante">
                <tr><th style="width:180px;">Categoria</th><td><?= e($material['categoria'] ?: '-') ?></td></tr>
                <tr><th>Quantidade atual</th><td style="<?= $abaixo ? 'color:var(--cor-erro);font-weight:600;' : '' ?>"><?= e(rtrim(rtrim($material['quantidade'], '0'), '.')) ?> <?= e($material['unidade']) ?></td></tr>
                <tr><th>Estoque mínimo</th><td><?= e(rtrim(rtrim($material['estoque_minimo'], '0'), '.')) ?> <?= e($material['unidade']) ?></td></tr>
                <tr><th>Custo unitário</th><td><?= $material['custo_unitario'] !== null ? formatarMoeda((float) $material['custo_unitario']) : '-' ?></td></tr>
                <tr><th>Fornecedor</th><td><?= $material['fornecedor_id'] ? '<a href="' . url('/fornecedores/' . $material['fornecedor_id']) . '">' . e($material['fornecedor_nome']) . '</a>' : '-' ?></td></tr>
            </table>
        </div>

        <div class="painel">
            <h3 style="margin-top:0;">Histórico de movimentações</h3>
            <?php if (empty($movimentacoes)): ?>
                <p style="color:var(--cor-texto-suave);">Nenhuma movimentação registrada.</p>
            <?php else: ?>
                <table class="tabela-elegante">
                    <thead><tr><th>Data</th><th>Tipo</th><th>Quantidade</th><th>Resultante</th><th>Motivo</th><th>Usuário</th></tr></thead>
                    <tbody>
                        <?php foreach ($movimentacoes as $mv): ?>
                            <tr>
                                <td><?= formatarData($mv['created_at'], true) ?></td>
                                <td><?= e($tiposMovimentacao[$mv['tipo']] ?? $mv['tipo']) ?></td>
                                <td><?= e(rtrim(rtrim($mv['quantidade'], '0'), '.')) ?></td>
                                <td><?= e(rtrim(rtrim($mv['quantidade_resultante'], '0'), '.')) ?></td>
                                <td><?= e($mv['motivo'] ?: '-') ?></td>
                                <td><?= e($mv['usuario_nome'] ?: '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <?php if (podeAcessar('estoque.editar')): ?>
        <div class="painel">
            <h3 style="margin-top:0;">Movimentar estoque</h3>
            <form method="post" action="<?= url('/estoque/' . $material['id'] . '/movimentar') ?>">
                <?= Csrf::field() ?>
                <div class="campo">
                    <label for="tipo">Tipo</label>
                    <select id="tipo" name="tipo">
                        <?php foreach (Material::TIPOS_MOVIMENTACAO as $slug => $nome): ?>
                            <option value="<?= e($slug) ?>"><?= e($nome) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="campo">
                    <label for="quantidade">
                        Quantidade
                        <span style="font-weight:400;color:var(--cor-texto-suave);">(para "Ajuste", informe a quantidade final)</span>
                    </label>
                    <input type="text" id="quantidade" name="quantidade" placeholder="0" required>
                </div>
                <div class="campo">
                    <label for="motivo">Motivo</label>
                    <input type="text" id="motivo" name="motivo" placeholder="Opcional">
                </div>
                <button type="submit" class="botao" style="width:auto;padding:10px 20px;">Registrar movimentação</button>
            </form>
        </div>
    <?php endif; ?>
</div>

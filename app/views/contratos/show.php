<?php

use App\Core\Csrf;
?>
<p style="margin-top:-8px;"><a href="<?= url('/contratos') ?>">← Voltar aos contratos</a></p>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start;">
    <div class="painel">
        <h3 style="margin-top:0;">Detalhes do contrato</h3>
        <table class="tabela-elegante">
            <tr><th style="width:200px;">Cliente</th><td><a href="<?= url('/clientes/' . $contrato['cliente_id']) ?>"><?= e($contrato['cliente_nome']) ?></a></td></tr>
            <tr><th>Vestido</th><td><?= $contrato['vestido_nome'] ? e($contrato['vestido_codigo'] . ' — ' . $contrato['vestido_nome']) : '-' ?></td></tr>
            <tr><th>Data do contrato</th><td><?= formatarData($contrato['data_contrato']) ?></td></tr>
            <tr><th>Valor</th><td><?= formatarMoeda((float) $contrato['valor']) ?></td></tr>
            <tr><th>Forma de pagamento</th><td><?= e($contrato['forma_pagamento'] ?: '-') ?></td></tr>
            <tr><th>Entrega prevista</th><td><?= formatarData($contrato['data_entrega']) ?></td></tr>
            <tr><th>Devolução prevista</th><td><?= formatarData($contrato['data_devolucao']) ?></td></tr>
            <tr><th>Cláusulas</th><td><?= nl2br(e($contrato['clausulas'] ?: '-')) ?></td></tr>
            <tr><th>Observações</th><td><?= nl2br(e($contrato['observacoes'] ?: '-')) ?></td></tr>
        </table>
    </div>

    <div class="painel">
        <h3 style="margin-top:0;">Documento PDF</h3>
        <?php if (!empty($contrato['arquivo_pdf'])): ?>
            <p style="color:var(--cor-texto-suave);font-size:13px;">Último PDF gerado disponível.</p>
            <a href="<?= url('/uploads/contratos/' . $contrato['arquivo_pdf']) ?>" target="_blank" class="botao botao-secundario" style="width:auto;padding:9px 18px;text-decoration:none;display:inline-block;margin-bottom:10px;">
                Abrir PDF atual
            </a>
        <?php else: ?>
            <p style="color:var(--cor-texto-suave);font-size:13px;">Nenhum PDF gerado ainda.</p>
        <?php endif; ?>

        <?php if (podeAcessar('contratos.visualizar')): ?>
            <form method="post" action="<?= url('/contratos/' . $contrato['id'] . '/gerar-pdf') ?>">
                <?= Csrf::field() ?>
                <button type="submit" class="botao" style="width:auto;padding:10px 20px;">
                    <?= !empty($contrato['arquivo_pdf']) ? 'Gerar novamente' : 'Gerar PDF' ?>
                </button>
            </form>
            <p style="font-size:12px;color:var(--cor-texto-suave);margin-top:10px;">
                Gera um novo PDF com os dados atuais do contrato (substitui o anterior).
            </p>
        <?php endif; ?>
    </div>
</div>

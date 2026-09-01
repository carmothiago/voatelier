<?php
$hoje = date('Y-m-d');
$atrasado = $producao['prazo'] && $producao['prazo'] < $hoje && $producao['etapa'] !== 'entrega';
?>
<p style="margin-top:-8px;"><a href="<?= url('/producao') ?>">← Voltar ao Kanban de produção</a></p>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start;">
    <div class="painel">
        <h3 style="margin-top:0;">Detalhes</h3>
        <table class="tabela-elegante">
            <tr><th style="width:180px;">Cliente</th><td><a href="<?= url('/clientes/' . $producao['cliente_id']) ?>"><?= e($producao['cliente_nome']) ?></a></td></tr>
            <tr><th>Vestido</th><td><?= $producao['vestido_nome'] ? e($producao['vestido_codigo'] . ' — ' . $producao['vestido_nome']) : '-' ?></td></tr>
            <tr><th>Responsável</th><td><?= e($producao['responsavel_nome'] ?: '-') ?></td></tr>
            <tr><th>Etapa atual</th><td><?= e($etapas[$producao['etapa']] ?? $producao['etapa']) ?></td></tr>
            <tr><th>Início</th><td><?= formatarData($producao['data_inicio']) ?></td></tr>
            <tr>
                <th>Prazo</th>
                <td style="<?= $atrasado ? 'color:var(--cor-erro);font-weight:600;' : '' ?>">
                    <?= formatarData($producao['prazo']) ?><?= $atrasado ? ' — ATRASADO' : '' ?>
                </td>
            </tr>
            <tr><th>Observações</th><td><?= nl2br(e($producao['observacoes'] ?: '-')) ?></td></tr>
        </table>
    </div>

    <div class="painel">
        <h3 style="margin-top:0;">Histórico de etapas</h3>
        <?php if (empty($historico)): ?>
            <p style="color:var(--cor-texto-suave);font-size:14px;">Sem movimentações.</p>
        <?php else: ?>
            <ul style="list-style:none;padding:0;margin:0;">
                <?php foreach ($historico as $h): ?>
                    <li style="padding:10px 0;border-bottom:1px solid #f0ece4;font-size:13px;">
                        <strong><?= e($etapas[$h['etapa_nova']] ?? $h['etapa_nova']) ?></strong><br>
                        <span style="color:var(--cor-texto-suave);">
                            <?= formatarData($h['created_at'], true) ?>
                            <?= $h['usuario_nome'] ? ' · ' . e($h['usuario_nome']) : '' ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>

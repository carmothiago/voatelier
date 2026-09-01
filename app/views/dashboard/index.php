<div class="cartoes-resumo">
    <div class="cartao">
        <h3>Agenda de hoje</h3>
        <div class="valor"><?= (int) $totalAgendaHoje ?></div>
    </div>
    <div class="cartao">
        <h3>Novos contatos (mês)</h3>
        <div class="valor"><?= (int) $novosContatosMes ?></div>
    </div>
    <div class="cartao">
        <h3>Vestidos em produção</h3>
        <div class="valor"><?= (int) $totalEmProducao ?></div>
    </div>
    <div class="cartao">
        <h3>Casamentos em 30 dias</h3>
        <div class="valor"><?= count($casamentosProximos) ?></div>
    </div>
</div>

<?php if (!empty($projetosAtrasados)): ?>
    <div class="alerta alerta-erro">
        ⚠️ <?= count($projetosAtrasados) ?> vestido(s) com produção atrasada:
        <?= implode(', ', array_map(fn($p) => e($p['cliente_nome']) . ' (prazo ' . formatarData($p['prazo']) . ')', $projetosAtrasados)) ?>
        — <a href="<?= url('/producao') ?>" style="color:inherit;text-decoration:underline;">ver no Kanban</a>
    </div>
<?php elseif ($projetosProximoPrazo > 0): ?>
    <div class="alerta alerta-aviso">
        ⚠️ <?= (int) $projetosProximoPrazo ?> vestido(s) com prazo de produção nos próximos 7 dias.
    </div>
<?php endif; ?>

<?php if (!empty($casamentosProximos)): ?>
    <div class="alerta alerta-aviso">
        ⚠️ <?= count($casamentosProximos) ?> casamento(s) nos próximos 30 dias:
        <?= implode(', ', array_map(fn($c) => e($c['nome_completo']) . ' (' . formatarData($c['data_casamento']) . ')', $casamentosProximos)) ?>
    </div>
<?php endif; ?>

<?php if (!empty($materiaisAbaixoMinimo)): ?>
    <div class="alerta alerta-aviso">
        ⚠️ <?= count($materiaisAbaixoMinimo) ?> material(is) de estoque abaixo do mínimo:
        <?= implode(', ', array_map(fn($m) => e($m['nome']), $materiaisAbaixoMinimo)) ?>
        — <a href="<?= url('/estoque?abaixo_minimo=1') ?>" style="color:inherit;text-decoration:underline;">ver estoque</a>
    </div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start;">
    <div class="painel">
        <h3 style="margin-top:0;">Agenda de hoje</h3>
        <?php if (empty($agendaHoje)): ?>
            <p style="color:var(--cor-texto-suave);">Nenhum compromisso agendado para hoje.</p>
        <?php else: ?>
            <table class="tabela-elegante">
                <thead><tr><th>Horário</th><th>Cliente</th><th>Tipo</th></tr></thead>
                <tbody>
                    <?php foreach ($agendaHoje as $ag): ?>
                        <tr>
                            <td><?= substr($ag['hora_inicio'], 0, 5) ?> - <?= substr($ag['hora_fim'], 0, 5) ?></td>
                            <td><?= e($ag['cliente_nome'] ?? '-') ?></td>
                            <td><?= e($tiposAgendamento[$ag['tipo']] ?? $ag['tipo']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <p style="margin-top:14px;"><a href="<?= url('/agenda') ?>">Ver agenda completa →</a></p>
    </div>

    <div class="painel">
        <h3 style="margin-top:0;">Pipeline comercial</h3>
        <table class="tabela-elegante">
            <tbody>
                <?php foreach ($nomesEtapasCrm as $slug => $nome): ?>
                    <tr>
                        <td><?= e($nome) ?></td>
                        <td style="text-align:right;font-weight:600;"><?= (int) ($etapasCrm[$slug] ?? 0) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p style="margin-top:14px;"><a href="<?= url('/crm') ?>">Ver pipeline completo (Kanban) →</a></p>
    </div>
</div>

<?php if (($vencidoReceber ?? 0) > 0 || ($vencidoPagar ?? 0) > 0): ?>
    <div class="alerta alerta-erro">
        ⚠️ Pendências financeiras vencidas —
        <?php if ($vencidoReceber > 0): ?>a receber: <?= formatarMoeda($vencidoReceber) ?><?php endif; ?>
        <?php if ($vencidoReceber > 0 && $vencidoPagar > 0): ?> · <?php endif; ?>
        <?php if ($vencidoPagar > 0): ?>a pagar: <?= formatarMoeda($vencidoPagar) ?><?php endif; ?>
        — <a href="<?= url('/financeiro') ?>" style="color:inherit;text-decoration:underline;">ver financeiro</a>
    </div>
<?php endif; ?>

<div class="painel">
    <h3 style="margin-top:0;">Financeiro</h3>
    <table class="tabela-elegante">
        <tr><td>Receitas do mês</td><td style="text-align:right;font-weight:600;"><?= formatarMoeda($receitasMes ?? 0) ?></td></tr>
        <tr><td>Despesas do mês</td><td style="text-align:right;font-weight:600;"><?= formatarMoeda($despesasMes ?? 0) ?></td></tr>
        <tr><td>A receber (pendente)</td><td style="text-align:right;font-weight:600;"><?= formatarMoeda($aReceber ?? 0) ?></td></tr>
    </table>
    <p style="margin-top:14px;"><a href="<?= url('/financeiro') ?>">Ver painel financeiro completo →</a></p>
</div>

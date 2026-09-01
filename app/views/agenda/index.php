<?php

$hoje = date('Y-m-d');
$diasSemana = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];

$coresTipo = [
    'atendimento' => '#b08d57',
    'medicao'     => '#6f8f9e',
    'prova'       => '#a4453a',
    'ajuste'      => '#8a6fae',
    'entrega'     => '#4c7a52',
    'devolucao'   => '#b0673f',
    'reuniao'     => '#6f675d',
];
?>
<div class="painel">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <div style="display:flex;align-items:center;gap:14px;">
            <a href="<?= url('/agenda?ano=' . $anoMesAnterior . '&mes=' . $mesAnterior) ?>" class="botao botao-secundario" style="width:auto;padding:8px 14px;text-decoration:none;">‹</a>
            <h2 style="margin:0;font-size:20px;"><?= e($nomeMes) ?> de <?= (int) $ano ?></h2>
            <a href="<?= url('/agenda?ano=' . $anoMesSeguinte . '&mes=' . $mesSeguinte) ?>" class="botao botao-secundario" style="width:auto;padding:8px 14px;text-decoration:none;">›</a>
        </div>

        <?php if (podeAcessar('agenda.criar')): ?>
            <a href="<?= url('/agenda/novo') ?>" class="botao" style="width:auto;padding:10px 20px;text-decoration:none;">+ Novo agendamento</a>
        <?php endif; ?>
    </div>

    <div class="calendario-grid calendario-cabecalho">
        <?php foreach ($diasSemana as $dia): ?>
            <div class="calendario-dia-semana"><?= $dia ?></div>
        <?php endforeach; ?>
    </div>

    <div class="calendario-grid">
        <?php for ($i = 0; $i < $diaSemanaInicio; $i++): ?>
            <div class="calendario-celula calendario-celula-vazia"></div>
        <?php endfor; ?>

        <?php for ($dia = 1; $dia <= $diasNoMes; $dia++):
            $dataCompleta = sprintf('%04d-%02d-%02d', $ano, $mes, $dia);
            $ehHoje = $dataCompleta === $hoje;
            $eventosDoDia = $eventosPorDia[$dia] ?? [];
        ?>
            <div class="calendario-celula <?= $ehHoje ? 'calendario-celula-hoje' : '' ?>">
                <div class="calendario-numero-dia">
                    <?= $dia ?>
                    <?php if (podeAcessar('agenda.criar')): ?>
                        <a href="<?= url('/agenda/novo?data=' . $dataCompleta) ?>" class="calendario-add" title="Novo agendamento neste dia">+</a>
                    <?php endif; ?>
                </div>
                <?php foreach ($eventosDoDia as $evento): ?>
                    <a href="<?= podeAcessar('agenda.editar') ? url('/agenda/' . $evento['id'] . '/editar') : '#' ?>"
                       class="calendario-evento"
                       style="background:<?= $coresTipo[$evento['tipo']] ?? '#999' ?>;"
                       title="<?= e(($evento['cliente_nome'] ?? 'Sem cliente') . ' · ' . ($tipos[$evento['tipo']] ?? $evento['tipo'])) ?>">
                        <?= substr($evento['hora_inicio'], 0, 5) ?> <?= e($evento['cliente_nome'] ?? ($tipos[$evento['tipo']] ?? $evento['tipo'])) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endfor; ?>
    </div>
</div>

<div class="painel">
    <h3 style="margin-top:0;">Legenda</h3>
    <div style="display:flex;gap:14px;flex-wrap:wrap;">
        <?php foreach ($tipos as $slug => $nome): ?>
            <div style="display:flex;align-items:center;gap:6px;font-size:13px;">
                <span style="width:10px;height:10px;border-radius:50%;display:inline-block;background:<?= $coresTipo[$slug] ?? '#999' ?>;"></span>
                <?= e($nome) ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php

use App\Core\Csrf;

$hoje = date('Y-m-d');
$projetosPorEtapa = array_fill_keys(array_keys($etapas), []);
foreach ($projetos as $projeto) {
    $projetosPorEtapa[$projeto['etapa']][] = $projeto;
}
?>
<?php if (!empty($atrasados)): ?>
    <div class="alerta alerta-erro">
        ⚠️ <?= count($atrasados) ?> vestido(s) com prazo de produção vencido:
        <?= implode(', ', array_map(fn($p) => e($p['cliente_nome']) . ' (prazo ' . formatarData($p['prazo']) . ')', $atrasados)) ?>
    </div>
<?php endif; ?>

<div style="display:flex;justify-content:flex-end;margin-bottom:14px;">
    <?php if (podeAcessar('producao.editar')): ?>
        <a href="<?= url('/producao/novo') ?>" class="botao" style="width:auto;padding:10px 20px;text-decoration:none;">+ Novo projeto</a>
    <?php endif; ?>
</div>

<div class="kanban-board" id="producao-board"
     data-csrf="<?= e(Csrf::token()) ?>"
     data-mover-url="<?= url('/producao/mover') ?>"
     data-item-key="producao_id"
     data-item-attr="data-producao-id">
    <?php foreach ($etapas as $slug => $nome): ?>
        <div class="kanban-coluna" data-etapa="<?= e($slug) ?>">
            <div class="kanban-coluna-titulo">
                <?= e($nome) ?>
                <span class="kanban-contador"><?= count($projetosPorEtapa[$slug]) ?></span>
            </div>
            <div class="kanban-lista" data-etapa-alvo="<?= e($slug) ?>">
                <?php foreach ($projetosPorEtapa[$slug] as $projeto): ?>
                    <?php $atrasado = $projeto['prazo'] && $projeto['prazo'] < $hoje; ?>
                    <div class="kanban-card" draggable="<?= podeAcessar('producao.editar') ? 'true' : 'false' ?>" data-producao-id="<?= (int) $projeto['id'] ?>">
                        <a href="<?= url('/producao/' . $projeto['id']) ?>" class="kanban-card-nome"><?= e($projeto['cliente_nome']) ?></a>
                        <?php if (!empty($projeto['vestido_codigo'])): ?>
                            <div class="kanban-card-info"><?= e($projeto['vestido_codigo']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($projeto['prazo'])): ?>
                            <div class="kanban-card-info" style="<?= $atrasado ? 'color:var(--cor-erro);font-weight:600;' : '' ?>">
                                Prazo: <?= formatarData($projeto['prazo']) ?><?= $atrasado ? ' (atrasado)' : '' ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

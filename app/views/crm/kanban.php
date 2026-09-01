<?php

use App\Core\Csrf;

$clientesPorEtapa = array_fill_keys(array_keys($etapas), []);
foreach ($clientes as $cliente) {
    $clientesPorEtapa[$cliente['etapa_crm']][] = $cliente;
}
?>
<div class="kanban-board" id="kanban-board" data-csrf="<?= e(Csrf::token()) ?>" data-mover-url="<?= url('/crm/mover') ?>">
    <?php foreach ($etapas as $slug => $nome): ?>
        <div class="kanban-coluna" data-etapa="<?= e($slug) ?>">
            <div class="kanban-coluna-titulo">
                <?= e($nome) ?>
                <span class="kanban-contador"><?= count($clientesPorEtapa[$slug]) ?></span>
            </div>
            <div class="kanban-lista" data-etapa-alvo="<?= e($slug) ?>">
                <?php foreach ($clientesPorEtapa[$slug] as $cliente): ?>
                    <div class="kanban-card" draggable="<?= podeAcessar('crm.editar') ? 'true' : 'false' ?>" data-cliente-id="<?= (int) $cliente['id'] ?>">
                        <a href="<?= url('/clientes/' . $cliente['id']) ?>" class="kanban-card-nome"><?= e($cliente['nome_completo']) ?></a>
                        <?php if (!empty($cliente['nome_noivo'])): ?>
                            <div class="kanban-card-info">& <?= e($cliente['nome_noivo']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($cliente['data_casamento'])): ?>
                            <div class="kanban-card-info"><?= formatarData($cliente['data_casamento']) ?></div>
                        <?php endif; ?>
                        <?php if ($slug === 'perdido' && !empty($cliente['motivo_perda'])): ?>
                            <div class="kanban-card-info" style="color:var(--cor-erro);"><?= e($cliente['motivo_perda']) ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php if (!podeAcessar('crm.editar')): ?>
    <p style="color:var(--cor-texto-suave);font-size:13px;margin-top:12px;">
        Você tem apenas permissão de visualização no CRM.
    </p>
<?php endif; ?>

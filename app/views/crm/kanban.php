<?php

use App\Core\Csrf;

$clientesPorEtapa = array_fill_keys(array_keys($etapas), []);
foreach ($clientes as $cliente) {
    $clientesPorEtapa[$cliente['etapa_crm']][] = $cliente;
}
?>

{{-- Modal de motivo de perda --}}
<div id="modal-perda" style="display:none;position:fixed;inset:0;z-index:1000;
     background:rgba(0,0,0,0.45);align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:10px;padding:28px 32px;max-width:440px;width:90%;
                box-shadow:0 8px 32px rgba(0,0,0,0.18);position:relative;">
        <h3 style="margin:0 0 6px;font-size:17px;">Motivo da perda</h3>
        <p style="margin:0 0 16px;font-size:14px;color:var(--cor-texto-suave);">
            Por que este contrato não foi fechado? O comentário ficará visível no card.
        </p>
        <textarea id="modal-perda-texto"
                  rows="4"
                  maxlength="500"
                  placeholder="Ex: Cliente optou por outro atelier, prazo incompatível..."
                  style="width:100%;box-sizing:border-box;padding:10px 12px;
                         border:1px solid var(--cor-borda);border-radius:6px;
                         font-size:14px;resize:vertical;font-family:inherit;"></textarea>
        <div style="display:flex;gap:10px;margin-top:16px;justify-content:flex-end;">
            <button id="modal-perda-cancelar"
                    class="botao botao-secundario"
                    style="width:auto;padding:9px 20px;">
                Cancelar
            </button>
            <button id="modal-perda-confirmar"
                    class="botao"
                    style="width:auto;padding:9px 20px;background:var(--cor-erro);">
                Confirmar perda
            </button>
        </div>
    </div>
</div>

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

<?php

use App\Core\Csrf;
?>
<p style="margin-top:-8px;"><a href="<?= url('/clientes/' . $cliente['id']) ?>">← Voltar para a ficha de <?= e($cliente['nome_completo']) ?></a></p>

<div style="display:grid;grid-template-columns:1fr 1.3fr;gap:20px;align-items:start;">
    <?php if (podeAcessar('medidas.criar')): ?>
        <div class="painel">
            <h3 style="margin-top:0;">Nova ficha de medidas</h3>
            <form method="post" action="<?= url('/clientes/' . $cliente['id'] . '/medidas') ?>">
                <?= Csrf::field() ?>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <?php foreach ($labels as $campo => $label): ?>
                        <div class="campo">
                            <label for="<?= e($campo) ?>"><?= e($label) ?> (cm)</label>
                            <input type="text" id="<?= e($campo) ?>" name="<?= e($campo) ?>" placeholder="0,0">
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="campo">
                    <label for="observacoes">Observações</label>
                    <textarea id="observacoes" name="observacoes" rows="2"></textarea>
                </div>

                <button type="submit" class="botao" style="width:auto;padding:10px 20px;">Registrar medidas</button>
            </form>
        </div>
    <?php endif; ?>

    <div class="painel">
        <h3 style="margin-top:0;">Histórico de medidas</h3>
        <?php if (empty($medidas)): ?>
            <p style="color:var(--cor-texto-suave);">Nenhuma ficha de medidas registrada ainda.</p>
        <?php else: ?>
            <?php foreach ($medidas as $i => $m): ?>
                <div style="border-bottom:1px solid #f0ece4;padding:14px 0;">
                    <div style="display:flex;justify-content:space-between;font-size:13px;color:var(--cor-texto-suave);margin-bottom:8px;">
                        <span><?= $i === 0 ? '<strong style="color:var(--cor-dourado);">Mais recente</strong>' : formatarData($m['created_at'], true) ?></span>
                        <span><?= $m['usuario_nome'] ? e($m['usuario_nome']) : '' ?></span>
                    </div>
                    <?php if ($i === 0): ?>
                        <div style="font-size:12px;color:var(--cor-texto-suave);margin-bottom:6px;"><?= formatarData($m['created_at'], true) ?></div>
                    <?php endif; ?>
                    <div style="display:flex;flex-wrap:wrap;gap:8px;">
                        <?php foreach ($labels as $campo => $label): ?>
                            <?php if ($m[$campo] !== null): ?>
                                <span style="font-size:12px;background:#f4f1ea;padding:4px 10px;border-radius:6px;">
                                    <?= e($label) ?>: <strong><?= e(rtrim(rtrim($m[$campo], '0'), '.')) ?></strong> cm
                                </span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php if (!empty($m['observacoes'])): ?>
                        <p style="font-size:13px;color:var(--cor-texto-suave);margin:8px 0 0;"><?= e($m['observacoes']) ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

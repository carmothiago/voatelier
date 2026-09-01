<div class="painel">
    <div style="display:flex;justify-content:flex-end;margin-bottom:20px;">
        <?php if (podeAcessar('contratos.criar')): ?>
            <a href="<?= url('/contratos/novo') ?>" class="botao" style="width:auto;text-decoration:none;padding:10px 20px;">+ Novo contrato</a>
        <?php endif; ?>
    </div>

    <?php if (empty($contratos)): ?>
        <p style="color:var(--cor-texto-suave);">Nenhum contrato registrado.</p>
    <?php else: ?>
        <table class="tabela-elegante">
            <thead><tr><th>Cliente</th><th>Vestido</th><th>Data</th><th>Valor</th><th>PDF</th></tr></thead>
            <tbody>
                <?php foreach ($contratos as $c): ?>
                    <tr>
                        <td><a href="<?= url('/contratos/' . $c['id']) ?>"><?= e($c['cliente_nome']) ?></a></td>
                        <td><?= $c['vestido_nome'] ? e($c['vestido_codigo'] . ' — ' . $c['vestido_nome']) : '-' ?></td>
                        <td><?= formatarData($c['data_contrato']) ?></td>
                        <td><?= formatarMoeda((float) $c['valor']) ?></td>
                        <td><?= $c['arquivo_pdf'] ? '✅' : '—' ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php

use App\Core\Csrf;
?>
<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;">
    <div>
        <div style="font-size:12px;color:var(--cor-dourado);text-transform:uppercase;letter-spacing:0.1em;">
            <?= e($statusLista[$prova['status']] ?? $prova['status']) ?>
        </div>
        <p style="margin:4px 0 0;color:var(--cor-texto-suave);">
            <a href="<?= url('/clientes/' . $prova['cliente_id']) ?>"><?= e($prova['cliente_nome']) ?></a>
            <?= $prova['vestido_nome'] ? ' · ' . e($prova['vestido_codigo'] . ' — ' . $prova['vestido_nome']) : '' ?>
            · <?= formatarData($prova['data_prova']) ?>
        </p>
    </div>

    <?php if (podeAcessar('provas.editar')): ?>
        <form method="post" action="<?= url('/provas/' . $prova['id'] . '/status') ?>" style="display:flex;gap:8px;">
            <?= Csrf::field() ?>
            <select name="status" onchange="this.form.submit()">
                <?php foreach ($statusLista as $slug => $nome): ?>
                    <option value="<?= e($slug) ?>" <?= $prova['status'] === $slug ? 'selected' : '' ?>><?= e($nome) ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    <?php endif; ?>
</div>

<div style="display:grid;grid-template-columns:1.4fr 1fr;gap:20px;align-items:start;">
    <div>
        <div class="painel">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
                <h3 style="margin:0;">Ajustes</h3>
            </div>

            <?php if (empty($ajustes)): ?>
                <p style="color:var(--cor-texto-suave);font-size:14px;">Nenhum ajuste registrado.</p>
            <?php else: ?>
                <table class="tabela-elegante">
                    <thead><tr><th>Descrição</th><th>Parte</th><th>Atual → Desejada</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php foreach ($ajustes as $a): ?>
                            <tr>
                                <td><?= e($a['descricao']) ?><?= $a['observacao'] ? '<br><span style="color:var(--cor-texto-suave);font-size:12px;">' . e($a['observacao']) . '</span>' : '' ?></td>
                                <td><?= e($a['parte_vestido'] ?: '-') ?></td>
                                <td><?= e($a['medida_atual'] ?: '-') ?> → <?= e($a['medida_desejada'] ?: '-') ?></td>
                                <td>
                                    <?php if (podeAcessar('provas.editar')): ?>
                                        <form method="post" action="<?= url('/provas/' . $prova['id'] . '/ajustes/' . $a['id'] . '/status') ?>">
                                            <?= Csrf::field() ?>
                                            <select name="status" onchange="this.form.submit()" style="font-size:12px;padding:4px 6px;">
                                                <?php foreach ($statusLista as $slug => $nome): ?>
                                                    <option value="<?= e($slug) ?>" <?= $a['status'] === $slug ? 'selected' : '' ?>><?= e($nome) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </form>
                                    <?php else: ?>
                                        <?= e($statusLista[$a['status']] ?? $a['status']) ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <?php if (podeAcessar('provas.editar')): ?>
                <details style="margin-top:16px;">
                    <summary style="cursor:pointer;color:var(--cor-dourado);font-size:14px;">+ Adicionar ajuste</summary>
                    <form method="post" action="<?= url('/provas/' . $prova['id'] . '/ajustes') ?>" style="margin-top:14px;">
                        <?= Csrf::field() ?>
                        <div class="campo">
                            <label for="descricao">Descrição *</label>
                            <input type="text" id="descricao" name="descricao" required placeholder="Ex: Apertar cintura">
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
                            <div class="campo">
                                <label for="parte_vestido">Parte do vestido</label>
                                <input type="text" id="parte_vestido" name="parte_vestido">
                            </div>
                            <div class="campo">
                                <label for="medida_atual">Medida atual</label>
                                <input type="text" id="medida_atual" name="medida_atual">
                            </div>
                            <div class="campo">
                                <label for="medida_desejada">Medida desejada</label>
                                <input type="text" id="medida_desejada" name="medida_desejada">
                            </div>
                        </div>
                        <div class="campo">
                            <label for="observacao">Observação</label>
                            <input type="text" id="observacao" name="observacao">
                        </div>
                        <button type="submit" class="botao botao-secundario" style="width:auto;padding:9px 18px;">Adicionar</button>
                    </form>
                </details>
            <?php endif; ?>
        </div>

        <?php if (!empty($prova['observacoes'])): ?>
            <div class="painel">
                <h3 style="margin-top:0;">Observações da prova</h3>
                <p style="color:var(--cor-texto-suave);"><?= nl2br(e($prova['observacoes'])) ?></p>
            </div>
        <?php endif; ?>
    </div>

    <div>
        <div class="painel">
            <h3 style="margin-top:0;">Fotos</h3>

            <?php if (podeAcessar('documentos.criar')): ?>
                <form method="post" action="<?= url('/provas/' . $prova['id'] . '/fotos') ?>" enctype="multipart/form-data" style="margin-bottom:16px;">
                    <?= Csrf::field() ?>
                    <div class="campo">
                        <input type="file" name="foto" accept=".jpg,.jpeg,.png,.pdf" required>
                    </div>
                    <button type="submit" class="botao botao-secundario" style="width:auto;padding:8px 16px;">Enviar foto</button>
                </form>
            <?php endif; ?>

            <?php if (empty($anexos)): ?>
                <p style="color:var(--cor-texto-suave);font-size:13px;">Nenhuma foto anexada.</p>
            <?php else: ?>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <?php foreach ($anexos as $anexo): ?>
                        <div style="border:1px solid var(--cor-borda);border-radius:8px;padding:8px;font-size:12px;">
                            <?php if (str_ends_with($anexo['nome_arquivo'], '.pdf')): ?>
                                <div style="padding:20px 0;text-align:center;color:var(--cor-texto-suave);">📄 PDF</div>
                            <?php else: ?>
                                <img src="<?= url('/uploads/provas/' . $anexo['nome_arquivo']) ?>" alt="Foto da prova" style="width:100%;border-radius:6px;display:block;">
                            <?php endif; ?>
                            <div style="margin-top:6px;color:var(--cor-texto-suave);"><?= formatarData($anexo['created_at'], true) ?></div>
                            <?php if (podeAcessar('documentos.criar')): ?>
                                <form method="post" action="<?= url('/provas/' . $prova['id'] . '/fotos/' . $anexo['id'] . '/excluir') ?>" onsubmit="return confirm('Excluir esta foto?');">
                                    <?= Csrf::field() ?>
                                    <button type="submit" style="background:none;border:none;color:var(--cor-erro);font-size:11px;cursor:pointer;padding:0;">Excluir</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

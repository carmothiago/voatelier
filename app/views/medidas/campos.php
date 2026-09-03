<?php use App\Core\Csrf; ?>

<p style="margin-top:-8px;">
    <a href="javascript:history.back()">← Voltar</a>
</p>

<div style="display:grid;grid-template-columns:1fr 1.6fr;gap:20px;align-items:start;">

    {{-- Formulário para adicionar novo campo --}}
    <div class="painel">
        <h3 style="margin-top:0;">Adicionar campo</h3>
        <p style="color:var(--cor-texto-suave);font-size:14px;margin-top:0;">
            Campos adicionados aqui aparecerão no formulário de medidas de todas as clientes.
        </p>

        <form method="post" action="<?= url('/medidas/campos') ?>">
            <?= Csrf::field() ?>
            <div class="campo">
                <label for="label">Nome do campo *</label>
                <input type="text" id="label" name="label" required maxlength="100"
                       placeholder="Ex: Colo, Busto alto, Cintura fina...">
                <small style="color:var(--cor-texto-suave);">
                    O nome técnico (slug) é gerado automaticamente.
                </small>
            </div>
            <button type="submit" class="botao" style="width:auto;padding:10px 22px;">
                + Adicionar campo
            </button>
        </form>
    </div>

    {{-- Lista de campos com drag-and-drop para reordenar --}}
    <div class="painel">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h3 style="margin:0;">Campos configurados</h3>
            <span style="font-size:12px;color:var(--cor-texto-suave);">
                Arraste para reordenar
            </span>
        </div>

        <?php if (empty($campos)): ?>
            <p style="color:var(--cor-texto-suave);">Nenhum campo configurado ainda.</p>
        <?php else: ?>
            <form id="form-reordenar" method="post" action="<?= url('/medidas/campos/reordenar') ?>">
                <?= Csrf::field() ?>
                <ul id="lista-campos" style="list-style:none;padding:0;margin:0;">
                    <?php foreach ($campos as $c): ?>
                        <li data-id="<?= (int) $c['id'] ?>"
                            style="display:flex;align-items:center;gap:12px;padding:10px 8px;
                                   border-bottom:1px solid #f0ece4;cursor:grab;
                                   <?= $c['ativo'] ? '' : 'opacity:0.45;' ?>">

                            <span class="drag-handle" title="Arrastar"
                                  style="color:var(--cor-texto-suave);font-size:18px;line-height:1;user-select:none;">
                                ⠿
                            </span>

                            <div style="flex:1;min-width:0;">
                                <strong style="font-size:14px;"><?= e($c['label']) ?></strong>
                                <span style="font-size:11px;color:var(--cor-texto-suave);margin-left:6px;">
                                    <?= e($c['slug']) ?>
                                </span>
                            </div>

                            <?php if ($c['ativo']): ?>
                                <span style="font-size:11px;background:#e8f5e9;color:#2e7d32;
                                             padding:2px 8px;border-radius:20px;white-space:nowrap;">
                                    Ativo
                                </span>
                            <?php else: ?>
                                <span style="font-size:11px;background:#fce4e4;color:#c62828;
                                             padding:2px 8px;border-radius:20px;white-space:nowrap;">
                                    Inativo
                                </span>
                            <?php endif; ?>

                            <form method="post"
                                  action="<?= url('/medidas/campos/' . $c['id'] . '/toggle') ?>"
                                  style="margin:0;"
                                  onsubmit="return confirm('<?= $c['ativo']
                                      ? 'Desativar o campo &quot;' . e($c['label']) . '&quot;? Ele sumirá do formulário, mas os dados históricos são preservados.'
                                      : 'Reativar o campo &quot;' . e($c['label']) . '&quot;?' ?>') ">
                                <?= Csrf::field() ?>
                                <button type="submit"
                                        style="background:none;border:1px solid var(--cor-borda);
                                               border-radius:4px;padding:4px 10px;cursor:pointer;
                                               font-size:12px;color:var(--cor-texto-suave);">
                                    <?= $c['ativo'] ? 'Desativar' : 'Reativar' ?>
                                </button>
                            </form>

                            {{-- Campo hidden para submissão da nova ordem --}}
                            <input type="hidden" name="ordem[]" value="<?= (int) $c['id'] ?>">
                        </li>
                    <?php endforeach; ?>
                </ul>
            </form>

            <div style="margin-top:14px;display:flex;justify-content:flex-end;">
                <button id="btn-salvar-ordem" class="botao botao-secundario"
                        style="width:auto;padding:8px 20px;font-size:13px;" disabled>
                    Salvar nova ordem
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
(function () {
    const lista   = document.getElementById('lista-campos');
    const btnSalvar = document.getElementById('btn-salvar-ordem');
    if (!lista) return;

    let arrastando = null;

    lista.addEventListener('dragstart', e => {
        arrastando = e.target.closest('li');
        arrastando.style.opacity = '0.4';
        e.dataTransfer.effectAllowed = 'move';
    });

    lista.addEventListener('dragend', e => {
        const li = e.target.closest('li');
        li.style.opacity = li.dataset.inativo === '1' ? '0.45' : '1';
        arrastando = null;
    });

    lista.addEventListener('dragover', e => {
        e.preventDefault();
        const sobre = e.target.closest('li');
        if (!sobre || sobre === arrastando) return;

        const rect = sobre.getBoundingClientRect();
        const abaixo = e.clientY > rect.top + rect.height / 2;
        lista.insertBefore(arrastando, abaixo ? sobre.nextSibling : sobre);
    });

    // Marca os itens como draggable
    lista.querySelectorAll('li').forEach(li => {
        li.setAttribute('draggable', 'true');
        if (!li.querySelector('[data-id]')) return;
        // Marca itens inativos para restaurar opacidade correta no dragend
        if (li.style.opacity === '0.45') li.dataset.inativo = '1';
    });

    // Qualquer reordenação habilita o botão salvar
    const observer = new MutationObserver(() => {
        if (btnSalvar) btnSalvar.disabled = false;
    });
    observer.observe(lista, { childList: true });

    // Ao salvar: atualiza os hidden inputs na ordem atual e submete
    if (btnSalvar) {
        btnSalvar.addEventListener('click', () => {
            const form = document.getElementById('form-reordenar');
            // Remove inputs antigos e recria na ordem visual atual
            form.querySelectorAll('input[name="ordem[]"]').forEach(i => i.remove());
            lista.querySelectorAll('li[data-id]').forEach(li => {
                const input = document.createElement('input');
                input.type  = 'hidden';
                input.name  = 'ordem[]';
                input.value = li.dataset.id;
                form.appendChild(input);
            });
            form.submit();
        });
    }
})();
</script>

<div class="painel">
    <form method="get" action="<?= url('/auditoria') ?>" style="display:flex;gap:10px;flex-wrap:wrap;align-items:end;margin-bottom:20px;">
        <div class="campo" style="margin:0;">
            <label for="modulo">Módulo</label>
            <select id="modulo" name="modulo">
                <option value="">Todos</option>
                <?php foreach ($modulos as $m): ?>
                    <option value="<?= e($m) ?>" <?= $filtros['modulo'] === $m ? 'selected' : '' ?>><?= e(ucfirst($m)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="campo" style="margin:0;">
            <label for="usuario_id">Usuário</label>
            <select id="usuario_id" name="usuario_id">
                <option value="">Todos</option>
                <?php foreach ($usuarios as $u): ?>
                    <option value="<?= (int) $u['id'] ?>" <?= $filtros['usuario_id'] === (string) $u['id'] ? 'selected' : '' ?>><?= e($u['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="campo" style="margin:0;">
            <label for="data_de">De</label>
            <input type="date" id="data_de" name="data_de" value="<?= e($filtros['data_de']) ?>">
        </div>

        <div class="campo" style="margin:0;">
            <label for="data_ate">Até</label>
            <input type="date" id="data_ate" name="data_ate" value="<?= e($filtros['data_ate']) ?>">
        </div>

        <div class="campo" style="margin:0;flex:1;min-width:180px;">
            <label for="busca">Buscar (registro/ação)</label>
            <input type="text" id="busca" name="busca" value="<?= e($filtros['busca']) ?>">
        </div>

        <button type="submit" class="botao botao-secundario" style="width:auto;padding:10px 20px;">Filtrar</button>
    </form>

    <p style="color:var(--cor-texto-suave);font-size:13px;margin-top:-10px;margin-bottom:16px;">
        Exibindo <?= count($registros) ?> de <?= (int) $total ?> registro(s) no total (limitado às 200 movimentações mais recentes que casam com o filtro).
    </p>

    <?php if (empty($registros)): ?>
        <p style="color:var(--cor-texto-suave);">Nenhum registro encontrado.</p>
    <?php else: ?>
        <table class="tabela-elegante">
            <thead><tr><th>Data/hora</th><th>Usuário</th><th>Módulo</th><th>Ação</th><th>Registro</th><th>IP</th></tr></thead>
            <tbody>
                <?php foreach ($registros as $r): ?>
                    <tr>
                        <td><?= formatarData($r['data_hora'], true) ?></td>
                        <td><?= e($r['usuario_nome'] ?: 'Sistema') ?></td>
                        <td><?= e($r['modulo']) ?></td>
                        <td><?= e($r['acao']) ?></td>
                        <td><?= e($r['registro_afetado'] ?: '-') ?></td>
                        <td style="font-size:12px;color:var(--cor-texto-suave);"><?= e($r['ip'] ?: '-') ?></td>
                    </tr>
                    <?php if (!empty($r['dados_anteriores']) || !empty($r['dados_novos'])): ?>
                        <tr>
                            <td colspan="6" style="padding-top:0;padding-bottom:14px;">
                                <details style="font-size:12px;color:var(--cor-texto-suave);">
                                    <summary style="cursor:pointer;color:var(--cor-dourado);">Ver detalhes</summary>
                                    <?php if (!empty($r['dados_anteriores'])): ?>
                                        <div style="margin-top:6px;"><strong>Antes:</strong> <?= e($r['dados_anteriores']) ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($r['dados_novos'])): ?>
                                        <div style="margin-top:4px;"><strong>Depois:</strong> <?= e($r['dados_novos']) ?></div>
                                    <?php endif; ?>
                                </details>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

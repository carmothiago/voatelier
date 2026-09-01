<?php

use App\Core\Csrf;
?>
<div class="painel">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:20px;flex-wrap:wrap;">
        <form method="get" action="<?= url('/financeiro/receber') ?>" style="display:flex;gap:8px;">
            <select name="filtro" onchange="this.form.submit()" style="padding:10px 13px;border:1px solid var(--cor-borda);border-radius:6px;">
                <option value="">Todas</option>
                <option value="pendente" <?= $filtro === 'pendente' ? 'selected' : '' ?>>Pendentes</option>
                <option value="vencido" <?= $filtro === 'vencido' ? 'selected' : '' ?>>Vencidas</option>
                <option value="pago" <?= $filtro === 'pago' ? 'selected' : '' ?>>Pagas</option>
            </select>
        </form>

        <?php if (podeAcessar('financeiro.editar')): ?>
            <a href="<?= url('/financeiro/receber/novo') ?>" class="botao" style="width:auto;text-decoration:none;padding:10px 20px;">+ Nova conta a receber</a>
        <?php endif; ?>
    </div>

    <?php if (empty($contas)): ?>
        <p style="color:var(--cor-texto-suave);">Nenhuma conta encontrada.</p>
    <?php else: ?>
        <table class="tabela-elegante">
            <thead><tr><th>Cliente</th><th>Descrição</th><th>Valor</th><th>Vencimento</th><th>Status</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($contas as $c): ?>
                    <tr>
                        <td><a href="<?= url('/clientes/' . $c['cliente_id']) ?>"><?= e($c['cliente_nome']) ?></a></td>
                        <td><?= e($c['descricao']) ?></td>
                        <td><?= formatarMoeda((float) $c['valor']) ?></td>
                        <td><?= formatarData($c['vencimento']) ?></td>
                        <td>
                            <?php if ($c['vencido']): ?>
                                <span style="font-size:12px;background:#fbeceb;color:var(--cor-erro);padding:3px 10px;border-radius:20px;">Vencido</span>
                            <?php else: ?>
                                <span style="font-size:12px;background:var(--cor-dourado-suave);color:var(--cor-dourado);padding:3px 10px;border-radius:20px;">
                                    <?= e($statusLista[$c['status']] ?? $c['status']) ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($c['status'] === 'pendente' && podeAcessar('financeiro.editar')): ?>
                                <form method="post" action="<?= url('/financeiro/receber/' . $c['id'] . '/pago') ?>" onsubmit="return confirm('Confirmar recebimento?');">
                                    <?= Csrf::field() ?>
                                    <button type="submit" class="botao botao-secundario" style="width:auto;padding:6px 12px;font-size:12px;">Marcar como pago</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

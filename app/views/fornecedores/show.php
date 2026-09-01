<?php

use App\Core\Csrf;
?>
<div style="display:flex;justify-content:flex-end;gap:10px;margin-bottom:20px;">
    <?php if (podeAcessar('fornecedores.editar')): ?>
        <a href="<?= url('/fornecedores/' . $fornecedor['id'] . '/editar') ?>" class="botao botao-secundario" style="width:auto;padding:9px 18px;text-decoration:none;">Editar</a>
        <form method="post" action="<?= url('/fornecedores/' . $fornecedor['id'] . '/excluir') ?>" onsubmit="return confirm('Remover este fornecedor da listagem?');">
            <?= Csrf::field() ?>
            <button type="submit" class="botao botao-secundario" style="width:auto;padding:9px 18px;color:var(--cor-erro);border-color:#f0cfcb;">Excluir</button>
        </form>
    <?php endif; ?>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start;">
    <div class="painel">
        <h3 style="margin-top:0;">Contato</h3>
        <table class="tabela-elegante">
            <tr><th style="width:140px;">CNPJ/CPF</th><td><?= e($fornecedor['cnpj_cpf'] ?: '-') ?></td></tr>
            <tr><th>Telefone</th><td><?= e($fornecedor['telefone'] ?: '-') ?></td></tr>
            <tr><th>WhatsApp</th><td><?= e($fornecedor['whatsapp'] ?: '-') ?></td></tr>
            <tr><th>E-mail</th><td><?= e($fornecedor['email'] ?: '-') ?></td></tr>
            <tr><th>Endereço</th><td><?= e($fornecedor['endereco'] ?: '-') ?></td></tr>
            <tr><th>Observações</th><td><?= nl2br(e($fornecedor['observacoes'] ?: '-')) ?></td></tr>
        </table>
    </div>

    <div class="painel">
        <h3 style="margin-top:0;">Materiais fornecidos</h3>
        <?php if (empty($materiais)): ?>
            <p style="color:var(--cor-texto-suave);">Nenhum material vinculado a este fornecedor.</p>
        <?php else: ?>
            <table class="tabela-elegante">
                <thead><tr><th>Código</th><th>Nome</th><th>Quantidade</th></tr></thead>
                <tbody>
                    <?php foreach ($materiais as $m): ?>
                        <tr>
                            <td><a href="<?= url('/estoque/' . $m['id']) ?>"><?= e($m['codigo']) ?></a></td>
                            <td><?= e($m['nome']) ?></td>
                            <td><?= e(rtrim(rtrim($m['quantidade'], '0'), '.')) ?> <?= e($m['unidade']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
    <a href="<?= url('/relatorios/comercial') ?>" class="painel" style="text-decoration:none;color:inherit;display:block;">
        <h3 style="margin-top:0;color:var(--cor-preto);">Comercial</h3>
        <p style="color:var(--cor-texto-suave);margin:0;">Leads por etapa, conversão e novos contatos no mês.</p>
    </a>

    <a href="<?= url('/relatorios/financeiro') ?>" class="painel" style="text-decoration:none;color:inherit;display:block;">
        <h3 style="margin-top:0;color:var(--cor-preto);">Financeiro</h3>
        <p style="color:var(--cor-texto-suave);margin:0;">Faturamento, receitas, despesas e inadimplência.</p>
    </a>

    <a href="<?= url('/relatorios/producao') ?>" class="painel" style="text-decoration:none;color:inherit;display:block;">
        <h3 style="margin-top:0;color:var(--cor-preto);">Produção</h3>
        <p style="color:var(--cor-texto-suave);margin:0;">Vestidos em produção por etapa e atrasos.</p>
    </a>

    <a href="<?= url('/relatorios/estoque') ?>" class="painel" style="text-decoration:none;color:inherit;display:block;">
        <h3 style="margin-top:0;color:var(--cor-preto);">Estoque</h3>
        <p style="color:var(--cor-texto-suave);margin:0;">Materiais disponíveis, abaixo do mínimo e valor total em estoque.</p>
    </a>
</div>

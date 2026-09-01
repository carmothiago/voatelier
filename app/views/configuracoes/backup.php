<?php

use App\Core\Csrf;
?>
<div class="painel">
    <h3 style="margin-top:0;">Backup do sistema</h3>
    <p style="color:var(--cor-texto-suave);">
        O backup completo do atelier tem duas partes: o <strong>banco de dados</strong>
        (clientes, agenda, vestidos, financeiro etc.) e a pasta de <strong>uploads</strong>
        (fotos e documentos anexados). Baixe os dois regularmente e guarde em um local
        seguro, de preferência fora deste computador (pendrive, HD externo ou nuvem).
    </p>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:20px;">
        <div style="border:1px solid var(--cor-borda);border-radius:8px;padding:20px;">
            <h4 style="margin-top:0;">Banco de dados</h4>
            <p style="font-size:13px;color:var(--cor-texto-suave);">
                <?= (int) $totalTabelas ?> tabelas. Gera um arquivo <code>.sql</code> com toda
                a estrutura e os dados, pronto para restaurar em caso de necessidade.
            </p>
            <form method="post" action="<?= url('/configuracoes/backup/banco') ?>">
                <?= Csrf::field() ?>
                <button type="submit" class="botao" style="width:auto;padding:10px 20px;">Baixar backup do banco (.sql)</button>
            </form>
        </div>

        <div style="border:1px solid var(--cor-borda);border-radius:8px;padding:20px;">
            <h4 style="margin-top:0;">Arquivos enviados (uploads)</h4>
            <p style="font-size:13px;color:var(--cor-texto-suave);">
                Aproximadamente <?= formatarTamanho($tamanhoUploads) ?> em fotos, PDFs de
                contratos e outros documentos anexados.
            </p>
            <form method="post" action="<?= url('/configuracoes/backup/uploads') ?>">
                <?= Csrf::field() ?>
                <button type="submit" class="botao" style="width:auto;padding:10px 20px;">Baixar uploads (.zip)</button>
            </form>
        </div>
    </div>
</div>

<div class="painel">
    <h3 style="margin-top:0;">Como restaurar um backup</h3>
    <ol style="color:var(--cor-texto-suave);font-size:14px;line-height:1.7;">
        <li>Abra o phpMyAdmin (<code>http://localhost/phpmyadmin</code>).</li>
        <li>Selecione (ou crie) o banco <code>voatelier</code>.</li>
        <li>Vá em <strong>Importar</strong> e selecione o arquivo <code>.sql</code> baixado.</li>
        <li>Para os uploads, extraia o <code>.zip</code> baixado dentro da pasta
            <code>uploads/</code> do sistema, substituindo o conteúdo atual.</li>
    </ol>
    <p style="font-size:13px;color:var(--cor-texto-suave);">
        Também é possível automatizar o backup do banco pela linha de comando usando o
        script <code>backup.bat</code> incluído na raiz do projeto (veja o README).
    </p>
</div>

<div class="painel">
    <h3 style="margin-top:0;">Auditoria</h3>
    <p style="color:var(--cor-texto-suave);">
        Todas as ações relevantes do sistema (login, criação/edição de registros,
        mudanças de status, exclusões) ficam registradas com usuário, data/hora e IP.
    </p>
    <?php if (podeAcessar('auditoria.visualizar')): ?>
        <a href="<?= url('/auditoria') ?>" class="botao botao-secundario" style="width:auto;padding:9px 18px;text-decoration:none;display:inline-block;">
            Consultar auditoria
        </a>
    <?php endif; ?>
</div>

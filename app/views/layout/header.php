<?php

use App\Core\Auth;

$usuarioLogado = Auth::user();
$flash = flash();

$caminhoAtual = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$ehAtivo = function (string $prefixo) use ($caminhoAtual): string {
    $prefixoCompleto = BASE_URL . $prefixo;
    return str_starts_with($caminhoAtual, $prefixoCompleto) ? 'ativo' : '';
};
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titulo ?? 'Sistema') ?> · <?= e(APP_SHORT_NAME) ?></title>
    <link rel="stylesheet" href="<?= url('/css/style.css') ?>">
    <link rel="icon" type="image/x-icon" href="<?= url('/assets/favicon.ico') ?>">
</head>
<body>
<div class="app-shell">
    <aside class="app-sidebar">
        <img src="<?= url('/assets/logo-branca.png') ?>" alt="<?= e(APP_NAME) ?>" class="logo-marca logo-marca-sidebar">
        <nav>
            <a href="<?= url('/dashboard') ?>" class="<?= $ehAtivo('/dashboard') ?>">Painel</a>
            <?php if (podeAcessar('clientes.visualizar')): ?>
                <a href="<?= url('/clientes') ?>" class="<?= $ehAtivo('/clientes') ?>">Clientes</a>
            <?php endif; ?>
            <?php if (podeAcessar('crm.visualizar')): ?>
                <a href="<?= url('/crm') ?>" class="<?= $ehAtivo('/crm') ?>">CRM</a>
            <?php endif; ?>
            <?php if (podeAcessar('agenda.visualizar')): ?>
                <a href="<?= url('/agenda') ?>" class="<?= $ehAtivo('/agenda') ?>">Agenda</a>
            <?php endif; ?>
            <?php if (podeAcessar('vestidos.visualizar')): ?>
                <a href="<?= url('/vestidos') ?>" class="<?= $ehAtivo('/vestidos') ?>">Vestidos</a>
            <?php endif; ?>
            <?php if (podeAcessar('provas.visualizar')): ?>
                <a href="<?= url('/provas') ?>" class="<?= $ehAtivo('/provas') ?>">Provas</a>
            <?php endif; ?>
            <?php if (podeAcessar('producao.visualizar')): ?>
                <a href="<?= url('/producao') ?>" class="<?= $ehAtivo('/producao') ?>">Produção</a>
            <?php endif; ?>
            <?php if (podeAcessar('estoque.visualizar')): ?>
                <a href="<?= url('/estoque') ?>" class="<?= $ehAtivo('/estoque') ?>">Estoque</a>
            <?php endif; ?>
            <?php if (podeAcessar('fornecedores.visualizar')): ?>
                <a href="<?= url('/fornecedores') ?>" class="<?= $ehAtivo('/fornecedores') ?>">Fornecedores</a>
            <?php endif; ?>
            <?php if (podeAcessar('financeiro.visualizar')): ?>
                <a href="<?= url('/financeiro') ?>" class="<?= $ehAtivo('/financeiro') ?>">Financeiro</a>
            <?php endif; ?>
            <?php if (podeAcessar('contratos.visualizar')): ?>
                <a href="<?= url('/contratos') ?>" class="<?= $ehAtivo('/contratos') ?>">Contratos</a>
            <?php endif; ?>
            <?php if (podeAcessar('relatorios.visualizar')): ?>
                <a href="<?= url('/relatorios') ?>" class="<?= $ehAtivo('/relatorios') ?>">Relatórios</a>
            <?php endif; ?>
            <?php if (podeAcessar('auditoria.visualizar')): ?>
                <a href="<?= url('/auditoria') ?>" class="<?= $ehAtivo('/auditoria') ?>">Auditoria</a>
            <?php endif; ?>
            <?php if (podeAcessar('usuarios.visualizar')): ?>
                <a href="<?= url('/usuarios') ?>" class="<?= $ehAtivo('/usuarios') ?>">Usuários</a>
            <?php endif; ?>
            <?php if (podeAcessar('configuracoes.visualizar')): ?>
                <a href="<?= url('/configuracoes/backup') ?>" class="<?= $ehAtivo('/configuracoes') ?>">Configurações</a>
            <?php endif; ?>
        </nav>
    </aside>

    <main class="app-conteudo">
        <div class="app-topbar">
            <h1 style="margin:0;font-size:22px;"><?= e($titulo ?? '') ?></h1>
            <?php if ($usuarioLogado): ?>
                <div class="usuario-info">
                    <?= e($usuarioLogado['nome']) ?> ·
                    <span style="text-transform:capitalize;"><?= e($usuarioLogado['perfil']) ?></span>
                    · <a href="<?= url('/logout') ?>">Sair</a>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($flash): ?>
            <div class="alerta alerta-<?= e($flash['tipo']) ?>">
                <?= e($flash['mensagem']) ?>
            </div>
        <?php endif; ?>

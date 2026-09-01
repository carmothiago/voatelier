<?php

use App\Core\Csrf;

$flash = flash();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trocar senha · <?= e(APP_SHORT_NAME) ?></title>
    <link rel="stylesheet" href="<?= url('/css/style.css') ?>">
    <link rel="icon" type="image/x-icon" href="<?= url('/assets/favicon.ico') ?>">
</head>
<body>
<div class="tela-auth">
    <div class="cartao-auth">
        <img src="<?= url('/assets/logo.png') ?>" alt="<?= e(APP_NAME) ?>" class="logo-marca logo-marca-pequena">
        <h1>Trocar senha</h1>

        <p style="font-size:14px;color:var(--cor-texto-suave);margin-top:-16px;margin-bottom:24px;">
            Por segurança, defina uma nova senha antes de continuar.
        </p>

        <?php if ($flash): ?>
            <div class="alerta alerta-<?= e($flash['tipo']) ?>">
                <?= e($flash['mensagem']) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= url('/trocar-senha') ?>">
            <?= Csrf::field() ?>

            <div class="campo">
                <label for="nova_senha">Nova senha</label>
                <input type="password" id="nova_senha" name="nova_senha" minlength="8" required autofocus>
            </div>

            <div class="campo">
                <label for="confirmacao_senha">Confirme a nova senha</label>
                <input type="password" id="confirmacao_senha" name="confirmacao_senha" minlength="8" required>
            </div>

            <button type="submit" class="botao">Salvar nova senha</button>
        </form>
    </div>
</div>
</body>
</html>

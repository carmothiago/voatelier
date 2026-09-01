<?php

use App\Core\Csrf;

$flash = flash();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar · <?= e(APP_SHORT_NAME) ?></title>
    <link rel="stylesheet" href="<?= url('/css/style.css') ?>">
    <link rel="icon" type="image/x-icon" href="<?= url('/assets/favicon.ico') ?>">
</head>
<body>
<div class="tela-auth">
    <div class="cartao-auth">
        <img src="<?= url('/assets/logo.png') ?>" alt="<?= e(APP_NAME) ?>" class="logo-marca">

        <?php if ($flash): ?>
            <div class="alerta alerta-<?= e($flash['tipo']) ?>">
                <?= e($flash['mensagem']) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= url('/login') ?>">
            <?= Csrf::field() ?>

            <div class="campo">
                <label for="usuario">Usuário</label>
                <input type="text" id="usuario" name="usuario" autocomplete="username" required autofocus>
            </div>

            <div class="campo">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" autocomplete="current-password" required>
            </div>

            <button type="submit" class="botao">Entrar</button>
        </form>
    </div>
</div>
</body>
</html>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Erro interno · <?= defined('APP_SHORT_NAME') ? htmlspecialchars(APP_SHORT_NAME) : 'Sistema' ?></title>
    <?php if (defined('BASE_URL')): ?>
        <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
    <?php endif; ?>
</head>
<body>
<div class="tela-auth">
    <div class="cartao-auth">
        <div class="marca">Vitória Oliver</div>
        <h1>Ops!</h1>
        <p style="color:var(--cor-texto-suave);">
            Não foi possível concluir esta ação no momento. Nossa equipe técnica
            já foi notificada através dos logs do sistema.
        </p>
        <a class="botao" style="display:inline-block;text-decoration:none;" href="<?= defined('BASE_URL') ? BASE_URL . '/dashboard' : '/' ?>">Voltar ao painel</a>
    </div>
</div>
</body>
</html>

<?php
use App\Core\Auth;
use App\Core\Session;
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="SGRH — Sistema de Gestão de Recursos Humanos">
    <title><?= e($titulo ?? 'Painel') ?> · SGRH</title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='22' fill='%234F46E5'/><text x='50' y='68' font-size='54' font-family='sans-serif' font-weight='700' fill='white' text-anchor='middle'>S</text></svg>">
</head>
<body class="app">
<a class="skip-link" href="#main">Pular para o conteúdo</a>

<?php require APP_PATH . '/Views/partials/sidebar.php'; ?>

<div class="app__main">
    <?php require APP_PATH . '/Views/partials/topbar.php'; ?>

    <main class="app__content" id="main">
        <?php require APP_PATH . '/Views/partials/flash.php'; ?>
        <?= $conteudo ?>
    </main>

    <footer class="app__footer">
        <span>SGRH · Sistema de Gestão de Recursos Humanos</span>
        <span>Arquitetura MVC · PHP <?= PHP_VERSION ?> · MySQL</span>
    </footer>
</div>

<div class="sidebar-overlay" data-sidebar-overlay hidden></div>
<div class="toast-root" id="toast-root" aria-live="polite" aria-atomic="true"></div>
<div class="modal-root" id="modal-root"></div>

<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>

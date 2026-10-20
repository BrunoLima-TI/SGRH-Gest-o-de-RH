<?php
use App\Core\Auth;
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titulo ?? 'Acesso') ?> · SGRH</title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body class="auth-page">
<?= $conteudo ?>
<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>

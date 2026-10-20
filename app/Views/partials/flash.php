<?php
use App\Core\Session;

$tipos = [
    'sucesso' => ['check', 'success'],
    'erro'    => ['alert', 'danger'],
    'atencao' => ['alert', 'warning'],
    'info'    => ['info', 'info'],
];

foreach ($tipos as $chave => [$icone, $variante]):
    $mensagem = Session::flash($chave);
    if (!$mensagem) {
        continue;
    }
?>
<div class="alert alert--<?= e($variante) ?>" role="status" data-alert>
    <?= icon($icone, 18) ?>
    <span><?= e($mensagem) ?></span>
    <button class="alert__close" type="button" data-alert-close aria-label="Fechar"><?= icon('x', 14) ?></button>
</div>
<?php endforeach; ?>

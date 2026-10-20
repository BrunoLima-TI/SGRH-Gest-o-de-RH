<?php
use App\Core\Auth;

$usuario = Auth::usuario();
$tituloPagina = $titulo ?? 'Painel';
$rotaAtual = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/');
$base = BASE_PATH === '/' ? '' : rtrim(BASE_PATH, '/');
if ($base !== '' && str_starts_with($rotaAtual, $base)) {
    $rotaAtual = substr($rotaAtual, strlen($base));
}
$rotaAtual = trim($rotaAtual, '/');

$rotulos = [
    'dashboard' => 'Painel',
    'colaboradores' => 'Colaboradores',
    'frequencia' => 'Frequência',
    'ferias' => 'Férias e ausências',
    'vagas' => 'Recrutamento',
    'treinamentos' => 'Treinamentos',
    'desempenho' => 'Desempenho',
    'documentos' => 'Documentos',
    'estrutura' => 'Estrutura',
    'relatorios' => 'Relatórios',
    'usuarios' => 'Usuários',
];
$segmentos = array_values(array_filter(explode('/', $rotaAtual)));
?>
<header class="topbar">
    <button class="icon-btn topbar__menu" type="button" data-sidebar-toggle aria-label="Abrir menu" aria-expanded="false">
        <?= icon('menu', 20) ?>
    </button>

    <nav class="breadcrumb" aria-label="Trilha de navegação">
        <a href="<?= url('dashboard') ?>">Início</a>
        <?php foreach ($segmentos as $i => $seg): ?>
            <span class="breadcrumb__sep"><?= icon('chevron-right', 14) ?></span>
            <?php if ($i === count($segmentos) - 1): ?>
                <span aria-current="page"><?= e($rotulos[$seg] ?? ucfirst(str_replace('-', ' ', $seg))) ?></span>
            <?php else: ?>
                <span><?= e($rotulos[$seg] ?? ucfirst(str_replace('-', ' ', $seg))) ?></span>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>

    <div class="topbar__search">
        <span class="topbar__search-icon"><?= icon('search', 16) ?></span>
        <input type="search" placeholder="Buscar colaborador, vaga, documento…" aria-label="Busca global"
               data-global-search autocomplete="off">
        <span class="kbd">Ctrl K</span>
    </div>

    <div class="topbar__actions">
        <button class="icon-btn" type="button" aria-label="Notificações" data-toast="Você está em dia com suas pendências.">
            <?= icon('bell', 20) ?>
            <span class="dot-badge" hidden></span>
        </button>
        <a class="icon-btn" href="<?= url('relatorios') ?>" aria-label="Relatórios"><?= icon('chart', 20) ?></a>

        <div class="dropdown" data-dropdown>
            <button class="topbar__user" type="button" data-dropdown-toggle aria-expanded="false" aria-haspopup="menu">
                <span class="avatar"><?= e(iniciais($usuario['nome'] ?? 'Usuario')) ?></span>
                <span class="topbar__user-text">
                    <strong><?= e($usuario['nome'] ?? 'Visitante') ?></strong>
                    <small><?= e(App\Core\Auth::PERFIS[$usuario['perfil'] ?? 'colaborador'] ?? 'Colaborador') ?></small>
                </span>
                <?= icon('chevron-down', 16) ?>
            </button>
            <div class="dropdown__menu" data-dropdown-menu role="menu">
                <a class="dropdown__item" href="<?= url('usuarios') ?>"><?= icon('user', 16) ?> Meu perfil</a>
                <a class="dropdown__item" href="<?= url('estrutura') ?>"><?= icon('building', 16) ?> Estrutura</a>
                <div class="dropdown__divider"></div>
                <a class="dropdown__item dropdown__item--danger" href="<?= url('logout') ?>"><?= icon('logout', 16) ?> Sair do sistema</a>
            </div>
        </div>
    </div>
</header>

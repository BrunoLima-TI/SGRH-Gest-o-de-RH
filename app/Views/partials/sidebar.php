<?php
use App\Core\Auth;

$usuario = Auth::usuario();
$rotaAtual = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/');
$base = BASE_PATH === '/' ? '' : rtrim(BASE_PATH, '/');
if ($base !== '' && str_starts_with($rotaAtual, $base)) {
    $rotaAtual = substr($rotaAtual, strlen($base));
}
$rotaAtual = trim($rotaAtual, '/');

$menu = [
    ['titulo' => 'Visão geral', 'itens' => [
        ['rota' => 'dashboard', 'icon' => 'dashboard', 'texto' => 'Painel de indicadores'],
    ]],
    ['titulo' => 'Gestão de pessoas', 'itens' => [
        ['rota' => 'colaboradores', 'icon' => 'users', 'texto' => 'Colaboradores'],
        ['rota' => 'frequencia', 'icon' => 'clock', 'texto' => 'Frequência'],
        ['rota' => 'ferias', 'icon' => 'calendar', 'texto' => 'Férias e ausências'],
        ['rota' => 'desempenho', 'icon' => 'star', 'texto' => 'Desempenho'],
    ]],
    ['titulo' => 'Desenvolvimento', 'itens' => [
        ['rota' => 'vagas', 'icon' => 'briefcase', 'texto' => 'Recrutamento'],
        ['rota' => 'treinamentos', 'icon' => 'graduation', 'texto' => 'Treinamentos'],
    ]],
    ['titulo' => 'Organização', 'itens' => [
        ['rota' => 'documentos', 'icon' => 'file', 'texto' => 'Documentos'],
        ['rota' => 'estrutura', 'icon' => 'building', 'texto' => 'Departamentos e cargos'],
        ['rota' => 'relatorios', 'icon' => 'chart', 'texto' => 'Relatórios'],
    ]],
    ['titulo' => 'Administração', 'itens' => [
        ['rota' => 'usuarios', 'icon' => 'shield', 'texto' => 'Usuários', 'admin' => true],
    ]],
];

function rota_ativa(string $rota, string $atual): bool
{
    if ($rota === 'dashboard') {
        return $atual === '' || $atual === 'dashboard';
    }
    return $atual === $rota || str_starts_with($atual, $rota . '/');
}
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar__brand">
        <span class="sidebar__logo">S</span>
        <span class="sidebar__brand-text">
            <strong>SGRH</strong>
            <small>Gestão de RH</small>
        </span>
        <button class="sidebar__close" type="button" data-sidebar-toggle aria-label="Fechar menu"><?= icon('x', 20) ?></button>
    </div>

    <nav class="sidebar__nav" aria-label="Menu principal">
        <?php foreach ($menu as $secao): ?>
            <?php
            $itens = array_filter($secao['itens'], fn($i) => empty($i['admin']) || Auth::ehAdmin());
            if (!$itens) {
                continue;
            }
            ?>
            <div class="sidebar__section">
                <span class="sidebar__label"><?= e($secao['titulo']) ?></span>
                <?php foreach ($itens as $item): ?>
                    <a class="sidebar__link <?= rota_ativa($item['rota'], $rotaAtual) ? 'is-active' : '' ?>"
                       href="<?= url($item['rota']) ?>"
                       <?= rota_ativa($item['rota'], $rotaAtual) ? 'aria-current="page"' : '' ?>>
                        <span class="sidebar__icon"><?= icon($item['icon'], 20) ?></span>
                        <span class="sidebar__text"><?= e($item['texto']) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </nav>

    <div class="sidebar__footer">
        <div class="user-chip">
            <span class="avatar avatar--sm"><?= e(iniciais($usuario['nome'] ?? 'Usuario')) ?></span>
            <span class="user-chip__text">
                <strong><?= e($usuario['nome'] ?? 'Visitante') ?></strong>
                <small><?= e(App\Core\Auth::PERFIS[$usuario['perfil'] ?? 'colaborador'] ?? 'Colaborador') ?></small>
            </span>
        </div>
        <a class="sidebar__link sidebar__link--danger" href="<?= url('logout') ?>">
            <span class="sidebar__icon"><?= icon('logout', 20) ?></span>
            <span class="sidebar__text">Sair</span>
        </a>
    </div>
</aside>

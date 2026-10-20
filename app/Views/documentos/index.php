<div class="page-head">
    <div class="page-head__text">
        <h1>Documentos</h1>
        <p>Registro de documentos funcionais e controle de validade.</p>
    </div>
    <div class="page-head__actions">
        <?php if (App\Core\Auth::ehRh()): ?>
            <a class="btn btn--primary" href="<?= url('documentos/novo') ?>"><?= icon('plus', 16) ?> Novo documento</a>
        <?php endif; ?>
    </div>
</div>

<?php if ($vencendo): ?>
    <div class="alert alert--warning">
        <?= icon('alert', 18) ?>
        <span><strong><?= count($vencendo) ?></strong> documento(s) vencem em até 30 dias. Revise as renovações.</span>
    </div>
<?php endif; ?>

<div class="grid grid--3 mb-6">
    <div class="kpi">
        <div class="kpi__top"><span class="kpi__chip"><?= icon('file', 18) ?></span></div>
        <div class="kpi__value" data-count="<?= (int) $total ?>">0</div>
        <div class="kpi__label">Documentos registrados</div>
    </div>
    <div class="kpi kpi--warning">
        <div class="kpi__top"><span class="kpi__chip"><?= icon('clock', 18) ?></span></div>
        <div class="kpi__value" data-count="<?= count($vencendo) ?>">0</div>
        <div class="kpi__label">Vencendo em 30 dias</div>
    </div>
    <div class="kpi kpi--info">
        <div class="kpi__top"><span class="kpi__chip"><?= icon('clipboard', 18) ?></span></div>
        <div class="kpi__value" data-count="<?= count($tipos) ?>">0</div>
        <div class="kpi__label">Tipos catalogados</div>
    </div>
</div>

<form class="filters" method="get" action="<?= url('documentos') ?>">
    <div class="filters__search">
        <span class="filters__search-icon"><?= icon('search', 16) ?></span>
        <input class="input" type="search" name="busca" placeholder="Buscar documento" value="<?= e($filtros['busca']) ?>">
    </div>
    <select class="select" name="tipo" aria-label="Filtrar por tipo">
        <option value="">Todos os tipos</option>
        <?php foreach ($tipos as $t): ?>
            <option value="<?= e($t['tipo']) ?>" <?= $filtros['tipo'] === $t['tipo'] ? 'selected' : '' ?>><?= e($t['tipo']) ?></option>
        <?php endforeach; ?>
    </select>
    <select class="select" name="colaborador_id" aria-label="Filtrar por colaborador">
        <option value="">Todos os colaboradores</option>
        <?php foreach ($colaboradores as $c): ?>
            <option value="<?= (int) $c['id'] ?>" <?= $filtros['colaborador_id'] === (string) $c['id'] ? 'selected' : '' ?>><?= e($c['nome']) ?></option>
        <?php endforeach; ?>
    </select>
    <button class="btn btn--secondary" type="submit"><?= icon('filter', 16) ?> Filtrar</button>
    <a class="btn btn--ghost" href="<?= url('documentos') ?>">Limpar</a>
</form>

<div class="card card--flush">
    <div class="table-wrap" style="border:0;border-radius:0">
        <table class="table">
            <caption class="sr-only">Documentos cadastrados</caption>
            <thead>
                <tr>
                    <th>Documento</th>
                    <th>Tipo</th>
                    <th>Colaborador</th>
                    <th>Emissão</th>
                    <th>Validade</th>
                    <th>Situação</th>
                    <?php if (App\Core\Auth::ehRh()): ?><th class="table__actions">Ações</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php if (!$documentos): ?>
                <tr><td colspan="7" class="table__empty">Nenhum documento encontrado.</td></tr>
            <?php endif; ?>
            <?php foreach ($documentos as $d):
                $vencido = $d['data_validade'] && strtotime($d['data_validade']) < time();
                $venceLogo = $d['data_validade'] && !$vencido && strtotime($d['data_validade']) <= strtotime('+30 days');
                $situacao = $vencido ? 'danger' : ($venceLogo ? 'warning' : 'success');
                $rotulo = $vencido ? 'Vencido' : ($venceLogo ? 'Vencendo' : 'Válido');
                ?>
                <tr>
                    <td data-label="Documento">
                        <div style="font-weight:600;color:var(--text-primary)"><?= e($d['nome']) ?></div>
                        <?php if ($d['observacoes']): ?><div class="user-cell__meta"><?= e($d['observacoes']) ?></div><?php endif; ?>
                    </td>
                    <td data-label="Tipo"><span class="badge badge--neutral"><?= e($d['tipo']) ?></span></td>
                    <td data-label="Colaborador"><?= e($d['colaborador'] ?? '—') ?></td>
                    <td data-label="Emissão"><?= e(formatar_data($d['data_emissao'])) ?></td>
                    <td data-label="Validade"><?= e(formatar_data($d['data_validade'])) ?></td>
                    <td data-label="Situação"><span class="badge badge--<?= $situacao ?>"><?= $rotulo ?></span></td>
                    <?php if (App\Core\Auth::ehRh()): ?>
                        <td class="table__actions">
                            <form method="post" action="<?= url('documentos/excluir') ?>" data-confirm="Excluir este documento?">
                                <?= App\Core\Csrf::campo() ?>
                                <input type="hidden" name="id" value="<?= (int) $d['id'] ?>">
                                <button class="icon-btn icon-btn--sm" type="submit" aria-label="Excluir" style="color:var(--danger)"><?= icon('trash', 16) ?></button>
                            </form>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

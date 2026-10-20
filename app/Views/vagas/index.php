<div class="page-head">
    <div class="page-head__text">
        <h1>Recrutamento e seleção</h1>
        <p>Vagas abertas, pipeline de candidatos e etapas do processo seletivo.</p>
    </div>
    <div class="page-head__actions">
        <?php if (App\Core\Auth::ehRh()): ?>
            <a class="btn btn--primary" href="<?= url('vagas/nova') ?>"><?= icon('plus', 16) ?> Nova vaga</a>
        <?php endif; ?>
    </div>
</div>

<div class="grid grid--4 mb-6">
    <?php
    $cores = ['aberta' => 'success', 'em_analise' => 'info', 'fechada' => 'neutral', 'cancelada' => 'danger'];
    $rotulos = ['aberta' => 'Abertas', 'em_analise' => 'Em análise', 'fechada' => 'Fechadas', 'cancelada' => 'Canceladas'];
    ?>
    <?php foreach ($rotulos as $k => $rotulo): ?>
        <div class="kpi kpi--<?= $cores[$k] ?>">
            <div class="kpi__top"><span class="kpi__chip"><?= icon('briefcase', 18) ?></span></div>
            <div class="kpi__value" data-count="<?= (int) ($totalPorStatus[$k] ?? 0) ?>">0</div>
            <div class="kpi__label"><?= e($rotulo) ?></div>
        </div>
    <?php endforeach; ?>
</div>

<h3 class="card__title mb-4">Pipeline de candidatos</h3>
<div class="pipeline mb-6">
    <?php foreach ($pipeline as $etapa): ?>
        <div class="pipeline__col">
            <div class="pipeline__title">
                <span><?= e($etapa['rotulo']) ?></span>
                <span class="badge badge--neutral"><?= (int) $etapa['total'] ?></span>
            </div>
            <?php foreach ($etapa['itens'] as $c): ?>
                <div class="pipeline__item">
                    <strong><?= e($c['nome']) ?></strong>
                    <small><?= e(mb_substr($c['vaga'], 0, 26)) ?></small>
                </div>
            <?php endforeach; ?>
            <?php if (!$etapa['itens']): ?>
                <p class="text-muted text-center" style="font-size:12px;padding:12px 0">—</p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<form class="filters" method="get" action="<?= url('vagas') ?>">
    <div class="filters__search">
        <span class="filters__search-icon"><?= icon('search', 16) ?></span>
        <input class="input" type="search" name="busca" placeholder="Buscar vaga" value="<?= e($filtros['busca']) ?>">
    </div>
    <select class="select" name="status" aria-label="Filtrar por status">
        <option value="">Todos os status</option>
        <?php foreach ($rotulos as $k => $rotulo): ?>
            <option value="<?= e($k) ?>" <?= $filtros['status'] === $k ? 'selected' : '' ?>><?= e($rotulo) ?></option>
        <?php endforeach; ?>
    </select>
    <button class="btn btn--secondary" type="submit"><?= icon('filter', 16) ?> Filtrar</button>
    <a class="btn btn--ghost" href="<?= url('vagas') ?>">Limpar</a>
</form>

<div class="card card--flush">
    <div class="table-wrap" style="border:0;border-radius:0">
        <table class="table">
            <caption class="sr-only">Vagas cadastradas</caption>
            <thead>
                <tr>
                    <th>Vaga</th>
                    <th>Departamento</th>
                    <th>Tipo</th>
                    <th>Quantidade</th>
                    <th>Candidatos</th>
                    <th>Abertura</th>
                    <th>Status</th>
                    <th class="table__actions">Ações</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$vagas): ?>
                <tr><td colspan="8" class="table__empty">Nenhuma vaga cadastrada.</td></tr>
            <?php endif; ?>
            <?php foreach ($vagas as $v): ?>
                <tr>
                    <td data-label="Vaga"><strong><?= e($v['titulo']) ?></strong></td>
                    <td data-label="Departamento"><?= e($v['departamento'] ?? '—') ?></td>
                    <td data-label="Tipo"><?= e(strtoupper($v['tipo_contrato'])) ?></td>
                    <td data-label="Quantidade" class="tnum"><?= (int) $v['quantidade'] ?></td>
                    <td data-label="Candidatos" class="tnum"><?= (int) $v['total_candidatos'] ?></td>
                    <td data-label="Abertura"><?= e(formatar_data($v['data_abertura'])) ?></td>
                    <td data-label="Status"><span class="badge badge--<?= e(classe_status($v['status'])) ?>"><?= e(rotulo_status($v['status'])) ?></span></td>
                    <td class="table__actions">
                        <div class="flex gap-2" style="justify-content:flex-end">
                            <a class="icon-btn icon-btn--sm" href="<?= url('vagas/' . (int) $v['id']) ?>" aria-label="Ver detalhes"><?= icon('eye', 16) ?></a>
                            <?php if (App\Core\Auth::ehRh()): ?>
                                <a class="icon-btn icon-btn--sm" href="<?= url('vagas/' . (int) $v['id'] . '/editar') ?>" aria-label="Editar"><?= icon('edit', 16) ?></a>
                                <form method="post" action="<?= url('vagas/excluir') ?>" style="display:inline" data-confirm="Excluir esta vaga?">
                                    <?= App\Core\Csrf::campo() ?>
                                    <input type="hidden" name="id" value="<?= (int) $v['id'] ?>">
                                    <button class="icon-btn icon-btn--sm" type="submit" aria-label="Excluir" style="color:var(--danger)"><?= icon('trash', 16) ?></button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

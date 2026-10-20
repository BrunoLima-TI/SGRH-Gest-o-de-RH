<div class="page-head">
    <div class="page-head__text">
        <h1>Avaliação de desempenho</h1>
        <p>Ciclos avaliativos, critérios de competency e evolução das notas.</p>
    </div>
    <div class="page-head__actions">
        <?php if (App\Core\Auth::pode('admin', 'rh', 'gestor')): ?>
            <a class="btn btn--primary" href="<?= url('desempenho/nova') ?>"><?= icon('plus', 16) ?> Nova avaliação</a>
        <?php endif; ?>
    </div>
</div>

<div class="grid grid--2-1 mb-6">
    <div class="card">
        <div class="chart-card__head">
            <div>
                <h3 class="card__title">Média por critério</h3>
                <p class="card__subtitle">Agregado de todas as avaliações concluídas</p>
            </div>
        </div>
        <?= App\Core\Charts::barras($porCriterio, ['titulo' => 'Média por critério', 'cor' => App\Core\Charts::CORES[3]]) ?>
    </div>
    <div class="card">
        <div class="chart-card__head">
            <div>
                <h3 class="card__title">Média geral</h3>
                <p class="card__subtitle">Escala de 0 a 10</p>
            </div>
        </div>
        <div class="flex flex-center gap-4">
            <span class="kpi__chip" style="width:56px;height:56px"><?= icon('star', 26) ?></span>
            <div>
                <div class="kpi__value" style="margin:0;font-size:34px">
                    <span data-count="<?= (float) $mediaGeral ?>" data-decimals="1">0</span>
                </div>
                <div class="kpi__label">Média das avaliações</div>
            </div>
        </div>
        <div class="divider"></div>
        <ul class="list-plain">
            <?php foreach ($porCriterio as $c): ?>
                <li class="list-row">
                    <span class="list-row__title"><?= e($c['rotulo']) ?></span>
                    <strong class="tnum"><?= e(formatar_numero($c['total'], 1)) ?>/10</strong>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<form class="filters" method="get" action="<?= url('desempenho') ?>">
    <select class="select" name="ciclo" aria-label="Filtrar por ciclo">
        <option value="">Todos os ciclos</option>
        <?php foreach ($ciclos as $c): ?>
            <option value="<?= e($c['ciclo']) ?>" <?= $filtros['ciclo'] === $c['ciclo'] ? 'selected' : '' ?>><?= e($c['ciclo']) ?></option>
        <?php endforeach; ?>
    </select>
    <select class="select" name="status" aria-label="Filtrar por status">
        <option value="">Todos os status</option>
        <?php foreach (['pendente' => 'Pendente', 'em_andamento' => 'Em andamento', 'concluida' => 'Concluída'] as $k => $v): ?>
            <option value="<?= e($k) ?>" <?= $filtros['status'] === $k ? 'selected' : '' ?>><?= e($v) ?></option>
        <?php endforeach; ?>
    </select>
    <select class="select" name="colaborador_id" aria-label="Filtrar por colaborador">
        <option value="">Todos os colaboradores</option>
        <?php foreach ($colaboradores as $c): ?>
            <option value="<?= (int) $c['id'] ?>" <?= $filtros['colaborador_id'] === (string) $c['id'] ? 'selected' : '' ?>><?= e($c['nome']) ?></option>
        <?php endforeach; ?>
    </select>
    <button class="btn btn--secondary" type="submit"><?= icon('filter', 16) ?> Filtrar</button>
    <a class="btn btn--ghost" href="<?= url('desempenho') ?>">Limpar</a>
</form>

<div class="card card--flush">
    <div class="table-wrap" style="border:0;border-radius:0">
        <table class="table">
            <caption class="sr-only">Avaliações de desempenho</caption>
            <thead>
                <tr>
                    <th>Colaborador</th>
                    <th>Departamento</th>
                    <th>Ciclo</th>
                    <th>Avaliador</th>
                    <th>Nota</th>
                    <th>Data</th>
                    <th>Status</th>
                    <?php if (App\Core\Auth::pode('admin', 'rh')): ?><th class="table__actions">Ações</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php if (!$avaliacoes): ?>
                <tr><td colspan="8" class="table__empty">Nenhuma avaliação encontrada.</td></tr>
            <?php endif; ?>
            <?php foreach ($avaliacoes as $a):
                $cor = (float) $a['nota_geral'] >= 8 ? 'success' : ((float) $a['nota_geral'] >= 6 ? 'warning' : 'danger'); ?>
                <tr>
                    <td data-label="Colaborador">
                        <div class="user-cell">
                            <span class="avatar avatar--sm"><?= e(iniciais($a['colaborador'])) ?></span>
                            <span class="user-cell__name"><?= e($a['colaborador']) ?></span>
                        </div>
                    </td>
                    <td data-label="Departamento"><?= e($a['departamento'] ?? '—') ?></td>
                    <td data-label="Ciclo"><?= e($a['ciclo']) ?></td>
                    <td data-label="Avaliador"><?= e($a['avaliador'] ?? '—') ?></td>
                    <td data-label="Nota"><span class="badge badge--<?= $cor ?>"><?= e(formatar_numero($a['nota_geral'], 1)) ?>/10</span></td>
                    <td data-label="Data"><?= e(formatar_data($a['data_avaliacao'])) ?></td>
                    <td data-label="Status"><span class="badge badge--<?= e(classe_status($a['status'])) ?>"><?= e(rotulo_status($a['status'])) ?></span></td>
                    <?php if (App\Core\Auth::pode('admin', 'rh')): ?>
                        <td class="table__actions">
                            <div class="flex gap-2" style="justify-content:flex-end">
                                <a class="icon-btn icon-btn--sm" href="<?= url('desempenho/' . (int) $a['id'] . '/editar') ?>" aria-label="Editar"><?= icon('edit', 16) ?></a>
                                <form method="post" action="<?= url('desempenho/excluir') ?>" data-confirm="Excluir esta avaliação?">
                                    <?= App\Core\Csrf::campo() ?>
                                    <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                                    <button class="icon-btn icon-btn--sm" type="submit" aria-label="Excluir" style="color:var(--danger)"><?= icon('trash', 16) ?></button>
                                </form>
                            </div>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

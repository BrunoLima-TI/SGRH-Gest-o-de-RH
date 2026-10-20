<div class="page-head">
    <div class="page-head__text">
        <h1>Férias e ausências</h1>
        <p>Controle de férias, atestados, licenças e demais afastamentos.</p>
    </div>
    <div class="page-head__actions">
        <a class="btn btn--secondary" href="<?= url('relatorios/exportar') ?>?tipo=ferias"><?= icon('download', 16) ?> Exportar</a>
        <?php if (App\Core\Auth::ehRh()): ?>
            <a class="btn btn--primary" href="<?= url('ferias/nova') ?>"><?= icon('plus', 16) ?> Nova ausência</a>
        <?php endif; ?>
    </div>
</div>

<?php if ($pendentes): ?>
    <div class="card mb-6">
        <div class="chart-card__head">
            <div>
                <h3 class="card__title">Solicitações aguardando decisão</h3>
                <p class="card__subtitle"><?= count($pendentes) ?> solicitação(ões) pendente(s)</p>
            </div>
        </div>
        <div class="table-wrap" style="border:0;border-radius:0">
            <table class="table">
                <thead>
                    <tr>
                        <th>Colaborador</th>
                        <th>Tipo</th>
                        <th>Período</th>
                        <th>Dias</th>
                        <th class="table__actions">Decisão</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($pendentes as $p): ?>
                    <tr>
                        <td data-label="Colaborador">
                            <div class="user-cell">
                                <span class="avatar avatar--sm"><?= e(iniciais($p['colaborador'])) ?></span>
                                <span class="user-cell__name"><?= e($p['colaborador']) ?></span>
                            </div>
                        </td>
                        <td data-label="Tipo"><span class="badge badge--<?= e(classe_status($p['tipo'])) ?>"><?= e(rotulo_status($p['tipo'])) ?></span></td>
                        <td data-label="Período"><?= e(formatar_data($p['data_inicio'])) ?> a <?= e(formatar_data($p['data_fim'])) ?></td>
                        <td data-label="Dias" class="tnum"><?= (int) $p['dias'] ?></td>
                        <td class="table__actions">
                            <div class="flex gap-2" style="justify-content:flex-end">
                                <?php if (App\Core\Auth::ehRh()): ?>
                                    <form method="post" action="<?= url('ferias/decidir') ?>" style="display:inline">
                                        <?= App\Core\Csrf::campo() ?>
                                        <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                                        <input type="hidden" name="decisao" value="aprovado">
                                        <button class="btn btn--sm btn--primary" type="submit"><?= icon('check', 14) ?> Aprovar</button>
                                    </form>
                                    <form method="post" action="<?= url('ferias/decidir') ?>" style="display:inline">
                                        <?= App\Core\Csrf::campo() ?>
                                        <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                                        <input type="hidden" name="decisao" value="reprovado">
                                        <button class="btn btn--sm btn--danger-ghost" type="submit"><?= icon('x', 14) ?> Reprovar</button>
                                    </form>
                                <?php else: ?>
                                    <span class="badge badge--warning">Pendente</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<div class="grid grid--4 mb-6">
    <?php
    $totais = ['ferias' => 0, 'atestado' => 0, 'falta' => 0, 'licenca' => 0, 'afastamento' => 0];
    foreach ($ausencias as $a) {
        $totais[$a['tipo']] = ($totais[$a['tipo']] ?? 0) + (int) $a['dias'];
    }
    $saldoMedio = 0;
    foreach ($saldo as $s) {
        $saldoMedio += (int) $s['saldo'];
    }
    $saldoMedio = count($saldo) ? round($saldoMedio / count($saldo), 1) : 0;
    ?>
    <div class="kpi kpi--warning">
        <div class="kpi__top"><span class="kpi__chip"><?= icon('sun', 18) ?></span></div>
        <div class="kpi__value"><span data-count="<?= (float) $saldoMedio ?>" data-decimals="1">0</span></div>
        <div class="kpi__label">Saldo médio de férias</div>
    </div>
    <div class="kpi kpi--success">
        <div class="kpi__top"><span class="kpi__chip"><?= icon('calendar', 18) ?></span></div>
        <div class="kpi__value" data-count="<?= (int) $totais['ferias'] ?>">0</div>
        <div class="kpi__label">Dias de férias</div>
    </div>
    <div class="kpi kpi--info">
        <div class="kpi__top"><span class="kpi__chip"><?= icon('file', 18) ?></span></div>
        <div class="kpi__value" data-count="<?= (int) $totais['atestado'] ?>">0</div>
        <div class="kpi__label">Dias de atestado</div>
    </div>
    <div class="kpi kpi--danger">
        <div class="kpi__top"><span class="kpi__chip"><?= icon('alert', 18) ?></span></div>
        <div class="kpi__value" data-count="<?= (int) ($totais['falta'] + $totais['licenca'] + $totais['afastamento']) ?>">0</div>
        <div class="kpi__label">Outros afastamentos</div>
    </div>
</div>

<form class="filters" method="get" action="<?= url('ferias') ?>">
    <select class="select" name="status" aria-label="Filtrar por status">
        <option value="">Todos os status</option>
        <?php foreach (['pendente' => 'Pendente', 'aprovado' => 'Aprovado', 'reprovado' => 'Reprovado'] as $k => $v): ?>
            <option value="<?= e($k) ?>" <?= $filtros['status'] === $k ? 'selected' : '' ?>><?= e($v) ?></option>
        <?php endforeach; ?>
    </select>
    <select class="select" name="tipo" aria-label="Filtrar por tipo">
        <option value="">Todos os tipos</option>
        <?php foreach (['ferias' => 'Férias', 'atestado' => 'Atestado', 'falta' => 'Falta', 'licenca' => 'Licença', 'afastamento' => 'Afastamento'] as $k => $v): ?>
            <option value="<?= e($k) ?>" <?= $filtros['tipo'] === $k ? 'selected' : '' ?>><?= e($v) ?></option>
        <?php endforeach; ?>
    </select>
    <input class="input" type="number" name="ano" value="<?= e($filtros['ano']) ?>" placeholder="Ano" style="width:120px" min="2000" max="2100">
    <button class="btn btn--secondary" type="submit"><?= icon('filter', 16) ?> Filtrar</button>
    <a class="btn btn--ghost" href="<?= url('ferias') ?>">Limpar</a>
</form>

<div class="card card--flush">
    <div class="table-wrap" style="border:0;border-radius:0">
        <table class="table">
            <caption class="sr-only">Registros de ausências</caption>
            <thead>
                <tr>
                    <th>Colaborador</th>
                    <th>Departamento</th>
                    <th>Tipo</th>
                    <th>Período</th>
                    <th>Dias</th>
                    <th>Status</th>
                    <?php if (App\Core\Auth::ehRh()): ?><th class="table__actions">Ações</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php if (!$ausencias): ?>
                <tr><td colspan="7" class="table__empty">Nenhum registro encontrado.</td></tr>
            <?php endif; ?>
            <?php foreach ($ausencias as $a): ?>
                <tr>
                    <td data-label="Colaborador">
                        <div class="user-cell">
                            <span class="avatar avatar--sm"><?= e(iniciais($a['colaborador'])) ?></span>
                            <span class="user-cell__name"><?= e($a['colaborador']) ?></span>
                        </div>
                    </td>
                    <td data-label="Departamento"><?= e($a['departamento'] ?? '—') ?></td>
                    <td data-label="Tipo"><?= e(rotulo_status($a['tipo'])) ?></td>
                    <td data-label="Período"><?= e(formatar_data($a['data_inicio'])) ?> a <?= e(formatar_data($a['data_fim'])) ?></td>
                    <td data-label="Dias" class="tnum"><?= (int) $a['dias'] ?></td>
                    <td data-label="Status"><span class="badge badge--<?= e(classe_status($a['status'])) ?>"><?= e(rotulo_status($a['status'])) ?></span></td>
                    <?php if (App\Core\Auth::ehRh()): ?>
                        <td class="table__actions">
                            <form method="post" action="<?= url('ferias/excluir') ?>" style="display:inline"
                                  data-confirm="Excluir este registro de ausência?">
                                <?= App\Core\Csrf::campo() ?>
                                <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
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

<div class="page-head">
    <div class="page-head__text">
        <h1>Painel de indicadores</h1>
        <p>Visão consolidada da força de trabalho, absenteísmo, rotatividade e resultados das ações de RH.</p>
    </div>
    <div class="page-head__actions">
        <a class="btn btn--secondary" href="<?= url('relatorios') ?>"><?= icon('chart', 16) ?> Relatórios</a>
        <?php if (App\Core\Auth::ehRh()): ?>
            <a class="btn btn--primary" href="<?= url('colaboradores/novo') ?>"><?= icon('plus', 16) ?> Novo colaborador</a>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($pendenciasAusencia)): ?>
    <div class="alert alert--warning" role="status">
        <?= icon('alert', 18) ?>
        <span><strong><?= count($pendenciasAusencia) ?></strong> solicitação(ões) de ausência aguardando decisão.</span>
        <a class="btn btn--sm btn--secondary" href="<?= url('ferias') ?>" style="margin-left:auto">Revisar</a>
    </div>
<?php endif; ?>

<!-- KPIs -->
<div class="grid grid--4 mb-6">
    <div class="kpi">
        <div class="kpi__top">
            <span class="kpi__chip"><?= icon('users', 18) ?></span>
            <span class="badge badge--success"><?= e(rotulo_status('ativo')) ?></span>
        </div>
        <div class="kpi__value" data-count="<?= (int) $totalAtivos ?>">0</div>
        <div class="kpi__label">Colaboradores ativos</div>
        <div class="kpi__footer">
            <span class="kpi__caption">
                <?= (int) ($porStatus['ferias'] ?? 0) ?> em férias ·
                <?= (int) ($porStatus['afastado'] ?? 0) ?> afastados
            </span>
            <span style="width:80px"><?= $sparklineAdmissoes ?></span>
        </div>
    </div>

    <div class="kpi kpi--danger">
        <div class="kpi__top">
            <span class="kpi__chip"><?= icon('trending', 18) ?></span>
            <span class="badge badge--danger">Rotatividade</span>
        </div>
        <div class="kpi__value"><span data-count="<?= (float) $rotatividade ?>" data-decimals="1">0</span>%</div>
        <div class="kpi__label">Rotatividade (12 meses)</div>
        <div class="kpi__footer">
            <span class="kpi__caption">Acompanhamento mensal</span>
            <span style="width:80px"><?= $sparklineRotatividade ?></span>
        </div>
    </div>

    <div class="kpi kpi--warning">
        <div class="kpi__top">
            <span class="kpi__chip"><?= icon('clock', 18) ?></span>
            <span class="badge badge--warning">Mês atual</span>
        </div>
        <div class="kpi__value"><span data-count="<?= (float) $absenteismo ?>" data-decimals="1">0</span>%</div>
        <div class="kpi__label">Taxa de absenteísmo</div>
        <div class="kpi__footer">
            <span class="kpi__caption">
                <?= (int) ($indicadoresMes['falta'] ?? 0) ?> faltas ·
                <?= (int) ($indicadoresMes['atraso'] ?? 0) ?> atrasos
            </span>
            <span style="width:80px"><?= $sparklineAbsenteismo ?></span>
        </div>
    </div>

    <div class="kpi kpi--info">
        <div class="kpi__top">
            <span class="kpi__chip"><?= icon('briefcase', 18) ?></span>
            <span class="badge badge--info">Pipeline</span>
        </div>
        <div class="kpi__value" data-count="<?= (int) $vagasAbertas ?>">0</div>
        <div class="kpi__label">Vagas abertas</div>
        <div class="kpi__footer">
            <span class="kpi__caption"><?= (int) $totalVagas ?> vagas cadastradas</span>
            <a class="btn btn--sm btn--soft" href="<?= url('vagas') ?>">Ver</a>
        </div>
    </div>
</div>

<!-- Gráficos -->
<div class="grid grid--2-1 mb-6">
    <div class="card">
        <div class="chart-card__head">
            <div>
                <h3 class="card__title">Absenteísmo nos últimos 12 meses</h3>
                <p class="card__subtitle">Percentual de faltas e ausências sobre o total de registros</p>
            </div>
            <a class="btn btn--sm btn--ghost" href="<?= url('relatorios') ?>">Ver detalhes</a>
        </div>
        <?= $graficoAbsenteismo ?>
    </div>

    <div class="card">
        <div class="chart-card__head">
            <div>
                <h3 class="card__title">Distribuição por departamento</h3>
                <p class="card__subtitle">Colaboradores ativos por área</p>
            </div>
        </div>
        <div style="max-width:220px;margin:0 auto"><?= $graficoDepartamentos ?></div>
        <?= App\Core\Charts::legenda(array_slice($departamentos, 0, 6)) ?>
    </div>
</div>

<!-- Tabelas e listas -->
<div class="grid grid--2-1 mb-6">
    <div class="card card--flush">
        <div class="card__head" style="padding:20px 20px 0">
            <div>
                <h3 class="card__title">Próximas férias</h3>
                <p class="card__subtitle">Afastamentos aprovados já programados</p>
            </div>
            <a class="btn btn--sm btn--ghost" href="<?= url('ferias') ?>">Ver todos</a>
        </div>
        <div class="table-wrap" style="border:0;border-radius:0">
            <table class="table">
                <thead>
                    <tr>
                        <th>Colaborador</th>
                        <th>Departamento</th>
                        <th>Início</th>
                        <th>Dias</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$proximasFerias): ?>
                    <tr><td colspan="5" class="table__empty">Nenhuma férias programada.</td></tr>
                <?php endif; ?>
                <?php foreach ($proximasFerias as $f): ?>
                    <tr>
                        <td data-label="Colaborador">
                            <div class="user-cell">
                                <span class="avatar avatar--sm"><?= e(iniciais($f['colaborador'])) ?></span>
                                <span class="user-cell__name"><?= e($f['colaborador']) ?></span>
                            </div>
                        </td>
                        <td data-label="Departamento"><?= e($f['departamento'] ?? '—') ?></td>
                        <td data-label="Início"><?= e(formatar_data($f['data_inicio'])) ?></td>
                        <td data-label="Dias" class="tnum"><?= (int) $f['dias'] ?></td>
                        <td data-label="Status"><span class="badge badge--warning">Aprovado</span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="chart-card__head">
            <div>
                <h3 class="card__title">Alertas e pendências</h3>
                <p class="card__subtitle">Itens que exigem atenção do RH</p>
            </div>
        </div>
        <ul class="list-plain">
            <?php foreach ($pendenciasAusencia as $p): ?>
                <li class="list-row">
                    <div>
                        <div class="list-row__title"><?= e($p['colaborador']) ?></div>
                        <div class="list-row__meta">
                            <?= e(rotulo_status($p['tipo'])) ?> · <?= e(formatar_data($p['data_inicio'])) ?> a <?= e(formatar_data($p['data_fim'])) ?>
                        </div>
                    </div>
                    <span class="badge badge--warning">Pendente</span>
                </li>
            <?php endforeach; ?>

            <?php foreach ($documentosVencendo as $d): ?>
                <li class="list-row">
                    <div>
                        <div class="list-row__title"><?= e($d['nome']) ?></div>
                        <div class="list-row__meta"><?= e($d['colaborador'] ?? '—') ?> · vence em <?= e(formatar_data($d['data_validade'])) ?></div>
                    </div>
                    <span class="badge badge--warning">Vencendo</span>
                </li>
            <?php endforeach; ?>

            <?php if (!$pendenciasAusencia && !$documentosVencendo): ?>
                <li class="text-muted text-center" style="padding:24px 0;font-size:13px">
                    <?= icon('check', 20) ?><br>Nenhuma pendência no momento.
                </li>
            <?php endif; ?>
        </ul>
    </div>
</div>

<!-- Rodapé de cards -->
<div class="grid grid--3">
    <div class="card">
        <div class="chart-card__head">
            <div>
                <h3 class="card__title">Treinamentos</h3>
                <p class="card__subtitle">Acompanhamento das turmas</p>
            </div>
            <a class="btn btn--sm btn--ghost" href="<?= url('treinamentos') ?>">Ver</a>
        </div>
        <?php if (!$treinamentos): ?>
            <p class="text-muted" style="font-size:13px">Nenhum treinamento cadastrado.</p>
        <?php endif; ?>
        <?php foreach ($treinamentos as $t): ?>
            <?php $perc = $t['total_inscritos'] > 0 ? round(((int) $t['concluidos'] / (int) $t['total_inscritos']) * 100) : 0; ?>
            <div style="margin-bottom:16px">
                <div class="progress-label">
                    <span><?= e(mb_substr($t['titulo'], 0, 34)) ?></span>
                    <span class="tnum"><?= $perc ?>%</span>
                </div>
                <div class="progress progress--slim <?= $perc >= 100 ? 'progress--success' : '' ?>">
                    <div class="progress__fill" style="width:<?= $perc ?>%"></div>
                </div>
                <div class="text-muted" style="font-size:11px;margin-top:4px">
                    <?= (int) $t['total_inscritos'] ?> inscritos · <?= e($t['categoria']) ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="card">
        <div class="chart-card__head">
            <div>
                <h3 class="card__title">Aniversariantes do mês</h3>
                <p class="card__subtitle"><?= e(date('F')) ?> — mês corrente</p>
            </div>
        </div>
        <ul class="list-plain">
            <?php foreach (array_slice($aniversariantes, 0, 6) as $a): ?>
                <li class="list-row">
                    <div class="user-cell">
                        <span class="avatar avatar--sm"><?= e(iniciais($a['nome'])) ?></span>
                        <div>
                            <div class="list-row__title"><?= e($a['nome']) ?></div>
                            <div class="list-row__meta"><?= e($a['departamento'] ?? '—') ?></div>
                        </div>
                    </div>
                    <span class="badge badge--neutral"><?= e(formatar_data($a['data_nascimento'], 'd/m')) ?></span>
                </li>
            <?php endforeach; ?>
            <?php if (!$aniversariantes): ?>
                <li class="text-muted text-center" style="padding:20px 0;font-size:13px">Nenhum aniversário neste mês.</li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="card">
        <div class="chart-card__head">
            <div>
                <h3 class="card__title">Desempenho</h3>
                <p class="card__subtitle">Média das avaliações registradas</p>
            </div>
            <a class="btn btn--sm btn--ghost" href="<?= url('desempenho') ?>">Ver</a>
        </div>
        <div class="flex flex-center gap-4" style="margin:12px 0 16px">
            <span class="kpi__chip" style="width:48px;height:48px"><?= icon('star', 22) ?></span>
            <div>
                <div class="kpi__value" style="margin:0;font-size:26px">
                    <span data-count="<?= (float) $mediaDesempenho ?>" data-decimals="1">0</span>
                </div>
                <div class="kpi__label">Média geral</div>
            </div>
        </div>
        <div class="divider"></div>
        <ul class="list-plain">
            <?php foreach ([['Treinamentos concluídos', $indicadoresTreinamento['concluidos'], $indicadoresTreinamento['inscritos'] ?: 1], ['Horas de capacitação', (int) $indicadoresTreinamento['horas'], 1]] as $item): ?>
                <li class="list-row">
                    <span class="list-row__title"><?= e($item[0]) ?></span>
                    <strong class="tnum"><?= e((string) $item[1]) ?></strong>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

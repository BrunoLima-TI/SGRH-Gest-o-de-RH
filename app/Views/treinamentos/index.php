<div class="page-head">
    <div class="page-head__text">
        <h1>Treinamentos e capacitação</h1>
        <p>Planos de desenvolvimento, turmas e acompanhamento de progresso.</p>
    </div>
    <div class="page-head__actions">
        <?php if (App\Core\Auth::ehRh()): ?>
            <a class="btn btn--primary" href="<?= url('treinamentos/novo') ?>"><?= icon('plus', 16) ?> Novo treinamento</a>
        <?php endif; ?>
    </div>
</div>

<div class="grid grid--4 mb-6">
    <div class="kpi">
        <div class="kpi__top"><span class="kpi__chip"><?= icon('graduation', 18) ?></span></div>
        <div class="kpi__value" data-count="<?= (int) $indicadores['total'] ?>">0</div>
        <div class="kpi__label">Treinamentos</div>
    </div>
    <div class="kpi kpi--info">
        <div class="kpi__top"><span class="kpi__chip"><?= icon('users', 18) ?></span></div>
        <div class="kpi__value" data-count="<?= (int) $indicadores['inscritos'] ?>">0</div>
        <div class="kpi__label">Inscrições</div>
    </div>
    <div class="kpi kpi--success">
        <div class="kpi__top"><span class="kpi__chip"><?= icon('check', 18) ?></span></div>
        <div class="kpi__value" data-count="<?= (int) $indicadores['concluidos'] ?>">0</div>
        <div class="kpi__label">Concluídos</div>
    </div>
    <div class="kpi kpi--violet">
        <div class="kpi__top"><span class="kpi__chip"><?= icon('trending', 18) ?></span></div>
        <div class="kpi__value"><span data-count="<?= (float) $indicadores['taxa'] ?>" data-decimals="1">0</span>%</div>
        <div class="kpi__label">Taxa de conclusão</div>
    </div>
</div>

<h3 class="card__title mb-4">Trilhas de desenvolvimento</h3>
<div class="grid grid--3 mb-6">
    <?php if (!$treinamentos): ?>
        <div class="card text-muted" style="font-size:13px">Nenhum treinamento cadastrado.</div>
    <?php endif; ?>
    <?php foreach ($treinamentos as $t):
        $perc = $t['total_inscritos'] > 0 ? round(((int) $t['concluidos'] / (int) $t['total_inscritos']) * 100) : 0; ?>
        <div class="card">
            <div class="chart-card__head">
                <span class="badge badge--violet"><?= e($t['categoria']) ?></span>
                <?php if (App\Core\Auth::ehRh()): ?>
                    <div class="flex gap-2">
                        <a class="icon-btn icon-btn--sm" href="<?= url('treinamentos/' . (int) $t['id'] . '/editar') ?>" aria-label="Editar"><?= icon('edit', 16) ?></a>
                        <form method="post" action="<?= url('treinamentos/excluir') ?>" data-confirm="Excluir este treinamento?">
                            <?= App\Core\Csrf::campo() ?>
                            <input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
                            <button class="icon-btn icon-btn--sm" type="submit" aria-label="Excluir" style="color:var(--danger)"><?= icon('trash', 16) ?></button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
            <h4 class="card__title" style="font-size:15px"><?= e($t['titulo']) ?></h4>
            <p class="card__subtitle" style="margin-top:4px">
                <?= e(formatar_numero($t['carga_horaria'], 0)) ?>h · <?= e($t['instrutor'] ?: 'Instrutor a definir') ?>
            </p>
            <div class="mt-4">
                <div class="progress-label">
                    <span>Conclusão</span>
                    <span class="tnum"><?= $perc ?>%</span>
                </div>
                <div class="progress <?= $perc >= 100 ? 'progress--success' : '' ?>">
                    <div class="progress__fill" style="width:<?= $perc ?>%"></div>
                </div>
            </div>
            <div class="card__footer flex between">
                <span><?= (int) $t['total_inscritos'] ?> inscritos</span>
                <span><?= (int) $t['concluidos'] ?> concluídos</span>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="card card--flush">
    <div class="card__head" style="padding:20px 20px 0">
        <div>
            <h3 class="card__title">Inscrições</h3>
            <p class="card__subtitle">Acompanhamento individual de progresso</p>
        </div>
    </div>
    <div class="table-wrap" style="border:0;border-radius:0">
        <table class="table">
            <thead>
                <tr>
                    <th>Colaborador</th>
                    <th>Departamento</th>
                    <th>Treinamento</th>
                    <th>Progresso</th>
                    <th>Status</th>
                    <th>Nota</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$inscricoes): ?>
                <tr><td colspan="6" class="table__empty">Nenhuma inscrição registrada.</td></tr>
            <?php endif; ?>
            <?php foreach ($inscricoes as $i): ?>
                <tr>
                    <td data-label="Colaborador">
                        <div class="user-cell">
                            <span class="avatar avatar--sm"><?= e(iniciais($i['colaborador'])) ?></span>
                            <span class="user-cell__name"><?= e($i['colaborador']) ?></span>
                        </div>
                    </td>
                    <td data-label="Departamento"><?= e($i['departamento'] ?? '—') ?></td>
                    <td data-label="Treinamento">
                        <div style="font-size:13px;font-weight:600;color:var(--text-primary)"><?= e($i['treinamento']) ?></div>
                        <div class="user-cell__meta"><?= e($i['categoria']) ?> · <?= e(formatar_numero($i['carga_horaria'], 0)) ?>h</div>
                    </td>
                    <td data-label="Progresso" style="min-width:140px">
                        <div class="flex flex-center gap-3">
                            <div class="progress progress--slim grow <?= (int) $i['progresso'] >= 100 ? 'progress--success' : '' ?>" style="min-width:70px">
                                <div class="progress__fill" style="width:<?= (int) $i['progresso'] ?>%"></div>
                            </div>
                            <span class="tnum" style="font-size:12px"><?= (int) $i['progresso'] ?>%</span>
                        </div>
                    </td>
                    <td data-label="Status"><span class="badge badge--<?= e(classe_status($i['status'])) ?>"><?= e(rotulo_status($i['status'])) ?></span></td>
                    <td data-label="Nota" class="tnum"><?= $i['nota'] !== null ? e(formatar_numero($i['nota'], 1)) : '—' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

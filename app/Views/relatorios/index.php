<div class="page-head">
    <div class="page-head__text">
        <h1>Relatórios e indicadores</h1>
        <p>Painel analítico com dados de absenteísmo, quadro de pessoal, capacitação e desempenho.</p>
    </div>
    <div class="page-head__actions">
        <a class="btn btn--secondary" href="<?= url('relatorios/exportar') ?>?tipo=colaboradores"><?= icon('download', 16) ?> Colaboradores</a>
        <a class="btn btn--secondary" href="<?= url('relatorios/exportar') ?>?tipo=frequencia"><?= icon('download', 16) ?> Frequência</a>
        <a class="btn btn--secondary" href="<?= url('relatorios/exportar') ?>?tipo=ferias"><?= icon('download', 16) ?> Férias</a>
        <a class="btn btn--secondary" href="<?= url('relatorios/exportar') ?>?tipo=desempenho"><?= icon('download', 16) ?> Desempenho</a>
    </div>
</div>

<form class="filters" method="get" action="<?= url('relatorios') ?>">
    <select class="select" name="periodo" aria-label="Período de análise">
        <?php foreach ([6 => 'Últimos 6 meses', 12 => 'Últimos 12 meses', 18 => 'Últimos 18 meses', 24 => 'Últimos 24 meses'] as $v => $r): ?>
            <option value="<?= $v ?>" <?= $meses === $v ? 'selected' : '' ?>><?= e($r) ?></option>
        <?php endforeach; ?>
    </select>
    <button class="btn btn--secondary" type="submit"><?= icon('filter', 16) ?> Aplicar período</button>
</form>

<div class="grid grid--2 mb-6">
    <div class="card">
        <div class="chart-card__head">
            <div>
                <h3 class="card__title">Taxa de absenteísmo</h3>
                <p class="card__subtitle">Faltas e ausências sobre o total de registros</p>
            </div>
        </div>
        <?= $graficoAbsenteismo ?>
    </div>
    <div class="card">
        <div class="chart-card__head">
            <div>
                <h3 class="card__title">Horas trabalhadas</h3>
                <p class="card__subtitle">Volume total de horas registradas por mês</p>
            </div>
        </div>
        <?= $graficoHoras ?>
    </div>
</div>

<div class="grid grid--2 mb-6">
    <div class="card">
        <div class="chart-card__head">
            <div>
                <h3 class="card__title">Quadro por departamento</h3>
                <p class="card__subtitle">Número de colaboradores ativos</p>
            </div>
        </div>
        <?= $graficoDepartamentos ?>
    </div>
    <div class="card">
        <div class="chart-card__head">
            <div>
                <h3 class="card__title">Tipo de contrato</h3>
                <p class="card__subtitle">Composição da força de trabalho</p>
            </div>
        </div>
        <div style="max-width:220px;margin:0 auto"><?= $graficoContrato ?></div>
        <?= App\Core\Charts::legenda($porContrato) ?>
    </div>
</div>

<div class="grid grid--2 mb-6">
    <div class="card">
        <div class="chart-card__head">
            <div>
                <h3 class="card__title">Distribuição das notas</h3>
                <p class="card__subtitle">Avaliações de desempenho por nota final</p>
            </div>
        </div>
        <?= $graficoDesempenho ?>
    </div>
    <div class="card">
        <div class="chart-card__head">
            <div>
                <h3 class="card__title">Média por critério</h3>
                <p class="card__subtitle">Competências avaliadas</p>
            </div>
        </div>
        <?= $graficoCriterios ?>
    </div>
</div>

<div class="card card--flush">
    <div class="card__head" style="padding:20px 20px 0">
        <div>
            <h3 class="card__title">Série histórica de movimentações</h3>
            <p class="card__subtitle">Admissões e desligamentos por mês</p>
        </div>
    </div>
    <div class="table-wrap" style="border:0;border-radius:0">
        <table class="table">
            <thead>
                <tr>
                    <th>Mês</th>
                    <th>Admissões</th>
                    <th>Desligamentos</th>
                    <th>Saldo</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $mesesMap = [];
            foreach ($admitidos as $a) { $mesesMap[$a['mes']] = ['rotulo' => $a['rotulo'], 'admitidos' => (int) $a['total'], 'desligados' => 0]; }
            foreach ($desligados as $d) {
                if (!isset($mesesMap[$d['mes']])) { $mesesMap[$d['mes']] = ['rotulo' => $d['rotulo'], 'admitidos' => 0, 'desligados' => 0]; }
                $mesesMap[$d['mes']]['desligados'] = (int) $d['total'];
            }
            ksort($mesesMap);
            ?>
            <?php if (!$mesesMap): ?>
                <tr><td colspan="4" class="table__empty">Sem movimentações no período.</td></tr>
            <?php endif; ?>
            <?php foreach ($mesesMap as $m): ?>
                <?php $saldo = $m['admitidos'] - $m['desligados']; ?>
                <tr>
                    <td data-label="Mês"><?= e($m['rotulo']) ?></td>
                    <td data-label="Admissões" class="tnum text-success">+<?= $m['admitidos'] ?></td>
                    <td data-label="Desligamentos" class="tnum text-danger">−<?= $m['desligados'] ?></td>
                    <td data-label="Saldo" class="tnum"><strong style="color:<?= $saldo >= 0 ? 'var(--success-ink)' : 'var(--danger)' ?>"><?= $saldo >= 0 ? '+' : '' ?><?= $saldo ?></strong></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$meses = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
$anoAtual = (int) date('Y');
$anos = range($anoAtual - 3, $anoAtual + 1);
?>

<div class="page-head">
    <div class="page-head__text">
        <h1>Frequência</h1>
        <p>Registros de presença, faltas, atrasos e horas extras por período.</p>
    </div>
    <div class="page-head__actions">
        <a class="btn btn--secondary" href="<?= url('relatorios/exportar') ?>?tipo=frequencia">
            <?= icon('download', 16) ?> Exportar
        </a>
        <?php if (App\Core\Auth::pode('admin', 'rh', 'gestor')): ?>
            <button class="btn btn--primary" type="button" data-open-template="tpl-freq" data-title="Registrar frequência">
                <?= icon('plus', 16) ?> Nova frequência
            </button>
        <?php endif; ?>
    </div>
</div>

<form class="filters" method="get" action="<?= url('frequencia') ?>">
    <select class="select" name="mes" aria-label="Mês">
        <?php foreach ($meses as $i => $nome): ?>
            <option value="<?= $i + 1 ?>" <?= $mes === $i + 1 ? 'selected' : '' ?>><?= e($nome) ?></option>
        <?php endforeach; ?>
    </select>
    <select class="select" name="ano" aria-label="Ano">
        <?php foreach ($anos as $a): ?>
            <option value="<?= $a ?>" <?= $ano === $a ? 'selected' : '' ?>><?= $a ?></option>
        <?php endforeach; ?>
    </select>
    <button class="btn btn--secondary" type="submit"><?= icon('filter', 16) ?> Aplicar</button>
</form>

<div class="grid grid--4 mb-6">
    <div class="kpi kpi--success">
        <div class="kpi__top"><span class="kpi__chip"><?= icon('check', 18) ?></span></div>
        <div class="kpi__value" data-count="<?= (int) ($indicadores['presente'] ?? 0) ?>">0</div>
        <div class="kpi__label">Presenças</div>
    </div>
    <div class="kpi kpi--danger">
        <div class="kpi__top"><span class="kpi__chip"><?= icon('x', 18) ?></span></div>
        <div class="kpi__value" data-count="<?= (int) ($indicadores['falta'] ?? 0) ?>">0</div>
        <div class="kpi__label">Faltas</div>
    </div>
    <div class="kpi kpi--warning">
        <div class="kpi__top"><span class="kpi__chip"><?= icon('clock', 18) ?></span></div>
        <div class="kpi__value" data-count="<?= (int) ($indicadores['atraso'] ?? 0) ?>">0</div>
        <div class="kpi__label">Atrasos</div>
    </div>
    <div class="kpi kpi--violet">
        <div class="kpi__top"><span class="kpi__chip"><?= icon('trending', 18) ?></span></div>
        <div class="kpi__value"><span data-count="<?= (float) ($indicadores['extras'] ?? 0) ?>" data-decimals="1">0</span>h</div>
        <div class="kpi__label">Horas extras</div>
    </div>
</div>

<div class="card card--flush">
    <div class="table-wrap" style="border:0;border-radius:0;max-height:70vh;overflow-y:auto">
        <table class="table freq-matrix">
            <caption class="sr-only">Matriz de frequência de <?= e($meses[$mes - 1]) ?> de <?= $ano ?></caption>
            <thead>
                <tr>
                    <th scope="col" style="text-align:left;position:sticky;left:0;z-index:3">Colaborador</th>
                    <?php for ($d = 1; $d <= $diasMes; $d++): ?>
                        <?php
                        $data = sprintf('%04d-%02d-%02d', $ano, $mes, $d);
                        $diaSemana = date('N', strtotime($data));
                        ?>
                        <th scope="col" style="text-align:center;padding:12px 6px" title="<?= e(formatar_data($data, 'd/m/Y')) ?>">
                            <?= $d ?><br><small style="font-weight:400;text-transform:none"><?= e(['S','T','Q','Q','S','S','D'][(int)$diaSemana - 1]) ?></small>
                        </th>
                    <?php endfor; ?>
                </tr>
            </thead>
            <tbody>
            <?php if (!$colaboradores): ?>
                <tr><td class="table__empty" colspan="<?= $diasMes + 1 ?>">Nenhum colaborador ativo cadastrado.</td></tr>
            <?php endif; ?>
            <?php foreach ($colaboradores as $c): ?>
                <tr>
                    <td style="text-align:left;position:sticky;left:0;background:#fff;z-index:1;white-space:nowrap">
                        <div class="user-cell">
                            <span class="avatar avatar--xs"><?= e(iniciais($c['nome'])) ?></span>
                            <span class="user-cell__name"><?= e($c['nome']) ?></span>
                        </div>
                    </td>
                    <?php for ($d = 1; $d <= $diasMes; $d++):
                        $data = sprintf('%04d-%02d-%02d', $ano, $mes, $d);
                        $reg = $mapa[$c['id']][$data] ?? null;
                        $status = $reg['status'] ?? '';
                        $classe = $status ? 'freq-cell--' . $status : 'freq-cell--vazio';
                        $rotulo = $status ? rotulo_status($status) : 'Sem registro';
                        ?>
                        <td data-label="<?= e(formatar_data($data, 'd/m')) ?>">
                            <span class="freq-cell <?= e($classe) ?>"
                                  title="<?= e($c['nome'] . ' · ' . formatar_data($data, 'd/m/Y') . ' · ' . $rotulo) ?>">
                                <?= $status === 'presente' ? '✓' : ($status === 'falta' ? 'F' : ($status === 'atraso' ? 'A' : ($status === 'ausente' ? '–' : ($status === 'ferias' ? 'F' : '')))) ?>
                            </span>
                        </td>
                    <?php endfor; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (App\Core\Auth::pode('admin', 'rh', 'gestor')): ?>
<template id="tpl-freq">
    <form method="post" action="<?= url('frequencia/registrar') ?>">
        <?= App\Core\Csrf::campo() ?>
        <div class="form-grid">
            <div class="form-field form-field--full">
                <label class="label" for="f-colaborador">Colaborador</label>
                <select class="select" id="f-colaborador" name="colaborador_id" required>
                    <?php foreach ($colaboradores as $c): ?>
                        <option value="<?= (int) $c['id'] ?>"><?= e($c['nome']) ?> — <?= e($c['departamento'] ?? '—') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-field">
                <label class="label" for="f-data">Data</label>
                <input class="input" id="f-data" type="date" name="data" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="form-field">
                <label class="label" for="f-status">Situação</label>
                <select class="select" id="f-status" name="status">
                    <?php foreach (['presente' => 'Presente', 'atraso' => 'Atraso', 'falta' => 'Falta', 'ausente' => 'Ausente', 'ferias' => 'Férias', 'meio_periodo' => 'Meio período'] as $k => $v): ?>
                        <option value="<?= e($k) ?>"><?= e($v) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-field">
                <label class="label" for="f-entrada">Entrada</label>
                <input class="input" id="f-entrada" type="time" name="entrada" value="08:00">
            </div>
            <div class="form-field">
                <label class="label" for="f-saida">Saída</label>
                <input class="input" id="f-saida" type="time" name="saida" value="17:00">
            </div>
            <div class="form-field">
                <label class="label" for="f-horas">Horas trabalhadas</label>
                <input class="input" id="f-horas" type="number" step="0.5" name="horas_trabalhadas" value="8">
            </div>
            <div class="form-field">
                <label class="label" for="f-extras">Horas extras</label>
                <input class="input" id="f-extras" type="number" step="0.5" name="horas_extras" value="0">
            </div>
            <div class="form-field form-field--full">
                <label class="label" for="f-obs">Observações</label>
                <input class="input" id="f-obs" name="observacoes">
            </div>
        </div>
    </form>
</template>
<?php endif; ?>

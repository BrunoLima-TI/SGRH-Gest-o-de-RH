<?php
use App\Core\Session;

$erros = Session::get('errors', []);
$valor = fn(string $c, $p = '') => old($c, $p);
?>

<div class="page-head">
    <div class="page-head__text">
        <h1>Registrar ausência</h1>
        <p>Férias, atestados, licenças, faltas e afastamentos de colaboradores.</p>
    </div>
    <div class="page-head__actions">
        <a class="btn btn--secondary" href="<?= url('ferias') ?>"><?= icon('chevron-left', 16) ?> Voltar</a>
    </div>
</div>

<?php if ($erros): ?>
    <div class="alert alert--danger" role="alert">
        <?= icon('alert', 18) ?>
        <div>
            <strong>Não foi possível salvar:</strong>
            <ul style="margin:6px 0 0;padding-left:18px;font-size:13px">
                <?php foreach ($erros as $msg): ?><li><?= e($msg) ?></li><?php endforeach; ?>
            </ul>
        </div>
    </div>
<?php endif; ?>

<form class="card" method="post" action="<?= e($action) ?>" data-validate novalidate>
    <?= App\Core\Csrf::campo() ?>

    <div class="form-grid">
        <div class="form-field form-field--full">
            <label class="label" for="colaborador_id">Colaborador <span class="label__req">*</span></label>
            <select class="select <?= isset($erros['colaborador_id']) ? 'is-invalid' : '' ?>" id="colaborador_id" name="colaborador_id" required>
                <option value="">Selecione…</option>
                <?php foreach ($colaboradores as $c): ?>
                    <option value="<?= (int) $c['id'] ?>" <?= (string) $valor('colaborador_id') === (string) $c['id'] ? 'selected' : '' ?>>
                        <?= e($c['nome']) ?> — <?= e($c['departamento'] ?? '—') ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($erros['colaborador_id'])): ?><span class="error-text"><?= icon('alert', 14) ?> <?= e($erros['colaborador_id']) ?></span><?php endif; ?>
        </div>

        <div class="form-field">
            <label class="label" for="tipo">Tipo de ausência <span class="label__req">*</span></label>
            <select class="select" id="tipo" name="tipo" required>
                <?php foreach (['ferias' => 'Férias', 'atestado' => 'Atestado médico', 'falta' => 'Falta', 'licenca' => 'Licença', 'afastamento' => 'Afastamento'] as $k => $v): ?>
                    <option value="<?= e($k) ?>" <?= (string) $valor('tipo', 'ferias') === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-field">
            <label class="label" for="dias">Dias (informativo)</label>
            <input class="input" id="dias" type="number" min="1" value="" placeholder="Calculado automaticamente" disabled>
            <span class="help-text">Calculado a partir das datas informadas.</span>
        </div>

        <div class="form-field">
            <label class="label" for="data_inicio">Data de início <span class="label__req">*</span></label>
            <input class="input <?= isset($erros['data_inicio']) ? 'is-invalid' : '' ?>" id="data_inicio" type="date" name="data_inicio"
                   value="<?= e($valor('data_inicio')) ?>" required>
            <?php if (isset($erros['data_inicio'])): ?><span class="error-text"><?= icon('alert', 14) ?> <?= e($erros['data_inicio']) ?></span><?php endif; ?>
        </div>

        <div class="form-field">
            <label class="label" for="data_fim">Data de fim <span class="label__req">*</span></label>
            <input class="input <?= isset($erros['data_fim']) ? 'is-invalid' : '' ?>" id="data_fim" type="date" name="data_fim"
                   value="<?= e($valor('data_fim')) ?>" required>
            <?php if (isset($erros['data_fim'])): ?><span class="error-text"><?= icon('alert', 14) ?> <?= e($erros['data_fim']) ?></span><?php endif; ?>
        </div>

        <div class="form-field form-field--full">
            <label class="label" for="justificativa">Justificativa</label>
            <textarea class="textarea" id="justificativa" name="justificativa" placeholder="Motivo do afastamento…"><?= e($valor('justificativa')) ?></textarea>
        </div>
    </div>

    <div class="form-actions">
        <a class="btn btn--secondary" href="<?= url('ferias') ?>">Cancelar</a>
        <button class="btn btn--primary" type="submit"><?= icon('check', 16) ?> Registrar ausência</button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var ini = document.getElementById('data_inicio');
    var fim = document.getElementById('data_fim');
    var dias = document.getElementById('dias');
    function calc() {
        if (!ini.value || !fim.value) { dias.value = ''; return; }
        var d = Math.round((new Date(fim.value) - new Date(ini.value)) / 86400000) + 1;
        dias.value = d > 0 ? d : '';
    }
    ini.addEventListener('change', calc);
    fim.addEventListener('change', calc);
});
</script>

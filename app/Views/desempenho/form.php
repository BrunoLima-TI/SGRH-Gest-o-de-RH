<?php
use App\Core\Session;

$erros = Session::get('errors', []);
$valor = fn(string $c, $p = '') => old($c, $p);

$criterios = ['Qualidade do trabalho', 'Relacionamento interpessoal', 'Proatividade', 'Cumprimento de metas', 'Conhecimento técnico', 'Comunicação'];
$notasExistentes = $avaliacao && $avaliacao['criterios'] ? (json_decode($avaliacao['criterios'], true) ?: []) : [];
?>

<div class="page-head">
    <div class="page-head__text">
        <h1><?= e($titulo) ?></h1>
        <p>Avalie as competências do colaborador em uma escala de 1 a 10.</p>
    </div>
    <div class="page-head__actions">
        <a class="btn btn--secondary" href="<?= url('desempenho') ?>"><?= icon('chevron-left', 16) ?> Voltar</a>
    </div>
</div>

<?php if ($erros): ?>
    <div class="alert alert--danger" role="alert">
        <?= icon('alert', 18) ?>
        <div>
            <strong>Revise os campos abaixo:</strong>
            <ul style="margin:6px 0 0;padding-left:18px;font-size:13px">
                <?php foreach ($erros as $msg): ?><li><?= e($msg) ?></li><?php endforeach; ?>
            </ul>
        </div>
    </div>
<?php endif; ?>

<form class="card" method="post" action="<?= e($action) ?>" data-validate novalidate>
    <?= App\Core\Csrf::campo() ?>
    <?php if ($avaliacao): ?><input type="hidden" name="id" value="<?= (int) $avaliacao['id'] ?>"><?php endif; ?>

    <div class="form-grid mb-6">
        <div class="form-field">
            <label class="label" for="colaborador_id">Colaborador <span class="label__req">*</span></label>
            <select class="select <?= isset($erros['colaborador_id']) ? 'is-invalid' : '' ?>" id="colaborador_id" name="colaborador_id" required>
                <option value="">Selecione…</option>
                <?php foreach ($colaboradores as $c): ?>
                    <option value="<?= (int) $c['id'] ?>" <?= (string) $valor('colaborador_id', $avaliacao['colaborador_id'] ?? '') === (string) $c['id'] ? 'selected' : '' ?>>
                        <?= e($c['nome']) ?> — <?= e($c['departamento'] ?? '—') ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($erros['colaborador_id'])): ?><span class="error-text"><?= icon('alert', 14) ?> <?= e($erros['colaborador_id']) ?></span><?php endif; ?>
        </div>

        <div class="form-field">
            <label class="label" for="ciclo">Ciclo de avaliação <span class="label__req">*</span></label>
            <input class="input <?= isset($erros['ciclo']) ? 'is-invalid' : '' ?>" id="ciclo" name="ciclo"
                   value="<?= e($valor('ciclo', $avaliacao['ciclo'] ?? ('Ciclo ' . date('Y') . ' - ' . (date('n') <= 6 ? '1º Semestre' : '2º Semestre')))) ?>" required>
            <?php if (isset($erros['ciclo'])): ?><span class="error-text"><?= icon('alert', 14) ?> <?= e($erros['ciclo']) ?></span><?php endif; ?>
        </div>

        <div class="form-field">
            <label class="label" for="avaliador_id">Avaliador</label>
            <select class="select" id="avaliador_id" name="avaliador_id">
                <option value="">Usuário logado</option>
                <?php foreach ($avaliadores as $u): ?>
                    <option value="<?= (int) $u['id'] ?>" <?= (string) $valor('avaliador_id', $avaliacao['avaliador_id'] ?? '') === (string) $u['id'] ? 'selected' : '' ?>>
                        <?= e($u['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-field">
            <label class="label" for="data_avaliacao">Data da avaliação</label>
            <input class="input" id="data_avaliacao" type="date" name="data_avaliacao"
                   value="<?= e($valor('data_avaliacao', $avaliacao['data_avaliacao'] ?? date('Y-m-d'))) ?>">
        </div>

        <div class="form-field">
            <label class="label" for="status">Status</label>
            <select class="select" id="status" name="status">
                <?php foreach (['pendente' => 'Pendente', 'em_andamento' => 'Em andamento', 'concluida' => 'Concluída'] as $k => $v): ?>
                    <option value="<?= e($k) ?>" <?= (string) $valor('status', $avaliacao['status'] ?? 'pendente') === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="divider"></div>

    <h3 class="card__title mb-4">Critérios de avaliação</h3>
    <?php if (isset($erros['criterios'])): ?>
        <div class="alert alert--danger"><?= icon('alert', 18) ?><span><?= e($erros['criterios']) ?></span></div>
    <?php endif; ?>

    <div class="stack stack--sm mb-6">
        <?php foreach ($criterios as $crit): ?>
            <?php $nota = $notasExistentes[$crit] ?? ''; ?>
            <div class="flex between flex-center gap-4" style="padding:10px 0;border-bottom:1px solid var(--border)">
                <label class="label" for="crit_<?= e(slug($crit)) ?>" style="margin:0"><?= e($crit) ?></label>
                <div class="flex gap-2 flex-center">
                    <input class="input" id="crit_<?= e(slug($crit)) ?>" type="number" name="criterios[<?= e($crit) ?>]"
                           min="1" max="10" step="0.5" value="<?= e($nota) ?>" style="width:90px" placeholder="1–10">
                    <span class="text-muted" style="font-size:12px">/10</span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="form-field mb-4">
        <label class="label" for="comentarios">Comentários e plano de desenvolvimento</label>
        <textarea class="textarea" id="comentarios" name="comentarios" placeholder="Pontos fortes, oportunidades e ações acordadas…"><?= e($valor('comentarios', $avaliacao['comentarios'] ?? '')) ?></textarea>
    </div>

    <div class="form-actions">
        <a class="btn btn--secondary" href="<?= url('desempenho') ?>">Cancelar</a>
        <button class="btn btn--primary" type="submit"><?= icon('check', 16) ?> Salvar avaliação</button>
    </div>
</form>

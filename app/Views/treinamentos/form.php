<?php
use App\Core\Session;

$erros = Session::get('errors', []);
$valor = fn(string $c, $p = '') => old($c, $p);
?>

<div class="page-head">
    <div class="page-head__text">
        <h1><?= e($titulo) ?></h1>
        <p>Planeje a capacitação da equipe: carga horária, instrutor, custos e turmas.</p>
    </div>
    <div class="page-head__actions">
        <a class="btn btn--secondary" href="<?= url('treinamentos') ?>"><?= icon('chevron-left', 16) ?> Voltar</a>
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
    <?php if ($treinamento): ?><input type="hidden" name="id" value="<?= (int) $treinamento['id'] ?>"><?php endif; ?>

    <div class="form-grid">
        <div class="form-field form-field--full">
            <label class="label" for="titulo">Título <span class="label__req">*</span></label>
            <input class="input <?= isset($erros['titulo']) ? 'is-invalid' : '' ?>" id="titulo" name="titulo"
                   value="<?= e($valor('titulo', $treinamento['titulo'] ?? '')) ?>" required>
            <?php if (isset($erros['titulo'])): ?><span class="error-text"><?= icon('alert', 14) ?> <?= e($erros['titulo']) ?></span><?php endif; ?>
        </div>

        <div class="form-field">
            <label class="label" for="categoria">Categoria</label>
            <input class="input" id="categoria" name="categoria" list="lista-categorias"
                   value="<?= e($valor('categoria', $treinamento['categoria'] ?? 'Geral')) ?>">
            <datalist id="lista-categorias">
                <?php foreach ($categorias as $c): ?><option value="<?= e($c['categoria']) ?>"><?php endforeach; ?>
            </datalist>
        </div>

        <div class="form-field">
            <label class="label" for="carga_horaria">Carga horária</label>
            <input class="input" id="carga_horaria" type="number" step="0.5" min="0" name="carga_horaria"
                   value="<?= e($valor('carga_horaria', $treinamento['carga_horaria'] ?? 8)) ?>">
        </div>

        <div class="form-field">
            <label class="label" for="data_inicio">Data de início</label>
            <input class="input" id="data_inicio" type="date" name="data_inicio"
                   value="<?= e($valor('data_inicio', $treinamento['data_inicio'] ?? '')) ?>">
        </div>

        <div class="form-field">
            <label class="label" for="data_fim">Data de término</label>
            <input class="input" id="data_fim" type="date" name="data_fim"
                   value="<?= e($valor('data_fim', $treinamento['data_fim'] ?? '')) ?>">
        </div>

        <div class="form-field">
            <label class="label" for="instrutor">Instrutor</label>
            <input class="input" id="instrutor" name="instrutor" value="<?= e($valor('instrutor', $treinamento['instrutor'] ?? '')) ?>">
        </div>

        <div class="form-field">
            <label class="label" for="vagas">Vagas por turma</label>
            <input class="input" id="vagas" type="number" min="1" name="vagas"
                   value="<?= e($valor('vagas', $treinamento['vagas'] ?? 20)) ?>">
        </div>

        <div class="form-field">
            <label class="label" for="custo">Custo estimado (R$)</label>
            <input class="input" id="custo" name="custo" data-mask="moeda"
                   value="<?= e($valor('custo') ? number_format((float) $valor('custo'), 2, ',', '.') : '0,00') ?>">
        </div>

        <div class="form-field form-field--full">
            <label class="label" for="descricao">Descrição</label>
            <textarea class="textarea" id="descricao" name="descricao" placeholder="Objetivos, metodologia e conteúdo programático…"><?= e($valor('descricao', $treinamento['descricao'] ?? '')) ?></textarea>
        </div>
    </div>

    <div class="form-actions">
        <a class="btn btn--secondary" href="<?= url('treinamentos') ?>">Cancelar</a>
        <button class="btn btn--primary" type="submit"><?= icon('check', 16) ?> <?= $treinamento ? 'Salvar alterações' : 'Criar treinamento' ?></button>
    </div>
</form>

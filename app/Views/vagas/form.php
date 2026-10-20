<?php
use App\Core\Session;

$erros = Session::get('errors', []);
$valor = fn(string $c, $p = '') => old($c, $p);
?>

<div class="page-head">
    <div class="page-head__text">
        <h1><?= e($titulo) ?></h1>
        <p>Defina o perfil da vaga, os requisitos e o responsável pelo processo.</p>
    </div>
    <div class="page-head__actions">
        <a class="btn btn--secondary" href="<?= url('vagas') ?>"><?= icon('chevron-left', 16) ?> Voltar</a>
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
    <?php if ($vaga): ?><input type="hidden" name="id" value="<?= (int) $vaga['id'] ?>"><?php endif; ?>

    <div class="form-grid">
        <div class="form-field form-field--full">
            <label class="label" for="titulo">Título da vaga <span class="label__req">*</span></label>
            <input class="input <?= isset($erros['titulo']) ? 'is-invalid' : '' ?>" id="titulo" name="titulo"
                   value="<?= e($valor('titulo', $vaga['titulo'] ?? '')) ?>" required>
            <?php if (isset($erros['titulo'])): ?><span class="error-text"><?= icon('alert', 14) ?> <?= e($erros['titulo']) ?></span><?php endif; ?>
        </div>

        <div class="form-field">
            <label class="label" for="departamento_id">Departamento</label>
            <select class="select" id="departamento_id" name="departamento_id">
                <option value="">Selecione…</option>
                <?php foreach ($departamentos as $d): ?>
                    <option value="<?= (int) $d['id'] ?>" <?= (string) $valor('departamento_id', $vaga['departamento_id'] ?? '') === (string) $d['id'] ? 'selected' : '' ?>>
                        <?= e($d['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-field">
            <label class="label" for="responsavel_id">Responsável</label>
            <select class="select" id="responsavel_id" name="responsavel_id">
                <option value="">Selecione…</option>
                <?php foreach ($responsaveis as $u): ?>
                    <option value="<?= (int) $u['id'] ?>" <?= (string) $valor('responsavel_id', $vaga['responsavel_id'] ?? '') === (string) $u['id'] ? 'selected' : '' ?>>
                        <?= e($u['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-field">
            <label class="label" for="tipo_contrato">Tipo de contrato</label>
            <select class="select" id="tipo_contrato" name="tipo_contrato">
                <?php foreach (['clt' => 'CLT', 'pj' => 'Pessoa jurídica', 'estagio' => 'Estágio', 'temporario' => 'Temporário'] as $k => $v): ?>
                    <option value="<?= e($k) ?>" <?= (string) $valor('tipo_contrato', $vaga['tipo_contrato'] ?? 'clt') === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-field">
            <label class="label" for="quantidade">Quantidade de vagas</label>
            <input class="input" id="quantidade" type="number" min="1" name="quantidade"
                   value="<?= e($valor('quantidade', $vaga['quantidade'] ?? 1)) ?>">
        </div>

        <div class="form-field">
            <label class="label" for="data_abertura">Data de abertura <span class="label__req">*</span></label>
            <input class="input <?= isset($erros['data_abertura']) ? 'is-invalid' : '' ?>" id="data_abertura" type="date" name="data_abertura"
                   value="<?= e($valor('data_abertura', $vaga['data_abertura'] ?? date('Y-m-d'))) ?>" required>
            <?php if (isset($erros['data_abertura'])): ?><span class="error-text"><?= icon('alert', 14) ?> <?= e($erros['data_abertura']) ?></span><?php endif; ?>
        </div>

        <div class="form-field">
            <label class="label" for="data_fechamento">Data de fechamento</label>
            <input class="input" id="data_fechamento" type="date" name="data_fechamento"
                   value="<?= e($valor('data_fechamento', $vaga['data_fechamento'] ?? '')) ?>">
        </div>

        <div class="form-field">
            <label class="label" for="status">Status</label>
            <select class="select" id="status" name="status">
                <?php foreach (['aberta' => 'Aberta', 'em_analise' => 'Em análise', 'fechada' => 'Fechada', 'cancelada' => 'Cancelada'] as $k => $v): ?>
                    <option value="<?= e($k) ?>" <?= (string) $valor('status', $vaga['status'] ?? 'aberta') === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-field form-field--full">
            <label class="label" for="descricao">Descrição da função</label>
            <textarea class="textarea" id="descricao" name="descricao" placeholder="Atividades e responsabilidades…"><?= e($valor('descricao', $vaga['descricao'] ?? '')) ?></textarea>
        </div>

        <div class="form-field form-field--full">
            <label class="label" for="requisitos">Requisitos</label>
            <textarea class="textarea" id="requisitos" name="requisitos" placeholder="Formação, experiência e conhecimentos exigidos…"><?= e($valor('requisitos', $vaga['requisitos'] ?? '')) ?></textarea>
        </div>
    </div>

    <div class="form-actions">
        <a class="btn btn--secondary" href="<?= url('vagas') ?>">Cancelar</a>
        <button class="btn btn--primary" type="submit"><?= icon('check', 16) ?> <?= $vaga ? 'Salvar alterações' : 'Criar vaga' ?></button>
    </div>
</form>

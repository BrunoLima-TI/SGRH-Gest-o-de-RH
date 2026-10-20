<?php
use App\Core\Session;

$erros = Session::get('errors', []);
$valor = fn(string $c, $p = '') => old($c, $p);
?>

<div class="page-head">
    <div class="page-head__text">
        <h1>Novo documento</h1>
        <p>Registre documentos funcionais e defina a data de validade para controle automático.</p>
    </div>
    <div class="page-head__actions">
        <a class="btn btn--secondary" href="<?= url('documentos') ?>"><?= icon('chevron-left', 16) ?> Voltar</a>
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

    <div class="form-grid">
        <div class="form-field form-field--full">
            <label class="label" for="nome">Nome do documento <span class="label__req">*</span></label>
            <input class="input <?= isset($erros['nome']) ? 'is-invalid' : '' ?>" id="nome" name="nome"
                   value="<?= e($valor('nome')) ?>" required placeholder="Ex.: Contrato de trabalho — Ana Souza">
            <?php if (isset($erros['nome'])): ?><span class="error-text"><?= icon('alert', 14) ?> <?= e($erros['nome']) ?></span><?php endif; ?>
        </div>

        <div class="form-field">
            <label class="label" for="colaborador_id">Colaborador</label>
            <select class="select" id="colaborador_id" name="colaborador_id">
                <option value="">Não vinculado</option>
                <?php foreach ($colaboradores as $c): ?>
                    <option value="<?= (int) $c['id'] ?>" <?= (string) $valor('colaborador_id') === (string) $c['id'] ? 'selected' : '' ?>>
                        <?= e($c['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-field">
            <label class="label" for="tipo">Tipo</label>
            <select class="select" id="tipo" name="tipo">
                <?php foreach (['Contrato' => 'Contrato', 'CTPS' => 'CTPS', 'RG' => 'RG', 'CPF' => 'CPF', 'Diploma' => 'Diploma', 'Certificado' => 'Certificado', 'Laudo' => 'Laudo', 'outro' => 'Outro'] as $k => $v): ?>
                    <option value="<?= e($k) ?>" <?= (string) $valor('tipo') === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-field">
            <label class="label" for="data_emissao">Data de emissão</label>
            <input class="input" id="data_emissao" type="date" name="data_emissao" value="<?= e($valor('data_emissao')) ?>">
        </div>

        <div class="form-field">
            <label class="label" for="data_validade">Data de validade</label>
            <input class="input" id="data_validade" type="date" name="data_validade" value="<?= e($valor('data_validade')) ?>">
            <span class="help-text">O sistema sinaliza documentos que vencem em até 30 dias.</span>
        </div>

        <div class="form-field form-field--full">
            <label class="label" for="observacoes">Observações</label>
            <input class="input" id="observacoes" name="observacoes" value="<?= e($valor('observacoes')) ?>">
        </div>
    </div>

    <div class="form-actions">
        <a class="btn btn--secondary" href="<?= url('documentos') ?>">Cancelar</a>
        <button class="btn btn--primary" type="submit"><?= icon('check', 16) ?> Registrar documento</button>
    </div>
</form>

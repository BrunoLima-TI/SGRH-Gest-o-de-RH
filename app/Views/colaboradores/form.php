<?php
use App\Core\Session;

$erros = Session::get('errors', []);
$isEdicao = !empty($colaborador);
$valor = function (string $campo, $padrao = '') use ($colaborador) {
    $v = old($campo, null);
    if ($v !== null && $v !== '') {
        return $v;
    }
    return $colaborador[$campo] ?? $padrao;
};
$classe = function (string $campo) use ($erros) {
    return isset($erros[$campo]) ? 'is-invalid' : '';
};
?>

<div class="page-head">
    <div class="page-head__text">
        <h1><?= e($titulo) ?></h1>
        <p>Preencha os dados cadastrais, funcionais e de remuneração.</p>
    </div>
    <div class="page-head__actions">
        <a class="btn btn--secondary" href="<?= url('colaboradores') ?>"><?= icon('chevron-left', 16) ?> Voltar</a>
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
    <?php if ($isEdicao): ?>
        <input type="hidden" name="id" value="<?= (int) $colaborador['id'] ?>">
    <?php endif; ?>

    <h3 class="card__title mb-4">Dados pessoais</h3>
    <div class="form-grid mb-6">
        <div class="form-field form-field--full">
            <label class="label" for="nome">Nome completo <span class="label__req">*</span></label>
            <input class="input <?= $classe('nome') ?>" id="nome" name="nome" value="<?= e($valor('nome')) ?>" required>
            <?php if (isset($erros['nome'])): ?><span class="error-text"><?= icon('alert', 14) ?> <?= e($erros['nome']) ?></span><?php endif; ?>
        </div>

        <div class="form-field">
            <label class="label" for="email">E-mail</label>
            <input class="input <?= $classe('email') ?>" id="email" type="email" name="email" value="<?= e($valor('email')) ?>">
            <?php if (isset($erros['email'])): ?><span class="error-text"><?= icon('alert', 14) ?> <?= e($erros['email']) ?></span><?php endif; ?>
        </div>

        <div class="form-field">
            <label class="label" for="cpf">CPF</label>
            <input class="input <?= $classe('cpf') ?>" id="cpf" name="cpf" value="<?= e($valor('cpf')) ?>"
                   data-mask="cpf" placeholder="000.000.000-00" maxlength="14">
            <?php if (isset($erros['cpf'])): ?><span class="error-text"><?= icon('alert', 14) ?> <?= e($erros['cpf']) ?></span><?php endif; ?>
        </div>

        <div class="form-field">
            <label class="label" for="telefone">Telefone</label>
            <input class="input" id="telefone" name="telefone" value="<?= e($valor('telefone')) ?>"
                   data-mask="telefone" placeholder="(00) 00000-0000">
        </div>

        <div class="form-field">
            <label class="label" for="data_nascimento">Data de nascimento</label>
            <input class="input <?= $classe('data_nascimento') ?>" id="data_nascimento" type="date" name="data_nascimento"
                   value="<?= e($valor('data_nascimento')) ?>" max="<?= date('Y-m-d') ?>">
            <?php if (isset($erros['data_nascimento'])): ?><span class="error-text"><?= icon('alert', 14) ?> <?= e($erros['data_nascimento']) ?></span><?php endif; ?>
        </div>

        <div class="form-field">
            <label class="label" for="estado_civil">Estado civil</label>
            <select class="select" id="estado_civil" name="estado_civil">
                <?php foreach (['' => 'Não informado', 'Solteiro(a)' => 'Solteiro(a)', 'Casado(a)' => 'Casado(a)', 'União estável' => 'União estável', 'Divorciado(a)' => 'Divorciado(a)', 'Viúvo(a)' => 'Viúvo(a)'] as $k => $v): ?>
                    <option value="<?= e($k) ?>" <?= (string) $valor('estado_civil') === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-field">
            <label class="label" for="endereco">Endereço</label>
            <input class="input" id="endereco" name="endereco" value="<?= e($valor('endereco')) ?>">
        </div>
    </div>

    <div class="divider"></div>

    <h3 class="card__title mb-4">Dados funcionais</h3>
    <div class="form-grid mb-6">
        <div class="form-field">
            <label class="label" for="departamento_id">Departamento</label>
            <select class="select" id="departamento_id" name="departamento_id">
                <option value="">Selecione…</option>
                <?php foreach ($departamentos as $d): ?>
                    <option value="<?= (int) $d['id'] ?>" <?= (string) $valor('departamento_id') === (string) $d['id'] ? 'selected' : '' ?>>
                        <?= e($d['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-field">
            <label class="label" for="cargo_id">Cargo</label>
            <select class="select" id="cargo_id" name="cargo_id">
                <option value="">Selecione…</option>
                <?php foreach ($cargos as $c): ?>
                    <option value="<?= (int) $c['id'] ?>" data-departamento="<?= (int) $c['departamento_id'] ?>"
                            <?= (string) $valor('cargo_id') === (string) $c['id'] ? 'selected' : '' ?>>
                        <?= e($c['titulo']) ?><?= $c['departamento'] ? ' — ' . e($c['departamento']) : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-field">
            <label class="label" for="tipo_contrato">Tipo de contrato</label>
            <select class="select" id="tipo_contrato" name="tipo_contrato">
                <?php foreach (['clt' => 'CLT', 'pj' => 'Pessoa jurídica', 'estagio' => 'Estágio', 'temporario' => 'Temporário'] as $k => $v): ?>
                    <option value="<?= e($k) ?>" <?= (string) $valor('tipo_contrato') === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-field">
            <label class="label" for="status">Status</label>
            <select class="select" id="status" name="status">
                <?php foreach (['ativo' => 'Ativo', 'ferias' => 'Em férias', 'afastado' => 'Afastado', 'inativo' => 'Inativo'] as $k => $v): ?>
                    <option value="<?= e($k) ?>" <?= (string) $valor('status', 'ativo') === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-field">
            <label class="label" for="data_admissao">Data de admissão</label>
            <input class="input <?= $classe('data_admissao') ?>" id="data_admissao" type="date" name="data_admissao"
                   value="<?= e($valor('data_admissao')) ?>">
            <?php if (isset($erros['data_admissao'])): ?><span class="error-text"><?= icon('alert', 14) ?> <?= e($erros['data_admissao']) ?></span><?php endif; ?>
        </div>

        <div class="form-field">
            <label class="label" for="salario">Remuneração (R$)</label>
            <input class="input <?= $classe('salario') ?>" id="salario" name="salario" data-mask="moeda"
                   value="<?= e($valor('salario') ? number_format((float) $valor('salario'), 2, ',', '.') : '0,00') ?>">
            <?php if (isset($erros['salario'])): ?><span class="error-text"><?= icon('alert', 14) ?> <?= e($erros['salario']) ?></span><?php endif; ?>
        </div>

        <div class="form-field">
            <label class="label" for="nivel_educacao">Nível de escolaridade</label>
            <select class="select" id="nivel_educacao" name="nivel_educacao">
                <?php foreach (['' => 'Não informado', 'Ensino Fundamental' => 'Ensino Fundamental', 'Ensino Médio' => 'Ensino Médio', 'Ensino Superior' => 'Ensino Superior', 'Pós-graduação' => 'Pós-graduação', 'Mestrado' => 'Mestrado', 'Doutorado' => 'Doutorado'] as $k => $v): ?>
                    <option value="<?= e($k) ?>" <?= (string) $valor('nivel_educacao') === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-field form-field--full">
            <label class="label" for="observacoes">Observações</label>
            <textarea class="textarea" id="observacoes" name="observacoes" placeholder="Informações complementares sobre o colaborador…"><?= e($valor('observacoes')) ?></textarea>
        </div>
    </div>

    <div class="form-actions">
        <a class="btn btn--secondary" href="<?= url('colaboradores') ?>">Cancelar</a>
        <button class="btn btn--primary" type="submit">
            <?= icon('check', 16) ?> <?= $isEdicao ? 'Salvar alterações' : 'Cadastrar colaborador' ?>
        </button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var dep = document.getElementById('departamento_id');
    var cargo = document.getElementById('cargo_id');
    if (!dep || !cargo) return;
    function filtrar() {
        var id = dep.value;
        Array.prototype.forEach.call(cargo.options, function (opt) {
            if (!opt.value) return;
            var pertence = !id || opt.dataset.departamento === id;
            opt.hidden = !pertence;
            opt.disabled = !pertence;
        });
    }
    dep.addEventListener('change', filtrar);
    filtrar();
});
</script>

<?php
use App\Core\Session;

$erros = Session::get('errors', []);
$valor = fn(string $c, $p = '') => old($c, $p);
?>

<div class="page-head">
    <div class="page-head__text">
        <h1><?= e($titulo) ?></h1>
        <p>Defina os dados de acesso e o perfil de permissões.</p>
    </div>
    <div class="page-head__actions">
        <a class="btn btn--secondary" href="<?= url('usuarios') ?>"><?= icon('chevron-left', 16) ?> Voltar</a>
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
    <?php if ($usuario): ?><input type="hidden" name="id" value="<?= (int) $usuario['id'] ?>"><?php endif; ?>

    <div class="form-grid">
        <div class="form-field">
            <label class="label" for="nome">Nome <span class="label__req">*</span></label>
            <input class="input <?= isset($erros['nome']) ? 'is-invalid' : '' ?>" id="nome" name="nome"
                   value="<?= e($valor('nome', $usuario['nome'] ?? '')) ?>" required>
            <?php if (isset($erros['nome'])): ?><span class="error-text"><?= icon('alert', 14) ?> <?= e($erros['nome']) ?></span><?php endif; ?>
        </div>

        <div class="form-field">
            <label class="label" for="email">E-mail <span class="label__req">*</span></label>
            <input class="input <?= isset($erros['email']) ? 'is-invalid' : '' ?>" id="email" type="email" name="email"
                   value="<?= e($valor('email', $usuario['email'] ?? '')) ?>" required>
            <?php if (isset($erros['email'])): ?><span class="error-text"><?= icon('alert', 14) ?> <?= e($erros['email']) ?></span><?php endif; ?>
        </div>

        <div class="form-field">
            <label class="label" for="perfil">Perfil de acesso <span class="label__req">*</span></label>
            <select class="select" id="perfil" name="perfil" required>
                <?php foreach ($perfis as $k => $v): ?>
                    <option value="<?= e($k) ?>" <?= (string) $valor('perfil', $usuario['perfil'] ?? 'rh') === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                <?php endforeach; ?>
            </select>
            <span class="help-text">Administrador: acesso total. RH: gestão de pessoas. Gestor: time e registros.</span>
        </div>

        <div class="form-field">
            <label class="label" for="cargo">Cargo</label>
            <input class="input" id="cargo" name="cargo" value="<?= e($valor('cargo', $usuario['cargo'] ?? '')) ?>">
        </div>

        <div class="form-field">
            <label class="label" for="status">Status</label>
            <select class="select" id="status" name="status">
                <?php foreach (['ativo' => 'Ativo', 'inativo' => 'Inativo'] as $k => $v): ?>
                    <option value="<?= e($k) ?>" <?= (string) $valor('status', $usuario['status'] ?? 'ativo') === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-field">
            <label class="label" for="senha"><?= $usuario ? 'Nova senha (opcional)' : 'Senha' ?> <?= $usuario ? '' : '<span class="label__req">*</span>' ?></label>
            <input class="input <?= isset($erros['senha']) ? 'is-invalid' : '' ?>" id="senha" type="password" name="senha"
                   <?= $usuario ? '' : 'required' ?> autocomplete="new-password" placeholder="Mínimo de 6 caracteres">
            <?php if (isset($erros['senha'])): ?><span class="error-text"><?= icon('alert', 14) ?> <?= e($erros['senha']) ?></span><?php endif; ?>
        </div>

        <div class="form-field">
            <label class="label" for="confirmar_senha">Confirmar senha</label>
            <input class="input" id="confirmar_senha" type="password" name="confirmar_senha" autocomplete="new-password">
            <?php if (isset($erros['confirmar_senha'])): ?><span class="error-text"><?= icon('alert', 14) ?> <?= e($erros['confirmar_senha']) ?></span><?php endif; ?>
        </div>
    </div>

    <div class="form-actions">
        <a class="btn btn--secondary" href="<?= url('usuarios') ?>">Cancelar</a>
        <button class="btn btn--primary" type="submit"><?= icon('check', 16) ?> Salvar usuário</button>
    </div>
</form>

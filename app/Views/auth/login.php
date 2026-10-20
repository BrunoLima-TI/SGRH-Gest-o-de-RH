<?php use App\Core\Session; ?>
<div class="auth-wrap">
    <section class="auth-brand">
        <div class="auth-brand__logo">
            <span>S</span>
            <strong style="font-size:18px">SGRH</strong>
        </div>
        <div>
            <h1>Gestão de pessoas com clareza e dados</h1>
            <p style="margin-top:12px">
                Um sistema integrado de gestão de recursos humanos: cadastro, frequência,
                férias, recrutamento, capacitação e avaliação de desempenho em um só lugar.
            </p>
        </div>
        <div class="auth-features">
            <div class="auth-feature">
                <span class="auth-feature__icon"><?= icon('users', 18) ?></span>
                Cadastro e acompanhamento de colaboradores
            </div>
            <div class="auth-feature">
                <span class="auth-feature__icon"><?= icon('chart', 18) ?></span>
                Indicadores gerenciais em tempo real
            </div>
            <div class="auth-feature">
                <span class="auth-feature__icon"><?= icon('shield', 18) ?></span>
                Controle de acesso e conformidade (LGPD)
            </div>
        </div>
    </section>

    <section class="auth-form-side">
        <div class="auth-form">
            <h2>Acessar o sistema</h2>
            <p class="auth-form__subtitle">Informe suas credenciais para entrar no painel.</p>

            <?php require APP_PATH . '/Views/partials/flash.php'; ?>

            <?php $erros = Session::get('errors', []); ?>

            <form method="post" action="<?= url('login') ?>" data-validate novalidate>
                <?= App\Core\Csrf::campo() ?>

                <div class="form-field mb-4">
                    <label class="label" for="email">E-mail <span class="label__req">*</span></label>
                    <input class="input <?= isset($erros['email']) ? 'is-invalid' : '' ?>" type="email" id="email" name="email"
                           value="<?= e(old('email', $_COOKIE['sgrh_email'] ?? '')) ?>"
                           placeholder="seu.email@empresa.com.br" required autocomplete="username">
                    <?php if (isset($erros['email'])): ?>
                        <span class="error-text"><?= icon('alert', 14) ?> <?= e($erros['email']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-field mb-4">
                    <label class="label" for="senha">Senha <span class="label__req">*</span></label>
                    <div class="password-wrap">
                        <input class="input <?= isset($erros['senha']) ? 'is-invalid' : '' ?>" type="password" id="senha" name="senha"
                               placeholder="••••••••" required autocomplete="current-password">
                        <button class="password-toggle" type="button" data-password-toggle aria-label="Mostrar senha"><?= icon('eye', 16) ?></button>
                    </div>
                    <?php if (isset($erros['senha'])): ?>
                        <span class="error-text"><?= icon('alert', 14) ?> <?= e($erros['senha']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="flex between flex-center mb-4" style="gap:12px">
                    <label class="flex flex-center gap-2" style="font-size:13px;cursor:pointer">
                        <input class="check" type="checkbox" name="lembrar" value="1" <?= isset($_COOKIE['sgrh_email']) ? 'checked' : '' ?>>
                        Lembrar-me
                    </label>
                    <a href="#" style="font-size:13px">Esqueci minha senha</a>
                </div>

                <button class="btn btn--primary btn--lg btn--block" type="submit">
                    <?= icon('lock', 18) ?> Entrar
                </button>
            </form>

            <div class="auth-divider">ou entre com</div>

            <div class="stack stack--sm">
                <button class="btn btn--secondary btn--block" type="button" data-toast="Login corporativo disponível na próxima versão.">
                    <?= icon('shield', 16) ?> Entrar com SSO corporativo
                </button>
            </div>

            <div class="auth-demo">
                <strong>Credenciais de demonstração</strong><br>
                Administrador: <code>admin@sgrh.local</code> / <code>sgrh@2026</code><br>
                RH: <code>rh@sgrh.local</code> · Gestor: <code>gestor@sgrh.local</code> / <code>sgrh@2026</code>
            </div>
        </div>
    </section>
</div>

<script>
document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-password-toggle]');
    if (!btn) return;
    var input = document.getElementById('senha');
    var mostrando = input.type === 'text';
    input.type = mostrando ? 'password' : 'text';
    btn.setAttribute('aria-label', mostrando ? 'Mostrar senha' : 'Ocultar senha');
});
</script>

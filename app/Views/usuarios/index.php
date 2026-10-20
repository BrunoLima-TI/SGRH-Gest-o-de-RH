<div class="page-head">
    <div class="page-head__text">
        <h1>Usuários do sistema</h1>
        <p>Controle de acesso e perfis de permissão.</p>
    </div>
    <div class="page-head__actions">
        <a class="btn btn--primary" href="<?= url('usuarios/novo') ?>"><?= icon('plus', 16) ?> Novo usuário</a>
    </div>
</div>

<div class="card card--flush">
    <div class="table-wrap" style="border:0;border-radius:0">
        <table class="table">
            <caption class="sr-only">Usuários cadastrados</caption>
            <thead>
                <tr>
                    <th>Usuário</th>
                    <th>E-mail</th>
                    <th>Perfil</th>
                    <th>Cargo</th>
                    <th>Último acesso</th>
                    <th>Status</th>
                    <th class="table__actions">Ações</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($usuarios as $u): ?>
                <tr>
                    <td data-label="Usuário">
                        <div class="user-cell">
                            <span class="avatar avatar--sm"><?= e(iniciais($u['nome'])) ?></span>
                            <span class="user-cell__name"><?= e($u['nome']) ?></span>
                        </div>
                    </td>
                    <td data-label="E-mail"><span class="mono"><?= e($u['email']) ?></span></td>
                    <td data-label="Perfil"><span class="badge badge--<?= $u['perfil'] === 'admin' ? 'violet' : 'info' ?>"><?= e($perfis[$u['perfil']] ?? $u['perfil']) ?></span></td>
                    <td data-label="Cargo"><?= e($u['cargo'] ?? '—') ?></td>
                    <td data-label="Último acesso"><?= e($u['ultimo_acesso'] ? formatar_data($u['ultimo_acesso'], 'd/m/Y H:i') : 'nunca') ?></td>
                    <td data-label="Status"><span class="badge badge--<?= e(classe_status($u['status'])) ?>"><?= e(rotulo_status($u['status'])) ?></span></td>
                    <td class="table__actions">
                        <div class="flex gap-2" style="justify-content:flex-end">
                            <a class="icon-btn icon-btn--sm" href="<?= url('usuarios/' . (int) $u['id'] . '/editar') ?>" aria-label="Editar"><?= icon('edit', 16) ?></a>
                            <form method="post" action="<?= url('usuarios/excluir') ?>" data-confirm="Excluir este usuário?">
                                <?= App\Core\Csrf::campo() ?>
                                <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                                <button class="icon-btn icon-btn--sm" type="submit" aria-label="Excluir" style="color:var(--danger)"><?= icon('trash', 16) ?></button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

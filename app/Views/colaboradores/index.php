<div class="page-head">
    <div class="page-head__text">
        <h1>Colaboradores</h1>
        <p>Cadastro, organização e histórico da força de trabalho.</p>
    </div>
    <div class="page-head__actions">
        <a class="btn btn--secondary" href="<?= url('relatorios/exportar') ?>?tipo=colaboradores">
            <?= icon('download', 16) ?> Exportar CSV
        </a>
        <?php if (App\Core\Auth::ehRh()): ?>
            <a class="btn btn--primary" href="<?= url('colaboradores/novo') ?>"><?= icon('plus', 16) ?> Novo colaborador</a>
        <?php endif; ?>
    </div>
</div>

<form class="filters" method="get" action="<?= url('colaboradores') ?>">
    <div class="filters__search">
        <span class="filters__search-icon"><?= icon('search', 16) ?></span>
        <input class="input" type="search" name="busca" placeholder="Buscar por nome, e-mail ou CPF"
               value="<?= e($filtros['busca']) ?>">
    </div>
    <select class="select" name="status" aria-label="Filtrar por status">
        <option value="">Todos os status</option>
        <?php foreach (['ativo' => 'Ativo', 'ferias' => 'Férias', 'afastado' => 'Afastado', 'inativo' => 'Inativo'] as $k => $v): ?>
            <option value="<?= e($k) ?>" <?= $filtros['status'] === $k ? 'selected' : '' ?>><?= e($v) ?></option>
        <?php endforeach; ?>
    </select>
    <select class="select" name="departamento_id" aria-label="Filtrar por departamento">
        <option value="">Todos os departamentos</option>
        <?php foreach ($departamentos as $d): ?>
            <option value="<?= (int) $d['id'] ?>" <?= (string) $filtros['departamento_id'] === (string) $d['id'] ? 'selected' : '' ?>>
                <?= e($d['nome']) ?> (<?= (int) $d['total_colaboradores'] ?>)
            </option>
        <?php endforeach; ?>
    </select>
    <select class="select" name="tipo_contrato" aria-label="Filtrar por tipo de contrato">
        <option value="">Todos os contratos</option>
        <?php foreach (['clt' => 'CLT', 'pj' => 'PJ', 'estagio' => 'Estágio', 'temporario' => 'Temporário'] as $k => $v): ?>
            <option value="<?= e($k) ?>" <?= $filtros['tipo_contrato'] === $k ? 'selected' : '' ?>><?= e($v) ?></option>
        <?php endforeach; ?>
    </select>
    <button class="btn btn--secondary" type="submit"><?= icon('filter', 16) ?> Filtrar</button>
    <a class="btn btn--ghost" href="<?= url('colaboradores') ?>">Limpar</a>
</form>

<div class="card card--flush">
    <div class="table-wrap" style="border:0;border-radius:0">
        <table class="table">
            <caption class="sr-only">Lista de colaboradores</caption>
            <thead>
                <tr>
                    <th scope="col">Colaborador</th>
                    <th scope="col">Departamento</th>
                    <th scope="col">Cargo</th>
                    <th scope="col">Contrato</th>
                    <th scope="col">Admissão</th>
                    <th scope="col">Status</th>
                    <th scope="col" class="table__actions">Ações</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$colaboradores): ?>
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <div class="empty-state__icon"><?= icon('users', 40) ?></div>
                            <h3 class="empty-state__title">Nenhum colaborador encontrado</h3>
                            <p class="empty-state__text">Ajuste os filtros ou cadastre o primeiro colaborador da organização.</p>
                            <?php if (App\Core\Auth::ehRh()): ?>
                                <a class="btn btn--primary mt-4" href="<?= url('colaboradores/novo') ?>"><?= icon('plus', 16) ?> Cadastrar colaborador</a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>

            <?php foreach ($colaboradores as $c): ?>
                <tr>
                    <td data-label="Colaborador">
                        <div class="user-cell">
                            <span class="avatar"><?= e(iniciais($c['nome'])) ?></span>
                            <div>
                                <div class="user-cell__name"><?= e($c['nome']) ?></div>
                                <div class="user-cell__meta"><?= e($c['email'] ?? 'sem e-mail') ?></div>
                            </div>
                        </div>
                    </td>
                    <td data-label="Departamento"><?= e($c['departamento'] ?? '—') ?></td>
                    <td data-label="Cargo"><?= e($c['cargo'] ?? '—') ?></td>
                    <td data-label="Contrato"><?= e(strtoupper($c['tipo_contrato'])) ?></td>
                    <td data-label="Admissão" class="tnum"><?= e(formatar_data($c['data_admissao'])) ?></td>
                    <td data-label="Status">
                        <span class="badge badge--<?= e(classe_status($c['status'])) ?>"><?= e(rotulo_status($c['status'])) ?></span>
                    </td>
                    <td class="table__actions">
                        <div class="flex gap-2" style="justify-content:flex-end">
                            <a class="icon-btn icon-btn--sm" href="<?= url('colaboradores/' . (int) $c['id']) ?>" aria-label="Ver detalhes"><?= icon('eye', 16) ?></a>
                            <?php if (App\Core\Auth::ehRh()): ?>
                                <a class="icon-btn icon-btn--sm" href="<?= url('colaboradores/' . (int) $c['id'] . '/editar') ?>" aria-label="Editar"><?= icon('edit', 16) ?></a>
                                <form method="post" action="<?= url('colaboradores/excluir') ?>" style="display:inline"
                                      data-confirm="Remover <?= e($c['nome']) ?> e todos os registros vinculados?">
                                    <?= App\Core\Csrf::campo() ?>
                                    <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                                    <button class="icon-btn icon-btn--sm" type="submit" aria-label="Excluir" style="color:var(--danger)"><?= icon('trash', 16) ?></button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pagina['total_paginas'] > 1): ?>
        <div class="pagination">
            <span class="pagination__info">
                Mostrando <?= $pagina['de'] ?>–<?= $pagina['ate'] ?> de <?= $pagina['total'] ?> colaboradores
            </span>
            <div class="pagination__pages">
                <a class="pagination__btn <?= $pagina['tem_anterior'] ? '' : 'is-disabled' ?>"
                   href="<?= querystring(['pagina' => max(1, $pagina['pagina'] - 1)]) ?>" aria-label="Anterior"><?= icon('chevron-left', 16) ?></a>
                <?php foreach ($pagina['links'] as $link): ?>
                    <a class="pagination__btn <?= $link['atual'] ? 'is-current' : '' ?>" href="<?= querystring(['pagina' => $link['numero']]) ?>"><?= $link['numero'] ?></a>
                <?php endforeach; ?>
                <a class="pagination__btn <?= $pagina['tem_proxima'] ? '' : 'is-disabled' ?>"
                   href="<?= querystring(['pagina' => min($pagina['total_paginas'], $pagina['pagina'] + 1)]) ?>" aria-label="Próxima"><?= icon('chevron-right', 16) ?></a>
            </div>
        </div>
    <?php endif; ?>
</div>

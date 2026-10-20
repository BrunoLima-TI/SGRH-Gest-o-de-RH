<div class="page-head">
    <div class="page-head__text">
        <h1>Departamentos e cargos</h1>
        <p>Estruture organizacional do quadro de pessoal.</p>
    </div>
</div>

<div class="grid grid--2">
    <div class="card">
        <div class="chart-card__head">
            <div>
                <h3 class="card__title">Departamentos</h3>
                <p class="card__subtitle"><?= count($departamentos) ?> área(s) cadastrada(s)</p>
            </div>
        </div>

        <form method="post" action="<?= url('estrutura/departamento') ?>" class="mb-4" data-validate novalidate>
            <?= App\Core\Csrf::campo() ?>
            <div class="form-grid" style="grid-template-columns:1fr 1fr auto">
                <div class="form-field">
                    <label class="label" for="dep_nome">Nome</label>
                    <input class="input" id="dep_nome" name="nome" required placeholder="Ex.: Operações">
                </div>
                <div class="form-field">
                    <label class="label" for="dep_desc">Descrição</label>
                    <input class="input" id="dep_desc" name="descricao" placeholder="Opcional">
                </div>
                <div class="form-field" style="justify-content:flex-end">
                    <button class="btn btn--primary" type="submit"><?= icon('plus', 16) ?> Criar</button>
                </div>
            </div>
        </form>

        <div class="table-wrap">
            <table class="table table--compact">
                <thead>
                    <tr><th>Departamento</th><th>Colaboradores</th><th class="table__actions">Ações</th></tr>
                </thead>
                <tbody>
                <?php foreach ($departamentos as $d): ?>
                    <tr>
                        <td data-label="Departamento">
                            <strong><?= e($d['nome']) ?></strong>
                            <?php if ($d['descricao']): ?><div class="user-cell__meta"><?= e($d['descricao']) ?></div><?php endif; ?>
                        </td>
                        <td data-label="Colaboradores" class="tnum"><?= (int) $d['total_colaboradores'] ?></td>
                        <td class="table__actions">
                            <form method="post" action="<?= url('estrutura/excluir') ?>" data-confirm="Excluir este departamento?">
                                <?= App\Core\Csrf::campo() ?>
                                <input type="hidden" name="tabela" value="departamentos">
                                <input type="hidden" name="id" value="<?= (int) $d['id'] ?>">
                                <button class="icon-btn icon-btn--sm" type="submit" aria-label="Excluir" style="color:var(--danger)"><?= icon('trash', 16) ?></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="chart-card__head">
            <div>
                <h3 class="card__title">Cargos</h3>
                <p class="card__subtitle"><?= count($cargos) ?> cargo(s) cadastrado(s)</p>
            </div>
        </div>

        <form method="post" action="<?= url('estrutura/cargo') ?>" class="mb-4" data-validate novalidate>
            <?= App\Core\Csrf::campo() ?>
            <div class="form-grid" style="grid-template-columns:1fr 1fr auto">
                <div class="form-field">
                    <label class="label" for="cargo_titulo">Título</label>
                    <input class="input" id="cargo_titulo" name="titulo" required placeholder="Ex.: Analista de RH">
                </div>
                <div class="form-field">
                    <label class="label" for="cargo_dep">Departamento</label>
                    <select class="select" id="cargo_dep" name="departamento_id">
                        <option value="">Sem departamento</option>
                        <?php foreach ($departamentos as $d): ?>
                            <option value="<?= (int) $d['id'] ?>"><?= e($d['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-field" style="justify-content:flex-end">
                    <button class="btn btn--primary" type="submit"><?= icon('plus', 16) ?> Criar</button>
                </div>
            </div>
        </form>

        <div class="table-wrap" style="max-height:420px;overflow-y:auto">
            <table class="table table--compact">
                <thead>
                    <tr><th>Cargo</th><th>Departamento</th><th class="table__actions">Ações</th></tr>
                </thead>
                <tbody>
                <?php foreach ($cargos as $c): ?>
                    <tr>
                        <td data-label="Cargo"><?= e($c['titulo']) ?></td>
                        <td data-label="Departamento"><?= e($c['departamento'] ?? '—') ?></td>
                        <td class="table__actions">
                            <form method="post" action="<?= url('estrutura/excluir') ?>" data-confirm="Excluir este cargo?">
                                <?= App\Core\Csrf::campo() ?>
                                <input type="hidden" name="tabela" value="cargos">
                                <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                                <button class="icon-btn icon-btn--sm" type="submit" aria-label="Excluir" style="color:var(--danger)"><?= icon('trash', 16) ?></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

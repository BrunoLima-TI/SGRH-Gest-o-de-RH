<div class="page-head">
    <div class="page-head__text">
        <h1><?= e($vaga['titulo']) ?></h1>
        <p><?= e($vaga['departamento'] ?? 'Sem departamento') ?> · <?= e(strtoupper($vaga['tipo_contrato'])) ?> · aberta em <?= e(formatar_data($vaga['data_abertura'])) ?></p>
    </div>
    <div class="page-head__actions">
        <a class="btn btn--secondary" href="<?= url('vagas') ?>"><?= icon('chevron-left', 16) ?> Voltar</a>
        <?php if (App\Core\Auth::ehRh()): ?>
            <a class="btn btn--primary" href="<?= url('vagas/' . (int) $vaga['id'] . '/editar') ?>"><?= icon('edit', 16) ?> Editar vaga</a>
        <?php endif; ?>
    </div>
</div>

<div class="grid grid--2-1 mb-6">
    <div class="card card--flush">
        <div class="card__head" style="padding:20px 20px 0">
            <div>
                <h3 class="card__title">Candidatos</h3>
                <p class="card__subtitle"><?= count($candidatos) ?> pessoa(s) no processo</p>
            </div>
        </div>
        <div class="table-wrap" style="border:0;border-radius:0">
            <table class="table">
                <thead>
                    <tr>
                        <th>Candidato</th>
                        <th>Contato</th>
                        <th>Etapa</th>
                        <th>Candidatura</th>
                        <th class="table__actions">Mover</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$candidatos): ?>
                    <tr><td colspan="5" class="table__empty">Nenhum candidato inscrito.</td></tr>
                <?php endif; ?>
                <?php foreach ($candidatos as $c): ?>
                    <tr>
                        <td data-label="Candidato">
                            <div class="user-cell">
                                <span class="avatar avatar--sm"><?= e(iniciais($c['nome'])) ?></span>
                                <span class="user-cell__name"><?= e($c['nome']) ?></span>
                            </div>
                        </td>
                        <td data-label="Contato">
                            <div style="font-size:12px"><?= e($c['email'] ?? '—') ?></div>
                            <div class="text-muted" style="font-size:11px"><?= e($c['telefone'] ?? '') ?></div>
                        </td>
                        <td data-label="Etapa"><span class="badge badge--<?= e(classe_status($c['etapa'])) ?>"><?= e(rotulo_status($c['etapa'])) ?></span></td>
                        <td data-label="Candidatura"><?= e(formatar_data($c['data_candidatura'])) ?></td>
                        <td class="table__actions">
                            <?php if (App\Core\Auth::ehRh()): ?>
                                <form method="post" action="<?= url('vagas/etapa') ?>" class="flex gap-2" style="justify-content:flex-end">
                                    <?= App\Core\Csrf::campo() ?>
                                    <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                                    <select class="select" name="etapa" style="height:32px;font-size:12px" aria-label="Mover etapa">
                                        <?php foreach (['candidatura' => 'Candidatura', 'analise' => 'Em análise', 'entrevista' => 'Entrevista', 'ofertado' => 'Ofertado', 'contratado' => 'Contratado', 'descartado' => 'Descartado'] as $k => $v): ?>
                                            <option value="<?= e($k) ?>" <?= $c['etapa'] === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button class="btn btn--sm btn--soft" type="submit">Mover</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="stack">
        <div class="card">
            <h3 class="card__title mb-3">Descrição</h3>
            <p class="text-muted" style="font-size:13px;line-height:21px"><?= nl2br(e($vaga['descricao'] ?: 'Não informado.')) ?></p>
            <div class="divider"></div>
            <h3 class="card__title mb-3">Requisitos</h3>
            <p class="text-muted" style="font-size:13px;line-height:21px"><?= nl2br(e($vaga['requisitos'] ?: 'Não informado.')) ?></p>
        </div>

        <?php if (App\Core\Auth::ehRh()): ?>
            <div class="card">
                <h3 class="card__title mb-4">Adicionar candidato</h3>
                <form method="post" action="<?= url('vagas/candidato') ?>">
                    <?= App\Core\Csrf::campo() ?>
                    <input type="hidden" name="vaga_id" value="<?= (int) $vaga['id'] ?>">
                    <div class="stack stack--sm">
                        <div class="form-field">
                            <label class="label" for="cand_nome">Nome</label>
                            <input class="input" id="cand_nome" name="nome" required>
                        </div>
                        <div class="form-field">
                            <label class="label" for="cand_email">E-mail</label>
                            <input class="input" id="cand_email" type="email" name="email">
                        </div>
                        <div class="form-field">
                            <label class="label" for="cand_tel">Telefone</label>
                            <input class="input" id="cand_tel" name="telefone" data-mask="telefone">
                        </div>
                        <div class="form-field">
                            <label class="label" for="cand_exp">Experiência</label>
                            <input class="input" id="cand_exp" name="experiencia">
                        </div>
                        <button class="btn btn--primary btn--block" type="submit"><?= icon('plus', 16) ?> Adicionar candidato</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

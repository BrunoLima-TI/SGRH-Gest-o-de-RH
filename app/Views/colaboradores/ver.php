<div class="page-head">
    <div class="page-head__text">
        <h1><?= e($colaborador['nome']) ?></h1>
        <p><?= e($colaborador['cargo'] ?? 'Cargo não informado') ?> · <?= e($colaborador['departamento'] ?? 'Sem departamento') ?></p>
    </div>
    <div class="page-head__actions">
        <a class="btn btn--secondary" href="<?= url('colaboradores') ?>"><?= icon('chevron-left', 16) ?> Voltar</a>
        <?php if (App\Core\Auth::ehRh()): ?>
            <a class="btn btn--primary" href="<?= url('colaboradores/' . (int) $colaborador['id'] . '/editar') ?>">
                <?= icon('edit', 16) ?> Editar cadastro
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="grid grid--1-2 mb-6">
    <div class="card">
        <div class="flex flex-center gap-4 mb-4">
            <span class="avatar avatar--xl"><?= e(iniciais($colaborador['nome'])) ?></span>
            <div>
                <h3 class="card__title"><?= e($colaborador['nome']) ?></h3>
                <p class="card__subtitle"><?= e($colaborador['email'] ?? 'sem e-mail') ?></p>
                <div class="mt-2">
                    <span class="badge badge--<?= e(classe_status($colaborador['status'])) ?>">
                        <?= e(rotulo_status($colaborador['status'])) ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="divider"></div>

        <ul class="list-plain">
            <li class="list-row"><span class="list-row__title">CPF</span><span class="mono"><?= e($colaborador['cpf'] ?: '—') ?></span></li>
            <li class="list-row"><span class="list-row__title">Telefone</span><span><?= e($colaborador['telefone'] ?: '—') ?></span></li>
            <li class="list-row"><span class="list-row__title">Nascimento</span><span><?= e(formatar_data($colaborador['data_nascimento'])) ?></span></li>
            <li class="list-row"><span class="list-row__title">Admissão</span><span><?= e(formatar_data($colaborador['data_admissao'])) ?></span></li>
            <li class="list-row"><span class="list-row__title">Contrato</span><span><?= e(strtoupper($colaborador['tipo_contrato'])) ?></span></li>
            <li class="list-row"><span class="list-row__title">Remuneração</span><span><?= e(formatar_moeda($colaborador['salario'])) ?></span></li>
            <li class="list-row"><span class="list-row__title">Escolaridade</span><span><?= e($colaborador['nivel_educacao'] ?: '—') ?></span></li>
            <li class="list-row"><span class="list-row__title">Endereço</span><span><?= e($colaborador['endereco'] ?: '—') ?></span></li>
        </ul>
    </div>

    <div class="stack">
        <div class="card">
            <div class="chart-card__head">
                <div>
                    <h3 class="card__title">Frequência do mês</h3>
                    <p class="card__subtitle">Registros de presença em <?= e(date('F', mktime(0,0,0,(int)date('n'),1))) ?></p>
                </div>
                <a class="btn btn--sm btn--ghost" href="<?= url('frequencia') ?>">Abrir</a>
            </div>
            <?php if (!$frequencias): ?>
                <p class="text-muted" style="font-size:13px">Nenhum registro de frequência neste mês.</p>
            <?php else: ?>
                <div class="flex gap-2 wrap">
                    <?php
                    $resumo = ['presente' => 0, 'falta' => 0, 'atraso' => 0, 'ausente' => 0, 'ferias' => 0];
                    foreach ($frequencias as $f) {
                        $resumo[$f['status']] = ($resumo[$f['status']] ?? 0) + 1;
                    }
                    ?>
                    <?php foreach ($resumo as $st => $qtd): ?>
                        <span class="badge badge--<?= e(classe_status($st)) ?>"><?= e(rotulo_status($st)) ?>: <?= (int) $qtd ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="card">
            <div class="chart-card__head">
                <div>
                    <h3 class="card__title">Férias e ausências</h3>
                    <p class="card__subtitle">Histórico de afastamentos</p>
                </div>
            </div>
            <ul class="list-plain">
                <?php if (!$ausencias): ?>
                    <li class="text-muted text-center" style="padding:20px 0;font-size:13px">Nenhum registro de ausência.</li>
                <?php endif; ?>
                <?php foreach ($ausencias as $a): ?>
                    <li class="list-row">
                        <div>
                            <div class="list-row__title"><?= e(rotulo_status($a['tipo'])) ?> · <?= (int) $a['dias'] ?> dia(s)</div>
                            <div class="list-row__meta"><?= e(formatar_data($a['data_inicio'])) ?> a <?= e(formatar_data($a['data_fim'])) ?></div>
                        </div>
                        <span class="badge badge--<?= e(classe_status($a['status'])) ?>"><?= e(rotulo_status($a['status'])) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="card">
            <div class="chart-card__head">
                <div>
                    <h3 class="card__title">Capacitação</h3>
                    <p class="card__subtitle">Treinamentos do colaborador</p>
                </div>
            </div>
            <ul class="list-plain">
                <?php if (!$treinamentos): ?>
                    <li class="text-muted text-center" style="padding:20px 0;font-size:13px">Nenhum treinamento inscrito.</li>
                <?php endif; ?>
                <?php foreach ($treinamentos as $t): ?>
                    <li class="list-row">
                        <div>
                            <div class="list-row__title"><?= e($t['treinamento']) ?></div>
                            <div class="list-row__meta"><?= e($t['categoria']) ?> · <?= e(formatar_numero($t['carga_horaria'], 0)) ?>h</div>
                        </div>
                        <span class="badge badge--<?= e(classe_status($t['status'])) ?>"><?= e(rotulo_status($t['status'])) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="card">
            <div class="chart-card__head">
                <div>
                    <h3 class="card__title">Avaliações de desempenho</h3>
                    <p class="card__subtitle">Histórico de ciclos avaliados</p>
                </div>
            </div>
            <ul class="list-plain">
                <?php if (!$avaliacoes): ?>
                    <li class="text-muted text-center" style="padding:20px 0;font-size:13px">Nenhuma avaliação registrada.</li>
                <?php endif; ?>
                <?php foreach ($avaliacoes as $av): ?>
                    <li class="list-row">
                        <div>
                            <div class="list-row__title"><?= e($av['ciclo']) ?></div>
                            <div class="list-row__meta"><?= e(formatar_data($av['data_avaliacao'])) ?></div>
                        </div>
                        <span class="badge badge--<?= $av['nota_geral'] >= 8 ? 'success' : ($av['nota_geral'] >= 6 ? 'warning' : 'danger') ?>">
                            <?= e(formatar_numero($av['nota_geral'], 1)) ?>/10
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="card">
            <div class="chart-card__head">
                <div>
                    <h3 class="card__title">Documentos</h3>
                    <p class="card__subtitle">Documentos vinculados</p>
                </div>
            </div>
            <ul class="list-plain">
                <?php if (!$documentos): ?>
                    <li class="text-muted text-center" style="padding:20px 0;font-size:13px">Nenhum documento vinculado.</li>
                <?php endif; ?>
                <?php foreach ($documentos as $d): ?>
                    <li class="list-row">
                        <div>
                            <div class="list-row__title"><?= e($d['nome']) ?></div>
                            <div class="list-row__meta"><?= e($d['tipo']) ?> · validade <?= e(formatar_data($d['data_validade'])) ?></div>
                        </div>
                        <span class="badge badge--<?= $d['data_validade'] && strtotime($d['data_validade']) < time() ? 'danger' : 'neutral' ?>">
                            <?= $d['data_validade'] && strtotime($d['data_validade']) < time() ? 'Vencido' : 'Válido' ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

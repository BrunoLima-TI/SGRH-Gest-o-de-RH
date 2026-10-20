<?php

use App\Core\Router;

$router = new Router();

/* ---------- Autenticação ---------- */
$router->get('login', 'Auth', 'index');
$router->post('login', 'Auth', 'entrar');
$router->qualquer('logout', 'Auth', 'sair');

/* ---------- Painel ---------- */
$router->get('', 'Dashboard', 'index');
$router->get('dashboard', 'Dashboard', 'index');

/* ---------- Colaboradores ---------- */
$router->get('colaboradores', 'Colaborador', 'index');
$router->get('colaboradores/novo', 'Colaborador', 'form');
$router->get('colaboradores/{id}/editar', 'Colaborador', 'form');
$router->get('colaboradores/{id}', 'Colaborador', 'ver');
$router->post('colaboradores/salvar', 'Colaborador', 'salvar');
$router->post('colaboradores/{id}/salvar', 'Colaborador', 'salvar');
$router->post('colaboradores/excluir', 'Colaborador', 'excluir');

/* ---------- Frequência ---------- */
$router->get('frequencia', 'Frequencia', 'index');
$router->post('frequencia/registrar', 'Frequencia', 'registrar');
$router->post('frequencia/excluir', 'Frequencia', 'excluir');

/* ---------- Férias e ausências ---------- */
$router->get('ferias', 'Ausencia', 'index');
$router->get('ferias/nova', 'Ausencia', 'form');
$router->post('ferias/salvar', 'Ausencia', 'salvar');
$router->post('ferias/decidir', 'Ausencia', 'decidir');
$router->post('ferias/excluir', 'Ausencia', 'excluir');

/* ---------- Recrutamento ---------- */
$router->get('vagas', 'Vaga', 'index');
$router->get('vagas/nova', 'Vaga', 'form');
$router->get('vagas/{id}/editar', 'Vaga', 'form');
$router->get('vagas/{id}', 'Vaga', 'ver');
$router->post('vagas/salvar', 'Vaga', 'salvar');
$router->post('vagas/{id}/salvar', 'Vaga', 'salvar');
$router->post('vagas/candidato', 'Vaga', 'candidato');
$router->post('vagas/etapa', 'Vaga', 'etapa');
$router->post('vagas/excluir', 'Vaga', 'excluir');

/* ---------- Treinamentos ---------- */
$router->get('treinamentos', 'Treinamento', 'index');
$router->get('treinamentos/novo', 'Treinamento', 'form');
$router->get('treinamentos/{id}/editar', 'Treinamento', 'form');
$router->post('treinamentos/salvar', 'Treinamento', 'salvar');
$router->post('treinamentos/{id}/salvar', 'Treinamento', 'salvar');
$router->post('treinamentos/inscricao', 'Treinamento', 'inscricao');
$router->post('treinamentos/atualizar-inscricao', 'Treinamento', 'atualizarInscricao');
$router->post('treinamentos/excluir', 'Treinamento', 'excluir');

/* ---------- Desempenho ---------- */
$router->get('desempenho', 'Desempenho', 'index');
$router->get('desempenho/nova', 'Desempenho', 'form');
$router->get('desempenho/{id}/editar', 'Desempenho', 'form');
$router->post('desempenho/salvar', 'Desempenho', 'salvar');
$router->post('desempenho/{id}/salvar', 'Desempenho', 'salvar');
$router->post('desempenho/excluir', 'Desempenho', 'excluir');

/* ---------- Documentos ---------- */
$router->get('documentos', 'Documento', 'index');
$router->get('documentos/novo', 'Documento', 'form');
$router->post('documentos/salvar', 'Documento', 'salvar');
$router->post('documentos/excluir', 'Documento', 'excluir');

/* ---------- Relatórios ---------- */
$router->get('relatorios', 'Relatorio', 'index');
$router->get('relatorios/exportar', 'Relatorio', 'exportar');

/* ---------- Estrutura organizacional ---------- */
$router->get('estrutura', 'Estrutura', 'index');
$router->post('estrutura/departamento', 'Estrutura', 'departamento');
$router->post('estrutura/cargo', 'Estrutura', 'cargo');
$router->post('estrutura/excluir', 'Estrutura', 'excluir');

/* ---------- Usuários ---------- */
$router->get('usuarios', 'Usuario', 'index');
$router->get('usuarios/novo', 'Usuario', 'form');
$router->get('usuarios/{id}/editar', 'Usuario', 'form');
$router->post('usuarios/salvar', 'Usuario', 'salvar');
$router->post('usuarios/{id}/salvar', 'Usuario', 'salvar');
$router->post('usuarios/excluir', 'Usuario', 'excluir');

return $router;

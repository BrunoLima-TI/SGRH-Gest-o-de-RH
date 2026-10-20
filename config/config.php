<?php
/**
 * Configuração central do SGRH.
 * Ajuste as credenciais aqui conforme o seu MySQL (XAMPP).
 */
return [
    'app' => [
        'nome'      => 'SGRH',
        'nome_completo' => 'Sistema de Gestão de Recursos Humanos',
        'empresa'   => 'Organização',
        'ambiente'  => 'dev',           // dev | prod
        'debug'     => true,
        'timezone'  => 'America/Sao_Paulo',
        'locale'    => 'pt_BR',
    ],

    'db' => [
        'host'    => '127.0.0.1',
        'porta'   => 3306,
        'nome'    => 'sgrh',
        'usuario' => 'sgrh',
        'senha'   => 'sgrh_dev',
        'charset' => 'utf8mb4',
    ],

    'sessao' => [
        'nome'    => 'SGRHSESSID',
        'tempo'   => 7200,
        'seguro'  => false, // true se for rodar em HTTPS
    ],

    'uploads' => [
        'dir'        => ROOT_PATH . '/uploads',
        'url'        => BASE_URL . '/uploads',
        'max_bytes'  => 5 * 1024 * 1024,
        'permitidos' => ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx', 'txt'],
    ],
];

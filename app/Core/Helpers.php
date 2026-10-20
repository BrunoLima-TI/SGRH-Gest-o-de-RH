<?php

use App\Core\Session;

if (!function_exists('e')) {
    /** Escapa saída para HTML. */
    function e($valor): string
    {
        return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('is_ajax')) {
    function is_ajax(): bool
    {
        return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
    }
}

if (!function_exists('old')) {
    /** Valor previously digitado no formulário (pós-redirect com sessão). */
    function old(string $campo, $padrao = '')
    {
        return Session::get('old.' . $campo, $padrao);
    }
}

if (!function_exists('erro_campo')) {
    /** Mensagem de erro de validação de um campo. */
    function erro_campo(string $campo): ?string
    {
        $erros = Session::get('errors', []);
        return $erros[$campo] ?? null;
    }
}

if (!function_exists('url')) {
    /** Monta uma URL da aplicação. */
    function url(string $caminho = ''): string
    {
        return BASE_URL . '/' . ltrim($caminho, '/');
    }
}

if (!function_exists('asset')) {
    function asset(string $caminho): string
    {
        $url = BASE_URL . '/assets/' . ltrim($caminho, '/');
        $arquivo = APP_PATH . '/../assets/' . ltrim($caminho, '/');
        $versao = is_file($arquivo) ? (string) filemtime($arquivo) : '1';
        return $url . '?v=' . $versao;
    }
}

if (!function_exists('redirect')) {
    function redirect(string $caminho, int $codigo = 302): void
    {
        if (str_starts_with($caminho, 'http')) {
            $destino = $caminho;
        } elseif (str_starts_with($caminho, '/')) {
            $destino = $caminho;
        } else {
            $destino = url($caminho);
        }
        header('Location: ' . $destino, true, $codigo);
        exit;
    }
}

if (!function_exists('old_input')) {
    function old_input(string $campo, $padrao = '')
    {
        return $_POST[$campo] ?? $padrao;
    }
}

if (!function_exists('formatar_data')) {
    function formatar_data(?string $data, string $formato = 'd/m/Y'): string
    {
        if (!$data) {
            return '—';
        }
        $ts = strtotime($data);
        return $ts ? date($formato, $ts) : '—';
    }
}

if (!function_exists('formatar_moeda')) {
    function formatar_moeda($valor): string
    {
        return 'R$ ' . number_format((float) $valor, 2, ',', '.');
    }
}

if (!function_exists('formatar_numero')) {
    function formatar_numero($valor, int $casas = 0): string
    {
        return number_format((float) $valor, $casas, ',', '.');
    }
}

if (!function_exists('iniciais')) {
    /** Iniciais para o avatar de fallback. */
    function iniciais(string $nome): string
    {
        $partes = preg_split('/\s+/', trim($nome)) ?: [];
        $partes = array_values(array_filter($partes, fn($p) => mb_strlen($p) > 2));
        if (!$partes) {
            return mb_strtoupper(mb_substr($nome, 0, 1));
        }
        $primeira = mb_strtoupper(mb_substr($partes[0], 0, 1));
        $ultima = count($partes) > 1 ? mb_strtoupper(mb_substr(end($partes), 0, 1)) : '';
        return $primeira . $ultima;
    }
}

if (!function_exists('slug')) {
    function slug(string $texto): string
    {
        $texto = preg_replace('/[^\p{L}\p{N}]+/u', '-', $texto) ?? '';
        return trim(mb_strtolower($texto), '-');
    }
}

if (!function_exists('classe_status')) {
    /** Converte um status de domínio em uma classe de badge. */
    function classe_status(?string $status): string
    {
        $mapa = [
            'ativo' => 'success', 'inativo' => 'danger', 'afastado' => 'neutral',
            'ferias' => 'warning', 'pendente' => 'info', 'aprovado' => 'success',
            'reprovado' => 'danger', 'contrato' => 'info', 'em_andamento' => 'violet',
            'concluido' => 'success', 'em_analise' => 'info', 'entrevista' => 'warning',
            'ofertado' => 'success', 'fechado' => 'neutral', 'vencido' => 'danger',
            'vencendo' => 'warning', 'valido' => 'success', 'presente' => 'success',
            'falta' => 'danger', 'atraso' => 'warning', 'ausente' => 'danger',
            'meio_periodo' => 'info', 'ferias_gozadas' => 'success',
        ];
        return $mapa[strtolower((string) $status)] ?? 'neutral';
    }
}

if (!function_exists('rotulo_status')) {
    function rotulo_status(?string $status): string
    {
        $mapa = [
            'ativo' => 'Ativo', 'inativo' => 'Inativo', 'afastado' => 'Afastado',
            'ferias' => 'Férias', 'pendente' => 'Pendente', 'aprovado' => 'Aprovado',
            'reprovado' => 'Reprovado', 'contrato' => 'Contrato', 'em_andamento' => 'Em andamento',
            'concluido' => 'Concluído', 'em_analise' => 'Em análise', 'entrevista' => 'Entrevista',
            'ofertado' => 'Ofertado', 'fechado' => 'Fechado', 'vencido' => 'Vencido',
            'vencendo' => 'Vencendo', 'valido' => 'Válido', 'presente' => 'Presente',
            'falta' => 'Falta', 'atraso' => 'Atraso', 'ausente' => 'Ausente',
            'meio_periodo' => 'Meio período', 'ferias_gozadas' => 'Gozadas',
            'demitido' => 'Demitido',
        ];
        return $mapa[strtolower((string) $status)] ?? ucfirst((string) $status);
    }
}

if (!function_exists('paginacao')) {
    /** Array de paginação para a view. */
    function paginacao(int $total, int $pagina, int $porPagina, int $maxLinks = 7): array
    {
        $totalPaginas = max(1, (int) ceil($total / max(1, $porPagina)));
        $pagina = max(1, min($pagina, $totalPaginas));
        $inicio = max(1, $pagina - intdiv($maxLinks, 2));
        $fim = min($totalPaginas, $inicio + $maxLinks - 1);
        $inicio = max(1, $fim - $maxLinks + 1);

        $links = [];
        for ($i = $inicio; $i <= $fim; $i++) {
            $links[] = ['numero' => $i, 'atual' => $i === $pagina];
        }

        return [
            'total'        => $total,
            'pagina'       => $pagina,
            'por_pagina'   => $porPagina,
            'total_paginas'=> $totalPaginas,
            'de'           => $total === 0 ? 0 : (($pagina - 1) * $porPagina) + 1,
            'ate'          => min($total, $pagina * $porPagina),
            'links'        => $links,
            'tem_anterior' => $pagina > 1,
            'tem_proxima'  => $pagina < $totalPaginas,
        ];
    }
}

if (!function_exists('icon')) {
    /**
     * Ícone SVG inline (24x24, stroke currentColor).
     */
    function icon(string $nome, int $tamanho = 20, string $classe = ''): string
    {
        $caminhos = [
            'dashboard'  => '<rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/>',
            'users'      => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
            'calendar'   => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
            'briefcase'  => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>',
            'graduation' => '<path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c0 1.5 3 3 6 3s6-1.5 6-3v-5"/>',
            'star'       => '<path d="m12 2 3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2Z"/>',
            'file'       => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/>',
            'chart'      => '<path d="M3 3v18h18"/><path d="M7 15l4-5 3 3 5-7"/>',
            'settings'   => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9v0a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1Z"/>',
            'logout'     => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/>',
            'search'     => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>',
            'bell'       => '<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>',
            'chevron-down'=> '<path d="m6 9 6 6 6-6"/>',
            'chevron-right'=> '<path d="m9 18 6-6-6-6"/>',
            'chevron-left'=> '<path d="m15 18-6-6 6-6"/>',
            'plus'       => '<path d="M12 5v14M5 12h14"/>',
            'filter'     => '<path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"/>',
            'download'   => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/><path d="M12 15V3"/>',
            'upload'     => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m17 8-5-5-5 5"/><path d="M12 3v12"/>',
            'edit'       => '<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4Z"/>',
            'trash'      => '<path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>',
            'eye'        => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
            'check'      => '<path d="m20 6-11 11-5-5"/>',
            'x'          => '<path d="M18 6 6 18M6 6l12 12"/>',
            'alert'      => '<path d="m10.29 3.86-8.47 14.14A2 2 0 0 0 3.53 21h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/><path d="M12 9v4M12 17h.01"/>',
            'info'       => '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>',
            'menu'       => '<path d="M3 12h18M3 6h18M3 18h18"/>',
            'clock'      => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
            'building'   => '<rect x="4" y="2" width="16" height="20" rx="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01M16 6h.01M8 10h.01M16 10h.01M8 14h.01M16 14h.01M12 6h.01M12 10h.01M12 14h.01"/>',
            'clipboard'  => '<rect x="8" y="2" width="8" height="4" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>',
            'trending'   => '<path d="m22 7-8.5 8.5-5-5L2 17"/><path d="M16 7h6v6"/>',
            'pie'        => '<path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/>',
            'user'       => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
            'shield'     => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/>',
            'mail'       => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/>',
            'lock'       => '<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
            'home'       => '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/>',
            'sun'        => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/>',
            'more'       => '<circle cx="12" cy="12" r="1"/><circle cx="12" cy="5" r="1"/><circle cx="12" cy="19" r="1"/>',
            'arrow-up'   => '<path d="m5 12 7-7 7 7M12 19V5"/>',
            'arrow-down' => '<path d="M19 12l-7 7-7-7M12 5v14"/>',
            'print'      => '<path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/>',
        ];
        $path = $caminhos[$nome] ?? $caminhos['info'];
        return '<svg class="icon ' . e($classe) . '" width="' . $tamanho . '" height="' . $tamanho . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $path . '</svg>';
    }
}

if (!function_exists('numero')) {
    /**
     * Converte "3.500,00", "3.500,50" ou "3500.50" em float.
     */
    function numero($valor): float
    {
        $v = preg_replace('/[^\d,.\-]/', '', (string) ($valor ?? '')) ?? '';
        if ($v === '' || $v === '-') {
            return 0.0;
        }
        if (str_contains($v, ',')) {
            $v = str_replace('.', '', $v);
            $v = str_replace(',', '.', $v);
        }
        return (float) $v;
    }
}

if (!function_exists('querystring')) {
    /** Rebuild da query string preservando filtros. */
    function querystring(array $mudancas = []): string
    {
        $params = array_merge($_GET, $mudancas);
        $params = array_filter($params, fn($v) => $v !== '' && $v !== null);
        return $params ? '?' . http_build_query($params) : '';
    }
}

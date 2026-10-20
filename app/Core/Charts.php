<?php

namespace App\Core;

/**
 * Geração de gráficos SVG inline (sem bibliotecas externas).
 */
class Charts
{
    public const CORES = ['#4F46E5', '#0EA5E9', '#14B8A6', '#F59E0B', '#EC4899', '#8B5CF6', '#64748B'];

    /**
     * Normaliza séries de diferentes formatos para o padrão ['rotulo' => ..., 'valor' => ...].
     *
     * @param array $series  Linhas vindas do banco
     * @param array $chaves  Ordem de prioridade das colunas com o valor numérico
     */
    private static function normalizar(array $series, array $chaves = ['valor', 'total', 'percentual', 'horas', 'nota']): array
    {
        $saida = [];
        foreach (array_values($series) as $i => $item) {
            if (!is_array($item)) {
                continue;
            }
            $rotulo = $item['rotulo'] ?? $item['mes'] ?? $item['nome'] ?? $item['titulo']
                ?? $item['categoria'] ?? $item['nota'] ?? ($i + 1);

            $valor = null;
            foreach ($chaves as $chave) {
                if (isset($item[$chave]) && is_numeric($item[$chave])) {
                    $valor = (float) $item[$chave];
                    break;
                }
            }
            if ($valor === null) {
                $valor = 0.0;
            }

            $saida[] = [
                'rotulo' => $rotulo,
                'valor'  => $valor,
                'cor'    => $item['cor'] ?? null,
            ];
        }
        return $saida;
    }

    /** Gráfico de barras verticais. */
    public static function barras(array $series, array $opcoes = []): string
    {
        $series = self::normalizar($series, $opcoes['chaves'] ?? ['valor', 'total']);
        if (!$series) {
            return self::vazio();
        }
        $w = 320;
        $h = 180;
        $padL = 34;
        $padB = 26;
        $padT = 14;
        $plotH = $h - $padT - $padB;
        $plotW = $w - $padL - 10;
        $max = max(array_map(fn($s) => (float) $s['valor'], $series));
        $max = $max <= 0 ? 1 : $max * 1.15;
        $n = count($series);
        $slot = $plotW / max(1, $n);
        $bw = min(34, max(6, (int) ($slot * 0.55)));
        $cor = $opcoes['cor'] ?? self::CORES[0];

        $svg = '<svg class="chart" viewBox="0 0 ' . $w . ' ' . $h . '" role="img" aria-label="' . e($opcoes['titulo'] ?? 'Gráfico de barras') . '" preserveAspectRatio="xMidYMid meet">';
        $svg .= '<title>' . e($opcoes['titulo'] ?? 'Gráfico de barras') . '</title>';

        for ($i = 0; $i <= 4; $i++) {
            $y = $padT + ($plotH / 4) * $i;
            $svg .= '<line x1="' . $padL . '" y1="' . round($y, 1) . '" x2="' . ($w - 4) . '" y2="' . round($y, 1) . '" stroke="#E2E8F0" stroke-width="1"/>';
        }

        foreach ($series as $i => $serie) {
            $valor = (float) $serie['valor'];
            $bh = (int) round(($valor / $max) * $plotH);
            $x = (int) round($padL + ($i * $slot) + (($slot - $bw) / 2));
            $y = $padT + $plotH - $bh;
            $rotulo = $serie['rotulo'];
            $svg .= '<g><title>' . e($rotulo . ': ' . $valor) . '</title>';
            if ($bh > 0) {
                $svg .= '<rect x="' . $x . '" y="' . $y . '" width="' . $bw . '" height="' . $bh . '" rx="4" fill="' . $cor . '"/>';
                $svg .= '<rect x="' . $x . '" y="' . ($y + $bh - 4) . '" width="' . $bw . '" height="4" fill="' . $cor . '"/>';
            }
            if ($n <= 12 && $bh > 0) {
                $svg .= '<text x="' . ($x + $bw / 2) . '" y="' . ($y - 5) . '" text-anchor="middle" font-size="10" font-weight="600" fill="#334155">' . e((string) round($valor, 1)) . '</text>';
            }
            $svg .= '<text x="' . ($x + $bw / 2) . '" y="' . ($h - 8) . '" text-anchor="middle" font-size="10" fill="#64748B">' . e(mb_substr((string) $rotulo, 0, 8)) . '</text>';
            $svg .= '</g>';
        }

        $svg .= '</svg>';
        return $svg;
    }

    /** Gráfico de rosca (donut). */
    public static function rosca(array $series, array $opcoes = []): string
    {
        $series = self::normalizar($series, $opcoes['chaves'] ?? ['valor', 'total']);
        if (!$series) {
            return self::vazio();
        }
        $total = array_sum(array_map(fn($s) => (float) $s['valor'], $series));
        $size = 160;
        $raio = 62;
        $centro = $size / 2;
        $circunferencia = 2 * M_PI * $raio;
        $grosso = 18;
        $gap = 2;

        $svg = '<svg class="chart" viewBox="0 0 ' . $size . ' ' . $size . '" role="img" aria-label="' . e($opcoes['titulo'] ?? 'Gráfico de rosca') . '" preserveAspectRatio="xMidYMid meet">';
        $svg .= '<title>' . e($opcoes['titulo'] ?? 'Gráfico de rosca') . '</title>';
        $svg .= '<circle cx="' . $centro . '" cy="' . $centro . '" r="' . $raio . '" fill="none" stroke="#F1F5F9" stroke-width="' . $grosso . '"/>';

        $offset = 0.0;
        foreach ($series as $i => $serie) {
            $valor = (float) $serie['valor'];
            if ($valor <= 0 || $total <= 0) {
                continue;
            }
            $frac = $valor / $total;
            $comp = max(0.0, ($frac * $circunferencia) - $gap);
            $cor = $serie['cor'] ?? self::CORES[$i % count(self::CORES)];
            $svg .= '<circle cx="' . $centro . '" cy="' . $centro . '" r="' . $raio . '" fill="none" stroke="' . $cor . '" stroke-width="' . $grosso . '"'
                . ' stroke-dasharray="' . round($comp, 2) . ' ' . round($circunferencia - $comp, 2) . '"'
                . ' stroke-dashoffset="' . round(-$offset, 2) . '" transform="rotate(-90 ' . $centro . ' ' . $centro . ')">';
            $svg .= '<title>' . e($serie['rotulo'] . ': ' . $valor . ' (' . round($frac * 100, 1) . '%)') . '</title></circle>';
            $offset += $frac * $circunferencia;
        }

        $svg .= '<text x="' . $centro . '" y="' . ($centro + 2) . '" text-anchor="middle" font-size="22" font-weight="700" fill="#0F172A">' . e((string) ($opcoes['total'] ?? (int) $total)) . '</text>';
        $svg .= '<text x="' . $centro . '" y="' . ($centro + 19) . '" text-anchor="middle" font-size="11" fill="#64748B">' . e($opcoes['legendaCentro'] ?? 'Total') . '</text>';
        $svg .= '</svg>';
        return $svg;
    }

    /** Gráfico de linha com área. */
    public static function linha(array $series, array $opcoes = []): string
    {
        $series = self::normalizar($series, $opcoes['chaves'] ?? ['valor', 'total']);
        if (count($series) < 2) {
            return self::vazio();
        }
        $w = 480;
        $h = 200;
        $padL = 36;
        $padB = 26;
        $padT = 14;
        $plotH = $h - $padT - $padB;
        $plotW = $w - $padL - 12;
        $max = max(array_map(fn($s) => (float) $s['valor'], $series));
        $min = min(array_map(fn($s) => (float) $s['valor'], $series));
        $max = $max <= 0 ? 1 : $max * 1.15;
        $min = $min < 0 ? $min * 1.15 : 0;
        $faixa = max(0.0001, $max - $min);
        $n = count($series);
        $passo = $plotW / max(1, $n - 1);
        $cor = $opcoes['cor'] ?? self::CORES[0];

        $svg = '<svg class="chart" viewBox="0 0 ' . $w . ' ' . $h . '" role="img" aria-label="' . e($opcoes['titulo'] ?? 'Gráfico de linha') . '" preserveAspectRatio="xMidYMid meet">';
        $svg .= '<title>' . e($opcoes['titulo'] ?? 'Gráfico de linha') . '</title>';
        $svg .= '<defs><linearGradient id="areaFade" x1="0" y1="0" x2="0" y2="1">'
            . '<stop offset="0%" stop-color="' . $cor . '" stop-opacity="0.16"/>'
            . '<stop offset="100%" stop-color="' . $cor . '" stop-opacity="0"/></linearGradient></defs>';

        for ($i = 0; $i <= 4; $i++) {
            $y = $padT + ($plotH / 4) * $i;
            $svg .= '<line x1="' . $padL . '" y1="' . round($y, 1) . '" x2="' . ($w - 6) . '" y2="' . round($y, 1) . '" stroke="#E2E8F0" stroke-width="1" stroke-dasharray="3 4"/>';
        }

        $pontos = [];
        foreach ($series as $i => $serie) {
            $x = $padL + ($i * $passo);
            $y = $padT + $plotH - ((($serie['valor'] - $min) / $faixa) * $plotH);
            $pontos[] = [$x, $y, $serie];
        }

        $d = '';
        foreach ($pontos as $i => $p) {
            $d .= ($i === 0 ? 'M' : 'L') . round($p[0], 1) . ' ' . round($p[1], 1) . ' ';
        }
        $area = $d . 'L' . round(end($pontos)[0], 1) . ' ' . ($padT + $plotH) . ' L' . round($pontos[0][0], 1) . ' ' . ($padT + $plotH) . ' Z';

        $svg .= '<path d="' . trim($area) . '" fill="url(#areaFade)"/>';
        $svg .= '<path d="' . trim($d) . '" fill="none" stroke="' . $cor . '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';

        $ultimo = end($pontos);
        $svg .= '<circle cx="' . round($ultimo[0], 1) . '" cy="' . round($ultimo[1], 1) . '" r="3.5" fill="' . $cor . '" stroke="#fff" stroke-width="1.5"/>';

        foreach ($pontos as $i => $p) {
            if ($n > 12 && $i % 2 !== 0 && $i !== $n - 1) {
                continue;
            }
            $svg .= '<text x="' . round($p[0], 1) . '" y="' . ($h - 8) . '" text-anchor="middle" font-size="10" fill="#64748B">' . e(mb_substr((string) $p[2]['rotulo'], 0, 7)) . '</text>';
        }

        $svg .= '</svg>';
        return $svg;
    }

    /** Mini-gráfico para cards de KPI. */
    public static function sparkline(array $valores, string $cor = '#4F46E5'): string
    {
        $valores = array_values(array_map('floatval', array_filter($valores, 'is_numeric')));
        if (count($valores) < 2) {
            return '';
        }
        $w = 120;
        $h = 36;
        $pad = 4;
        $max = max($valores);
        $min = min($valores);
        $faixa = max(0.0001, $max - $min);
        $n = count($valores);
        $passo = ($w - 2 * $pad) / ($n - 1);

        $pontos = [];
        foreach ($valores as $i => $v) {
            $x = $pad + $i * $passo;
            $y = $h - $pad - ((($v - $min) / $faixa) * ($h - 2 * $pad));
            $pontos[] = [$x, $y];
        }

        $d = '';
        foreach ($pontos as $i => $p) {
            $d .= ($i === 0 ? 'M' : 'L') . round($p[0], 1) . ' ' . round($p[1], 1) . ' ';
        }
        $area = $d . 'L' . round(end($pontos)[0], 1) . ' ' . $h . ' L' . round($pontos[0][0], 1) . ' ' . $h . ' Z';

        $svg = '<svg class="sparkline" viewBox="0 0 ' . $w . ' ' . $h . '" preserveAspectRatio="none" aria-hidden="true">';
        $svg .= '<defs><linearGradient id="sparkFade" x1="0" y1="0" x2="0" y2="1">'
            . '<stop offset="0%" stop-color="' . $cor . '" stop-opacity="0.18"/>'
            . '<stop offset="100%" stop-color="' . $cor . '" stop-opacity="0"/></linearGradient></defs>';
        $svg .= '<path d="' . trim($area) . '" fill="url(#sparkFade)"/>';
        $svg .= '<path d="' . trim($d) . '" fill="none" stroke="' . $cor . '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';
        $ultimo = end($pontos);
        $svg .= '<circle cx="' . round($ultimo[0], 1) . '" cy="' . round($ultimo[1], 1) . '" r="2.5" fill="' . $cor . '" stroke="#fff" stroke-width="1.5"/>';
        $svg .= '</svg>';
        return $svg;
    }

    /** Legenda HTML para os gráficos. */
    public static function legenda(array $series): string
    {
        $series = self::normalizar($series, ['valor', 'total']);
        if (!$series) {
            return '';
        }
        $html = '<div class="legend">';
        foreach ($series as $i => $serie) {
            $cor = $serie['cor'] ?? self::CORES[$i % count(self::CORES)];
            $html .= '<span class="legend__item"><span class="legend__swatch" style="background:' . e($cor) . '"></span>'
                . e((string) $serie['rotulo']) . ' <strong>' . e((string) round($serie['valor'])) . '</strong></span>';
        }
        return $html . '</div>';
    }

    private static function vazio(): string
    {
        return '<div class="chart-empty"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 15l4-5 3 3 5-7"/></svg>'
            . '<span>Sem dados no período</span></div>';
    }
}

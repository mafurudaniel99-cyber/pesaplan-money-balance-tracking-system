<?php
/**
 * PesaPlan — Helper functions.
 * These are loaded once and available everywhere via plain function calls.
 */

declare(strict_types=1);

if (!function_exists('e')) {
    function e(?string $value): string {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('money')) {
    /**
     * Format a number as TZS with thousand separators, no decimals.
     * Example: 1500000 -> "1,500,000 TZS"
     */
    function money($amount, bool $withCurrency = true): string {
        $amount = (float) ($amount ?? 0);
        $formatted = number_format($amount, 0, '.', ',');
        return $withCurrency ? $formatted . ' TZS' : $formatted;
    }
}

if (!function_exists('money_decimal')) {
    function money_decimal($amount, bool $withCurrency = true): string {
        $amount = (float) ($amount ?? 0);
        $formatted = number_format($amount, 2, '.', ',');
        return $withCurrency ? $formatted . ' TZS' : $formatted;
    }
}

if (!function_exists('sw_date')) {
    /**
     * Format a date in Kiswahili: "12 Januari 2026"
     */
    function sw_date(?string $date, string $format = 'j F Y'): string {
        if (!$date) return '-';
        try {
            $dt = new DateTime($date);
        } catch (Throwable $e) {
            return '-';
        }
        $months = [
            'January' => 'Januari', 'February' => 'Februari', 'March' => 'Machi',
            'April' => 'Aprili', 'May' => 'Mei', 'June' => 'Juni',
            'July' => 'Julai', 'August' => 'Agosti', 'September' => 'Septemba',
            'October' => 'Oktoba', 'November' => 'Novemba', 'December' => 'Desemba'
        ];
        $out = $dt->format($format);
        return strtr($out, $months);
    }
}

if (!function_exists('current_month')) {
    function current_month(): string {
        return date('Y-m');
    }
}

if (!function_exists('percent')) {
    function percent($numerator, $denominator): float {
        $num = (float) $numerator;
        $den = (float) $denominator;
        if ($den <= 0) return 0.0;
        return ($num / $den) * 100.0;
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string {
        if ($path === '') return '/';
        if ($path[0] !== '/') $path = '/' . $path;
        return $path;
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string {
        if ($path[0] !== '/') $path = '/' . $path;
        return '/assets' . $path . '?v=' . filemtime(dirname(__DIR__, 2) . '/public/assets' . $path);
    }
}

if (!function_exists('flash_messages')) {
    function flash_messages(array $flash): string {
        $html = '';
        foreach (['success' => 'success', 'error' => 'danger', 'info' => 'info'] as $key => $cls) {
            if (!empty($flash[$key])) {
                $html .= '<div class="alert alert-' . $cls . '" role="alert"><i class="fa-solid fa-' .
                    ($cls === 'success' ? 'circle-check' : ($cls === 'danger' ? 'circle-exclamation' : 'circle-info')) .
                    '"></i> ' . e($flash[$key]) . '</div>';
            }
        }
        return $html;
    }
}

if (!function_exists('percent_bar_class')) {
    function percent_bar_class(float $pct): string {
        if ($pct >= 100) return 'bar-danger';
        if ($pct >= 80)  return 'bar-warning';
        return 'bar-success';
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string {
        return \App\Core\Csrf::field();
    }
}

if (!function_exists('old_input')) {
    function old_input(string $key, $default = '') {
        return \App\Core\Session::flash('old:' . $key) ?? $default;
    }
}

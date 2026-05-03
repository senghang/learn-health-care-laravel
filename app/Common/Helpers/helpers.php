<?php

if (!function_exists('currentClinic')) {
    function currentClinic()
    {
        return app('currentClinic');
    }
}

if (!function_exists('formatDate')) {
    function formatDate(?string $date): string
    {
        if (!$date) return '—';
        return \Carbon\Carbon::parse($date)->timezone('Asia/Phnom_Penh')->format('d/m/Y');
    }
}

if (!function_exists('formatDateTime')) {
    function formatDateTime(?string $date): string
    {
        if (!$date) return '—';
        return \Carbon\Carbon::parse($date)->timezone('Asia/Phnom_Penh')->format('d/m/Y H:i');
    }
}

if (!function_exists('formatCurrency')) {
    function formatCurrency(float|int|null $amount, string $currency = 'USD'): string
    {
        if ($amount === null) return '—';
        return '$' . number_format((float) $amount, 2);
    }
}
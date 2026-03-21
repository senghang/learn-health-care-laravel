<?php

if (!function_exists('generate_code')) {

    function generate_code(string $prefix, string $modelClass, string $column = 'code', int $pad = 3): string
    {
        $today = now()->format('Ymd');
        $last = $modelClass::whereDate('created_at', today())
            ->orderByDesc('id')
            ->first();
        $number = 1;
        if ($last && isset($last->$column)) {
            $lastNumber = intval(substr($last->$column, -$pad));
            $number = $lastNumber + 1;
        }

        return $prefix . $today . str_pad($number, $pad, '0', STR_PAD_LEFT);
    }

}

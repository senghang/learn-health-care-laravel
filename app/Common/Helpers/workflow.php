<?php

if (!function_exists('wf_step_url')) {
    /**
     * Generate a URL for a workflow step route.
     *
     * All workflow routes live under Route::domain('{subdomain}.localhost')
     * which adds a required {subdomain} parameter. Passing a positional array
     * like [$code, $step] maps $code → subdomain and breaks the URL.
     *
     * This helper always uses named parameters so {subdomain} is filled
     * from URL::defaults (set by BindSubdomainParameter middleware).
     *
     * Usage in Blade:
     *   action="{{ wf_save_url($visit->code, 'triage') }}"
     *   href="{{ wf_step_url($visit->code, 'vitals') }}"
     *   href="{{ wf_skip_url($visit->code, 'labs') }}"
     */
    function wf_step_url(string $code, string $step): string
    {
        return route('workflow.step', ['code' => $code, 'step' => $step]);
    }
}

if (!function_exists('wf_save_url')) {
    function wf_save_url(string $code, string $step): string
    {
        return route('workflow.step.save', ['code' => $code, 'step' => $step]);
    }
}

if (!function_exists('wf_skip_url')) {
    function wf_skip_url(string $code, string $step): string
    {
        return route('workflow.skip', ['code' => $code, 'step' => $step]);
    }
}

if (!function_exists('wf_show_url')) {
    function wf_show_url(string $code): string
    {
        return route('workflow.show', ['code' => $code]);
    }
}

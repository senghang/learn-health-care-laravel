@extends('clinics.layout.app')

@section('title', ($visit->surname ?? '') . ', ' . ($visit->name ?? '') . ' — ' . $visit->code)

@section('content')

    @php
        $skipUrl  = url('/workflow/' . $visit->code . '/' . $currentStep . '/skip');
        $prevUrl  = $prevStep ? url('/workflow/' . $visit->code . '/' . $prevStep) : null;
        $nextUrl  = $nextStep ? url('/workflow/' . $visit->code . '/' . $nextStep) : null;
        $total    = count($steps);
        $doneCount    = count($visit->done_steps ?? []);
        $skippedCount = count($visit->skipped_steps ?? []);
    @endphp

    {{-- Page header --}}
    <x-workflow.header :visit="$visit"/>

    {{-- Step bar --}}
    <x-workflow.step-bar :visit="$visit" :steps="$steps" :current-step="$currentStep"/>

    <div class="row g-3">

        {{-- ── Main step content ──────────────────────────────────────── --}}
        <div class="col-12 col-lg-9">
            <div class="card-emr">

                @include('clinics.workflow.steps.' . $currentStep, [
                    'visit'       => $visit,
                    'steps'       => $steps,
                    'currentStep' => $currentStep,
                    'stepIdx'     => array_search($currentStep, array_column($steps, 'id')),
                ])

                {{-- Action bar: save / skip / prev / next --}}
                <x-workflow.action-bar
                    :form-id="'stepForm'"
                    :skip-route="$skipUrl"
                    :prev-step-route="$prevUrl"
                    :next-step-route="$nextUrl"
                    :step-number="$stepNumber"
                    :total-steps="$total"
                />

            </div>
        </div>

        {{-- ── Right sidebar: visit summary ──────────────────────────── --}}
        <div class="col-lg-3 d-none d-lg-block" id="vssCol">
            @include('clinics.workflow.visit-summary', [
                'visit'       => $visit,
                'steps'       => $steps,
                'currentStep' => $currentStep,
            ])
        </div>

    </div>

@endsection

{{-- ── Client-side form validation for ALL step forms ─────────────── --}}
<script>
    (function () {
        var style = document.createElement('style');
        style.textContent =
            '.field-error{font-size:11px;color:#e74c3c;margin-top:3px;display:flex;align-items:center;gap:4px}' +
            '.field-error::before{content:"⚠";font-size:10px}' +
            '.form-control.is-invalid,.form-select.is-invalid{border-color:#e74c3c!important;background-image:none!important}' +
            '.form-control.is-invalid:focus,.form-select.is-invalid:focus{box-shadow:0 0 0 3px rgba(231,76,60,.15)!important}';
        document.head.appendChild(style);

        document.addEventListener('DOMContentLoaded', function () {
            var form = document.getElementById('stepForm');
            if (!form) return;
            form.setAttribute('novalidate', '');

            form.addEventListener('submit', function (e) {
                clearErrors(form);
                var invalid = validateForm(form);
                if (!invalid.length) return;
                e.preventDefault();
                invalid.forEach(showError);
                invalid[0].scrollIntoView({behavior: 'smooth', block: 'center'});
                invalid[0].focus({preventScroll: true});
            });

            form.addEventListener('input', function (e) {
                clearFieldError(e.target);
            });
            form.addEventListener('change', function (e) {
                clearFieldError(e.target);
            });
        });

        function validateForm(form) {
            var invalid = [];
            form.querySelectorAll('input[required],select[required],textarea[required]').forEach(function (el) {
                if (el.disabled || el.readOnly || el.type === 'hidden') return;
                if (!el.offsetParent) return;
                if (!el.value.trim()) invalid.push(el);
            });
            return invalid;
        }

        function showError(el) {
            el.classList.add('is-invalid');
            var msg = el.getAttribute('data-error-msg') || labelFor(el) || el.placeholder || 'Field';
            var err = document.createElement('div');
            err.className = 'field-error';
            err.textContent = msg + ' is required';
            (el.closest('.fld') || el.parentNode).appendChild(err);
        }

        function clearFieldError(el) {
            if (!el || !el.classList) return;
            el.classList.remove('is-invalid');
            var parent = el.closest('.fld') || el.parentNode;
            var err = parent && parent.querySelector('.field-error');
            if (err) err.remove();
        }

        function clearErrors(form) {
            form.querySelectorAll('.is-invalid').forEach(function (el) {
                el.classList.remove('is-invalid');
            });
            form.querySelectorAll('.field-error').forEach(function (el) {
                el.remove();
            });
        }

        function labelFor(el) {
            var fld = el.closest('.fld');
            if (fld) {
                var lbl = fld.querySelector('.flbl .km,.flbl .en,.flbl');
                if (lbl) return lbl.textContent.trim().replace(/\s*\*\s*$/, '');
            }
            if (el.id) {
                var label = document.querySelector('label[for="' + el.id + '"]');
                if (label) return label.textContent.trim().replace(/\s*\*\s*$/, '');
            }
            return null;
        }
    })();
</script>

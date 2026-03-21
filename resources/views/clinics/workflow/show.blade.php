@extends('clinics.layout.app')

@section('title', $visit->patient_name . ' — ' . $visit->code)

@section('content')

    @php
        $skipUrl    = url('/workflow/' . $visit->code . '/' . $currentStep . '/skip');
        $prevUrl    = $prevStep ? url('/workflow/' . $visit->code . '/' . $prevStep) : null;
        $nextUrl    = $nextStep ? url('/workflow/' . $visit->code . '/' . $nextStep) : null;
        $totalSteps = count($steps);
    @endphp

    <x-workflow.header :visit="$visit"/>

    <x-workflow.step-bar
        :visit="$visit"
        :steps="$steps"
        :currentStep="$currentStep"
    />

    <div class="row g-3">

        <div class="col-12 col-lg-9">
            <div class="card-emr">

                @include('clinics.workflow.steps.' . $currentStep, [
                    'visit'       => $visit,
                    'steps'       => $steps,
                    'currentStep' => $currentStep,
                    'stepIdx'     => array_search($currentStep, array_column($steps, 'id')),
                ])

                <x-workflow.action-bar
                    :form-id="'stepForm'"
                    :skip-route="$skipUrl"
                    :prev-step-route="$prevUrl"
                    :next-step-route="$nextUrl"
                    :step-number="$stepNumber"
                    :total-steps="$totalSteps"
                />

            </div>
        </div>

        <div class="col-lg-3 d-none d-lg-block" id="vssCol">
            @include('clinics.workflow.visit-summary', [
                'visit'       => $visit,
                'steps'       => $steps,
                'currentStep' => $currentStep,
            ])
        </div>

    </div>

@endsection

{{--
    Shared client-side validation for ALL workflow step forms.
    - No @push needed — lives here once, applies to every step.
    - Form has novalidate so browser default bubbles don't show.
    - On submit: checks required inputs/selects/textareas.
    - Invalid fields get .is-invalid class (Bootstrap red border).
    - An inline error message appears below each invalid field.
    - First invalid field is scrolled into view and focused.
    - Error clears as soon as the user types / changes the field.
--}}
<script>
(function () {

    /* ── Styles for inline error messages ─────────────────────── */
    var style = document.createElement('style');
    style.textContent = [
        '.field-error{font-size:11px;color:#e74c3c;margin-top:3px;display:flex;align-items:center;gap:4px}',
        '.field-error::before{content:"⚠";font-size:10px}',
        '.form-control.is-invalid,.form-select.is-invalid{border-color:#e74c3c!important;background-image:none!important}',
        '.form-control.is-invalid:focus,.form-select.is-invalid:focus{box-shadow:0 0 0 3px rgba(231,76,60,.15)!important}',
    ].join('');
    document.head.appendChild(style);

    /* ── Attach on DOMContentLoaded ────────────────────────────── */
    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('stepForm');
        if (!form) return;

        /* Disable native browser validation bubbles */
        form.setAttribute('novalidate', '');

        /* ── Submit handler ──────────────────────────────────── */
        form.addEventListener('submit', function (e) {
            clearErrors(form);

            var invalid = validateForm(form);
            if (invalid.length === 0) return; /* all good — submit */

            e.preventDefault();

            invalid.forEach(function (el) { showError(el); });

            /* Scroll first invalid field into view */
            invalid[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            invalid[0].focus({ preventScroll: true });
        });

        /* ── Clear error on user input ───────────────────────── */
        form.addEventListener('input',  function (e) { clearFieldError(e.target); });
        form.addEventListener('change', function (e) { clearFieldError(e.target); });
    });

    /* ── Validate all required fields ─────────────────────────── */
    function validateForm(form) {
        var invalid = [];
        var fields  = form.querySelectorAll('input[required], select[required], textarea[required]');

        fields.forEach(function (el) {
            /* Skip hidden / disabled / readonly */
            if (el.disabled || el.readOnly || el.type === 'hidden') return;
            /* Skip fields inside display:none parents */
            if (!el.offsetParent) return;

            var val = el.value.trim();
            if (!val) invalid.push(el);
        });

        return invalid;
    }

    /* ── Show error below a field ──────────────────────────────── */
    function showError(el) {
        el.classList.add('is-invalid');

        /* Build message from data-error-msg attr, placeholder, or generic */
        var msg = el.getAttribute('data-error-msg')
            || labelFor(el)
            || el.placeholder
            || 'This field is required';

        var err = document.createElement('div');
        err.className   = 'field-error';
        err.textContent = msg + ' is required';
        err.setAttribute('data-for', el.name || el.id);

        /* Insert after the field, or after its wrapper if inside .fld */
        var parent = el.closest('.fld') || el.parentNode;
        parent.appendChild(err);
    }

    /* ── Clear one field's error ───────────────────────────────── */
    function clearFieldError(el) {
        if (!el || !el.classList) return;
        el.classList.remove('is-invalid');

        var parent = el.closest('.fld') || el.parentNode;
        var err    = parent.querySelector('.field-error');
        if (err) err.remove();
    }

    /* ── Clear all errors in form ──────────────────────────────── */
    function clearErrors(form) {
        form.querySelectorAll('.is-invalid').forEach(function (el) {
            el.classList.remove('is-invalid');
        });
        form.querySelectorAll('.field-error').forEach(function (el) {
            el.remove();
        });
    }

    /* ── Find label text for an input ─────────────────────────── */
    function labelFor(el) {
        /* Check for .flbl sibling inside .fld */
        var fld = el.closest('.fld');
        if (fld) {
            var lbl = fld.querySelector('.flbl .km, .flbl .en, .flbl');
            if (lbl) return lbl.textContent.trim().replace(/\s*\*\s*$/, '');
        }
        /* Check for <label for="id"> */
        if (el.id) {
            var label = document.querySelector('label[for="' + el.id + '"]');
            if (label) return label.textContent.trim().replace(/\s*\*\s*$/, '');
        }
        return null;
    }

})();
</script>

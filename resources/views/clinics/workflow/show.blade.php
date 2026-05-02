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

{{-- ── Auto-save system ──────────────────────────────────────────────── --}}
<script>
(function () {
    var SAVE_URL    = '{{ route("workflow.step.save", [$visit->code, $currentStep]) }}';
    var CSRF        = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    var saveTimer   = null;
    var formDirty   = false;
    var lastSaved   = null;
    var AUTO_MS     = 90000; // 90 seconds
    var DEBOUNCE_MS = 3000;  // 3 seconds after last keystroke

    function getIndicator() { return document.getElementById('wfAutoSaveIndicator'); }

    function showIndicator(state, msg) {
        var el = getIndicator();
        if (!el) return;
        var states = {
            saving: { color:'#4154f1', icon:'bi-cloud-arrow-up', text: msg || 'Saving…' },
            saved:  { color:'#2eca6a', icon:'bi-cloud-check',    text: msg || 'Auto-saved' },
            error:  { color:'#e74c3c', icon:'bi-cloud-slash',    text: msg || 'Auto-save failed' },
            dirty:  { color:'#f59e0b', icon:'bi-pencil',         text: msg || 'Unsaved changes' },
        };
        var cfg = states[state] || states.saved;
        el.style.display = 'inline-flex';
        el.style.color   = cfg.color;
        el.innerHTML = '<i class="bi ' + cfg.icon + '" style="font-size:11px"></i> <span>' + cfg.text + '</span>';
    }

    function autoSave() {
        var form = document.getElementById('stepForm');
        if (!form || !formDirty) return;
        showIndicator('saving');
        var data = new FormData(form);
        data.set('_method', 'PATCH');
        fetch(SAVE_URL, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
            body: data
        })
        .then(function(r) {
            if (r.ok) {
                formDirty = false;
                lastSaved = new Date();
                var t = lastSaved.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                showIndicator('saved', 'Saved ' + t);
            } else {
                showIndicator('error');
            }
        })
        .catch(function() { showIndicator('error'); });
    }

    function scheduleAutoSave() {
        clearTimeout(saveTimer);
        formDirty = true;
        showIndicator('dirty');
        // Debounce: save 3s after last change
        saveTimer = setTimeout(autoSave, DEBOUNCE_MS);
    }

    // Periodic save every 90s
    setInterval(function() {
        if (formDirty) autoSave();
    }, AUTO_MS);

    // Warn on unload if dirty
    window.addEventListener('beforeunload', function(e) {
        if (formDirty) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    // Don't warn if submitting the form intentionally
    document.addEventListener('DOMContentLoaded', function() {
        var form = document.getElementById('stepForm');
        if (!form) return;
        form.addEventListener('submit', function() { formDirty = false; });
        form.addEventListener('input',  scheduleAutoSave);
        form.addEventListener('change', scheduleAutoSave);
    });
})();
</script>

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

        // ── Keyboard shortcuts ──────────────────────────────────────────
        document.addEventListener('keydown', function (e) {
            // Ctrl+S / Cmd+S → submit the step form
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                var form = document.getElementById('stepForm');
                if (form) {
                    var btn = document.querySelector('[type="submit"][form="stepForm"]');
                    if (btn) btn.click();
                    else form.requestSubmit ? form.requestSubmit() : form.submit();
                }
            }

            // Arrow keys for prev/next (only when NOT in a text/textarea/select)
            var tag = document.activeElement?.tagName?.toLowerCase();
            if (tag === 'input' || tag === 'textarea' || tag === 'select') return;

            @if($prevUrl)
            if (e.altKey && e.key === 'ArrowLeft') {
                e.preventDefault();
                window.location.href = '{{ $prevUrl }}';
            }
            @endif
            @if($nextUrl)
            if (e.altKey && e.key === 'ArrowRight') {
                e.preventDefault();
                window.location.href = '{{ $nextUrl }}';
            }
            @endif
        });

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

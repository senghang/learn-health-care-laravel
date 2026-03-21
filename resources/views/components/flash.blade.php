{{--
    Flash component — renders hidden divs that clinic.js picks up and
    converts into toast notifications.

    Supports two session key patterns:
      1. workflow pattern: session('flash') + session('flash_type')  →  ok | wrn | err
      2. legacy pattern:   session('success') | session('error') | session('warning')
--}}

@if(session('flash'))
    <div data-flash="{{ session('flash') }}"
         data-type="{{ session('flash_type', 'ok') }}"
         style="display:none"></div>
@endif

@if(session('success'))
    <div data-flash="{{ session('success') }}"
         data-type="ok"
         style="display:none"></div>
@endif

@if(session('error'))
    <div data-flash="{{ session('error') }}"
         data-type="err"
         style="display:none"></div>
@endif

@if(session('warning'))
    <div data-flash="{{ session('warning') }}"
         data-type="wrn"
         style="display:none"></div>
@endif

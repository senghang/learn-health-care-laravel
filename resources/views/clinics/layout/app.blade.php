<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MediFlow EMR') — {{ currentClinic()?->name ?? 'Clinic' }}</title>

    {{-- Bootstrap + Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"/>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Hanuman:wght@300;400;700&family=Nunito:wght@400;500;600;700;800&display=swap"/>

    {{-- EMR CSS --}}
    @vite(['resources/css/clinic.css'])
    <link rel="stylesheet" href="{{ asset('css/ux-enhancements.css') }}">

    {{-- Sidebar + Header CSS --}}
    <link rel="stylesheet" href="{{ asset('css/sidebar-header.css') }}">

    {{-- Visual enhancement layer: operations-first, live indicators, toasts --}}
    @if(file_exists(public_path('css/emr-enhance.css')))
        <link rel="stylesheet" href="{{ asset('css/emr-enhance.css') }}">
    @endif

    {{-- App layout utilities: reports, modals, settings tabs, print --}}
    <link rel="stylesheet" href="{{ asset('css/app-layout.css') }}">

    @yield('style')
    @stack('styles')
</head>
<body>

{{-- Sidebar overlay (mobile) --}}
<div id="sidebarOverlay" onclick="closeSidebar()"></div>

{{-- Sidebar --}}
@include('clinics.layout.sidebar')

{{-- Topbar --}}
@include('clinics.layout.header')

{{-- Main content --}}
<main id="main">
    <div class="main-body">
        @yield('content')
    </div>
</main>

{{-- Mobile Bottom Nav --}}
@include('clinics.layout.bottom-nav')

{{-- Toast container --}}
<div class="toast-wrap" id="toastWrap"></div>

{{-- Flash toast (from session) --}}
<x-flash/>

{{-- Global confirm dialog --}}
<div id="emrConfirmModal" class="emr-modal-wrap" role="dialog" aria-modal="true" aria-labelledby="emrConfirmTitle">
    <div class="emr-modal modal-sm">
        <div class="emr-modal-hd">
            <i id="emrConfirmIcon" class="bi bi-question-circle-fill" style="font-size:17px;flex-shrink:0;color:#4154f1"></i>
            <div class="emr-modal-title" id="emrConfirmTitle">Confirm</div>
            <button type="button" id="emrConfirmClose" class="emr-modal-close" aria-label="Close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="emr-modal-body" style="padding:20px 24px">
            <p id="emrConfirmMsg" style="font-size:13.5px;color:#374151;margin:0;line-height:1.65"></p>
        </div>
        <div class="emr-modal-ft">
            <button type="button" id="emrConfirmNo" class="btn btn-sm btn-outline-secondary">Cancel</button>
            <button type="button" id="emrConfirmOk" class="btn btn-sm btn-danger">Delete</button>
        </div>
    </div>
</div>

{{-- Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@vite(['resources/js/clinic.js'])

@stack('scripts')
</body>
</html>

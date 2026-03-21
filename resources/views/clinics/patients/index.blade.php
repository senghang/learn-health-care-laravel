@extends('clinics.layout.app')
@section('content')
    <div class="pg" id="pg-visits">
        <div class="pg-header d-flex align-items-start justify-content-between flex-wrap gap-2">
            <div>
                <h1 class="pg-title">ការចូលព្យាបាល <small>/ Patient Visits</small></h1>
                <div class="breadcrumb-row"><a href="#"
                                               onclick="showPage('dashboard')">ដើម</a><span>›</span><span>Visits</span>
                </div>
            </div>
            <a href="{{ route('workflow.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i><span>ថ្មី / New</span>
            </a>
        </div>
        <!-- Filter Bar -->
        <div class="card-emr">
            <div class="card-bd">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-sm-6 col-md-4">
                        <label class="flbl"><span class="km">ស្វែងរក</span><span class="en">/ Search</span></label>
                        <div style="position:relative">
                            <i class="bi bi-search"
                               style="position:absolute;left:11px;top:50%;transform:translateY(-50%);color:#bbb;font-size:13px"></i>
                            <input type="text" class="form-control" style="padding-left:32px"
                                   placeholder="ឈ្មោះ ឬ លេខ…"/>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3 col-md-2">
                        <label class="flbl"><span class="km">ប្រភេទ</span><span class="en">/ Type</span></label>
                        <select class="form-select">
                            <option>ទាំងអស់</option>
                            <option>OPD</option>
                            <option>IPD</option>
                        </select>
                    </div>
                    <div class="col-6 col-sm-3 col-md-2">
                        <label class="flbl"><span class="km">ស្ថានភាព</span><span class="en">/ Status</span></label>
                        <select class="form-select">
                            <option>ទាំងអស់</option>
                            <option>Active</option>
                            <option>Done</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="flbl"><span class="km">ថ្ងៃ</span><span class="en">/ Date</span></label>
                        <input type="date" class="form-control"/>
                    </div>
                    <div class="col-6 col-md-2">
                        <button class="btn btn-primary btn-w100"><i class="bi bi-funnel-fill"></i>តម្រង</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Visit Rows -->
        <div class="visit-row" onclick="openVisit('V420650')">
            <div class="v-avatar">JS</div>
            <div class="v-info">
                <div class="v-name">Smith, John</div>
                <div class="v-meta">PT20250317001 · V420650 · 17/03/2025 09:30</div>
            </div>
            <div class="v-badges">
                <span class="badge-s b-opd">OPD</span>
                <span class="badge-s b-active d-none d-sm-inline-flex"><i class="bi bi-circle-fill"
                                                                          style="font-size:7px"></i>Active</span>
            </div>
            <i class="bi bi-chevron-right" style="color:#ddd;flex-shrink:0"></i>
        </div>
        <div class="visit-row" onclick="openVisit('V420651')">
            <div class="v-avatar" style="background:linear-gradient(135deg,#2eca6a,#17a04a)">KS</div>
            <div class="v-info">
                <div class="v-name">Kim, Sopheap</div>
                <div class="v-meta">PT20250318002 · V420651 · 18/03/2025 10:15</div>
            </div>
            <div class="v-badges">
                <span class="badge-s b-opd">OPD</span>
                <span class="badge-s b-done d-none d-sm-inline-flex">✓ Done</span>
            </div>
            <i class="bi bi-chevron-right" style="color:#ddd;flex-shrink:0"></i>
        </div>
        <div class="visit-row" onclick="openVisit('V420652')">
            <div class="v-avatar" style="background:linear-gradient(135deg,#ff771d,#e65a00)">CN</div>
            <div class="v-info">
                <div class="v-name">Chan, Narith</div>
                <div class="v-meta">PT20250315003 · V420652 · 15/03/2025 14:00</div>
            </div>
            <div class="v-badges">
                <span class="badge-s b-ipd">IPD</span>
                <span class="badge-s b-active d-none d-sm-inline-flex"><i class="bi bi-circle-fill"
                                                                          style="font-size:7px"></i>Active</span>
            </div>
            <i class="bi bi-chevron-right" style="color:#ddd;flex-shrink:0"></i>
        </div>
    </div>
    <script>
        function openVisit(id) {
            alert()
        }
    </script>
@endsection




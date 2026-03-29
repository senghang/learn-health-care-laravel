@extends('clinics.layout.app')
@section('title', __('app.store_settings'))
@section('content')

<x-page-header :title="__('app.system_setup')" :subtitle="__('app.store_settings')"
    :breadcrumbs="[
        ['label' => __('app.nav.dashboard'), 'url' => route('dashboard')],
        ['label' => __('app.nav.settings'),  'url' => route('settings.general')],
        ['label' => __('app.store_settings')],
    ]">
</x-page-header>

{{-- Flash messages --}}
@if(session('flash'))
<div class="flex items-center gap-2 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4 text-sm">
    <i class="bi bi-check-circle-fill"></i> {{ session('flash') }}
</div>
@endif
@if(session('flash_error'))
<div class="flex items-center gap-2 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
    <i class="bi bi-exclamation-triangle-fill"></i> {{ session('flash_error') }}
</div>
@endif

<div class="grid grid-cols-12 gap-4">

    {{-- ── Left: Filter + Add ──────────────────────────────────────────────── --}}
    <div class="col-span-12 lg:col-span-3">

        {{-- Filter --}}
        <div class="bg-white rounded-xl shadow-sm border border-blue-50 mb-4">
            <div class="px-4 py-3 border-b border-blue-50 bg-slate-50 rounded-t-xl">
                <div class="font-bold text-sm text-blue-900 flex items-center gap-2">
                    <i class="bi bi-funnel-fill text-blue-600"></i> {{ __('app.filter') }}
                </div>
            </div>
            <div class="p-4">
                <form method="GET">
                    <div class="mb-3">
                        <label class="block text-xs font-semibold text-slate-500 mb-1">{{ __('app.search') }}</label>
                        <input type="text" name="search" value="{{ $search }}"
                               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="{{ __('app.search') }}…"/>
                    </div>
                    <div class="mb-3">
                        <label class="block text-xs font-semibold text-slate-500 mb-1">{{ __('app.setting_group') }}</label>
                        <select name="group" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                            <option value="">{{ __('app.all') }}</option>
                            @foreach($groups as $g)
                                <option value="{{ $g }}" {{ $filterGroup === $g ? 'selected' : '' }}>{{ $g }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700 transition">
                        <i class="bi bi-search"></i> {{ __('app.search') }}
                    </button>
                    @if($search || $filterGroup)
                        <a href="{{ route('settings.store-settings') }}" class="block text-center text-blue-600 text-xs mt-2 hover:underline">
                            {{ __('app.clear') }}
                        </a>
                    @endif
                </form>
            </div>
        </div>

        {{-- Add new --}}
        <div class="bg-white rounded-xl shadow-sm border border-blue-50">
            <div class="px-4 py-3 border-b border-blue-50 bg-slate-50 rounded-t-xl">
                <div class="font-bold text-sm text-blue-900 flex items-center gap-2">
                    <i class="bi bi-plus-circle-fill text-green-600"></i> {{ __('app.add') }} {{ __('app.new') }}
                </div>
            </div>
            <div class="p-4">
                <form method="POST" action="{{ route('settings.store-settings.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="block text-xs font-semibold text-slate-500 mb-1">{{ __('app.setting_group') }} *</label>
                        <input type="text" name="group" required list="groupList"
                               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                               placeholder="e.g. visit_type"/>
                        <datalist id="groupList">
                            @foreach($groups as $g)<option value="{{ $g }}">@endforeach
                        </datalist>
                    </div>
                    <div class="mb-3">
                        <label class="block text-xs font-semibold text-slate-500 mb-1">{{ __('app.setting_key') }} *</label>
                        <input type="text" name="key" required
                               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                               placeholder="e.g. OPD"/>
                    </div>
                    <div class="grid grid-cols-2 gap-2 mb-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">{{ __('app.label_en') }}</label>
                            <input type="text" name="label_en"
                                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"/>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">{{ __('app.label_km') }}</label>
                            <input type="text" name="label_km"
                                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"/>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2 mb-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">{{ __('app.color') }}</label>
                            <input type="color" name="color" value="#4154f1"
                                   class="w-full h-9 border border-slate-300 rounded-lg cursor-pointer"/>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">{{ __('app.icon') }}</label>
                            <input type="text" name="icon"
                                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                                   placeholder="🏥 or bi-heart"/>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="block text-xs font-semibold text-slate-500 mb-1">{{ __('app.sort_order') }}</label>
                        <input type="number" name="sort_order" value="0" min="0"
                               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"/>
                    </div>
                    <button type="submit" class="w-full bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-green-700 transition">
                        <i class="bi bi-plus-circle"></i> {{ __('app.save') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Right: Settings Table ───────────────────────────────────────────── --}}
    <div class="col-span-12 lg:col-span-9">
        @forelse($settings as $group => $items)
        <div class="bg-white rounded-xl shadow-sm border border-blue-50 mb-4">
            <div class="px-4 py-3 border-b border-blue-50 bg-slate-50 rounded-t-xl flex items-center justify-between">
                <div class="font-bold text-sm text-blue-900 flex items-center gap-2">
                    <i class="bi bi-gear-fill text-blue-600"></i>
                    {{ $group }}
                    <span class="bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded-full font-semibold">{{ $items->count() }}</span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider">
                            <th class="px-4 py-2 text-left font-semibold">{{ __('app.setting_key') }}</th>
                            <th class="px-4 py-2 text-left font-semibold">{{ __('app.label_en') }}</th>
                            <th class="px-4 py-2 text-left font-semibold">{{ __('app.label_km') }}</th>
                            <th class="px-4 py-2 text-center font-semibold">{{ __('app.color') }}</th>
                            <th class="px-4 py-2 text-center font-semibold">{{ __('app.icon') }}</th>
                            <th class="px-4 py-2 text-center font-semibold">{{ __('app.sort_order') }}</th>
                            <th class="px-4 py-2 text-center font-semibold">{{ __('app.status') }}</th>
                            <th class="px-4 py-2 text-center font-semibold">{{ __('app.is_system') }}</th>
                            <th class="px-4 py-2 text-center font-semibold">{{ __('app.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($items as $item)
                        <tr class="hover:bg-blue-50/50 transition" id="row-{{ $item->id }}">
                            <td class="px-4 py-2.5">
                                <code class="bg-slate-100 text-blue-700 px-2 py-0.5 rounded text-xs font-mono">{{ $item->key }}</code>
                            </td>
                            <td class="px-4 py-2.5 text-slate-700">{{ $item->label_en ?? '—' }}</td>
                            <td class="px-4 py-2.5 text-slate-700">{{ $item->label_km ?? '—' }}</td>
                            <td class="px-4 py-2.5 text-center">
                                @if($item->color)
                                    <span class="inline-block w-5 h-5 rounded-full border border-slate-200" style="background:{{ $item->color }}"></span>
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-center text-base">{{ $item->icon ?? '—' }}</td>
                            <td class="px-4 py-2.5 text-center text-slate-400 text-xs">{{ $item->sort_order }}</td>
                            <td class="px-4 py-2.5 text-center">
                                @if($item->is_active)
                                    <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 text-xs px-2 py-0.5 rounded-full font-semibold">
                                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span> {{ __('app.active') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 bg-slate-100 text-slate-500 text-xs px-2 py-0.5 rounded-full font-semibold">
                                        <span class="w-1.5 h-1.5 bg-slate-400 rounded-full"></span> {{ __('app.inactive') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-center">
                                @if($item->is_system && $item->clinic_id === null)
                                    <span class="bg-amber-100 text-amber-700 text-xs px-2 py-0.5 rounded-full font-semibold">{{ __('app.is_system') }}</span>
                                @else
                                    <span class="text-slate-300 text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button type="button" onclick="editRow({{ $item->id }})"
                                            class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-100 transition" title="{{ __('app.edit') }}">
                                        <i class="bi bi-pencil text-xs"></i>
                                    </button>
                                    @if(!($item->is_system && $item->clinic_id === null))
                                    <form method="POST" action="{{ route('settings.store-settings.destroy', $item->id) }}"
                                          class="inline" onsubmit="return confirm('{{ __('app.confirm') }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg text-red-500 hover:bg-red-100 transition" title="{{ __('app.delete') }}">
                                            <i class="bi bi-trash3 text-xs"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl shadow-sm border border-blue-50 p-12 text-center">
            <div class="text-4xl mb-3 opacity-30">⚙️</div>
            <div class="font-semibold text-slate-500">{{ __('app.none') }}</div>
            <div class="text-xs text-slate-400 mt-1">Run: php artisan db:seed --class=StoreSettingsSeeder</div>
        </div>
        @endforelse
    </div>
</div>

{{-- ── Edit Modal ──────────────────────────────────────────────────────────── --}}
<div id="editModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40" onclick="closeEdit()"></div>
    <div class="absolute right-0 top-0 h-full w-full max-w-md bg-white shadow-xl overflow-y-auto">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white z-10">
            <h3 class="font-bold text-blue-900 flex items-center gap-2">
                <i class="bi bi-pencil-square text-blue-600"></i> {{ __('app.edit') }} {{ __('app.store_settings') }}
            </h3>
            <button onclick="closeEdit()" class="text-slate-400 hover:text-slate-700 text-xl">&times;</button>
        </div>
        <form method="POST" id="editForm" class="p-5">
            @csrf @method('PATCH')
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">{{ __('app.label_en') }}</label>
                    <input type="text" name="label_en" id="edit_label_en"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"/>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">{{ __('app.label_km') }}</label>
                    <input type="text" name="label_km" id="edit_label_km"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"/>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">{{ __('app.color') }}</label>
                        <input type="color" name="color" id="edit_color"
                               class="w-full h-9 border border-slate-300 rounded-lg cursor-pointer"/>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">{{ __('app.icon') }}</label>
                        <input type="text" name="icon" id="edit_icon"
                               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"/>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">{{ __('app.sort_order') }}</label>
                    <input type="number" name="sort_order" id="edit_sort_order" min="0"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"/>
                </div>
                <div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_active" value="0"/>
                        <input type="checkbox" name="is_active" value="1" id="edit_is_active"
                               class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"/>
                        <span class="text-sm text-slate-700">{{ __('app.active') }}</span>
                    </label>
                </div>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2.5 rounded-lg text-sm font-semibold hover:bg-blue-700 transition mt-6">
                <i class="bi bi-check2-circle"></i> {{ __('app.update') }}
            </button>
        </form>
    </div>
</div>

<script>
// Setting data map populated from server
const settingsData = @json(
    $settings->flatten()->mapWithKeys(fn($s) => [$s->id => [
        'label_en' => $s->label_en,
        'label_km' => $s->label_km,
        'color' => $s->color ?? '#4154f1',
        'icon' => $s->icon,
        'sort_order' => $s->sort_order,
        'is_active' => $s->is_active,
    ]])
);

function editRow(id) {
    const d = settingsData[id];
    if (!d) return;
    document.getElementById('editForm').action = '{{ route("settings.store-settings.update", "") }}/' + id;
    document.getElementById('edit_label_en').value = d.label_en || '';
    document.getElementById('edit_label_km').value = d.label_km || '';
    document.getElementById('edit_color').value = d.color || '#4154f1';
    document.getElementById('edit_icon').value = d.icon || '';
    document.getElementById('edit_sort_order').value = d.sort_order || 0;
    document.getElementById('edit_is_active').checked = d.is_active;
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEdit() {
    document.getElementById('editModal').classList.add('hidden');
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeEdit(); });
</script>
@endsection

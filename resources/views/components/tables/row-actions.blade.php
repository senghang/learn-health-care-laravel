{{--
    Table row action menu — a compact dropdown for edit/delete/view per row.
    Uses <x-ui.dropdown> under the hood; slot items should be <x-ui.dropdown-item>.

    <x-tables.row-actions>
        <x-ui.dropdown-item href="{{ route('patients.show', $p) }}" icon="bi-eye">View</x-ui.dropdown-item>
        <x-ui.dropdown-item href="{{ route('patients.edit', $p) }}" icon="bi-pencil">Edit</x-ui.dropdown-item>
        <x-ui.dropdown-divider />
        <x-ui.dropdown-item
            href="{{ route('patients.destroy', $p) }}"
            method="DELETE"
            icon="bi-trash"
            variant="danger">Delete</x-ui.dropdown-item>
    </x-tables.row-actions>

    Props:
        label — screen-reader label for the trigger (default: "Actions")
--}}
@props(['label' => 'Actions'])

<x-ui.dropdown align="right" {{ $attributes }}>
    <x-slot:trigger>
        <button type="button"
                class="w-7 h-7 flex items-center justify-center rounded-lg text-[#94a3b8]
                       hover:bg-[#f1f5f9] hover:text-[#4154f1] transition-colors"
                aria-label="{{ $label }}">
            <i class="bi bi-three-dots-vertical" style="font-size:14px" aria-hidden="true"></i>
        </button>
    </x-slot:trigger>

    {{ $slot }}
</x-ui.dropdown>

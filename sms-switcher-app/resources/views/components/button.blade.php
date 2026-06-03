@props(['color' => 'blue', 'type' => 'submit', 'id' => null])

@php
    $colors = [
        'blue'  => 'bg-blue-500 hover:bg-blue-600',
        'green' => 'bg-green-500 hover:bg-green-600',
    ];
@endphp

<button
    type="{{ $type }}"
    id="{{ $id }}"
    class="w-full text-white font-medium py-2.5 px-4 rounded-lg transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed {{ $colors[$color] ?? $colors['blue'] }}"
    {{ $attributes }}
>
    {{ $slot }}
</button>
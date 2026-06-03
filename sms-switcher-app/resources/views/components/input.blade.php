@props(['label', 'name', 'placeholder' => '', 'required' => false, 'pattern' => null])

<div>
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1">
        {{ $label }}
    </label>
    <input
        type="text"
        name="{{ $name }}"
        id="{{ $name }}"
        placeholder="{{ $placeholder }}"
        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        {{ $required ? 'required' : '' }}
        {{ $pattern ? "pattern={$pattern}" : '' }}
        {{ $attributes }}
    />
</div>
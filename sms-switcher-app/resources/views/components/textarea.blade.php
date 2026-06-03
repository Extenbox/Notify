@props(['label', 'name', 'placeholder' => '', 'required' => false, 'maxlength' => null])

<div>
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1">
        {{ $label }}
    </label>
    <textarea
        name="{{ $name }}"
        id="{{ $name }}"
        rows="3"
        placeholder="{{ $placeholder }}"
        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        {{ $required ? 'required' : '' }}
        {{ $maxlength ? "maxlength={$maxlength}" : '' }}
        {{ $attributes }}
    ></textarea>
</div>
@props(['label', 'name', 'type' => 'text', 'placeholder' => '', 'required' => false, 'autocomplete' => null, 'autofocus' => false, 'value' => null])

@php
    $inputClass = 'w-full rounded-lg border border-zinc-200 bg-white py-3 text-sm text-brand-black placeholder:text-zinc-400 focus:border-brand-red focus:ring-2 focus:ring-brand-red/15 outline-none transition';
@endphp

<div>
    <label for="{{ $name }}" class="block text-sm font-medium text-zinc-700 mb-1.5">
        {{ $label }} @if($required)<span class="text-brand-red">*</span>@endif
    </label>
    <div class="relative">
        @if(isset($icon))
        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400">
            {{ $icon }}
        </div>
        @endif
        <input
            id="{{ $name }}"
            name="{{ $name }}"
            type="{{ $type }}"
            value="{{ $value ?? old($name) }}"
            placeholder="{{ $placeholder }}"
            @if($required) required @endif
            @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
            @if($autofocus) autofocus @endif
            class="{{ $inputClass }} {{ isset($icon) ? 'pl-11 pr-4' : 'px-4' }}"
        >
    </div>
    @error($name)
    <p class="mt-1.5 text-xs text-brand-red">{{ $message }}</p>
    @enderror
</div>

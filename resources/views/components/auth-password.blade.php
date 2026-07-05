@props(['name' => 'password', 'label' => 'Password', 'placeholder' => 'Enter your password', 'autocomplete' => 'current-password', 'required' => true])

<div x-data="{ show: false }">
    <label for="{{ $name }}" class="block text-sm font-medium text-zinc-700 mb-1.5">
        {{ $label }} @if($required)<span class="text-brand-red">*</span>@endif
    </label>
    <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400">
            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
        </div>
        <input
            id="{{ $name }}"
            name="{{ $name }}"
            :type="show ? 'text' : 'password'"
            @if($required) required @endif
            autocomplete="{{ $autocomplete }}"
            placeholder="{{ $placeholder }}"
            class="w-full rounded-lg border border-zinc-200 bg-white py-3 pl-11 pr-11 text-sm text-brand-black placeholder:text-zinc-400 focus:border-brand-red focus:ring-2 focus:ring-brand-red/15 outline-none transition"
        >
        <button type="button" @click="show = !show" tabindex="-1" class="absolute inset-y-0 right-0 pr-3 flex items-center text-zinc-400 hover:text-brand-black transition-colors" aria-label="Toggle password visibility">
            <svg x-show="!show" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            <svg x-show="show" x-cloak class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
        </button>
    </div>
    @error($name)
    <p class="mt-1.5 text-xs text-brand-red">{{ $message }}</p>
    @enderror
</div>

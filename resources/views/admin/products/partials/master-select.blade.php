@props(['name', 'label', 'options' => [], 'value' => '', 'required' => false, 'master' => '', 'storeUrl' => '', 'fields' => '[]', 'id' => null])

@php $selectId = $id ?? $name; @endphp

<div data-master-wrap data-store-url="{{ $storeUrl }}" data-master-label="Add {{ $label }}" data-fields="{{ $fields }}">
    <label class="block text-sm font-medium text-slate-700 mb-1">{{ $label }} @if($required)<span class="text-red-500">*</span>@endif</label>
    <div class="flex gap-2">
        <select name="{{ $name }}" id="{{ $selectId }}" {{ $required ? 'required' : '' }} class="admin-input text-sm flex-1 py-2.5 @error($name) admin-input-error @enderror">
            <option value="">Select {{ $label }}</option>
            @foreach($options as $opt)
                <option value="{{ $opt->id ?? $opt['id'] }}" @selected(old($name, $value) == ($opt->id ?? $opt['id']))>{{ $opt->name ?? $opt['name'] }}</option>
            @endforeach
        </select>
        @if($storeUrl)
        <button type="button" data-quick-add class="shrink-0 w-10 h-10 flex items-center justify-center bg-indigo-100 text-indigo-700 hover:bg-indigo-200 rounded-xl font-bold text-lg" title="Add {{ $label }}">+</button>
        @endif
    </div>
    @error($name)
        <p class="admin-field-error">{{ $message }}</p>
    @enderror
</div>

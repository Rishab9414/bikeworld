@extends('admin.layouts.app')
@php $c = $customer; $isEdit = $c->exists; @endphp
@section('title', $isEdit ? 'Edit Customer' : 'Add Customer')
@section('page-title', $isEdit ? 'Edit Customer' : 'Add Customer')

@section('content')
@if($errors->any())
<div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4">
    <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li class="text-sm text-red-700">{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form action="{{ $isEdit ? route('admin.customers.update', $c) : route('admin.customers.store') }}" method="POST" class="max-w-3xl">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
        <h3 class="font-bold text-lg text-slate-900">Basic Information</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">First Name <span class="text-red-500">*</span></label>
                <input name="first_name" value="{{ old('first_name', $c->first_name) }}" required class="admin-input text-sm @error('first_name') admin-input-error @enderror">
                @error('first_name')<p class="admin-field-error">{{ $message }}</p>@enderror
            </div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Last Name</label><input name="last_name" value="{{ old('last_name', $c->last_name) }}" class="admin-input text-sm"></div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Email <span class="text-red-500">*</span></label>
                <input name="email" type="email" value="{{ old('email', $c->email) }}" required class="admin-input text-sm @error('email') admin-input-error @enderror">
                @error('email')<p class="admin-field-error">{{ $message }}</p>@enderror
            </div>
            <div class="flex gap-2">
                <div class="w-24"><label class="block text-sm font-medium text-slate-700 mb-1">Code</label><input name="country_code" value="{{ old('country_code', $c->country_code ?? '+91') }}" class="admin-input text-sm"></div>
                <div class="flex-1">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Mobile <span class="text-red-500">*</span></label>
                    <input name="mobile" value="{{ old('mobile', $c->mobile) }}" required class="admin-input text-sm @error('mobile') admin-input-error @enderror">
                    @error('mobile')<p class="admin-field-error">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Password {{ $isEdit ? '(leave blank to keep)' : '*' }}</label>
                <input name="password" type="password" {{ $isEdit ? '' : 'required' }} class="admin-input text-sm @error('password') admin-input-error @enderror">
                @error('password')<p class="admin-field-error">{{ $message }}</p>@enderror
            </div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Gender</label>
                <select name="gender" class="admin-input text-sm"><option value="">—</option>
                    @foreach(['male'=>'Male','female'=>'Female','other'=>'Other'] as $v=>$l)
                    <option value="{{ $v }}" @selected(old('gender',$c->gender)===$v)>{{ $l }}</option>@endforeach
                </select>
            </div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Date of Birth</label><input name="date_of_birth" type="date" value="{{ old('date_of_birth', $c->date_of_birth?->format('Y-m-d')) }}" class="admin-input text-sm"></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Anniversary</label><input name="anniversary_date" type="date" value="{{ old('anniversary_date', $c->anniversary_date?->format('Y-m-d')) }}" class="admin-input text-sm"></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Registration Source</label>
                <select name="registration_source" class="admin-input text-sm">@foreach(['website','app','admin'] as $s)<option value="{{ $s }}" @selected(old('registration_source',$c->registration_source??'admin')===$s)>{{ ucfirst($s) }}</option>@endforeach</select>
            </div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Login Type</label>
                <select name="login_type" class="admin-input text-sm">@foreach(['email','mobile','google','facebook'] as $s)<option value="{{ $s }}" @selected(old('login_type',$c->login_type??'email')===$s)>{{ ucfirst($s) }}</option>@endforeach</select>
            </div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Account Status</label>
                <select name="account_status" class="admin-input text-sm">@foreach(['active','inactive','blocked'] as $s)<option value="{{ $s }}" @selected(old('account_status',$c->account_status??'active')===$s)>{{ ucfirst($s) }}</option>@endforeach</select>
            </div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Customer Type</label>
                <select name="customer_type" class="admin-input text-sm"><option value="regular" @selected(old('customer_type',$c->customer_type??'regular')==='regular')>Regular</option><option value="vip" @selected(old('customer_type',$c->customer_type)==='vip')>VIP</option><option value="wholesale" @selected(old('customer_type',$c->customer_type)==='wholesale')>Wholesale</option></select>
            </div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Loyalty Tier</label>
                <select name="loyalty_tier" class="admin-input text-sm">@foreach(['bronze','silver','gold','platinum'] as $t)<option value="{{ $t }}" @selected(old('loyalty_tier',$c->loyalty_tier??'bronze')===$t)>{{ ucfirst($t) }}</option>@endforeach</select>
            </div>
        </div>
        <div class="flex flex-wrap gap-4 pt-2">
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="email_verified" value="1" @checked(old('email_verified',$c->email_verified)) class="rounded text-indigo-600"> Email Verified</label>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="mobile_verified" value="1" @checked(old('mobile_verified',$c->mobile_verified)) class="rounded text-indigo-600"> Mobile Verified</label>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="newsletter_subscription" value="1" @checked(old('newsletter_subscription',$c->newsletter_subscription)) class="rounded text-indigo-600"> Newsletter</label>
        </div>
        @unless($isEdit)
        <div class="grid grid-cols-2 gap-4 pt-4 border-t border-slate-100">
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Initial Wallet (₹)</label><input name="initial_wallet" type="number" step="0.01" min="0" value="{{ old('initial_wallet', 0) }}" class="admin-input text-sm"></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Initial Loyalty Points</label><input name="initial_points" type="number" min="0" value="{{ old('initial_points', 100) }}" class="admin-input text-sm"></div>
        </div>
        @endunless
        <div class="flex gap-3 pt-4">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-3 rounded-xl">{{ $isEdit ? 'Update' : 'Create' }} Customer</button>
            <a href="{{ $isEdit ? route('admin.customers.show', $c) : route('admin.customers.index') }}" class="px-6 py-3 text-sm text-slate-600 border border-slate-200 rounded-xl">Cancel</a>
        </div>
    </div>
</form>
@endsection

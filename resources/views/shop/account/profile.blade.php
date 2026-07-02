@extends('layouts.shop')
@section('title', 'Edit Profile')
@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <a href="{{ route('dashboard') }}" class="text-sm text-orange-600 mb-4 inline-block">← Back to Dashboard</a>
    <h1 class="text-2xl font-bold mb-6">Edit Profile</h1>
    @if(session('success'))<div class="mb-4 p-3 bg-emerald-50 text-emerald-700 rounded-xl text-sm">{{ session('success') }}</div>@endif
    <form action="{{ route('account.profile.update') }}" method="POST" class="bg-white rounded-2xl border p-6 space-y-4">
        @csrf @method('PATCH')
        <div class="grid sm:grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium mb-1">First Name</label><input name="first_name" value="{{ old('first_name', $customer->first_name) }}" required class="w-full border rounded-xl px-4 py-2.5"></div>
            <div><label class="block text-sm font-medium mb-1">Last Name</label><input name="last_name" value="{{ old('last_name', $customer->last_name) }}" class="w-full border rounded-xl px-4 py-2.5"></div>
            <div><label class="block text-sm font-medium mb-1">Mobile</label><input name="mobile" value="{{ old('mobile', $customer->mobile) }}" required class="w-full border rounded-xl px-4 py-2.5"></div>
            <div><label class="block text-sm font-medium mb-1">Gender</label><select name="gender" class="w-full border rounded-xl px-4 py-2.5"><option value="">—</option>@foreach(['male','female','other'] as $g)<option value="{{ $g }}" @selected(old('gender',$customer->gender)===$g)>{{ ucfirst($g) }}</option>@endforeach</select></div>
            <div><label class="block text-sm font-medium mb-1">Date of Birth</label><input name="date_of_birth" type="date" value="{{ old('date_of_birth', $customer->date_of_birth?->format('Y-m-d')) }}" class="w-full border rounded-xl px-4 py-2.5"></div>
            <div><label class="block text-sm font-medium mb-1">Anniversary</label><input name="anniversary_date" type="date" value="{{ old('anniversary_date', $customer->anniversary_date?->format('Y-m-d')) }}" class="w-full border rounded-xl px-4 py-2.5"></div>
        </div>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="newsletter_subscription" value="1" @checked($customer->newsletter_subscription) class="rounded text-orange-600"> Subscribe to newsletter</label>
        <button type="submit" class="bg-orange-600 text-white font-semibold px-6 py-3 rounded-xl">Save Changes</button>
    </form>
</div>
@endsection

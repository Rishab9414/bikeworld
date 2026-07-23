@props(['product', 'orderItemId' => null])

<form action="{{ route('reviews.store') }}" method="POST" class="bg-zinc-50 border border-zinc-100 rounded-2xl p-5 space-y-4">
    @csrf
    <input type="hidden" name="product_id" value="{{ $product->id }}">
    @if($orderItemId)
        <input type="hidden" name="order_item_id" value="{{ $orderItemId }}">
    @endif

    <h3 class="font-bold text-brand-black">Write a Review</h3>
    <p class="text-xs text-zinc-500">Share your experience with {{ $product->name }}</p>

    <div>
        <label class="block text-sm font-medium text-zinc-700 mb-2">Rating <span class="text-brand-red">*</span></label>
        <div class="flex gap-1" x-data="{ rating: {{ old('rating', 5) }} }">
            @for($i = 5; $i >= 1; $i--)
            <label class="cursor-pointer">
                <input type="radio" name="rating" value="{{ $i }}" class="sr-only" x-model="rating" @checked(old('rating', 5) == $i)>
                <span class="text-2xl transition-colors" :class="rating >= {{ $i }} ? 'text-amber-400' : 'text-zinc-300'">★</span>
            </label>
            @endfor
        </div>
        @error('rating')<p class="text-brand-red text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-zinc-700 mb-1.5">Title <span class="text-zinc-400 font-normal">(optional)</span></label>
        <input type="text" name="title" value="{{ old('title') }}" maxlength="150" placeholder="Great product!"
            class="w-full rounded-xl border border-zinc-200 px-4 py-2.5 text-sm focus:border-brand-red focus:ring-2 focus:ring-brand-red/15 outline-none">
    </div>

    <div>
        <label class="block text-sm font-medium text-zinc-700 mb-1.5">Your Review <span class="text-brand-red">*</span></label>
        <textarea name="review" rows="4" required minlength="10" maxlength="2000" placeholder="Tell others what you liked about this product…"
            class="w-full rounded-xl border border-zinc-200 px-4 py-2.5 text-sm focus:border-brand-red focus:ring-2 focus:ring-brand-red/15 outline-none">{{ old('review') }}</textarea>
        @error('review')<p class="text-brand-red text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <button type="submit" class="bg-brand-red text-white font-bold px-6 py-2.5 rounded-xl hover:bg-red-700 transition-colors text-sm">
        Submit Review
    </button>
</form>

@props(['announcements'])

@if($announcements->isNotEmpty())
<section class="py-4 overflow-hidden" style="background-color: var(--theme-ticker-bg, var(--brand-red, #E31E24));">
    <div class="flex animate-marquee whitespace-nowrap">
        @for($i = 0; $i < 2; $i++)
        <div class="flex shrink-0 items-center gap-12 px-6 text-white text-sm font-semibold">
            @foreach($announcements as $announcement)
                <x-announcement-item :announcement="$announcement" class="inline-flex items-center gap-1 shrink-0" />
            @endforeach
        </div>
        @endfor
    </div>
</section>
@endif

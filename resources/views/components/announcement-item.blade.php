@props(['announcement'])

@php
    $content = $announcement->label();
    $class = $attributes->get('class', '');
@endphp

@if($announcement->link_url)
    <a href="{{ $announcement->link_url }}" {{ $attributes->merge(['class' => 'hover:underline '.$class]) }}>{{ $content }}</a>
@else
    <span {{ $attributes->merge(['class' => $class]) }}>{{ $content }}</span>
@endif

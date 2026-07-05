@props(['meta'])

@php
    $title = $meta['title'] ?? config('seo.default_title');
    $description = $meta['description'] ?? config('seo.default_description');
    $keywords = $meta['keywords'] ?? config('seo.default_keywords');
    $canonical = $meta['canonical'] ?? url()->current();
    $image = $meta['image'] ?? asset(config('seo.og_default_image'));
    $type = $meta['type'] ?? 'website';
    $robots = $meta['robots'] ?? config('seo.robots', 'index,follow');
    $siteName = $meta['site_name'] ?? config('seo.site_name');
    $locale = str_replace('_', '-', $meta['locale'] ?? config('seo.locale', 'en_IN'));
    $twitterHandle = config('seo.twitter_handle');
    $googleVerification = config('seo.google_site_verification');
@endphp

<title>{{ $title }}</title>
<meta name="description" content="{{ $description }}">
<meta name="keywords" content="{{ $keywords }}">
<meta name="robots" content="{{ $robots }}">
<meta name="author" content="{{ $siteName }}">
<link rel="canonical" href="{{ $canonical }}">

<meta property="og:type" content="{{ $type }}">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:image" content="{{ $image }}">
<meta property="og:locale" content="{{ $locale }}">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ $image }}">
@if($twitterHandle)
<meta name="twitter:site" content="@{{ ltrim($twitterHandle, '@') }}">
@endif

@if($googleVerification)
<meta name="google-site-verification" content="{{ $googleVerification }}">
@endif

@if(!empty($meta['json_ld']))
    @foreach((array) $meta['json_ld'] as $schema)
        @if(is_array($schema))
<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
        @endif
    @endforeach
@endif

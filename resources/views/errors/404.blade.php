@extends('layouts.shop')
@section('title', 'Page Not Found')
@section('content')
<x-error-page code="404" heading="Page not found" message="The page you're looking for doesn't exist or may have been moved. Try searching or head back to the shop." />
@endsection

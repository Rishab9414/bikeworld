@extends('layouts.shop')
@section('title', 'Server Error')
@section('content')
<x-error-page code="500" heading="Something went wrong" message="We're having a temporary issue. Please try again in a few minutes." />
@endsection

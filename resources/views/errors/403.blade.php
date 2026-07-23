@extends('layouts.shop')
@section('title', 'Access Denied')
@section('content')
<x-error-page code="403" heading="Access denied" message="You don't have permission to view this page." />
@endsection

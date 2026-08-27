@extends('layouts.admin')

@section('title', isset($page['props']['pageTitle']) ? $page['props']['pageTitle'] : 'Admin')
@section('page-title', isset($page['props']['pageTitle']) ? $page['props']['pageTitle'] : '')

@push('styles')
    @routes
    @vite(['resources/js/app.js'])
@endpush

@section('content')
<div class="-m-6">
    @inertia
</div>
@endsection

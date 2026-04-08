@extends('errors.layout')

@section('title', 'Maintenance en cours')
@section('code', '503')
@section('heading', 'Maintenance en cours')
@section('message', 'Le site est temporairement indisponible pour maintenance. Nous serons de retour très bientôt.')

@section('illustration')
<svg viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
    <circle cx="60" cy="60" r="56" stroke="#e2e8f0" stroke-width="2" fill="#f8fafc"/>
    <path d="M60 35v25l15 10" stroke="#f59e0b" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
    <circle cx="60" cy="60" r="25" stroke="#f59e0b" stroke-width="2.5" fill="none"/>
    <circle cx="60" cy="35" r="2" fill="#f59e0b"/>
    <circle cx="60" cy="85" r="2" fill="#f59e0b"/>
    <circle cx="35" cy="60" r="2" fill="#f59e0b"/>
    <circle cx="85" cy="60" r="2" fill="#f59e0b"/>
</svg>
@endsection

@section('actions')
<a href="javascript:location.reload()" class="btn btn-primary">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
    Actualiser la page
</a>
@endsection

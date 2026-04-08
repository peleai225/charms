@extends('errors.layout')

@section('title', 'Session expirée')
@section('code', '419')
@section('heading', 'Session expirée')
@section('message', 'Votre session a expiré. Veuillez actualiser la page et réessayer.')

@section('illustration')
<svg viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
    <circle cx="60" cy="60" r="56" stroke="#e2e8f0" stroke-width="2" fill="#f8fafc"/>
    <path d="M60 35v25l15 10" stroke="#6366f1" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
    <circle cx="60" cy="60" r="25" stroke="#6366f1" stroke-width="2.5" fill="none" stroke-dasharray="6 4"/>
</svg>
@endsection

@section('actions')
<a href="javascript:location.reload()" class="btn btn-primary">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
    Actualiser la page
</a>
<a href="javascript:history.back()" class="btn btn-secondary">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
    Page précédente
</a>
@endsection

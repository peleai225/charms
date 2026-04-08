@extends('errors.layout')

@section('title', 'Accès refusé')
@section('code', '403')
@section('heading', 'Accès refusé')
@section('message', 'Vous n\'avez pas les permissions nécessaires pour accéder à cette page.')

@section('illustration')
<svg viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
    <circle cx="60" cy="60" r="56" stroke="#e2e8f0" stroke-width="2" fill="#f8fafc"/>
    <rect x="46" y="42" width="28" height="32" rx="3" stroke="#6366f1" stroke-width="2.5" fill="none"/>
    <path d="M50 42V36a10 10 0 0120 0v6" stroke="#6366f1" stroke-width="2.5" fill="none" stroke-linecap="round"/>
    <circle cx="60" cy="56" r="3" fill="#6366f1"/>
    <line x1="60" y1="59" x2="60" y2="65" stroke="#6366f1" stroke-width="2.5" stroke-linecap="round"/>
</svg>
@endsection

@section('actions')
<a href="{{ url('/') }}" class="btn btn-primary">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
    Retour à l'accueil
</a>
<a href="javascript:history.back()" class="btn btn-secondary">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
    Page précédente
</a>
@endsection

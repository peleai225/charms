@extends('errors.layout')

@section('title', 'Erreur serveur')
@section('code', '500')
@section('heading', 'Erreur interne du serveur')
@section('message', 'Une erreur inattendue s\'est produite. Notre équipe a été informée et travaille à résoudre le problème.')

@section('illustration')
<svg viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
    <circle cx="60" cy="60" r="56" stroke="#e2e8f0" stroke-width="2" fill="#f8fafc"/>
    <path d="M45 45l30 30M75 45L45 75" stroke="#ef4444" stroke-width="3" stroke-linecap="round"/>
    <circle cx="60" cy="60" r="25" stroke="#ef4444" stroke-width="2.5" fill="none"/>
</svg>
@endsection

@section('actions')
<a href="{{ url('/') }}" class="btn btn-primary">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
    Retour à l'accueil
</a>
<a href="javascript:location.reload()" class="btn btn-secondary">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
    Réessayer
</a>
@endsection

@extends('errors.layout')

@section('title', 'Page introuvable')
@section('code', '404')
@section('heading', 'Page introuvable')
@section('message', 'Oups ! La page que vous cherchez n\'existe pas ou a été déplacée.')

@section('illustration')
<svg viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
    <circle cx="60" cy="60" r="56" stroke="#e2e8f0" stroke-width="2" fill="#f8fafc"/>
    <path d="M40 75 Q60 55 80 75" stroke="#6366f1" stroke-width="3" fill="none" stroke-linecap="round"/>
    <circle cx="42" cy="50" r="4" fill="#6366f1"/>
    <circle cx="78" cy="50" r="4" fill="#6366f1"/>
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

@extends('layouts.admin')

@section('title', 'Paramètres — Paiement')

@section('content')
@php $defaultTab = 'payment'; @endphp
@include('admin.settings._content')
@endsection

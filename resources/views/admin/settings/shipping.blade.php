@extends('layouts.admin')

@section('title', 'Paramètres — Livraison')

@section('content')
@php $defaultTab = 'shipping'; @endphp
@include('admin.settings._content')
@endsection

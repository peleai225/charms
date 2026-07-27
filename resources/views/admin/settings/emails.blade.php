@extends('layouts.admin')

@section('title', 'Paramètres — Emails')

@section('content')
@php $defaultTab = 'emails'; @endphp
@include('admin.settings._content')
@endsection

@extends('layouts.admin')

@section('title', 'Paramètres')

@section('content')
@php $defaultTab = 'general'; @endphp
@include('admin.settings._content')
@endsection

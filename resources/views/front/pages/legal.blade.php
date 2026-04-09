@extends('layouts.front')

@section('title', $title)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-3xl font-bold text-slate-900 mb-8">{{ $title }}</h1>
    <div class="prose prose-slate max-w-none">
        {!! $content !!}
    </div>
</div>
@endsection

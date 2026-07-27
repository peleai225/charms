@extends('layouts.admin')

@section('title', 'Modifier — ' . $product->name)
@section('page-title', 'Modifier le produit')

@section('content')

@include('admin.products.partials._flash')

@include('admin.products.partials._form-main')

@include('admin.products.partials._variants-scripts')

<div class="mt-5 space-y-4" x-data="variantManagerData()">

    @include('admin.products.partials._variants-generator')

    @include('admin.products.partials._variants-table')

</div>

@foreach($product->images as $image)
@if(!$image->is_primary)
<form id="form-primary-{{ $image->id }}" method="POST" action="{{ route('admin.products.images.primary', [$product, $image]) }}" class="hidden no-ajax">
    @csrf
</form>
@endif
<form id="form-delete-{{ $image->id }}" method="POST" action="{{ route('admin.products.images.destroy', [$product, $image]) }}" class="hidden no-ajax">
    @csrf
    @method('DELETE')
</form>
@endforeach

@endsection

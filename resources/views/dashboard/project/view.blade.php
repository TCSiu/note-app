@extends('layouts.default')

@section('content')
<main class="content">
	<div class="container-fluid p-0">
		<h1 class="h3 mb-3">{{ __('Content Management System') }}</h1>
		<div class="row justify-content-center" data-masonry='{"percentPosition": true }'>
            @isset($tasks)
            @foreach($tasks as $k =>  task)
            <div class="col-12 col-sm-6 col-lg-4 mb-4">
                <div class="card">

                </div>
            </div>
            @endforeach
            @endif
        </div>
	</div>
</main>
@stop

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/masonry-layout@4.2.2/dist/masonry.pkgd.min.js" integrity="sha384-GNFwBvfVxBkLMJpYMOABq3c+d3KnQxudP/mGPkzpZSTYykLBNsZEnG2D9G/X/+7D" crossorigin="anonymous" async></script>
<script>

</script>
@endpush
@extends('layouts.auth')

@section('content')
<div class="col-sm-10 col-md-8 col-lg-6 col-xl-5 mx-auto d-table h-100">
    <div class="d-table-cell align-middle">
        <div class="text-center mt-4">
            <div class="h2">Welcome Back! {{ Request::ip() }}</div>
            <p class="lead">Sign in to your account to continue</p>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="m-sm-3">
                    <form action="{{ route('login') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input class="form-control form-control-lg" type="email" name="email" placeholder="Enter your email">
                            @error('email')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input class="form-control form-control-lg" type="password" name="password" placeholder="Enter your password">
                            @error('password')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <div class="form-check align-items-center">
                                <input id="customControlInline" type="checkbox" class="form-check-input" value="1" name="remember-me">
                                <label class="form-check-label text-small" for="customControlInline">Remember me</label>
                            </div>
                        </div>
                        <div class="d-grid gap-2 mt-3">
                            <input type="submit" class="btn btn-lg btn-primary" value="Sign in" />
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="text-center mb-3">
            Don't have an account? <a href="{{ route('register') }}">Sign up</a>
        </div>
    </div>
</div>
@stop
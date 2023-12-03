@extends('layouts.auth')

@section('content')
<div class="container d-flex flex-column">
    <div class="row vh-100">
        <div class="col-sm-10 col-md-8 col-lg-6 col-xl-5 mx-auto d-table h-100">
            <div class="d-table-cell align-middle">

                <div class="text-center mt-4">
                    <h1 class="h2">Get started</h1>
                    <p class="lead">
                        Start creating the best possible user experience for you customers.
                    </p>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="m-sm-3">
                            <form action="{{ route('register') }}" method="post" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Full name</label>
                                    <input class="form-control form-control-lg" type="text" name="name" placeholder="Enter your name">
                                    @error('name')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input class="form-control form-control-lg" type="email" name="email" placeholder="Enter your email">
                                    @error('email')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Password</label>
                                    <input class="form-control form-control-lg" type="password" name="password" placeholder="Enter password">
                                    @error('password')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Password Confirmation</label>
                                    <input class="form-control form-control-lg" type="password" name="password_confirmation" placeholder="Enter password again">
                                </div>
                                <div class="d-grid gap-2 mt-3">
                                    <input type="submit" class="btn btn-lg btn-primary" value="Sign up" />
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="text-center mb-3">
                    Already have account? <a href="{{ route('login') }}">Log In</a>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
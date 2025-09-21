@extends('adminlte.layouts.auth')

@section('content')
<body class="hold-transition login-page">
        <div class="login-box">
            <div class="login-logo">
                <a href="{{ route('dashboard') }}"><b>{{ config('app.name', 'Laravel') }}</b> 1.0</a>
            </div>
            <div class="card">
                <div class="card-body login-card-body">
                    <p class="login-box-msg">{{ __('Please confirm your password before continuing.') }}</p>

                    <form method="POST" action="{{ route('password.confirm') }}">
                        @csrf
                        <div class="input-group mb-3">
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="{{ __('Password') }}">
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-lock"></span>
                                </div>
                            </div>
                            @error('password')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-block">{{ __('Confirm Password') }}</button>
                            </div>
                        </div>
                    </form>

                    @if (Route::has('password.request'))
                    <p class="mt-3 mb-1">
                        <a href="{{ route('password.request') }}">{{ __('Forgot Your Password?') }}</a>
                    </p>
                    @endif
                </div>
            </div>
        </div>
@endsection

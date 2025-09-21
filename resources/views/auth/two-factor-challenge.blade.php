@extends('adminlte.layouts.auth')

@section('content')
<body class="hold-transition login-page">
  <div class="login-box">
    <div class="login-logo">
      <a href="{{ route('dashboard') }}"><b>{{ config('app.name', 'Laravel') }}</b> 1.0</a>
    </div>
    <div class="card">
      <div class="card-body login-card-body">
        <p class="login-box-msg">{{ __('Please confirm access to your account by entering the authentication code provided by your authenticator application.') }}</p>

        <div id="code-section">
          <form method="POST" action="{{ route('two-factor.login') }}">
            @csrf
            <div class="input-group mb-3">
              <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" class="form-control @error('code') is-invalid @enderror" placeholder="{{ __('Authentication Code') }}" autofocus>
              <div class="input-group-append">
                <div class="input-group-text">
                  <span class="fas fa-shield-alt"></span>
                </div>
              </div>
              @error('code')
              <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
              @enderror
            </div>

            <div class="row">
              <div class="col-12 d-flex justify-content-between align-items-center">
                <a href="#" id="use-recovery" class="small">{{ __('Use a recovery code') }}</a>
                <button type="submit" class="btn btn-primary">{{ __('Login') }}</button>
              </div>
            </div>
          </form>
        </div>

        <div id="recovery-section" style="display:none;">
          <form method="POST" action="{{ route('two-factor.login') }}">
            @csrf
            <div class="input-group mb-3">
              <input id="recovery_code" name="recovery_code" type="text" class="form-control @error('recovery_code') is-invalid @enderror" placeholder="{{ __('Recovery Code') }}">
              <div class="input-group-append">
                <div class="input-group-text">
                  <span class="fas fa-key"></span>
                </div>
              </div>
              @error('recovery_code')
              <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
              @enderror
            </div>

            <div class="row">
              <div class="col-12 d-flex justify-content-between align-items-center">
                <a href="#" id="use-code" class="small">{{ __('Use an authentication code') }}</a>
                <button type="submit" class="btn btn-primary">{{ __('Login') }}</button>
              </div>
            </div>
          </form>
        </div>

        @if ($errors->any())
          <div class="alert alert-danger mt-3">
            <ul class="mb-0">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const codeSection = document.getElementById('code-section');
      const recoverySection = document.getElementById('recovery-section');
      const useRecovery = document.getElementById('use-recovery');
      const useCode = document.getElementById('use-code');

      useRecovery?.addEventListener('click', function(e) {
        e.preventDefault();
        codeSection.style.display = 'none';
        recoverySection.style.display = '';
        document.getElementById('recovery_code').focus();
      });

      useCode?.addEventListener('click', function(e) {
        e.preventDefault();
        recoverySection.style.display = 'none';
        codeSection.style.display = '';
        document.getElementById('code').focus();
      });
    });
  </script>
@endsection

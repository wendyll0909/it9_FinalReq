@extends('layouts.app')

@section('content')
<div class="login-container">
    <h2>Login</h2>
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                {{ $error }}<br>
            @endforeach
        </div>
    @endif
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required autofocus>
            @error('email')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
            @error('password')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
            <label class="form-check-label" for="remember">Remember Me</label>
        </div>
        <button type="submit" class="btn btn-primary w-100" style="background-color: #007bff; border-color: #007bff;">Login</button>
    </form>
    @if (Route::has('password.request'))
        <div class="forgot-password">
            <p><a href="{{ route('password.request') }}">Forgot Your Password?</a></p>
        </div>
    @endif
    @if (Route::has('register'))
        <div class="register-link">
            <p>Don't have an account? <a href="{{ route('register') }}">Register here</a></p>
        </div>
    @endif
</div>

<style>
    body {
        background-image: url('{{ asset('/assets/img/img.jpg') }}');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        margin: 0;
    }
    .login-container {
        background-color: rgba(42, 45, 46, 0.9);
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(255, 255, 255, 0.1);
        width: 100%;
        max-width: 400px;
    }
    .login-container h2 {
        color: #74aebd;
        text-align: center;
        margin-bottom: 20px;
    }
    .register-link, .forgot-password {
        text-align: center;
        margin-top: 15px;
    }
    .register-link a, .forgot-password a {
        color: #74aebd;
        text-decoration: none;
    }
    .register-link a:hover, .forgot-password a:hover {
        text-decoration: underline;
    }
    .form-label,
    .form-check-label {
    color: #ffffff;
    }

</style>
@endsection
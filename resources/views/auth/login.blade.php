@extends('layouts.app')

@section('title', 'Login')

@section('content')
    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            overflow: hidden;
        }

        .login-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-image: url('{{ asset('img/bg-login.jpg') }}');
            background-size: 100% 100%;
            background-position: center center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            position: relative;
        }

        .login-wrapper::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.2);
            z-index: 1;
        }

        .header-logos {
            position: relative;
            z-index: 2;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 30px 50px;
        }

        .logos-left {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .logos-right {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .logo-img {
            height: 80px;
            width: auto;
            filter: drop-shadow(2px 2px 4px rgba(0, 0, 0, 0.3));
        }

        .login-content {
            margin-top: 100px;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 2;
            padding: 20px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px 45px;
            width: 100%;
            max-width: 420px;
            backdrop-filter: blur(10px);
        }

        .login-title {
            font-size: 26px;
            font-weight: 600;
            color: #333;
            margin-bottom: 35px;
            text-align: left;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #666;
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            padding: 14px 18px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .form-input:focus {
            outline: none;
            border-color: #Ac9b73;
            box-shadow: 0 0 0 3px rgba(172, 155, 115, 0.1);
        }

        .form-input.is-invalid {
            border-color: #dc3545;
        }

        .invalid-feedback {
            color: #dc3545;
            font-size: 13px;
            margin-top: 5px;
            display: block;
        }

        .remember-group {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
        }

        .remember-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin-right: 10px;
            cursor: pointer;
        }

        .remember-group label {
            font-size: 14px;
            color: #666;
            margin: 0;
            cursor: pointer;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: #Ac9b73;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(172, 155, 115, 0.4);
            background: #9a8a65;
        }

        .login-links {
            text-align: center;
            margin-top: 20px;
        }

        .login-links a {
            color: #Ac9b73;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            display: block;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }

        .login-links a:hover {
            color: #9a8a65;
            text-decoration: underline;
        }

        .footer-copyright {
            position: relative;
            z-index: 2;
            padding: 20px;
            text-align: left;
            color: white;
            font-size: 14px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            padding-left: 50px;
        }

        @media (max-width: 768px) {
            .header-logos {
                flex-direction: column;
                gap: 20px;
                padding: 20px;
            }

            .logos-left,
            .logos-right {
                gap: 15px;
            }

            .logo-img {
                height: 60px;
            }

            .login-card {
                padding: 30px 25px;
                max-width: 90%;
            }

            .login-title {
                font-size: 24px;
                margin-bottom: 30px;
            }
        }
    </style>

    <div class="login-wrapper">
        <!-- Header Logos -->


        <!-- Login Content -->
        <div class="login-content">
            <div class="login-card">
                <h2 class="login-title">Login</h2>

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="form-group">
                        <label for="emailOrUsername" class="form-label">Email / Username</label>
                        <input id="emailOrUsername" type="text"
                            class="form-input @error('emailOrUsername') is-invalid @enderror" name="emailOrUsername"
                            value="{{ old('emailOrUsername') }}" required autocomplete="emailOrUsername" autofocus>

                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input id="password" type="password" class="form-input @error('password') is-invalid @enderror"
                            name="password" required autocomplete="current-password">

                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="remember-group">
                        <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label for="remember">Remember Me</label>
                    </div>

                    <button type="submit" class="btn-login">
                        Login
                    </button>

                    {{-- <div class="login-links">
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}">
                                Forgot Your Password?
                            </a>
                        @endif
                        <a href="{{ route('register') }}">
                            Belum Punya Akun? Registers
                        </a>
                    </div> --}}
                </form>
            </div>
        </div>

        <!-- Footer -->
        {{-- <div class="footer-copyright">
            © 2026 by UPT PPD Nganjuk
        </div> --}}
    </div>
@endsection

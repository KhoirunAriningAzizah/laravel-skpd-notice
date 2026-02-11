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

        .password-wrapper {
            position: relative;
        }

        .password-wrapper .form-input {
            padding-right: 45px;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
            transition: color 0.3s ease;
            font-size: 18px;
            user-select: none;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .toggle-password:hover {
            color: #Ac9b73;
        }

        .toggle-password svg {
            width: 20px;
            height: 20px;
            fill: currentColor;
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
                        <label for="emailOrUsername" class="form-label">Email</label>
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
                        <div class="password-wrapper">
                            <input id="password" type="password" class="form-input @error('password') is-invalid @enderror"
                                name="password" required autocomplete="current-password">
                            <span class="toggle-password" onclick="togglePassword()">
                                <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                    <path
                                        d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z" />
                                </svg>
                            </span>
                        </div>

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

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');

            if (passwordInput.type === 'password') {
                // Show password - ubah jadi mata terbuka
                passwordInput.type = 'text';
                eyeIcon.innerHTML =
                    '<path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>';
            } else {
                // Hide password - ubah jadi mata tertutup dengan garis
                passwordInput.type = 'password';
                eyeIcon.innerHTML =
                    '<path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/>';
            }
        }
    </script>
@endsection

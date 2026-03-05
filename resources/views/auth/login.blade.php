@extends('layouts.app')

@section('title', 'Login')

@section('content')
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            overflow: hidden;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Full page background */
        .login-page {
            min-height: 100vh;
            background-image: url('{{ asset('img/bg-login-new.png') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        /* Main card container */
        .login-container {
            display: flex;
            max-width: 950px;
            width: 100%;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.3);
            z-index: 99;
            height: 70vh;

        }

        /* Welcome Section (Left Side - Inside Card) */
        .welcome-section {
            flex: 1;
            background-image: url('{{ asset('img/bg-section-form-login.png') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 50px 100px;
            position: relative;
            color: #1e5f6f;
            min-height: 480px;
        }

        .welcome-heading {
            font-size: 32px;
            font-weight: 700;
            font-style: italic;
            text-align: center;
            margin-bottom: 25px;
            color: #1e5f6f;
            letter-spacing: 1px;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 25px;
        }

        .logo-image {
            width: 120px;
            height: auto;
            margin: 0 auto 20px;
        }

        .sinova-logo {
            font-size: 42px;
            font-weight: 900;
            color: #1e5f6f;
            letter-spacing: 5px;
            margin-bottom: 8px;
        }

        .logo-subtitle {
            font-size: 11px;
            font-weight: 600;
            color: #1e5f6f;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .welcome-description {
            font-size: 13px;
            font-weight: 600;
            text-align: center;
            line-height: 1.6;
            color: #1e5f6f;
            max-width: 320px;
        }

        /* Form Section (Right Side - Inside Card) */
        .form-section {
            flex: 1;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 50px 45px;
        }

        .login-card {
            width: 100%;
            max-width: 350px;
        }

        .login-title {
            font-size: 28px;
            font-weight: 700;
            color: #0c5351;
            margin-bottom: 30px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            display: none;
        }

        .form-input {
            width: 100%;
            padding: 14px 18px;
            border: 1.5px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            box-sizing: border-box;
            background: #fff;
        }

        .form-input::placeholder {
            color: #999;
        }

        .form-input:focus {
            outline: none;
            border-color: #4169e1;
            box-shadow: 0 0 0 3px rgba(65, 105, 225, 0.1);
        }

        .form-input.is-invalid {
            border-color: #dc3545;
        }

        .invalid-feedback {
            color: #dc3545;
            font-size: 13px;
            margin-top: 6px;
            display: block;
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper .form-input {
            padding-right: 50px;
        }

        .toggle-password {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #888;
            transition: color 0.3s ease;
            user-select: none;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .toggle-password:hover {
            color: #4169e1;
        }

        .toggle-password svg {
            width: 20px;
            height: 20px;
            fill: currentColor;
        }

        .remember-group {
            display: none;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: #0c5351;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 8px;
            margin-bottom: 12px;
        }

        .btn-login:hover {
            background: #2d4fb8;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(65, 105, 225, 0.3);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .login-links {
            text-align: center;
            margin-top: 12px;
        }

        .login-links a {
            color: #4169e1;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .login-links a:hover {
            color: #2d4fb8;
            text-decoration: underline;
        }

        /* Responsive Design */
        @media (max-width: 900px) {
            .login-container {
                flex-direction: column;
                max-width: 550px;
                height: 80vh;
            }

            .welcome-section {
                min-height: 120px;
                padding: 135px 25px;
            }

            .welcome-heading {
                font-size: 26px;
                margin-bottom: 18px;
            }

            .sinova-logo {
                font-size: 32px;
                letter-spacing: 4px;
            }

            .logo-subtitle {
                font-size: 9px;
            }

            .logo-image {
                width: 90px;
            }

            .welcome-description {
                font-size: 12px;
            }

            .form-section {
                padding: 35px 25px;
            }

            .login-title {
                font-size: 22px;
                margin-bottom: 22px;
            }
        }

        @media (max-width: 500px) {
            .login-page {
                padding: 15px 10px;
            }

            .welcome-section {
                min-height: 260px;
                padding: 28px 18px;
            }

            .welcome-heading {
                font-size: 22px;
            }

            .sinova-logo {
                font-size: 26px;
                letter-spacing: 3px;
            }

            .logo-image {
                width: 70px;
            }

            .welcome-description {
                font-size: 11px;
            }

            .form-section {
                padding: 28px 20px;
            }

            .login-title {
                font-size: 20px;
            }

            .form-input {
                padding: 12px 14px;
                font-size: 13px;
            }

            .btn-login {
                padding: 12px;
                font-size: 14px;
            }
        }
    </style>

    <div class="login-page">
        <div class="login-container">
            <!-- Welcome Section (Left Side - Inside Card) -->
            <div class="welcome-section">
                {{-- <h1 class="welcome-heading">Selamat Datang di</h1>

                <div class="logo-container">
                    <!-- Logo bulan sabit seperti di gambar SICIKA -->
                    <svg width="120" height="120" viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg"
                        style="margin-bottom: 15px;">
                        <!-- Crescent moon shape -->
                        <defs>
                            <clipPath id="crescentClip">
                                <circle cx="60" cy="60" r="45" />
                            </clipPath>
                        </defs>
                        <g clip-path="url(#crescentClip)">
                            <circle cx="60" cy="60" r="45" fill="#1e5f6f" />
                            <circle cx="85" cy="45" r="40" fill="#f5e6c8" />
                        </g>
                        <!-- Yellow accent -->
                        <circle cx="75" cy="30" r="12" fill="#f5c842" />
                    </svg>

                    <div class="sinova-logo">SINOVA</div>
                    <div class="logo-subtitle">SISTEM INFORMASI NOTICE & VISUAL ANALYTICS</div>
                </div>

                <p class="welcome-description">
                    SINOVA adalah Sistem Informasi Pencatatan dan Pelaporan Data Notice Pajak UPT PPD Nganjuk
                </p> --}}
            </div>

            <!-- Form Section (Right Side - Inside Card) -->
            <div class="form-section">
                <div class="login-card">
                    <h2 class="login-title">LOGIN</h2>

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="form-group">
                            <label for="emailOrUsername" class="form-label">Masukkan Email</label>
                            <input id="emailOrUsername" type="text"
                                class="form-input @error('emailOrUsername') is-invalid @enderror" name="emailOrUsername"
                                value="{{ old('emailOrUsername') }}" required autocomplete="emailOrUsername" autofocus
                                placeholder="Masukkan Email">

                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password" class="form-label">Masukkan Password</label>
                            <div class="password-wrapper">
                                <input id="password" type="password"
                                    class="form-input @error('password') is-invalid @enderror" name="password" required
                                    autocomplete="current-password" placeholder="Masukkan Password">
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

                        <button type="submit" class="btn-login">
                            Login
                        </button>

                        {{-- <div class="login-links">
                            <a href="#">
                                Lupa Kata Sandi ?
                            </a>
                        </div> --}}
                    </form>
                </div>
            </div>
        </div>
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

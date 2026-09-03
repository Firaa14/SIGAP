<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - SIGAP</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            min-height: 100vh;

            display: flex;
            align-items: center;
            justify-content: center;

            background:
                linear-gradient(rgba(0, 0, 0, 0.68),
                    rgba(0, 0, 0, 0.68)),
                url('/images/alt-PLTA.PNG');

            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;

            position: relative;
        }

        .login-wrapper {
            width: 900px;
            min-height: 540px;

            background: transparent;

            border-radius: 18px;
            overflow: hidden;

            box-shadow:
                0 20px 50px rgba(0, 0, 0, 0.45);

            display: flex;

            position: relative;
            z-index: 2;
        }

        .login-left {
            width: 45%;

            background: rgba(0, 59, 123, 0.88);

            color: white;

            padding: 32px 45px 45px 45px;

            display: flex;
            flex-direction: column;
            justify-content: flex-start;

            backdrop-filter: blur(3px);
        }

        .logo-pln {
            margin-bottom: 32px;

            display: flex;
            align-items: flex-start;
            justify-content: flex-start;
        }

        .logo-pln img {
            width: 220px;
            height: auto;

            display: block;

            object-fit: contain;
        }

        .login-left h2 {
            font-size: 25px;
            line-height: 1.3;

            margin-bottom: 15px;

            color: #ffffff;
        }

        .login-left p {
            font-size: 14px;
            line-height: 1.7;

            color: #e2e8f0;
        }

        .info-box {
            margin-top: 30px;

            padding: 15px;

            border-left: 4px solid #ffffff;

            background: rgba(255, 255, 255, 0.10);

            border-radius: 6px;

            font-size: 13px;
            line-height: 1.6;

            color: #ffffff;

            backdrop-filter: blur(3px);
        }

        .info-box strong {
            font-size: 14px;
            color: #ffffff;
        }

        .login-right {
            width: 55%;

            padding: 50px 55px;

            display: flex;
            flex-direction: column;
            justify-content: center;

            background: rgba(255, 255, 255, 0.93);

            backdrop-filter: blur(5px);
        }

        .login-title {
            font-size: 30px;

            color: #1e293b;

            margin-bottom: 8px;
        }

        .login-subtitle {
            color: #64748b;

            font-size: 14px;

            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;

            margin-bottom: 7px;

            font-size: 14px;

            font-weight: bold;

            color: #334155;
        }

        .input-box {
            width: 100%;
            height: 46px;

            padding: 0 14px;

            border: 1px solid #cbd5e1;

            border-radius: 7px;

            font-size: 14px;

            outline: none;

            background: rgba(248, 250, 252, 0.95);

            color: #334155;

            transition: 0.2s;
        }

        .input-box:focus {
            border-color: #003b7b;

            background: #ffffff;

            box-shadow:
                0 0 0 3px rgba(0, 59, 123, 0.12);
        }

        .input-box::placeholder {
            color: #94a3b8;
        }

        select.input-box {
            cursor: pointer;
            appearance: auto;
        }

        .error {
            background: #fee2e2;

            color: #b91c1c;

            border: 1px solid #fecaca;

            padding: 10px;

            border-radius: 7px;

            margin-bottom: 18px;

            font-size: 13px;
        }

        .btn-login {
            width: 100%;
            height: 48px;

            border: none;

            border-radius: 7px;

            background: #003b7b;

            color: #ffffff;

            font-size: 15px;

            font-weight: bold;

            cursor: pointer;

            margin-top: 5px;

            transition: all 0.2s ease;
        }

        .btn-login:hover {
            background: #002f62;

            transform: translateY(-1px);

            box-shadow:
                0 5px 12px rgba(0, 59, 123, 0.30);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .footer-text {
            text-align: center;

            margin-top: 20px;

            color: #94a3b8;

            font-size: 11px;
        }

        @media (max-width: 900px) {
            .login-wrapper {
                width: 92%;
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 20px;
            }

            .login-wrapper {
                width: 100%;

                min-height: auto;

                flex-direction: column;

                border-radius: 15px;
            }

            .login-left,
            .login-right {
                width: 100%;
            }

            .login-left {
                padding: 30px 35px 40px 35px;
            }

            .logo-pln {
                margin-bottom: 25px;
            }

            .logo-pln img {
                width: 180px;
            }

            .login-left h2 {
                font-size: 22px;
            }

            .login-left p {
                font-size: 13px;
            }

            .info-box {
                width: 100%;
            }

            .login-right {
                padding: 40px 35px;
            }

            .login-title {
                font-size: 26px;
            }
        }

        @media (max-width: 480px) {
            .login-left {
                padding: 25px 25px 35px 25px;
            }

            .login-right {
                padding: 30px 25px;
            }

            .logo-pln img {
                width: 160px;
            }

            .login-title {
                font-size: 24px;
            }
        }
    </style>

</head>

<body>

    <div class="login-wrapper">

        <div class="login-left">

            <div class="logo-pln">
                <img src="{{ asset('images/logo-pln-np.png') }}" alt="Logo PLN Nusantara Power">
            </div>

            <h2>
                SIGAP
            </h2>

            <p>
                Sistem Informasi Gangguan Andal Pembangkit sebagai sarana monitoring terpadu kesehatan unit dan
                equipment PLTA untuk mendukung keandalan operasional pembangkitan.
            </p>

            <div class="info-box">
                <strong>
                    Manajemen Pemeliharaan Aset
                </strong>

                <br>

                Kelola status operasi, keandalan alat, dan Work Order dalam satu platform terpadu.
            </div>

        </div>

        <div class="login-right">

            <h1 class="login-title">
                Selamat Datang
            </h1>

            <p class="login-subtitle">
                Silakan masuk ke akun SIGAP Anda
            </p>

            @if ($errors->any())

                <div class="error">
                    {{ $errors->first() }}
                </div>

            @endif

            <form action="{{ route('login.process') }}" method="POST">

                @csrf

                <div class="form-group">

                    <label for="email">
                        Email
                    </label>

                    <input type="email" id="email" name="email" class="input-box" placeholder="Masukkan email"
                        value="{{ old('email') }}" required>

                </div>

                <div class="form-group">

                    <label for="password">
                        Password
                    </label>

                    <input type="password" id="password" name="password" class="input-box"
                        placeholder="Masukkan password" required>

                </div>

                <div class="form-group">

                    <label for="role">
                        Role
                    </label>

                    <select name="role" id="role" class="input-box" required>

                        <option value="" disabled selected>
                            Pilih Role
                        </option>

                        <option value="SO">
                            SO
                        </option>

                        <option value="CBM">
                            CBM
                        </option>

                        <option value="REVIEWER">
                            REVIEWER
                        </option>

                    </select>

                </div>

                <button type="submit" class="btn-login">
                    MASUK
                </button>

            </form>

            <div class="footer-text">
                © {{ date('Y') }} SIGAP — Sistem Informasi Monitoring
            </div>

        </div>

    </div>

</body>

</html>
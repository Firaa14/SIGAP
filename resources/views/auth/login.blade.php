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
            background: linear-gradient(135deg, #0f3d91, #1769d1);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-wrapper {
            width: 900px;
            min-height: 540px;
            background: #ffffff;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.25);
            display: flex;
        }

        /* =========================
           BAGIAN KIRI
        ========================= */

        .login-left {
            width: 45%;
            background: linear-gradient(160deg, #0f3d91, #1d6ed8);
            color: white;
            padding: 55px 45px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .logo {
            font-size: 42px;
            font-weight: bold;
            letter-spacing: 3px;
            margin-bottom: 25px;
        }

        .logo span {
            color: #7dd3fc;
        }

        .login-left h2 {
            font-size: 25px;
            margin-bottom: 15px;
        }

        .login-left p {
            font-size: 14px;
            line-height: 1.7;
            color: #e5efff;
        }

        .info-box {
            margin-top: 30px;
            padding: 15px;
            border-left: 4px solid #7dd3fc;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 6px;
            font-size: 13px;
            line-height: 1.6;
        }

        /* =========================
           BAGIAN KANAN
        ========================= */

        .login-right {
            width: 55%;
            padding: 50px 55px;
            display: flex;
            flex-direction: column;
            justify-content: center;
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
            background: #f8fafc;
        }

        .input-box:focus {
            border-color: #2563eb;
            background: white;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        /* DROPDOWN ROLE */

        select.input-box {
            cursor: pointer;
            appearance: auto;
        }

        /* ERROR */

        .error {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
            padding: 10px;
            border-radius: 7px;
            margin-bottom: 18px;
            font-size: 13px;
        }

        /* BUTTON */

        .btn-login {
            width: 100%;
            height: 48px;
            border: none;
            border-radius: 7px;
            background: #2563eb;
            color: white;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 5px;
            transition: 0.2s;
        }

        .btn-login:hover {
            background: #1d4ed8;
        }

        .footer-text {
            text-align: center;
            margin-top: 20px;
            color: #94a3b8;
            font-size: 11px;
        }

        /* RESPONSIVE */

        @media (max-width: 768px) {

            .login-wrapper {
                width: 92%;
                flex-direction: column;
            }

            .login-left,
            .login-right {
                width: 100%;
            }

            .login-left {
                padding: 35px;
            }

            .login-right {
                padding: 35px;
            }
        }
    </style>
</head>

<body>

    <div class="login-wrapper">

        <!-- =========================
             BAGIAN KIRI
        ========================= -->

        <div class="login-left">

            <div class="logo">
                SI<span>GAP</span>
            </div>

            <h2>
                Sistem Informasi Monitoring
            </h2>

            <p>
                Sistem informasi untuk membantu monitoring
                equipment dan Work Order pada PLTA secara
                terintegrasi.
            </p>

            <div class="info-box">
                <strong>PLTA Monitoring System</strong>
                <br>
                Kelola data equipment, status operasi,
                dan Work Order dengan lebih mudah.
            </div>

        </div>


        <!-- =========================
             BAGIAN KANAN
        ========================= -->

        <div class="login-right">

            <h1 class="login-title">
                Selamat Datang
            </h1>

            <p class="login-subtitle">
                Silakan masuk ke akun SIGAP Anda
            </p>


            <!-- PESAN ERROR -->

            @if ($errors->any())

                <div class="error">
                    {{ $errors->first() }}
                </div>

            @endif


            <!-- FORM LOGIN -->

            <form action="{{ route('login.process') }}" method="POST">

                @csrf


                <!-- EMAIL -->

                <div class="form-group">

                    <label for="email">
                        Email
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="input-box"
                        placeholder="Masukkan email"
                        value="{{ old('email') }}"
                        required
                    >

                </div>


                <!-- PASSWORD -->

                <div class="form-group">

                    <label for="password">
                        Password
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="input-box"
                        placeholder="Masukkan password"
                        required
                    >

                </div>


                <!-- ROLE -->

                <div class="form-group">

    <label for="role">Role</label>

    <select
        name="role"
        id="role"
        class="input-box"
        required
    >
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


                <!-- BUTTON MASUK -->

                <button
                    type="submit"
                    class="btn-login"
                >
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
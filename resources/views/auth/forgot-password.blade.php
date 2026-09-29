<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Lupa Password - Monitoring Penugasan Layanan
    </title>


    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;

            min-height: 100vh;

            display: grid;

            place-items: center;

            padding: 24px;

            background: #f4f6fa;

            color: #0f172a;

            font-family:
                Inter,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;
        }

        .auth-card {
            width: 100%;
            max-width: 430px;

            overflow: hidden;

            border: 1px solid #e2e8f0;
            border-radius: 16px;

            background: #ffffff;

            box-shadow:
                0 12px 35px
                rgba(15, 23, 42, .08);
        }

        .auth-header {
            padding: 28px 28px 18px;

            text-align: center;
        }

        .auth-logo {
            width: 54px;
            height: 54px;

            display: grid;

            place-items: center;

            margin: 0 auto 14px;

            border-radius: 14px;

            background:
                linear-gradient(
                    135deg,
                    #10b981,
                    #3b82f6
                );

            color: #ffffff;

            font-size: 20px;
            font-weight: 800;
        }

        .auth-header h1 {
            margin: 0;

            font-size: 22px;
            font-weight: 700;
        }

        .auth-header p {
            margin: 6px 0 0;

            color: #64748b;

            font-size: 13px;
            line-height: 1.5;
        }

        .auth-body {
            padding: 10px 28px 28px;
        }

        .field {
            margin-bottom: 14px;
        }

        .field label {
            display: block;

            margin-bottom: 6px;

            color: #475569;

            font-size: 12px;
            font-weight: 600;
        }

        .control {
            width: 100%;
            height: 44px;

            padding: 0 12px;

            border: 1px solid #e2e8f0;
            border-radius: 9px;

            outline: none;

            background: #f8fafc;

            color: #0f172a;

            font: inherit;
            font-size: 13px;
        }

        .control:focus {
            border-color: #059669;

            box-shadow:
                0 0 0 1px
                #059669;
        }

        .button {
            width: 100%;
            height: 44px;

            border: 0;
            border-radius: 9px;

            background: #059669;

            color: #ffffff;

            font: inherit;
            font-size: 13px;
            font-weight: 700;

            cursor: pointer;
        }

        .button:hover {
            background: #047857;
        }

        .auth-links {
            display: flex;

            align-items: center;
            justify-content: space-between;

            gap: 10px;

            margin: 5px 0 16px;

            color: #64748b;

            font-size: 12px;
        }

        .auth-links a,
        .auth-footer a {
            color: #059669;

            font-weight: 700;

            text-decoration: none;
        }

        .auth-links a:hover,
        .auth-footer a:hover {
            text-decoration: underline;
        }

        .remember {
            display: flex;

            align-items: center;

            gap: 7px;
        }

        .alert {
            margin-bottom: 14px;

            padding: 11px 12px;

            border-radius: 9px;

            font-size: 12px;
            line-height: 1.5;
        }

        .alert-error {
            border: 1px solid #fecaca;

            background: #fef2f2;

            color: #b91c1c;
        }

        .alert-success {
            border: 1px solid #bbf7d0;

            background: #f0fdf4;

            color: #166534;
        }

        .notice {
            margin-bottom: 16px;

            padding: 11px 12px;

            border: 1px solid #bae6fd;
            border-radius: 9px;

            background: #f0f9ff;

            color: #0369a1;

            font-size: 12px;
            line-height: 1.5;
        }

        .auth-footer {
            padding: 17px 28px;

            border-top: 1px solid #e2e8f0;

            background: #f8fafc;

            color: #64748b;

            font-size: 12px;

            text-align: center;
        }
    </style>

</head>

<body>

    <div class="auth-card">

        <div class="auth-header">

            <div class="auth-logo">
                MP
            </div>

            <h1>
                Lupa Password
            </h1>

            <p>
                Masukkan email akun untuk menerima link reset password.
            </p>

        </div>


        <div class="auth-body">

            @if (session('status'))

                <div class="alert alert-success">
                    {{ session('status') }}
                </div>

            @endif


            @if ($errors->any())

                <div class="alert alert-error">

                    @foreach ($errors->all() as $error)

                        <div>
                            {{ $error }}
                        </div>

                    @endforeach

                </div>

            @endif


            <form
                action="{{ route('password.email') }}"
                method="POST"
            >

                @csrf


                <div class="field">

                    <label for="email">
                        Email
                    </label>

                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        class="control"
                        placeholder="nama@perusahaan.com"
                        autocomplete="email"
                        required
                        autofocus
                    >

                </div>


                <button
                    type="submit"
                    class="button"
                >
                    Kirim Link Reset
                </button>

            </form>

        </div>


        <div class="auth-footer">

            <a href="{{ route('login') }}">
                ← Kembali ke Login
            </a>

        </div>

    </div>

</body>
</html>

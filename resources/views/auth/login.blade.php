<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Login - Monitoring Penugasan Layanan
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


        /* =========================================================
           HEADER
        ========================================================= */

        .auth-header {
            padding: 28px 28px 18px;

            text-align: center;
        }

        .auth-logo-wrap {
            display: flex;

            align-items: center;
            justify-content: center;

            margin: 0 auto 14px;
        }

        .auth-logo-image {
            width: 88px;
            height: 88px;

            display: block;

            object-fit: contain;
        }

        .auth-header h1 {
            margin: 0;

            color: #0f172a;

            font-size: 22px;
            font-weight: 700;

            line-height: 1.3;
        }

        .auth-header p {
            margin: 6px 0 0;

            color: #64748b;

            font-size: 13px;
            line-height: 1.5;
        }


        /* =========================================================
           BODY
        ========================================================= */

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

            border: 1px solid #dbe4f0;
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


        /* =========================================================
           PASSWORD
        ========================================================= */

        .password-wrapper {
            position: relative;
        }

        .password-wrapper .control {
            padding-right: 50px;
        }

        .password-toggle {
            position: absolute;

            top: 50%;
            right: 8px;

            transform: translateY(-50%);

            display: inline-flex;

            width: 34px;
            height: 34px;

            align-items: center;
            justify-content: center;

            padding: 0;

            border: 0;
            border-radius: 8px;

            background: transparent;
            color: #059669;

            cursor: pointer;

            transition: .15s ease;
        }

        .password-toggle:hover {
            background: #ecfdf5;
        }

        .password-toggle:focus-visible {
            outline: 2px solid #10b981;
            outline-offset: 2px;
        }

        .password-toggle svg {
            width: 19px;
            height: 19px;

            display: block;

            pointer-events: none;
        }


        /*
        |--------------------------------------------------------------------------
        | LOGIKA ICON
        |--------------------------------------------------------------------------
        |
        | Default:
        | password tersembunyi -> mata dicoret
        |
        | is-visible:
        | password terlihat -> mata normal
        |
        */

        .password-toggle .icon-eye {
            display: none;
        }

        .password-toggle .icon-eye-off {
            display: block;
        }

        .password-toggle.is-visible .icon-eye {
            display: block;
        }

        .password-toggle.is-visible .icon-eye-off {
            display: none;
        }


        /* =========================================================
           REMEMBER / FORGOT
        ========================================================= */

        .row-between {
            display: flex;

            align-items: center;
            justify-content: space-between;

            gap: 12px;

            margin: 6px 0 16px;
        }

        .remember-me {
            display: inline-flex;

            align-items: center;

            gap: 8px;

            color: #64748b;

            font-size: 12px;
        }

        .remember-me input {
            width: 14px;
            height: 14px;

            accent-color: #059669;
        }

        .forgot-link {
            color: #059669;

            font-size: 12px;
            font-weight: 700;

            text-decoration: none;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }


        /* =========================================================
           BUTTON
        ========================================================= */

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

            transition: .15s ease;
        }

        .button:hover {
            background: #047857;
        }


        /* =========================================================
           ALERT
        ========================================================= */

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

        .alert-status {
            border: 1px solid #bae6fd;

            background: #f0f9ff;
            color: #0369a1;
        }


        /* =========================================================
           FOOTER
        ========================================================= */

        .auth-footer {
            padding: 17px 28px;

            border-top: 1px solid #e2e8f0;

            background: #f8fafc;
            color: #64748b;

            font-size: 12px;

            text-align: center;
        }

        .auth-footer a {
            color: #059669;

            font-weight: 700;

            text-decoration: none;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }


        /* =========================================================
           RESPONSIVE
        ========================================================= */

        @media (max-width: 500px) {

            body {
                padding: 16px;
            }

            .auth-header {
                padding:
                    24px
                    20px
                    16px;
            }

            .auth-logo-image {
                width: 78px;
                height: 78px;
            }

            .auth-body {
                padding:
                    10px
                    20px
                    22px;
            }

            .auth-footer {
                padding:
                    15px
                    20px;
            }

            .row-between {
                flex-wrap: wrap;
            }

        }
    </style>
</head>


<body>

    <div class="auth-card">


        {{-- =====================================================
             HEADER
        ====================================================== --}}

        <div class="auth-header">

            <div class="auth-logo-wrap">

                <img
                    src="{{ asset('bbkfk-logo.png') }}"
                    alt="Logo BBKFK"
                    class="auth-logo-image"
                >

            </div>


            <h1>
                Monitoring Penugasan Layanan
            </h1>


            <p>
                Masuk menggunakan email atau username.
            </p>

        </div>


        {{-- =====================================================
             BODY
        ====================================================== --}}

        <div class="auth-body">


            {{-- SUCCESS --}}
            @if (session('success'))

                <div class="alert alert-success">

                    {{ session('success') }}

                </div>

            @endif


            {{-- STATUS --}}
            @if (session('status'))

                <div class="alert alert-status">

                    {{ session('status') }}

                </div>

            @endif


            {{-- ERROR --}}
            @if ($errors->any())

                <div class="alert alert-error">

                    @foreach ($errors->all() as $error)

                        <div>
                            {{ $error }}
                        </div>

                    @endforeach

                </div>

            @endif


            {{-- =================================================
                 LOGIN FORM
            ================================================== --}}

            <form
                action="{{ route('login.attempt') }}"
                method="POST"
            >

                @csrf


                {{-- =================================================
                     EMAIL / USERNAME
                ================================================== --}}

                <div class="field">

                    <label for="login">
                        Email / Username
                    </label>

                    <input
                        id="login"
                        type="text"
                        name="login"
                        value="{{ old('login') }}"
                        class="control"
                        placeholder="Masukkan email atau username"
                        autocomplete="username"
                        required
                        autofocus
                    >

                </div>


                {{-- =================================================
                     PASSWORD
                ================================================== --}}

                <div class="field">

                    <label for="password">
                        Password
                    </label>


                    <div class="password-wrapper">

                        <input
                            id="password"
                            type="password"
                            name="password"
                            class="control"
                            placeholder="Masukkan password"
                            autocomplete="current-password"
                            required
                        >


                        <button
                            type="button"
                            class="password-toggle"
                            data-target="password"
                            aria-label="Lihat password"
                            aria-pressed="false"
                            title="Lihat password"
                        >

                            {{-- =================================================
                                 ICON MATA NORMAL
                                 Password sedang terlihat
                            ================================================== --}}

                            <svg
                                class="icon-eye"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                                aria-hidden="true"
                            >

                                <path
                                    d="
                                        M2.5 12
                                        C4.7 7.8 8 5.5 12 5.5
                                        C16 5.5 19.3 7.8 21.5 12
                                        C19.3 16.2 16 18.5 12 18.5
                                        C8 18.5 4.7 16.2 2.5 12Z
                                    "
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />

                                <circle
                                    cx="12"
                                    cy="12"
                                    r="2.5"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                />

                            </svg>


                            {{-- =================================================
                                 ICON MATA DICORET
                                 Password sedang tersembunyi
                            ================================================== --}}

                            <svg
                                class="icon-eye-off"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                                aria-hidden="true"
                            >

                                <path
                                    d="M3 3L21 21"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                />

                                <path
                                    d="
                                        M10.6 5.7
                                        C11.05 5.57 11.52 5.5 12 5.5
                                        C16 5.5 19.3 7.8 21.5 12
                                        C20.72 13.48 19.81 14.72 18.79 15.7
                                    "
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />

                                <path
                                    d="
                                        M15.2 17.7
                                        C14.2 18.23 13.13 18.5 12 18.5
                                        C8 18.5 4.7 16.2 2.5 12
                                        C3.35 10.38 4.35 9.04 5.49 7.99
                                    "
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />

                                <path
                                    d="
                                        M9.88 9.88
                                        C9.33 10.43 9 11.18 9 12
                                        C9 13.66 10.34 15 12 15
                                        C12.82 15 13.57 14.67 14.12 14.12
                                    "
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                />

                            </svg>

                        </button>

                    </div>

                </div>


                {{-- =================================================
                     REMEMBER / FORGOT
                ================================================== --}}

                <div class="row-between">

                    <label class="remember-me">

                        <input
                            type="checkbox"
                            name="remember"
                            value="1"
                            {{ old('remember') ? 'checked' : '' }}
                        >

                        <span>
                            Ingat saya
                        </span>

                    </label>


                    <a
                        href="{{ route('password.request') }}"
                        class="forgot-link"
                    >
                        Lupa password?
                    </a>

                </div>


                {{-- =================================================
                     SUBMIT
                ================================================== --}}

                <button
                    type="submit"
                    class="button"
                >
                    Masuk
                </button>

            </form>

        </div>


        {{-- =====================================================
             FOOTER
        ====================================================== --}}

        <div class="auth-footer">

            Belum punya akun?

            <a href="{{ route('register') }}">
                Buat akun
            </a>

        </div>

    </div>


    {{-- =========================================================
         SHOW / HIDE PASSWORD
    ========================================================== --}}

    <script>
        document
            .querySelectorAll(
                '.password-toggle'
            )
            .forEach(
                function (button) {

                    button.addEventListener(
                        'click',
                        function () {

                            const targetId =
                                button.dataset.target;


                            const input =
                                document.getElementById(
                                    targetId
                                );


                            if (!input) {
                                return;
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | Cek Kondisi Password
                            |--------------------------------------------------------------------------
                            */

                            const isHidden =
                                input.type ===
                                'password';


                            /*
                            |--------------------------------------------------------------------------
                            | Jika Tersembunyi -> Tampilkan
                            | Jika Terlihat -> Sembunyikan
                            |--------------------------------------------------------------------------
                            */

                            input.type =
                                isHidden
                                    ? 'text'
                                    : 'password';


                            /*
                            |--------------------------------------------------------------------------
                            | Class Icon
                            |--------------------------------------------------------------------------
                            |
                            | is-visible:
                            | mata normal
                            |
                            | tanpa is-visible:
                            | mata dicoret
                            |
                            */

                            button.classList.toggle(
                                'is-visible',
                                isHidden
                            );


                            /*
                            |--------------------------------------------------------------------------
                            | Accessibility
                            |--------------------------------------------------------------------------
                            */

                            button.setAttribute(
                                'aria-pressed',
                                isHidden
                                    ? 'true'
                                    : 'false'
                            );


                            button.setAttribute(
                                'aria-label',
                                isHidden
                                    ? 'Sembunyikan password'
                                    : 'Lihat password'
                            );


                            button.setAttribute(
                                'title',
                                isHidden
                                    ? 'Sembunyikan password'
                                    : 'Lihat password'
                            );

                        }
                    );

                }
            );
    </script>

</body>
</html>
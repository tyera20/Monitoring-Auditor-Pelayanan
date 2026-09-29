<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Buat Akun - Monitoring Penugasan Layanan
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
            box-shadow: 0 12px 35px rgba(15, 23, 42, .08);
        }

        .auth-header {
            padding: 28px 28px 18px;
            text-align: center;
        }

        .auth-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            margin: 0 auto 14px;
            background: transparent;
        }

        .auth-logo-image {
            display: block;
            width: 88px;
            height: 88px;
            max-width: 100%;
            object-fit: contain;
        }

        .auth-header h1 {
            margin: 0;
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
            box-shadow: 0 0 0 1px #059669;
        }

        /* PASSWORD */

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

        @media (max-width: 500px) {
            body {
                padding: 16px;
            }

            .auth-header {
                padding: 24px 20px 16px;
            }

            .auth-logo-image {
                width: 78px;
                height: 78px;
            }

            .auth-body {
                padding: 10px 20px 22px;
            }

            .auth-footer {
                padding: 15px 20px;
            }
        }
    </style>
</head>

<body>

    <div class="auth-card">

        <div class="auth-header">

            <div class="auth-logo">
                <img
                    src="{{ asset('bbkfk-logo.png') }}"
                    alt="Logo BBKFK"
                    class="auth-logo-image"
                >
            </div>

            <h1>
                Buat Akun
            </h1>

            <p>
                Akun aktif setelah disetujui Administrator.
            </p>

        </div>


        <div class="auth-body">

            @if (session('success'))

                <div class="alert alert-success">
                    {{ session('success') }}
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
                action="{{ route('register.store') }}"
                method="POST"
            >

                @csrf


                <div class="field">

                    <label for="name">
                        Nama Lengkap
                    </label>

                    <input
                        id="name"
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        class="control"
                        placeholder="Masukkan nama lengkap"
                        autocomplete="name"
                        required
                        autofocus
                    >

                </div>


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
                    >

                </div>


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
                            placeholder="Minimal 8 karakter"
                            autocomplete="new-password"
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
                            <svg
                                class="icon-eye"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                                aria-hidden="true"
                            >
                                <path
                                    d="M2.5 12C4.7 7.8 8 5.5 12 5.5C16 5.5 19.3 7.8 21.5 12C19.3 16.2 16 18.5 12 18.5C8 18.5 4.7 16.2 2.5 12Z"
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
                                    d="M10.6 5.7C11.05 5.57 11.52 5.5 12 5.5C16 5.5 19.3 7.8 21.5 12C20.72 13.48 19.81 14.72 18.79 15.7"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />

                                <path
                                    d="M15.2 17.7C14.2 18.23 13.13 18.5 12 18.5C8 18.5 4.7 16.2 2.5 12C3.35 10.38 4.35 9.04 5.49 7.99"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />

                                <path
                                    d="M9.88 9.88C9.33 10.43 9 11.18 9 12C9 13.66 10.34 15 12 15C12.82 15 13.57 14.67 14.12 14.12"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                />
                            </svg>
                        </button>

                    </div>

                </div>


                <div class="field">

                    <label for="password_confirmation">
                        Konfirmasi Password
                    </label>

                    <div class="password-wrapper">

                        <input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            class="control"
                            placeholder="Ulangi password"
                            autocomplete="new-password"
                            required
                        >

                        <button
                            type="button"
                            class="password-toggle"
                            data-target="password_confirmation"
                            aria-label="Lihat konfirmasi password"
                            aria-pressed="false"
                            title="Lihat konfirmasi password"
                        >
                            <svg
                                class="icon-eye"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                                aria-hidden="true"
                            >
                                <path
                                    d="M2.5 12C4.7 7.8 8 5.5 12 5.5C16 5.5 19.3 7.8 21.5 12C19.3 16.2 16 18.5 12 18.5C8 18.5 4.7 16.2 2.5 12Z"
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
                                    d="M10.6 5.7C11.05 5.57 11.52 5.5 12 5.5C16 5.5 19.3 7.8 21.5 12C20.72 13.48 19.81 14.72 18.79 15.7"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />

                                <path
                                    d="M15.2 17.7C14.2 18.23 13.13 18.5 12 18.5C8 18.5 4.7 16.2 2.5 12C3.35 10.38 4.35 9.04 5.49 7.99"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />

                                <path
                                    d="M9.88 9.88C9.33 10.43 9 11.18 9 12C9 13.66 10.34 15 12 15C12.82 15 13.57 14.67 14.12 14.12"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                />
                            </svg>
                        </button>

                    </div>

                </div>


                <button
                    type="submit"
                    class="button"
                >
                    Ajukan Akun
                </button>

            </form>

        </div>


        <div class="auth-footer">

            Sudah punya akun?

            <a href="{{ route('login') }}">
                Kembali ke Login
            </a>

        </div>

    </div>


    <script>
        document
            .querySelectorAll('.password-toggle')
            .forEach(function (button) {

                button.addEventListener(
                    'click',
                    function () {

                        const targetId =
                            button.dataset.target;

                        const input =
                            document.getElementById(
                                targetId
                            );

                        if (! input) {
                            return;
                        }

                        const isHidden =
                            input.type === 'password';

                        input.type =
                            isHidden
                                ? 'text'
                                : 'password';

                        button.classList.toggle(
                            'is-visible',
                            isHidden
                        );

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

            });
    </script>

</body>
</html>

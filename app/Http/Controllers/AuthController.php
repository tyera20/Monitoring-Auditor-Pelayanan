<?php

namespace App\Http\Controllers;

use App\Models\AccountRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LOGIN PAGE
    |--------------------------------------------------------------------------
    */

    public function showLogin(): View
    {
        return view('auth.login');
    }

    /*
    |--------------------------------------------------------------------------
    | LOGIN
    |--------------------------------------------------------------------------
    |
    | Akun baru login menggunakan email.
    | Akun lama tetap dapat login menggunakan username.
    |
    */

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            [
                'login' => [
                    'required',
                    'string',
                    'max:255',
                ],
                'password' => [
                    'required',
                    'string',
                ],
            ],
            [
                'login.required' => 'Email atau username wajib diisi.',
                'password.required' => 'Password wajib diisi.',
            ]
        );

        $login = trim($validated['login']);

        /*
        |--------------------------------------------------------------------------
        | Cari User Aktif / Existing User
        |--------------------------------------------------------------------------
        */

        $user = User::query()
            ->where(function ($query) use ($login) {
                $query
                    ->where('email', $login)
                    ->orWhere('username', $login);
            })
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Jika Belum Menjadi User, Cek Pengajuan
        |--------------------------------------------------------------------------
        */

        if (! $user) {
            $pendingRequest = AccountRequest::query()
                ->where('email', strtolower($login))
                ->exists();

            if ($pendingRequest) {
                return back()
                    ->withErrors([
                        'login' => 'Pengajuan akun masih menunggu persetujuan Administrator.',
                    ])
                    ->onlyInput('login');
            }

            return back()
                ->withErrors([
                    'login' => 'Email/username atau password tidak sesuai.',
                ])
                ->onlyInput('login');
        }

        /*
        |--------------------------------------------------------------------------
        | Cek Password
        |--------------------------------------------------------------------------
        */

        if (! Hash::check($validated['password'], $user->password)) {
            return back()
                ->withErrors([
                    'login' => 'Email/username atau password tidak sesuai.',
                ])
                ->onlyInput('login');
        }

        /*
        |--------------------------------------------------------------------------
        | Status Akun
        |--------------------------------------------------------------------------
        |
        | Akun lama yang belum mempunyai account_status dianggap aktif.
        |
        */

        $accountStatus = trim(
            (string) ($user->account_status ?? '')
        );

        if ($accountStatus === '') {
            $accountStatus = 'active';
        }

        if ($accountStatus === 'pending') {
            return back()
                ->withErrors([
                    'login' => 'Akun masih menunggu persetujuan Administrator.',
                ])
                ->onlyInput('login');
        }

        if ($accountStatus === 'rejected') {
            return back()
                ->withErrors([
                    'login' => 'Pengajuan akun tidak disetujui Administrator.',
                ])
                ->onlyInput('login');
        }

        if ($accountStatus !== 'active') {
            return back()
                ->withErrors([
                    'login' => 'Akun belum aktif. Silakan hubungi Administrator.',
                ])
                ->onlyInput('login');
        }

        /*
        |--------------------------------------------------------------------------
        | Login
        |--------------------------------------------------------------------------
        */

        Auth::login(
            $user,
            $request->boolean('remember')
        );

        $request
            ->session()
            ->regenerate();

        return redirect()
            ->intended(
                route('dashboard')
            );
    }

    /*
    |--------------------------------------------------------------------------
    | REGISTER PAGE
    |--------------------------------------------------------------------------
    */

    public function showRegister(): View
    {
        return view('auth.register');
    }

    /*
    |--------------------------------------------------------------------------
    | REGISTER / AJUKAN AKUN
    |--------------------------------------------------------------------------
    |
    | Register tidak langsung membuat akun pada tabel users.
    | Data masuk ke account_requests.
    | User baru dibuat setelah Administrator menyetujui pengajuan.
    |
    */

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            [
                'name' => [
                    'required',
                    'string',
                    'max:100',
                ],
                'email' => [
                    'required',
                    'email',
                    'max:150',
                ],
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                ],
            ],
            [
                'name.required' => 'Nama lengkap wajib diisi.',
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Format email tidak valid.',
                'password.required' => 'Password wajib diisi.',
                'password.min' => 'Password minimal 8 karakter.',
                'password.confirmed' => 'Konfirmasi password tidak sama.',
            ]
        );

        $email = strtolower(
            trim($validated['email'])
        );

        /*
        |--------------------------------------------------------------------------
        | Cek Email Sudah Menjadi User
        |--------------------------------------------------------------------------
        */

        if (
            User::query()
                ->where('email', $email)
                ->exists()
        ) {
            return back()
                ->withErrors([
                    'email' => 'Email sudah terdaftar sebagai akun.',
                ])
                ->withInput(
                    $request->only(
                        'name',
                        'email'
                    )
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Cek Email Sedang Mengajukan Akun
        |--------------------------------------------------------------------------
        */

        if (
            AccountRequest::query()
                ->where('email', $email)
                ->exists()
        ) {
            return back()
                ->withErrors([
                    'email' => 'Email ini sudah mengajukan akun dan masih menunggu persetujuan Administrator.',
                ])
                ->withInput(
                    $request->only(
                        'name',
                        'email'
                    )
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Simpan Sebagai Pengajuan
        |--------------------------------------------------------------------------
        */

        AccountRequest::create([
            'name' => trim($validated['name']),
            'email' => $email,
            'password' => Hash::make(
                $validated['password']
            ),
        ]);

        return redirect()
            ->route('login')
            ->with(
                'success',
                'Pengajuan akun berhasil dikirim. Akun akan dibuat setelah disetujui Administrator.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | FORGOT PASSWORD PAGE
    |--------------------------------------------------------------------------
    */

    public function showForgotPassword(): View
    {
        return view('auth.forgot-password');
    }

    /*
    |--------------------------------------------------------------------------
    | SEND RESET LINK
    |--------------------------------------------------------------------------
    */

    public function sendResetLink(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            [
                'email' => [
                    'required',
                    'email',
                    'max:150',
                ],
            ],
            [
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Format email tidak valid.',
            ]
        );

        $email = strtolower(
            trim($validated['email'])
        );

        /*
        |--------------------------------------------------------------------------
        | Pengajuan Belum Disetujui
        |--------------------------------------------------------------------------
        */

        if (
            AccountRequest::query()
                ->where('email', $email)
                ->exists()
        ) {
            return back()
                ->withErrors([
                    'email' => 'Pengajuan akun masih menunggu persetujuan Administrator.',
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | Cari User
        |--------------------------------------------------------------------------
        */

        $user = User::query()
            ->where('email', $email)
            ->first();

        if (! $user) {
            return back()
                ->with(
                    'status',
                    'Jika email terdaftar dan aktif, link reset password akan dikirim.'
                )
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | Cek Status User
        |--------------------------------------------------------------------------
        */

        $accountStatus = trim(
            (string) ($user->account_status ?? '')
        );

        if ($accountStatus === '') {
            $accountStatus = 'active';
        }

        if ($accountStatus !== 'active') {
            return back()
                ->withErrors([
                    'email' => 'Akun belum aktif. Silakan hubungi Administrator.',
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | Kirim Link Reset
        |--------------------------------------------------------------------------
        */

        $status = Password::sendResetLink([
            'email' => $email,
        ]);

        if ($status === Password::RESET_LINK_SENT) {
            return back()
                ->with(
                    'status',
                    'Link reset password berhasil dikirim. Silakan periksa email.'
                );
        }

        return back()
            ->withErrors([
                'email' => match ($status) {
                    Password::RESET_THROTTLED =>
                        'Terlalu banyak permintaan. Coba lagi beberapa saat.',

                    default =>
                        'Link reset password belum dapat dikirim. Periksa konfigurasi email aplikasi.',
                },
            ])
            ->withInput();
    }

    /*
    |--------------------------------------------------------------------------
    | RESET PASSWORD PAGE
    |--------------------------------------------------------------------------
    */

    public function showResetPassword(
        Request $request,
        string $token
    ): View {
        return view(
            'auth.reset-password',
            [
                'token' => $token,
                'email' => (string) $request->query(
                    'email',
                    ''
                ),
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RESET PASSWORD
    |--------------------------------------------------------------------------
    */

    public function resetPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            [
                'token' => [
                    'required',
                    'string',
                ],
                'email' => [
                    'required',
                    'email',
                ],
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                ],
            ],
            [
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Format email tidak valid.',
                'password.required' => 'Password baru wajib diisi.',
                'password.min' => 'Password minimal 8 karakter.',
                'password.confirmed' => 'Konfirmasi password tidak sama.',
            ]
        );

        $email = strtolower(
            trim($validated['email'])
        );

        /*
        |--------------------------------------------------------------------------
        | Pastikan User Aktif
        |--------------------------------------------------------------------------
        */

        $user = User::query()
            ->where('email', $email)
            ->first();

        if (! $user) {
            return back()
                ->withErrors([
                    'email' => 'Email tidak ditemukan.',
                ])
                ->withInput(
                    $request->only('email')
                );
        }

        $accountStatus = trim(
            (string) ($user->account_status ?? '')
        );

        if ($accountStatus === '') {
            $accountStatus = 'active';
        }

        if ($accountStatus !== 'active') {
            return back()
                ->withErrors([
                    'email' => 'Akun belum aktif atau belum disetujui Administrator.',
                ])
                ->withInput(
                    $request->only('email')
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Reset Password
        |--------------------------------------------------------------------------
        */

        $status = Password::reset(
            [
                'email' => $email,
                'password' => $validated['password'],
                'password_confirmation' => $request->input(
                    'password_confirmation'
                ),
                'token' => $validated['token'],
            ],
            function (
                User $user,
                string $password
            ) {
                $user
                    ->forceFill([
                        'password' => Hash::make(
                            $password
                        ),
                        'remember_token' => Str::random(60),
                    ])
                    ->save();

                event(
                    new PasswordReset($user)
                );
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()
                ->route('login')
                ->with(
                    'success',
                    'Password berhasil diubah. Silakan login menggunakan password baru.'
                );
        }

        return back()
            ->withErrors([
                'email' => match ($status) {
                    Password::INVALID_TOKEN =>
                        'Link reset password tidak valid atau sudah kedaluwarsa.',

                    Password::INVALID_USER =>
                        'Email tidak ditemukan.',

                    default =>
                        'Password belum dapat diubah. Silakan minta link reset baru.',
                },
            ])
            ->withInput(
                $request->only('email')
            );
    }

    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    */

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request
            ->session()
            ->invalidate();

        $request
            ->session()
            ->regenerateToken();

        return redirect()
            ->route('login');
    }
}

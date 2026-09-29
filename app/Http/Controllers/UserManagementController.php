<?php

namespace App\Http\Controllers;

use App\Models\AccountRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    /**
     * ============================================================
     * INDEX
     * ============================================================
     */
    public function index(Request $request): View
    {
        $this->ensureAdmin();

        /*
        |--------------------------------------------------------------------------
        | Validasi Filter
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:255',
            ],

            'status' => [
                'nullable',
                Rule::in([
                    'pending',
                    'active',
                    'rejected',
                ]),
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Ambil Filter
        |--------------------------------------------------------------------------
        */

        $search = trim(
            (string) $request->input(
                'search',
                ''
            )
        );

        $status = trim(
            (string) $request->input(
                'status',
                ''
            )
        );


        /*
        |--------------------------------------------------------------------------
        | Pengajuan Akun
        |--------------------------------------------------------------------------
        |
        | Pengajuan register disimpan di tabel:
        |
        | account_requests
        |
        | Belum masuk ke users sampai disetujui Admin.
        |
        */

        $pendingApprovals = AccountRequest::query()
            ->latest('id')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Query User
        |--------------------------------------------------------------------------
        */

        $users = User::query()

            /*
            |--------------------------------------------------------------------------
            | Search
            |--------------------------------------------------------------------------
            */

            ->when(
                $search !== '',
                function ($query) use ($search) {

                    $query->where(
                        function ($subQuery) use ($search) {

                            $subQuery
                                ->where(
                                    'name',
                                    'like',
                                    '%' . $search . '%'
                                )
                                ->orWhere(
                                    'email',
                                    'like',
                                    '%' . $search . '%'
                                )
                                ->orWhere(
                                    'username',
                                    'like',
                                    '%' . $search . '%'
                                )
                                ->orWhere(
                                    'role',
                                    'like',
                                    '%' . $search . '%'
                                );

                        }
                    );

                }
            )

            /*
            |--------------------------------------------------------------------------
            | Filter Active
            |--------------------------------------------------------------------------
            |
            | User lama mungkin mempunyai account_status:
            |
            | NULL
            | ''
            |
            | Keduanya dianggap aktif.
            |
            */

            ->when(
                $status === 'active',
                function ($query) {

                    $query->where(
                        function ($subQuery) {

                            $subQuery
                                ->where(
                                    'account_status',
                                    'active'
                                )
                                ->orWhereNull(
                                    'account_status'
                                )
                                ->orWhere(
                                    'account_status',
                                    ''
                                );

                        }
                    );

                }
            )

            /*
            |--------------------------------------------------------------------------
            | Filter Rejected
            |--------------------------------------------------------------------------
            */

            ->when(
                $status === 'rejected',
                function ($query) {

                    $query->where(
                        'account_status',
                        'rejected'
                    );

                }
            )

            /*
            |--------------------------------------------------------------------------
            | Filter Pending
            |--------------------------------------------------------------------------
            |
            | Pending sekarang ada di account_requests.
            | Jadi ketika memilih pending, tabel USERS memang kosong.
            | Pengajuannya tetap tampil di card "Pengajuan Akun".
            |
            */

            ->when(
                $status === 'pending',
                function ($query) {

                    $query->whereRaw('1 = 0');

                }
            )

            ->orderBy('id')
            ->paginate(5)
            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | Statistik
        |--------------------------------------------------------------------------
        */

        $totalPending = AccountRequest::query()
            ->count();


        $totalActive = User::query()
            ->where(
                function ($query) {

                    $query
                        ->where(
                            'account_status',
                            'active'
                        )
                        ->orWhereNull(
                            'account_status'
                        )
                        ->orWhere(
                            'account_status',
                            ''
                        );

                }
            )
            ->count();


        $totalRejected = User::query()
            ->where(
                'account_status',
                'rejected'
            )
            ->count();


        /*
        |--------------------------------------------------------------------------
        | View
        |--------------------------------------------------------------------------
        */

        return view(
            'users.index',
            compact(
                'users',
                'pendingApprovals',
                'totalPending',
                'totalActive',
                'totalRejected',
                'search',
                'status'
            )
        );
    }


    /**
     * ============================================================
     * TAMBAH USER LANGSUNG OLEH ADMIN
     * ============================================================
     */
    public function store(
        Request $request
    ): RedirectResponse {

        $admin = $this->ensureAdmin();


        /*
        |--------------------------------------------------------------------------
        | Validasi
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
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
            ],

            'role' => [
                'required',
                Rule::in([
                    'admin',
                    'guest',
                ]),
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Normalisasi Email
        |--------------------------------------------------------------------------
        */

        $email = strtolower(
            trim(
                $validated['email']
            )
        );


        /*
        |--------------------------------------------------------------------------
        | Cek Email Sudah Menjadi User
        |--------------------------------------------------------------------------
        */

        if (
            User::query()
                ->where(
                    'email',
                    $email
                )
                ->exists()
        ) {

            return back()
                ->withErrors([
                    'email' =>
                        'Email sudah digunakan.',
                ])
                ->withInput();

        }


        /*
        |--------------------------------------------------------------------------
        | Cek Email Sedang Mengajukan Akun
        |--------------------------------------------------------------------------
        */

        if (
            AccountRequest::query()
                ->where(
                    'email',
                    $email
                )
                ->exists()
        ) {

            return back()
                ->withErrors([
                    'email' =>
                        'Email ini sedang memiliki pengajuan akun. Setujui atau tolak pengajuannya terlebih dahulu.',
                ])
                ->withInput();

        }


        /*
        |--------------------------------------------------------------------------
        | Generate Username
        |--------------------------------------------------------------------------
        */

        $username = $this->generateUsername(
            $email
        );


        /*
        |--------------------------------------------------------------------------
        | Buat User
        |--------------------------------------------------------------------------
        |
        | Karena dibuat langsung oleh Admin:
        |
        | account_status = active
        |
        */

        User::create([
            'name' =>
                trim(
                    $validated['name']
                ),

            'username' =>
                $username,

            'email' =>
                $email,

            'password' =>
                Hash::make(
                    $validated['password']
                ),

            'role' =>
                $validated['role'],

            'account_status' =>
                'active',

            'approved_at' =>
                now(),

            'approved_by' =>
                $admin->id,
        ]);


        return redirect()
            ->route(
                'user-management.index'
            )
            ->with(
                'success',
                'User berhasil ditambahkan.'
            );
    }


    /**
     * ============================================================
     * UPDATE USER
     * ============================================================
     */
    public function update(
        Request $request,
        User $user_management
    ): RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | Ambil Admin yang Sedang Login
        |--------------------------------------------------------------------------
        */

        $admin = $this->ensureAdmin();


        /*
        |--------------------------------------------------------------------------
        | Validasi
        |--------------------------------------------------------------------------
        |
        | PENTING:
        |
        | Kita TIDAK lagi mewajibkan:
        |
        | - username
        | - account_status
        |
        | karena form Edit User saat ini memang tidak mengirim field tersebut.
        |
        */

        $validated = $request->validate([
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

            'role' => [
                'required',
                Rule::in([
                    'admin',
                    'guest',
                ]),
            ],

            'password' => [
                'nullable',
                'string',
                'min:8',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | PROTEKSI ADMIN YANG SEDANG LOGIN
        |--------------------------------------------------------------------------
        |
        | Administrator boleh mengubah:
        |
        | - nama
        | - email
        | - password
        |
        | Tetapi TIDAK BOLEH mengubah role dirinya sendiri:
        |
        | admin -> guest
        |
        | karena nanti akun tersebut langsung kehilangan akses
        | User Management.
        |
        */

        if (
            $admin->id ===
            $user_management->id
            &&
            $validated['role'] !== 'admin'
        ) {

            return redirect()
                ->route(
                    'user-management.index'
                )
                ->with(
                    'error',
                    'Role akun Administrator yang sedang digunakan tidak dapat diubah menjadi guest.'
                );

        }


        /*
        |--------------------------------------------------------------------------
        | Normalisasi Email
        |--------------------------------------------------------------------------
        */

        $email = strtolower(
            trim(
                $validated['email']
            )
        );


        /*
        |--------------------------------------------------------------------------
        | Cek Email Dipakai User Lain
        |--------------------------------------------------------------------------
        */

        $emailUsed = User::query()
            ->where(
                'email',
                $email
            )
            ->where(
                'id',
                '!=',
                $user_management->id
            )
            ->exists();


        if ($emailUsed) {

            return back()
                ->withErrors([
                    'email' =>
                        'Email sudah digunakan user lain.',
                ])
                ->withInput();

        }


        /*
        |--------------------------------------------------------------------------
        | Cek Email Sedang Dipakai Account Request
        |--------------------------------------------------------------------------
        */

        $requestEmailUsed = AccountRequest::query()
            ->where(
                'email',
                $email
            )
            ->exists();


        if ($requestEmailUsed) {

            return back()
                ->withErrors([
                    'email' =>
                        'Email tersebut sedang digunakan pada pengajuan akun.',
                ])
                ->withInput();

        }


        /*
        |--------------------------------------------------------------------------
        | Data yang Akan Diupdate
        |--------------------------------------------------------------------------
        |
        | Username tidak disentuh.
        | Status akun juga tidak disentuh.
        |
        */

        $data = [
            'name' =>
                trim(
                    $validated['name']
                ),

            'email' =>
                $email,

            'role' =>
                $validated['role'],
        ];


        /*
        |--------------------------------------------------------------------------
        | Password Opsional
        |--------------------------------------------------------------------------
        */

        if (
            isset(
                $validated['password']
            )
            &&
            $validated['password'] !== ''
        ) {

            $data['password'] =
                Hash::make(
                    $validated['password']
                );

        }


        /*
        |--------------------------------------------------------------------------
        | Update
        |--------------------------------------------------------------------------
        */

        $user_management->update(
            $data
        );


        return redirect()
            ->route(
                'user-management.index'
            )
            ->with(
                'success',
                'Data user berhasil diperbarui.'
            );
    }


    /**
     * ============================================================
     * SETUJUI PENGAJUAN AKUN
     * ============================================================
     */
    public function approve(
        AccountRequest $accountRequest
    ): RedirectResponse {

        $admin = $this->ensureAdmin();


        /*
        |--------------------------------------------------------------------------
        | Pastikan Email Belum Menjadi User
        |--------------------------------------------------------------------------
        */

        if (
            User::query()
                ->where(
                    'email',
                    $accountRequest->email
                )
                ->exists()
        ) {

            return redirect()
                ->route(
                    'user-management.index'
                )
                ->with(
                    'error',
                    'Email tersebut sudah terdaftar sebagai user.'
                );

        }


        /*
        |--------------------------------------------------------------------------
        | Generate Username
        |--------------------------------------------------------------------------
        */

        $username = $this->generateUsername(
            $accountRequest->email
        );


        /*
        |--------------------------------------------------------------------------
        | Transaction
        |--------------------------------------------------------------------------
        |
        | Kalau User berhasil dibuat:
        |
        | account_requests dihapus.
        |
        | Kalau pembuatan User gagal:
        |
        | account_requests tetap ada.
        |
        */

        DB::transaction(
            function () use (
                $accountRequest,
                $admin,
                $username
            ) {

                User::create([
                    'name' =>
                        $accountRequest->name,

                    'username' =>
                        $username,

                    'email' =>
                        $accountRequest->email,

                    /*
                    |--------------------------------------------------------------------------
                    | Password Sudah Hash
                    |--------------------------------------------------------------------------
                    |
                    | Password AccountRequest sudah di-hash ketika register.
                    | Jangan Hash::make lagi.
                    |
                    */

                    'password' =>
                        $accountRequest->password,

                    /*
                    |--------------------------------------------------------------------------
                    | User Register Selalu Guest
                    |--------------------------------------------------------------------------
                    */

                    'role' =>
                        'guest',

                    'account_status' =>
                        'active',

                    'approved_at' =>
                        now(),

                    'approved_by' =>
                        $admin->id,
                ]);


                /*
                |--------------------------------------------------------------------------
                | Hapus Request Setelah Berhasil
                |--------------------------------------------------------------------------
                */

                $accountRequest->delete();

            }
        );


        return redirect()
            ->route(
                'user-management.index'
            )
            ->with(
                'success',
                'Pengajuan akun berhasil disetujui.'
            );
    }


    /**
     * ============================================================
     * TOLAK PENGAJUAN AKUN
     * ============================================================
     */
    public function reject(
        AccountRequest $accountRequest
    ): RedirectResponse {

        $this->ensureAdmin();


        /*
        |--------------------------------------------------------------------------
        | Simpan Nama untuk Notification
        |--------------------------------------------------------------------------
        */

        $name =
            $accountRequest->name;


        /*
        |--------------------------------------------------------------------------
        | Hapus Request
        |--------------------------------------------------------------------------
        */

        $accountRequest->delete();


        return redirect()
            ->route(
                'user-management.index'
            )
            ->with(
                'success',
                'Pengajuan akun ' .
                $name .
                ' berhasil ditolak.'
            );
    }


    /**
     * ============================================================
     * HAPUS USER
     * ============================================================
     */
    public function destroy(
        User $user_management
    ): RedirectResponse {

        $admin = $this->ensureAdmin();


        /*
        |--------------------------------------------------------------------------
        | Tidak Boleh Menghapus Akun Sendiri
        |--------------------------------------------------------------------------
        */

        if (
            $admin->id ===
            $user_management->id
        ) {

            return redirect()
                ->route(
                    'user-management.index'
                )
                ->with(
                    'error',
                    'Anda tidak dapat menghapus akun yang sedang digunakan.'
                );

        }


        /*
        |--------------------------------------------------------------------------
        | Hapus
        |--------------------------------------------------------------------------
        */

        $user_management->delete();


        return redirect()
            ->route(
                'user-management.index'
            )
            ->with(
                'success',
                'User berhasil dihapus.'
            );
    }


    /**
     * ============================================================
     * GENERATE USERNAME
     * ============================================================
     */
    private function generateUsername(
        string $email
    ): string {

        /*
        |--------------------------------------------------------------------------
        | Ambil Bagian Sebelum @
        |--------------------------------------------------------------------------
        */

        $emailName = Str::before(
            strtolower($email),
            '@'
        );


        /*
        |--------------------------------------------------------------------------
        | Bersihkan Username
        |--------------------------------------------------------------------------
        */

        $baseUsername = Str::slug(
            $emailName,
            ''
        );


        /*
        |--------------------------------------------------------------------------
        | Fallback
        |--------------------------------------------------------------------------
        */

        if (
            $baseUsername === ''
        ) {

            $baseUsername =
                'user';

        }


        /*
        |--------------------------------------------------------------------------
        | Username Awal
        |--------------------------------------------------------------------------
        */

        $username =
            $baseUsername;


        $number =
            1;


        /*
        |--------------------------------------------------------------------------
        | Pastikan Unik
        |--------------------------------------------------------------------------
        |
        | Contoh:
        |
        | tyera
        | tyera1
        | tyera2
        | tyera3
        |
        */

        while (
            User::query()
                ->where(
                    'username',
                    $username
                )
                ->exists()
        ) {

            $username =
                $baseUsername .
                $number;

            $number++;

        }


        return $username;
    }


    /**
     * ============================================================
     * PASTIKAN ADMIN
     * ============================================================
     */
    private function ensureAdmin(): User
    {
        /*
        |--------------------------------------------------------------------------
        | Ambil User Login
        |--------------------------------------------------------------------------
        */

        $user =
            Auth::user();


        /*
        |--------------------------------------------------------------------------
        | Harus User dan Role Admin
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $user instanceof User
            &&
            $user->role === 'admin',
            403
        );


        return $user;
    }
}
<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportPenugasanController;
use App\Http\Controllers\LayananController;
use App\Http\Controllers\PenugasanController;
use App\Http\Controllers\PetugasController;
use App\Http\Controllers\UserManagementController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| ROOT
|--------------------------------------------------------------------------
|
| Tidak menggunakan Auth::check() supaya warning Intelephense
| "Undefined method check" tidak muncul lagi.
|
*/

Route::get(
    '/',
    function () {
        $user =
            Auth::user();


        return $user instanceof User
            ? redirect()->route(
                'dashboard'
            )
            : redirect()->route(
                'login'
            );
    }
);


/*
|--------------------------------------------------------------------------
| GUEST
|--------------------------------------------------------------------------
*/

Route::middleware(
    'guest'
)
    ->group(
        function () {

            /*
            |--------------------------------------------------------------------------
            | LOGIN
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/login',
                [
                    AuthController::class,
                    'showLogin',
                ]
            )->name(
                'login'
            );


            Route::post(
                '/login',
                [
                    AuthController::class,
                    'login',
                ]
            )->name(
                'login.attempt'
            );


            /*
            |--------------------------------------------------------------------------
            | REGISTER
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/register',
                [
                    AuthController::class,
                    'showRegister',
                ]
            )->name(
                'register'
            );


            Route::post(
                '/register',
                [
                    AuthController::class,
                    'register',
                ]
            )->name(
                'register.store'
            );


            /*
            |--------------------------------------------------------------------------
            | FORGOT PASSWORD
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/forgot-password',
                [
                    AuthController::class,
                    'showForgotPassword',
                ]
            )->name(
                'password.request'
            );


            Route::post(
                '/forgot-password',
                [
                    AuthController::class,
                    'sendResetLink',
                ]
            )->name(
                'password.email'
            );


            /*
            |--------------------------------------------------------------------------
            | RESET PASSWORD
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/reset-password/{token}',
                [
                    AuthController::class,
                    'showResetPassword',
                ]
            )->name(
                'password.reset'
            );


            Route::post(
                '/reset-password',
                [
                    AuthController::class,
                    'resetPassword',
                ]
            )->name(
                'password.update'
            );
        }
    );


/*
|--------------------------------------------------------------------------
| AUTHENTICATED
|--------------------------------------------------------------------------
*/

Route::middleware(
    'auth'
)
    ->group(
        function () {

            /*
            |--------------------------------------------------------------------------
            | LOGOUT
            |--------------------------------------------------------------------------
            */

            Route::post(
                '/logout',
                [
                    AuthController::class,
                    'logout',
                ]
            )->name(
                'logout'
            );


            /*
            |--------------------------------------------------------------------------
            | DASHBOARD
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/dashboard',
                DashboardController::class
            )->name(
                'dashboard'
            );


            /*
            |--------------------------------------------------------------------------
            | EXPORT PENUGASAN
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/penugasan/export',
                ExportPenugasanController::class
            )->name(
                'penugasan.export'
            );


            /*
            |--------------------------------------------------------------------------
            | PENUGASAN
            |--------------------------------------------------------------------------
            */

            Route::resource(
                'penugasan',
                PenugasanController::class
            )->only([
                'index',
                'store',
                'update',
                'destroy',
            ]);


            /*
            |--------------------------------------------------------------------------
            | PETUGAS
            |--------------------------------------------------------------------------
            */

            Route::resource(
                'petugas',
                PetugasController::class
            )->only([
                'index',
                'store',
                'update',
                'destroy',
            ]);


            /*
            |--------------------------------------------------------------------------
            | LAYANAN
            |--------------------------------------------------------------------------
            */

            Route::resource(
                'layanan',
                LayananController::class
            )->only([
                'index',
                'store',
                'update',
                'destroy',
            ]);


            /*
            |--------------------------------------------------------------------------
            | USER MANAGEMENT
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/user-management',
                [
                    UserManagementController::class,
                    'index',
                ]
            )->name(
                'user-management.index'
            );


            /*
            |--------------------------------------------------------------------------
            | ADMIN BUAT USER MANUAL
            |--------------------------------------------------------------------------
            */

            Route::post(
                '/user-management',
                [
                    UserManagementController::class,
                    'store',
                ]
            )->name(
                'user-management.store'
            );


            /*
            |--------------------------------------------------------------------------
            | UPDATE USER
            |--------------------------------------------------------------------------
            */

            Route::put(
                '/user-management/{user_management}',
                [
                    UserManagementController::class,
                    'update',
                ]
            )->name(
                'user-management.update'
            );


            /*
            |--------------------------------------------------------------------------
            | DELETE USER
            |--------------------------------------------------------------------------
            */

            Route::delete(
                '/user-management/{user_management}',
                [
                    UserManagementController::class,
                    'destroy',
                ]
            )->name(
                'user-management.destroy'
            );


            /*
            |--------------------------------------------------------------------------
            | APPROVE ACCOUNT REQUEST
            |--------------------------------------------------------------------------
            |
            | accountRequest adalah data dari tabel account_requests,
            | BUKAN dari tabel users.
            |
            */

            Route::patch(
                '/user-management/account-request/{accountRequest}/approve',
                [
                    UserManagementController::class,
                    'approve',
                ]
            )->name(
                'user-management.approve'
            );


            /*
            |--------------------------------------------------------------------------
            | REJECT ACCOUNT REQUEST
            |--------------------------------------------------------------------------
            */

            Route::patch(
                '/user-management/account-request/{accountRequest}/reject',
                [
                    UserManagementController::class,
                    'reject',
                ]
            )->name(
                'user-management.reject'
            );
        }
    );
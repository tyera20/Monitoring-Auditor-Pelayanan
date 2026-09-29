@extends('layouts.app', ['title' => 'Menu Petugas'])

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | USER / ROLE
    |--------------------------------------------------------------------------
    */

    $currentUser = auth()->user();

    $isAdmin =
        $currentUser
        &&
        $currentUser->role === 'admin';


    /*
    |--------------------------------------------------------------------------
    | FALLBACK VARIABLE
    |--------------------------------------------------------------------------
    |
    | Supaya view tetap aman jika beberapa statistik belum dikirim controller.
    |
    */

    $search =
        $search
        ?? request('search', '');

    $jenjang =
        $jenjang
        ?? request('jenjang', '');

    $jenjangOptions =
        $jenjangOptions
        ?? collect();

    $totalPetugas =
        $totalPetugas
        ?? $petugas->total();

    $ditampilkan =
        $ditampilkan
        ?? $petugas->count();

    $petugasDitugaskan =
        $petugasDitugaskan
        ?? 0;

    $totalPenugasan =
        $totalPenugasan
        ?? 0;


    /*
    |--------------------------------------------------------------------------
    | DATA EDIT
    |--------------------------------------------------------------------------
    */

    $editItems = $petugas
        ->getCollection()
        ->map(function ($item) {
            return [
                'id' => $item->id,

                'name' =>
                    $item->name,

                'nip' =>
                    $item->nip,

                'position' =>
                    $item->position,

                'update_url' =>
                    route(
                        'petugas.update',
                        $item
                    ),

                'destroy_url' =>
                    route(
                        'petugas.destroy',
                        $item
                    ),
            ];
        })
        ->values()
        ->all();


    /*
    |--------------------------------------------------------------------------
    | OLD INPUT
    |--------------------------------------------------------------------------
    */

    $initialForm = [
        'name' =>
            old(
                'name',
                ''
            ),

        'nip' =>
            old(
                'nip',
                ''
            ),

        'position' =>
            old(
                'position',
                ''
            ),
    ];


    /*
    |--------------------------------------------------------------------------
    | VALIDATION ERROR
    |--------------------------------------------------------------------------
    */

    $errorMode = null;

    if ($errors->any()) {
        $errorMode =
            old('_method') === 'PUT'
                ? 'edit'
                : 'create';
    }

    $editingId =
        old('editing_id');


    /*
    |--------------------------------------------------------------------------
    | WARNA AVATAR
    |--------------------------------------------------------------------------
    */

    $avatarPalette = [
        '#84cc16',
        '#f59e0b',
        '#0ea5e9',
        '#10b981',
        '#8b5cf6',
        '#ec4899',
        '#6366f1',
        '#14b8a6',
        '#f97316',
    ];
@endphp


<style>
    [x-cloak] {
        display: none !important;
    }


    /* =========================================================
       ROOT
    ========================================================= */

    .petugas-page {
        --pet-card: #ffffff;

        --pet-text: #0f172a;

        --pet-muted: #64748b;

        --pet-border: #e2e8f0;

        --pet-background: #f4f6fa;

        --pet-green: #059669;

        --pet-green-hover: #047857;

        --pet-red: #e11d48;

        --pet-red-hover: #be123c;

        color: var(--pet-text);

        font-family:
            Inter,
            system-ui,
            -apple-system,
            BlinkMacSystemFont,
            "Segoe UI",
            sans-serif;
    }

    .petugas-page * {
        box-sizing: border-box;
    }


    /* =========================================================
       HEADER
    ========================================================= */

    .pet-header {
        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 16px;

        flex-wrap: wrap;

        margin-bottom: 20px;
    }

    .pet-title {
        margin: 0;

        color: var(--pet-text);

        font-size: 22px;

        font-weight: 700;

        line-height: 1.3;
    }

    .pet-subtitle {
        margin: 3px 0 0;

        color: var(--pet-muted);

        font-size: 13px;
    }

    .pet-header-actions {
        display: flex;

        align-items: center;

        gap: 8px;

        flex-wrap: wrap;
    }


    /* =========================================================
       BUTTON
    ========================================================= */

    .pet-button {
        display: inline-flex;

        min-height: 40px;

        align-items: center;

        justify-content: center;

        gap: 7px;

        padding: 8px 14px;

        border: 1px solid var(--pet-border);

        border-radius: 9px;

        background: #ffffff;

        color: var(--pet-text);

        font: inherit;

        font-size: 13px;

        font-weight: 600;

        line-height: 1;

        text-decoration: none;

        cursor: pointer;

        transition: .15s ease;
    }

    .pet-button:hover {
        background: #f1f5f9;
    }

    .pet-button-primary {
        border-color: var(--pet-green);

        background: var(--pet-green);

        color: #ffffff;
    }

    .pet-button-primary:hover {
        border-color: var(--pet-green-hover);

        background: var(--pet-green-hover);
    }

    .pet-button-danger {
        border-color: var(--pet-red);

        background: var(--pet-red);

        color: #ffffff;
    }

    .pet-button-danger:hover {
        border-color: var(--pet-red-hover);

        background: var(--pet-red-hover);
    }


    /* =========================================================
       ALERT
    ========================================================= */

    .pet-alert {
        margin-bottom: 16px;

        padding: 12px 14px;

        border: 1px solid #fecaca;

        border-radius: 10px;

        background: #fef2f2;

        color: #b91c1c;

        font-size: 13px;
    }

    .pet-alert-success {
        border-color: #bbf7d0;

        background: #f0fdf4;

        color: #166534;
    }

    .pet-alert ul {
        margin: 6px 0 0;

        padding-left: 18px;
    }


    /* =========================================================
       STATISTIC
    ========================================================= */

    .pet-stats {
        display: grid;

        grid-template-columns:
            repeat(
                4,
                minmax(0, 1fr)
            );

        gap: 14px;

        margin-bottom: 16px;
    }

    .pet-stat {
        min-width: 0;

        padding: 14px 16px;

        border: 1px solid var(--pet-border);

        border-radius: 12px;

        background: var(--pet-card);
    }

    .pet-stat span {
        display: block;

        color: var(--pet-muted);

        font-size: 12px;
    }

    .pet-stat strong {
        display: block;

        margin-top: 4px;

        color: var(--pet-text);

        font-size: 24px;

        font-weight: 700;

        line-height: 1.3;
    }


    /* =========================================================
       CARD
    ========================================================= */

    .pet-card {
        overflow: visible;

        border: 1px solid var(--pet-border);

        border-radius: 12px;

        background: var(--pet-card);
    }


    /* =========================================================
       FILTER
    ========================================================= */

    .pet-filter-bar {
        display: flex;

        align-items: center;

        gap: 10px;

        flex-wrap: wrap;

        padding: 14px;

        border-bottom:
            1px solid
            var(--pet-border);
    }

    .pet-filter-form {
        display: flex;

        flex: 1;

        min-width: 0;

        align-items: center;

        gap: 10px;

        flex-wrap: wrap;
    }

    .pet-control {
        min-height: 40px;

        padding: 8px 12px;

        border:
            1px solid
            var(--pet-border);

        border-radius: 9px;

        outline: none;

        background:
            var(--pet-background);

        color:
            var(--pet-text);

        font: inherit;

        font-size: 13px;
    }

    .pet-control:focus {
        border-color:
            var(--pet-green);

        box-shadow:
            0 0 0 1px
            var(--pet-green);
    }

    .pet-search {
        flex: 1;

        min-width: 280px;
    }

    .pet-jenjang-filter {
        width: 220px;
    }


    /* =========================================================
       TABLE
    ========================================================= */

    .pet-table-wrapper {
        width: 100%;

        overflow-x: auto;
    }

    .pet-table {
        width: 100%;

        min-width: 850px;

        border-collapse: collapse;

        font-size: 13px;
    }

    .pet-table thead {
        background:
            var(--pet-background);
    }

    .pet-table th {
        padding: 10px 14px;

        color:
            var(--pet-muted);

        font-size: 11px;

        font-weight: 600;

        letter-spacing: .04em;

        text-align: left;

        text-transform: uppercase;

        white-space: nowrap;
    }

    .pet-table td {
        padding: 12px 14px;

        border-top:
            1px solid
            var(--pet-border);

        color: #334155;

        vertical-align: middle;
    }

    .pet-table tbody tr:hover {
        background: #f8fafc;
    }


    /* =========================================================
       NAME
    ========================================================= */

    .pet-name-wrapper {
        display: flex;

        align-items: center;

        gap: 10px;

        min-width: 230px;
    }

    .pet-avatar {
        display: grid;

        width: 34px;

        height: 34px;

        place-items: center;

        flex-shrink: 0;

        border-radius: 50%;

        color: #ffffff;

        font-size: 11px;

        font-weight: 700;
    }

    .pet-name {
        color: var(--pet-text);

        font-weight: 600;
    }


    /* =========================================================
       BADGE
    ========================================================= */

    .pet-count {
        display: inline-flex;

        min-width: 32px;

        min-height: 26px;

        align-items: center;

        justify-content: center;

        padding: 4px 9px;

        border-radius: 999px;

        background: #ecfdf5;

        color: #047857;

        font-size: 11px;

        font-weight: 700;
    }


    /* =========================================================
       EDIT
    ========================================================= */

    .pet-edit {
        border: 0;

        padding: 0;

        background: transparent;

        color: var(--pet-green);

        font: inherit;

        font-size: 12px;

        font-weight: 700;

        cursor: pointer;
    }

    .pet-edit:hover {
        text-decoration: underline;
    }


    /* =========================================================
       EMPTY
    ========================================================= */

    .pet-empty {
        padding: 32px !important;

        color:
            var(--pet-muted) !important;

        text-align: center;
    }


    /* =========================================================
       FOOTER
    ========================================================= */

    .pet-footer {
        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 10px;

        flex-wrap: wrap;

        padding: 12px 14px;

        border-top:
            1px solid
            var(--pet-border);
    }

    .pet-result-info {
        color:
            var(--pet-muted);

        font-size: 12px;
    }


    /* =========================================================
       PAGINATION
    ========================================================= */

    .pet-pagination {
        display: flex;

        align-items: center;

        gap: 4px;

        flex-wrap: wrap;
    }

    .pet-page-link {
        display: inline-grid;

        width: 32px;

        height: 32px;

        place-items: center;

        border:
            1px solid
            var(--pet-border);

        border-radius: 8px;

        background: #ffffff;

        color:
            var(--pet-text);

        font-size: 12px;

        text-decoration: none;
    }

    .pet-page-link:hover {
        background: #f1f5f9;
    }

    .pet-page-link.active {
        border-color:
            var(--pet-green);

        background:
            var(--pet-green);

        color: #ffffff;
    }

    .pet-page-link.disabled {
        color: #cbd5e1;

        cursor: default;

        pointer-events: none;
    }


    /* =========================================================
       OVERLAY
    ========================================================= */

    .pet-overlay {
        position: fixed;

        z-index: 80;

        inset: 0;

        background:
            rgba(
                2,
                6,
                23,
                .52
            );
    }


    /* =========================================================
       DRAWER
    ========================================================= */

    .pet-drawer {
        position: fixed;

        z-index: 90;

        top: 0;

        right: 0;

        bottom: 0;

        width:
            min(
                440px,
                100%
            );

        padding: 22px;

        overflow-y: auto;

        border-left:
            1px solid
            var(--pet-border);

        background: #ffffff;

        transform:
            translateX(100%);

        transition:
            transform
            .25s
            ease;
    }

    .pet-drawer.is-open {
        transform:
            translateX(0);
    }

    .pet-drawer-header {
        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 12px;

        margin-bottom: 20px;
    }

    .pet-drawer-title {
        margin: 0;

        color:
            var(--pet-text);

        font-size: 18px;

        font-weight: 700;
    }

    .pet-drawer-close {
        display: grid;

        width: 36px;

        height: 36px;

        place-items: center;

        border:
            1px solid
            var(--pet-border);

        border-radius: 8px;

        background: #ffffff;

        color:
            var(--pet-muted);

        cursor: pointer;
    }

    .pet-drawer-close:hover {
        background: #f1f5f9;
    }


    /* =========================================================
       FORM
    ========================================================= */

    .pet-field {
        margin-bottom: 14px;
    }

    .pet-field label {
        display: block;

        margin-bottom: 5px;

        color:
            var(--pet-muted);

        font-size: 12px;

        font-weight: 500;
    }

    .pet-form-control {
        display: block;

        width: 100%;

        min-height: 42px;

        padding: 9px 11px;

        border:
            1px solid
            var(--pet-border);

        border-radius: 9px;

        outline: none;

        background:
            var(--pet-background);

        color:
            var(--pet-text);

        font: inherit;

        font-size: 13px;
    }

    .pet-form-control:focus {
        border-color:
            var(--pet-green);

        box-shadow:
            0 0 0 1px
            var(--pet-green);
    }


    /* =========================================================
       DRAWER ACTION
    ========================================================= */

    .pet-drawer-actions {
        display: flex;

        gap: 8px;

        margin-top: 20px;
    }

    .pet-drawer-actions
    .pet-button:first-child {
        flex: 1;
    }

    .pet-drawer-actions
    .pet-button-primary {
        flex: 2;
    }

    .pet-delete-form {
        margin-top: 10px;
    }

    .pet-delete-form
    .pet-button {
        width: 100%;
    }


    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 1000px) {
        .pet-stats {
            grid-template-columns:
                repeat(
                    2,
                    minmax(0, 1fr)
                );
        }

        .pet-search {
            flex-basis: 100%;
        }
    }


    @media (max-width: 650px) {
        .pet-header {
            align-items:
                flex-start;
        }

        .pet-header-actions {
            width: 100%;
        }

        .pet-header-actions
        .pet-button {
            width: 100%;
        }

        .pet-title {
            font-size: 20px;
        }

        .pet-stats {
            grid-template-columns:
                1fr;
        }

        .pet-filter-form {
            display: grid;

            grid-template-columns:
                1fr;
        }

        .pet-search,
        .pet-jenjang-filter {
            width: 100%;

            min-width: 0;
        }

        .pet-filter-form
        .pet-button {
            width: 100%;
        }
    }
</style>


<div
    class="petugas-page"

    x-data="petugasPage(
        @js($editItems),
        @js($initialForm),
        @js($errorMode),
        @js($editingId),
        @js(route('petugas.store'))
    )"

    @keydown.escape.window="closeDrawer()"
>


    {{-- =====================================================
         HEADER
    ====================================================== --}}

    <div class="pet-header">

        <div>

            <h1 class="pet-title">
                Menu Petugas
            </h1>

            <p class="pet-subtitle">
                Kelola data petugas dan informasi jabatan
            </p>

        </div>


        @if ($isAdmin)

            <div class="pet-header-actions">

                <button
                    type="button"
                    class="
                        pet-button
                        pet-button-primary
                    "
                    @click="openCreate()"
                >
                    ＋ Tambah Petugas
                </button>

            </div>

        @endif

    </div>


    {{-- =====================================================
         SUCCESS
    ====================================================== --}}

    @if (session('success'))

        <div class="
            pet-alert
            pet-alert-success
        ">
            {{ session('success') }}
        </div>

    @endif


    {{-- =====================================================
         ERROR SESSION
    ====================================================== --}}

    @if (session('error'))

        <div class="pet-alert">
            {{ session('error') }}
        </div>

    @endif


    {{-- =====================================================
         VALIDATION ERROR
    ====================================================== --}}

    @if ($errors->any())

        <div class="pet-alert">

            <strong>
                Data belum dapat disimpan.
            </strong>

            <ul>

                @foreach ($errors->all() as $error)

                    <li>
                        {{ $error }}
                    </li>

                @endforeach

            </ul>

        </div>

    @endif


    {{-- =====================================================
         STATISTICS
    ====================================================== --}}

    <div class="pet-stats">

        <div class="pet-stat">

            <span>
                Total Petugas
            </span>

            <strong>
                {{ $totalPetugas }}
            </strong>

        </div>


        <div class="pet-stat">

            <span>
                Ditampilkan
            </span>

            <strong>
                {{ $ditampilkan }}
            </strong>

        </div>


        <div class="pet-stat">

            <span>
                Petugas Ditugaskan
            </span>

            <strong>
                {{ $petugasDitugaskan }}
            </strong>

        </div>


        <div class="pet-stat">

            <span>
                Total Penugasan
            </span>

            <strong>
                {{ $totalPenugasan }}
            </strong>

        </div>

    </div>


    {{-- =====================================================
         TABLE CARD
    ====================================================== --}}

    <div class="pet-card">


        {{-- =================================================
             FILTER
        ================================================== --}}

        <div class="pet-filter-bar">

            <form
                action="{{ route('petugas.index') }}"
                method="GET"
                class="pet-filter-form"
            >

                {{-- SEARCH --}}

                <input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    class="
                        pet-control
                        pet-search
                    "
                    placeholder="🔍 Cari nama, NIP, atau jabatan..."
                    autocomplete="off"
                >


                {{-- FILTER JENJANG --}}

                <select
                    name="jenjang"
                    class="
                        pet-control
                        pet-jenjang-filter
                    "
                >

                    <option value="">
                        Semua jenjang
                    </option>


                    @foreach ($jenjangOptions as $jenjangOption)

                        <option
                            value="{{ $jenjangOption }}"
                            @selected(
                                $jenjang
                                ===
                                $jenjangOption
                            )
                        >
                            {{ $jenjangOption }}
                        </option>

                    @endforeach

                </select>


                {{-- CARI --}}

                <button
                    type="submit"
                    class="
                        pet-button
                        pet-button-primary
                    "
                >
                    Cari
                </button>

            </form>

        </div>


        {{-- =================================================
             TABLE
        ================================================== --}}

        <div class="pet-table-wrapper">

            <table class="pet-table">

                <thead>

                    <tr>

                        <th>
                            Nama
                        </th>

                        <th>
                            NIP
                        </th>

                        <th>
                            Jabatan
                        </th>

                        <th>
                            Penugasan
                        </th>


                        @if ($isAdmin)

                            <th>
                                Aksi
                            </th>

                        @endif

                    </tr>

                </thead>


                <tbody>

                    @forelse ($petugas as $item)

                        @php
                            /*
                            |--------------------------------------------------------------------------
                            | INITIAL
                            |--------------------------------------------------------------------------
                            */

                            $parts =
                                preg_split(
                                    '/\s+/',
                                    trim(
                                        (string)
                                        $item->name
                                    )
                                );


                            $firstInitial =
                                isset($parts[0])
                                    ? mb_substr(
                                        $parts[0],
                                        0,
                                        1
                                    )
                                    : 'P';


                            $secondInitial =
                                isset($parts[1])
                                    ? mb_substr(
                                        $parts[1],
                                        0,
                                        1
                                    )
                                    : '';


                            $initial =
                                strtoupper(
                                    $firstInitial
                                    .
                                    $secondInitial
                                );


                            /*
                            |--------------------------------------------------------------------------
                            | WARNA AVATAR
                            |--------------------------------------------------------------------------
                            */

                            $avatarIndex =
                                abs(
                                    crc32(
                                        (string)
                                        $item->name
                                    )
                                )
                                %
                                count(
                                    $avatarPalette
                                );


                            $avatarColor =
                                $avatarPalette[
                                    $avatarIndex
                                ];


                            /*
                            |--------------------------------------------------------------------------
                            | JUMLAH PENUGASAN
                            |--------------------------------------------------------------------------
                            */

                            $jumlahPenugasan =
                                (int) (
                                    $item->penugasan_count
                                    ??
                                    $item->penugasans_count
                                    ??
                                    0
                                );
                        @endphp


                        <tr>

                            {{-- NAMA --}}

                            <td>

                                <div class="pet-name-wrapper">

                                    <span
                                        class="pet-avatar"
                                        style="
                                            background:
                                                {{ $avatarColor }};
                                        "
                                    >
                                        {{ $initial }}
                                    </span>

                                    <span class="pet-name">
                                        {{ $item->name }}
                                    </span>

                                </div>

                            </td>


                            {{-- NIP --}}

                            <td>
                                {{ $item->nip ?: '-' }}
                            </td>


                            {{-- JABATAN --}}

                            <td>
                                {{ $item->position ?: '-' }}
                            </td>


                            {{-- PENUGASAN --}}

                            <td>

                                <span class="pet-count">
                                    {{ $jumlahPenugasan }}
                                </span>

                            </td>


                            {{-- AKSI --}}

                            @if ($isAdmin)

                                <td>

                                    <button
                                        type="button"
                                        class="pet-edit"
                                        @click="
                                            openEditById(
                                                {{ (int) $item->id }}
                                            )
                                        "
                                    >
                                        ✎ Edit
                                    </button>

                                </td>

                            @endif

                        </tr>


                    @empty

                        <tr>

                            <td
                                colspan="{{ $isAdmin ? 5 : 4 }}"
                                class="pet-empty"
                            >
                                Tidak ada data petugas yang cocok.
                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- =================================================
             FOOTER
        ================================================== --}}

        <div class="pet-footer">

            <div class="pet-result-info">

                Menampilkan

                {{
                    $petugas->firstItem()
                    ??
                    0
                }}

                –

                {{
                    $petugas->lastItem()
                    ??
                    0
                }}

                dari

                {{ $petugas->total() }}

                hasil

            </div>


            @if ($petugas->lastPage() > 1)

                <div class="pet-pagination">


                    {{-- PREVIOUS --}}

                    @if ($petugas->onFirstPage())

                        <span
                            class="
                                pet-page-link
                                disabled
                            "
                        >
                            ‹
                        </span>

                    @else

                        <a
                            href="{{
                                $petugas
                                    ->previousPageUrl()
                            }}"
                            class="pet-page-link"
                        >
                            ‹
                        </a>

                    @endif


                    {{-- ==========================================
                         MAKSIMAL 3 NOMOR HALAMAN
                    =========================================== --}}

                    @php

                        $totalPages =
                            $petugas->lastPage();

                        $currentPage =
                            $petugas->currentPage();


                        if ($totalPages <= 3) {

                            $startPage = 1;

                            $endPage =
                                $totalPages;

                        } elseif (
                            $currentPage <= 2
                        ) {

                            $startPage = 1;

                            $endPage = 3;

                        } elseif (
                            $currentPage >=
                            $totalPages - 1
                        ) {

                            $startPage =
                                $totalPages - 2;

                            $endPage =
                                $totalPages;

                        } else {

                            $startPage =
                                $currentPage - 1;

                            $endPage =
                                $currentPage + 1;

                        }

                    @endphp


                    @for (
                        $page = $startPage;
                        $page <= $endPage;
                        $page++
                    )

                        <a
                            href="{{
                                $petugas
                                    ->url(
                                        $page
                                    )
                            }}"
                            class="
                                pet-page-link

                                {{
                                    $page ===
                                    $petugas->currentPage()
                                        ? 'active'
                                        : ''
                                }}
                            "
                        >
                            {{ $page }}
                        </a>

                    @endfor


                    {{-- NEXT --}}

                    @if ($petugas->hasMorePages())

                        <a
                            href="{{
                                $petugas
                                    ->nextPageUrl()
                            }}"
                            class="pet-page-link"
                        >
                            ›
                        </a>

                    @else

                        <span
                            class="
                                pet-page-link
                                disabled
                            "
                        >
                            ›
                        </span>

                    @endif

                </div>

            @endif

        </div>

    </div>


    {{-- =====================================================
         DRAWER ADMIN
    ====================================================== --}}

    @if ($isAdmin)


        {{-- OVERLAY --}}

        <div
            x-show="drawerOpen"
            x-cloak
            x-transition.opacity
            class="pet-overlay"
            @click="closeDrawer()"
        ></div>


        {{-- DRAWER --}}

        <aside
            class="pet-drawer"
            :class="{
                'is-open':
                    drawerOpen
            }"
        >

            {{-- HEADER --}}

            <div class="pet-drawer-header">

                <h2
                    class="pet-drawer-title"
                    x-text="
                        drawerMode === 'edit'
                            ? 'Edit Petugas'
                            : 'Tambah Petugas'
                    "
                ></h2>


                <button
                    type="button"
                    class="pet-drawer-close"
                    @click="closeDrawer()"
                >
                    ✕
                </button>

            </div>


            {{-- =================================================
                 FORM
            ================================================== --}}

            <form
                :action="
                    drawerMode === 'edit'
                        ? updateUrl
                        : createUrl
                "
                method="POST"
            >

                @csrf


                {{-- METHOD UPDATE --}}

                <template
                    x-if="
                        drawerMode === 'edit'
                    "
                >

                    <input
                        type="hidden"
                        name="_method"
                        value="PUT"
                    >

                </template>


                {{-- EDITING ID --}}

                <template
                    x-if="
                        drawerMode === 'edit'
                    "
                >

                    <input
                        type="hidden"
                        name="editing_id"
                        :value="editId"
                    >

                </template>


                {{-- NAMA --}}

                <div class="pet-field">

                    <label for="pet-name">
                        Nama Petugas
                    </label>

                    <input
                        id="pet-name"
                        type="text"
                        name="name"
                        x-model="form.name"
                        class="pet-form-control"
                        placeholder="Masukkan nama petugas"
                        required
                    >

                </div>


                {{-- NIP --}}

                <div class="pet-field">

                    <label for="pet-nip">
                        NIP
                    </label>

                    <input
                        id="pet-nip"
                        type="text"
                        name="nip"
                        x-model="form.nip"
                        class="pet-form-control"
                        placeholder="Masukkan NIP"
                    >

                </div>


                {{-- JABATAN --}}

                <div class="pet-field">

                    <label for="pet-position">
                        Jabatan
                    </label>

                    <input
                        id="pet-position"
                        type="text"
                        name="position"
                        x-model="form.position"
                        class="pet-form-control"
                        placeholder="Masukkan jabatan"
                        required
                    >

                </div>


                {{-- ACTION --}}

                <div class="pet-drawer-actions">

                    <button
                        type="button"
                        class="pet-button"
                        @click="closeDrawer()"
                    >
                        Batal
                    </button>


                    <button
                        type="submit"
                        class="
                            pet-button
                            pet-button-primary
                        "
                        x-text="
                            drawerMode === 'edit'
                                ? 'Update Petugas'
                                : 'Simpan Petugas'
                        "
                    ></button>

                </div>

            </form>


            {{-- =================================================
                 DELETE
            ================================================== --}}

            <form
                x-show="
                    drawerMode === 'edit'
                "
                x-cloak
                :action="destroyUrl"
                method="POST"
                class="pet-delete-form"
                onsubmit="
                    return confirm(
                        'Yakin ingin menghapus data petugas ini?'
                    )
                "
            >

                @csrf

                <input
                    type="hidden"
                    name="_method"
                    value="DELETE"
                >


                <button
                    type="submit"
                    class="
                        pet-button
                        pet-button-danger
                    "
                >
                    Hapus Petugas
                </button>

            </form>

        </aside>

    @endif

</div>


<script>
    function petugasPage(
        editItems,
        initialForm,
        errorMode,
        editingId,
        createUrl
    ) {

        return {

            /*
            |--------------------------------------------------------------------------
            | DATA
            |--------------------------------------------------------------------------
            */

            editItems:
                editItems,

            createUrl:
                createUrl,


            /*
            |--------------------------------------------------------------------------
            | DRAWER
            |--------------------------------------------------------------------------
            */

            drawerOpen:
                false,

            drawerMode:
                'create',

            editId:
                null,

            updateUrl:
                '',

            destroyUrl:
                '',


            /*
            |--------------------------------------------------------------------------
            | FORM
            |--------------------------------------------------------------------------
            */

            form: {
                name: '',
                nip: '',
                position: '',
            },


            /*
            |--------------------------------------------------------------------------
            | INIT
            |--------------------------------------------------------------------------
            */

            init()
            {

                /*
                |--------------------------------------------------------------------------
                | VALIDATION CREATE
                |--------------------------------------------------------------------------
                */

                if (
                    errorMode ===
                    'create'
                ) {

                    this.openCreate(
                        initialForm
                    );

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | VALIDATION EDIT
                |--------------------------------------------------------------------------
                */

                if (
                    errorMode ===
                    'edit'
                    &&
                    editingId
                ) {

                    const item =
                        this.editItems.find(
                            editItem =>
                                Number(
                                    editItem.id
                                )
                                ===
                                Number(
                                    editingId
                                )
                        );


                    if (item) {

                        this.openEdit(
                            item
                        );


                        this.form.name =
                            initialForm.name
                            ??
                            item.name
                            ??
                            '';


                        this.form.nip =
                            initialForm.nip
                            ??
                            item.nip
                            ??
                            '';


                        this.form.position =
                            initialForm.position
                            ??
                            item.position
                            ??
                            '';

                    }

                }

            },


            /*
            |--------------------------------------------------------------------------
            | RESET FORM
            |--------------------------------------------------------------------------
            */

            resetForm()
            {

                this.form = {
                    name: '',
                    nip: '',
                    position: '',
                };

            },


            /*
            |--------------------------------------------------------------------------
            | OPEN CREATE
            |--------------------------------------------------------------------------
            */

            openCreate(
                data = null
            )
            {

                this.drawerMode =
                    'create';

                this.editId =
                    null;

                this.updateUrl =
                    '';

                this.destroyUrl =
                    '';

                this.resetForm();


                if (data) {

                    this.form.name =
                        data.name
                        ??
                        '';

                    this.form.nip =
                        data.nip
                        ??
                        '';

                    this.form.position =
                        data.position
                        ??
                        '';

                }


                this.drawerOpen =
                    true;

            },


            /*
            |--------------------------------------------------------------------------
            | EDIT BY ID
            |--------------------------------------------------------------------------
            */

            openEditById(
                id
            )
            {

                const item =
                    this.editItems.find(
                        editItem =>
                            Number(
                                editItem.id
                            )
                            ===
                            Number(
                                id
                            )
                    );


                if (!item) {

                    console.error(
                        'Data petugas tidak ditemukan:',
                        id
                    );

                    return;

                }


                this.openEdit(
                    item
                );

            },


            /*
            |--------------------------------------------------------------------------
            | OPEN EDIT
            |--------------------------------------------------------------------------
            */

            openEdit(
                item
            )
            {

                if (!item) {
                    return;
                }


                this.drawerMode =
                    'edit';

                this.editId =
                    item.id;

                this.updateUrl =
                    item.update_url;

                this.destroyUrl =
                    item.destroy_url;


                this.form = {

                    name:
                        item.name
                        ??
                        '',

                    nip:
                        item.nip
                        ??
                        '',

                    position:
                        item.position
                        ??
                        '',

                };


                this.drawerOpen =
                    true;

            },


            /*
            |--------------------------------------------------------------------------
            | CLOSE
            |--------------------------------------------------------------------------
            */

            closeDrawer()
            {

                this.drawerOpen =
                    false;

            },

        };

    }
</script>

@endsection
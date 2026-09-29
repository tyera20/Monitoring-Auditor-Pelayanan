@extends('layouts.app', ['title' => 'Menu Layanan'])

@section('content')

@php
    $editItems = $layanans
        ->getCollection()
        ->map(function ($item) {
            return [
                'id' => $item->id,
                'nama_layanan' => $item->nama_layanan,
                'penugasan_count' => (int) ($item->penugasan_count ?? 0),
                'update_url' => route('layanan.update', $item),
                'destroy_url' => route('layanan.destroy', $item),
            ];
        })
        ->values()
        ->all();

    $initialForm = [
        'nama_layanan' => old('nama_layanan', ''),
    ];

    $errorMode = null;

    if ($errors->any()) {
        $errorMode =
            old('_method') === 'PUT'
                ? 'edit'
                : 'create';
    }

    $editingId = old('editing_id');
@endphp


<style>
    [x-cloak] {
        display: none !important;
    }

    .layanan-page {
        --lay-card: #ffffff;
        --lay-text: #0f172a;
        --lay-muted: #64748b;
        --lay-border: #e2e8f0;
        --lay-background: #f4f6fa;

        --lay-green: #059669;
        --lay-green-hover: #047857;

        --lay-red: #e11d48;
        --lay-red-hover: #be123c;

        color: var(--lay-text);

        font-family:
            Inter,
            system-ui,
            -apple-system,
            "Segoe UI",
            sans-serif;
    }

    .layanan-page * {
        box-sizing: border-box;
    }


    /* =========================================================
       HEADER
    ========================================================= */

    .lay-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;

        gap: 16px;

        margin-bottom: 20px;
    }

    .lay-title {
        margin: 0;

        color: var(--lay-text);

        font-size: 22px;
        font-weight: 700;

        line-height: 1.3;
    }

    .lay-subtitle {
        margin: 3px 0 0;

        color: var(--lay-muted);

        font-size: 13px;
    }

    .lay-header-actions {
        display: flex;
        align-items: center;

        gap: 8px;

        flex-wrap: wrap;
    }


    /* =========================================================
       BUTTON
    ========================================================= */

    .lay-button {
        display: inline-flex;

        min-height: 40px;

        align-items: center;
        justify-content: center;

        gap: 7px;

        padding: 8px 14px;

        border: 1px solid var(--lay-border);
        border-radius: 9px;

        background: #ffffff;

        color: var(--lay-text);

        font: inherit;
        font-size: 13px;
        font-weight: 600;

        line-height: 1;

        text-decoration: none;

        cursor: pointer;

        transition: .15s ease;
    }

    .lay-button:hover {
        background: #f1f5f9;
    }

    .lay-button-primary {
        border-color: var(--lay-green);

        background: var(--lay-green);

        color: #ffffff;
    }

    .lay-button-primary:hover {
        border-color: var(--lay-green-hover);

        background: var(--lay-green-hover);
    }

    .lay-button-danger {
        border-color: var(--lay-red);

        background: var(--lay-red);

        color: #ffffff;
    }

    .lay-button-danger:hover {
        border-color: var(--lay-red-hover);

        background: var(--lay-red-hover);
    }


    /* =========================================================
       ALERT
    ========================================================= */

    .lay-alert {
        margin-bottom: 16px;

        padding: 12px 14px;

        border: 1px solid #fecaca;
        border-radius: 10px;

        background: #fef2f2;

        color: #b91c1c;

        font-size: 13px;
    }

    .lay-alert-success {
        border-color: #bbf7d0;

        background: #f0fdf4;

        color: #166534;
    }

    .lay-alert ul {
        margin: 6px 0 0;

        padding-left: 18px;
    }


    /* =========================================================
       STATISTIC
    ========================================================= */

    .lay-stats {
        display: grid;

        grid-template-columns:
            repeat(
                4,
                minmax(0, 1fr)
            );

        gap: 14px;

        margin-bottom: 16px;
    }

    .lay-stat {
        min-width: 0;

        padding: 14px 16px;

        border: 1px solid var(--lay-border);
        border-radius: 12px;

        background: var(--lay-card);
    }

    .lay-stat span {
        display: block;

        color: var(--lay-muted);

        font-size: 12px;
    }

    .lay-stat strong {
        display: block;

        margin-top: 4px;

        color: var(--lay-text);

        font-size: 24px;
        font-weight: 700;

        line-height: 1.3;
    }


    /* =========================================================
       CARD
    ========================================================= */

    .lay-card {
        border: 1px solid var(--lay-border);
        border-radius: 12px;

        background: var(--lay-card);

        overflow: visible;
    }


    /* =========================================================
       FILTER
    ========================================================= */

    .lay-filter-bar {
        display: flex;
        align-items: center;

        gap: 10px;

        flex-wrap: wrap;

        padding: 14px;

        border-bottom: 1px solid var(--lay-border);
    }

    .lay-filter-form {
        display: flex;

        flex: 1;

        min-width: 0;

        align-items: center;

        gap: 10px;

        flex-wrap: wrap;
    }

    .lay-control {
        min-height: 40px;

        padding: 8px 12px;

        border: 1px solid var(--lay-border);
        border-radius: 9px;

        outline: none;

        background: var(--lay-background);

        color: var(--lay-text);

        font: inherit;
        font-size: 13px;
    }

    .lay-control:focus {
        border-color: var(--lay-green);

        box-shadow:
            0 0 0 1px
            var(--lay-green);
    }

    .lay-search {
        flex: 1;

        min-width: 280px;
    }


    /* =========================================================
       TABLE
    ========================================================= */

    .lay-table-wrapper {
        width: 100%;

        overflow-x: auto;
    }

    .lay-table {
        width: 100%;

        min-width: 720px;

        border-collapse: collapse;

        font-size: 13px;
    }

    .lay-table thead {
        background: var(--lay-background);
    }

    .lay-table th {
        padding: 10px 14px;

        color: var(--lay-muted);

        font-size: 11px;
        font-weight: 600;

        letter-spacing: .04em;

        text-align: left;
        text-transform: uppercase;

        white-space: nowrap;
    }

    .lay-table td {
        padding: 13px 14px;

        border-top: 1px solid var(--lay-border);

        color: #334155;

        vertical-align: middle;
    }

    .lay-table tbody tr:hover {
        background: #f8fafc;
    }


    /* =========================================================
       LAYANAN
    ========================================================= */

    .lay-name-wrapper {
        display: flex;

        align-items: center;

        gap: 10px;
    }

    .lay-icon {
        display: grid;

        width: 30px;
        height: 30px;

        place-items: center;

        flex-shrink: 0;

        border-radius: 8px;

        background: #ecfdf5;

        color: #059669;

        font-size: 13px;
        font-weight: 700;
    }

    .lay-name {
        color: var(--lay-text);

        font-weight: 600;
    }

    .lay-count {
        color: var(--lay-text);

        font-weight: 700;
    }


    /* =========================================================
       STATUS
    ========================================================= */

    .lay-status {
        display: inline-flex;

        align-items: center;

        padding: 4px 9px;

        border-radius: 999px;

        font-size: 11px;
        font-weight: 600;

        white-space: nowrap;
    }

    .lay-status-used {
        background: #ecfdf5;

        color: #047857;
    }

    .lay-status-empty {
        background: #f1f5f9;

        color: #64748b;
    }


    /* =========================================================
       EDIT
    ========================================================= */

    .lay-edit {
        border: 0;

        padding: 0;

        background: transparent;

        color: var(--lay-green);

        font: inherit;
        font-size: 12px;
        font-weight: 700;

        cursor: pointer;
    }

    .lay-edit:hover {
        text-decoration: underline;
    }


    /* =========================================================
       EMPTY
    ========================================================= */

    .lay-empty {
        padding: 32px !important;

        color: var(--lay-muted) !important;

        text-align: center;
    }


    /* =========================================================
       FOOTER
    ========================================================= */

    .lay-footer {
        display: flex;

        align-items: center;
        justify-content: space-between;

        gap: 10px;

        flex-wrap: wrap;

        padding: 12px 14px;

        border-top:
            1px solid
            var(--lay-border);
    }

    .lay-result-info {
        color: var(--lay-muted);

        font-size: 12px;
    }


    /* =========================================================
       PAGINATION
    ========================================================= */

    .lay-pagination {
        display: flex;

        align-items: center;

        gap: 4px;

        flex-wrap: wrap;
    }

    .lay-page-link {
        display: inline-grid;

        width: 32px;
        height: 32px;

        place-items: center;

        border: 1px solid var(--lay-border);
        border-radius: 8px;

        background: #ffffff;

        color: var(--lay-text);

        font-size: 12px;

        text-decoration: none;
    }

    .lay-page-link:hover {
        background: #f1f5f9;
    }

    .lay-page-link.active {
        border-color: var(--lay-green);

        background: var(--lay-green);

        color: #ffffff;
    }

    .lay-page-link.disabled {
        color: #cbd5e1;

        cursor: default;
    }


    /* =========================================================
       OVERLAY
    ========================================================= */

    .lay-overlay {
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

    .lay-drawer {
        position: fixed;

        z-index: 90;

        top: 0;
        right: 0;
        bottom: 0;

        width:
            min(
                430px,
                100%
            );

        padding: 22px;

        overflow-y: auto;

        border-left: 1px solid var(--lay-border);

        background: #ffffff;

        transform:
            translateX(100%);

        transition:
            transform
            .25s
            ease;
    }

    .lay-drawer.is-open {
        transform:
            translateX(0);
    }

    .lay-drawer-header {
        display: flex;

        align-items: center;
        justify-content: space-between;

        gap: 12px;

        margin-bottom: 20px;
    }

    .lay-drawer-title {
        margin: 0;

        color: var(--lay-text);

        font-size: 18px;
        font-weight: 700;
    }

    .lay-drawer-close {
        display: grid;

        width: 36px;
        height: 36px;

        place-items: center;

        border: 1px solid var(--lay-border);
        border-radius: 8px;

        background: #ffffff;

        color: var(--lay-muted);

        cursor: pointer;
    }

    .lay-drawer-close:hover {
        background: #f1f5f9;
    }


    /* =========================================================
       FORM
    ========================================================= */

    .lay-field {
        margin-bottom: 14px;
    }

    .lay-field label {
        display: block;

        margin-bottom: 5px;

        color: var(--lay-muted);

        font-size: 12px;
        font-weight: 500;
    }

    .lay-form-control {
        display: block;

        width: 100%;

        min-height: 42px;

        padding: 9px 11px;

        border: 1px solid var(--lay-border);
        border-radius: 9px;

        outline: none;

        background: var(--lay-background);

        color: var(--lay-text);

        font: inherit;
        font-size: 13px;
    }

    .lay-form-control:focus {
        border-color: var(--lay-green);

        box-shadow:
            0 0 0 1px
            var(--lay-green);
    }


    /* =========================================================
       INFO
    ========================================================= */

    .lay-info-box {
        margin-bottom: 16px;

        padding: 11px 12px;

        border: 1px solid var(--lay-border);
        border-radius: 9px;

        background: #f8fafc;

        color: var(--lay-muted);

        font-size: 12px;
    }

    .lay-info-box strong {
        color: var(--lay-text);
    }


    /* =========================================================
       DRAWER ACTION
    ========================================================= */

    .lay-drawer-actions {
        display: flex;

        gap: 8px;

        margin-top: 20px;
    }

    .lay-drawer-actions
    .lay-button:first-child {
        flex: 1;
    }

    .lay-drawer-actions
    .lay-button-primary {
        flex: 2;
    }

    .lay-delete-form {
        margin-top: 10px;
    }

    .lay-delete-form
    .lay-button {
        width: 100%;
    }


    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 1000px) {
        .lay-stats {
            grid-template-columns:
                repeat(
                    2,
                    minmax(0, 1fr)
                );
        }
    }

    @media (max-width: 650px) {
        .lay-header {
            align-items: flex-start;
        }

        .lay-header-actions {
            width: 100%;
        }

        .lay-header-actions
        .lay-button {
            width: 100%;
        }

        .lay-title {
            font-size: 20px;
        }

        .lay-stats {
            grid-template-columns: 1fr;
        }

        .lay-filter-form {
            display: grid;

            grid-template-columns: 1fr;
        }

        .lay-search {
            width: 100%;

            min-width: 0;
        }

        .lay-filter-form
        .lay-button {
            width: 100%;
        }
    }
</style>


<div
    class="layanan-page"

    x-data="layananPage(
        @js($editItems),
        @js($initialForm),
        @js($errorMode),
        @js($editingId),
        @js(route('layanan.store'))
    )"

    @keydown.escape.window="closeDrawer()"
>


    {{-- =====================================================
         HEADER
    ====================================================== --}}

    <div class="lay-header">

        <div>

            <h1 class="lay-title">
                Menu Layanan
            </h1>

            <p class="lay-subtitle">
                Kelola jenis layanan yang digunakan pada penugasan
            </p>

        </div>


        @if (auth()->user()->isAdmin())

            <div class="lay-header-actions">

                <button
                    type="button"
                    class="
                        lay-button
                        lay-button-primary
                    "
                    @click="openCreate()"
                >
                    ＋ Tambah Layanan
                </button>

            </div>

        @endif

    </div>


    {{-- =====================================================
         SUCCESS
    ====================================================== --}}

    @if (session('success'))

        <div class="lay-alert lay-alert-success">

            {{ session('success') }}

        </div>

    @endif


    {{-- =====================================================
         ERROR
    ====================================================== --}}

    @if (session('error'))

        <div class="lay-alert">

            {{ session('error') }}

        </div>

    @endif


    {{-- =====================================================
         VALIDATION
    ====================================================== --}}

    @if ($errors->any())

        <div class="lay-alert">

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
         STATISTIC
    ====================================================== --}}

    <div class="lay-stats">

        <div class="lay-stat">

            <span>
                Total Layanan
            </span>

            <strong>
                {{ $totalLayanan }}
            </strong>

        </div>


        <div class="lay-stat">

            <span>
                Ditampilkan
            </span>

            <strong>
                {{ $ditampilkan }}
            </strong>

        </div>


        <div class="lay-stat">

            <span>
                Layanan Digunakan
            </span>

            <strong>
                {{ $layananDigunakan }}
            </strong>

        </div>


        <div class="lay-stat">

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

    <div class="lay-card">


        {{-- =================================================
             FILTER
        ================================================== --}}

        <div class="lay-filter-bar">

            <form
                action="{{ route('layanan.index') }}"
                method="GET"
                class="lay-filter-form"
            >

                {{-- SEARCH --}}
                <input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    class="
                        lay-control
                        lay-search
                    "
                    placeholder="🔍 Cari nama layanan..."
                    autocomplete="off"
                >


                {{-- =================================================
                     CARI
                ================================================== --}}

                <button
                    type="submit"
                    class="
                        lay-button
                        lay-button-primary
                    "
                >
                    Cari
                </button>

            </form>

        </div>


        {{-- =================================================
             TABLE
        ================================================== --}}

        <div class="lay-table-wrapper">

            <table class="lay-table">

                <thead>

                    <tr>

                        <th>
                            Nama Layanan
                        </th>

                        <th>
                            Penugasan
                        </th>

                        <th>
                            Status
                        </th>


                        @if (auth()->user()->isAdmin())

                            <th>
                                Aksi
                            </th>

                        @endif

                    </tr>

                </thead>


                <tbody>

                    @forelse ($layanans as $item)

                        @php
                            $jumlahPenugasan =
                                (int) (
                                    $item->penugasan_count
                                    ??
                                    0
                                );
                        @endphp


                        <tr>

                            {{-- NAMA --}}
                            <td>

                                <div class="lay-name-wrapper">

                                    <span class="lay-icon">
                                        L
                                    </span>

                                    <span class="lay-name">
                                        {{ $item->nama_layanan }}
                                    </span>

                                </div>

                            </td>


                            {{-- PENUGASAN --}}
                            <td>

                                <span class="lay-count">
                                    {{ $jumlahPenugasan }}
                                </span>

                            </td>


                            {{-- STATUS --}}
                            <td>

                                @if ($jumlahPenugasan > 0)

                                    <span
                                        class="
                                            lay-status
                                            lay-status-used
                                        "
                                    >
                                        Digunakan
                                    </span>

                                @else

                                    <span
                                        class="
                                            lay-status
                                            lay-status-empty
                                        "
                                    >
                                        Belum digunakan
                                    </span>

                                @endif

                            </td>


                            {{-- AKSI --}}
                            @if (auth()->user()->isAdmin())

                                <td>

                                    <button
                                        type="button"
                                        class="lay-edit"

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
                                colspan="{{ auth()->user()->isAdmin() ? 4 : 3 }}"
                                class="lay-empty"
                            >
                                Belum ada data layanan.
                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- =================================================
             FOOTER
        ================================================== --}}

        <div class="lay-footer">

            <div class="lay-result-info">

                Menampilkan

                {{
                    $layanans->firstItem()
                    ??
                    0
                }}

                –

                {{
                    $layanans->lastItem()
                    ??
                    0
                }}

                dari

                {{ $layanans->total() }}

                hasil

            </div>


            @if ($layanans->lastPage() > 1)

                <div class="lay-pagination">


                    {{-- PREVIOUS --}}
                    @if ($layanans->onFirstPage())

                        <span
                            class="
                                lay-page-link
                                disabled
                            "
                        >
                            ‹
                        </span>

                    @else

                        <a
                            href="{{ $layanans->previousPageUrl() }}"
                            class="lay-page-link"
                        >
                            ‹
                        </a>

                    @endif


                    {{-- RANGE --}}
                    @php

                        $startPage =
                            max(
                                1,
                                $layanans->currentPage() - 2
                            );

                        $endPage =
                            min(
                                $layanans->lastPage(),
                                $layanans->currentPage() + 2
                            );

                    @endphp


                    {{-- PAGE --}}
                    @for (
                        $page = $startPage;
                        $page <= $endPage;
                        $page++
                    )

                        <a
                            href="{{ $layanans->url($page) }}"

                            class="
                                lay-page-link

                                {{
                                    $page ===
                                    $layanans->currentPage()
                                        ? 'active'
                                        : ''
                                }}
                            "
                        >
                            {{ $page }}
                        </a>

                    @endfor


                    {{-- NEXT --}}
                    @if ($layanans->hasMorePages())

                        <a
                            href="{{ $layanans->nextPageUrl() }}"
                            class="lay-page-link"
                        >
                            ›
                        </a>

                    @else

                        <span
                            class="
                                lay-page-link
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
         DRAWER
    ====================================================== --}}

    @if (auth()->user()->isAdmin())


        {{-- OVERLAY --}}
        <div
            x-show="drawerOpen"
            x-cloak
            x-transition.opacity
            class="lay-overlay"
            @click="closeDrawer()"
        ></div>


        {{-- DRAWER --}}
        <aside
            class="lay-drawer"

            :class="{
                'is-open':
                    drawerOpen
            }"
        >


            {{-- HEADER --}}
            <div class="lay-drawer-header">

                <h2
                    class="lay-drawer-title"

                    x-text="
                        drawerMode === 'edit'
                            ? 'Edit Layanan'
                            : 'Tambah Layanan'
                    "
                ></h2>


                <button
                    type="button"
                    class="lay-drawer-close"
                    @click="closeDrawer()"
                >
                    ✕
                </button>

            </div>


            {{-- INFO EDIT --}}
            <div
                x-show="
                    drawerMode === 'edit'
                "

                x-cloak

                class="lay-info-box"
            >

                Digunakan pada

                <strong
                    x-text="penugasanCount"
                ></strong>

                penugasan.

            </div>


            {{-- FORM --}}
            <form
                :action="
                    drawerMode === 'edit'
                        ? updateUrl
                        : createUrl
                "

                method="POST"
            >

                @csrf


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


                <div class="lay-field">

                    <label>
                        Nama Layanan
                    </label>

                    <input
                        type="text"
                        name="nama_layanan"
                        x-model="form.nama_layanan"
                        class="lay-form-control"
                        placeholder="Masukkan nama layanan"
                        required
                    >

                </div>


                <div class="lay-drawer-actions">

                    <button
                        type="button"
                        class="lay-button"
                        @click="closeDrawer()"
                    >
                        Batal
                    </button>


                    <button
                        type="submit"
                        class="
                            lay-button
                            lay-button-primary
                        "

                        x-text="
                            drawerMode === 'edit'
                                ? 'Update Layanan'
                                : 'Simpan Layanan'
                        "
                    ></button>

                </div>

            </form>


            {{-- DELETE --}}
            <form
                x-show="
                    drawerMode === 'edit'
                "

                x-cloak

                :action="destroyUrl"

                method="POST"

                class="lay-delete-form"

                onsubmit="
                    return confirm(
                        'Hapus data layanan ini?'
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
                        lay-button
                        lay-button-danger
                    "
                >
                    Hapus Layanan
                </button>

            </form>

        </aside>

    @endif

</div>


<script>
    function layananPage(
        editItems,
        initialForm,
        errorMode,
        editingId,
        createUrl
    ) {
        return {

            editItems:
                editItems,

            createUrl:
                createUrl,

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

            penugasanCount:
                0,

            form: {
                nama_layanan:
                    '',
            },


            init()
            {
                if (
                    errorMode ===
                    'create'
                ) {
                    this.openCreate(
                        initialForm
                    );

                    return;
                }


                if (
                    errorMode ===
                    'edit'
                    &&
                    editingId
                ) {
                    const item =
                        this.editItems.find(
                            editItem =>
                                Number(editItem.id)
                                ===
                                Number(editingId)
                        );


                    if (item) {
                        this.openEdit(
                            item
                        );

                        this.form.nama_layanan =
                            initialForm.nama_layanan
                            ??
                            '';
                    }
                }
            },


            resetForm()
            {
                this.form = {
                    nama_layanan:
                        '',
                };
            },


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

                this.penugasanCount =
                    0;

                this.resetForm();


                if (data) {
                    this.form.nama_layanan =
                        data.nama_layanan
                        ??
                        '';
                }


                this.drawerOpen =
                    true;
            },


            openEditById(id)
            {
                const item =
                    this.editItems.find(
                        editItem =>
                            Number(editItem.id)
                            ===
                            Number(id)
                    );


                if (!item) {
                    console.error(
                        'Data layanan tidak ditemukan:',
                        id
                    );

                    return;
                }


                this.openEdit(
                    item
                );
            },


            openEdit(item)
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

                this.penugasanCount =
                    Number(
                        item.penugasan_count
                        ??
                        0
                    );

                this.form = {
                    nama_layanan:
                        item.nama_layanan
                        ??
                        '',
                };

                this.drawerOpen =
                    true;
            },


            closeDrawer()
            {
                this.drawerOpen =
                    false;
            },

        };
    }
</script>

@endsection
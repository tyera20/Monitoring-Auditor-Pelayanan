@extends('layouts.app', ['title' => 'User Management'])

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | Data Drawer Edit
    |--------------------------------------------------------------------------
    */

    $editItems =
        $users
            ->getCollection()
            ->map(
                function ($item) {
                    return [
                        'id' =>
                            $item->id,

                        'name' =>
                            $item->name,

                        'email' =>
                            $item->email,

                        'role' =>
                            $item->role,

                        'update_url' =>
                            route(
                                'user-management.update',
                                $item
                            ),
                    ];
                }
            )
            ->values()
            ->all();

    $editError =
        old('_method')
        ===
        'PUT';

    $editingId =
        old('editing_id');
@endphp


<style>
    [x-cloak] {
        display: none !important;
    }

    .um-page {
        --um-card: #ffffff;
        --um-text: #0f172a;
        --um-muted: #64748b;
        --um-border: #e2e8f0;
        --um-bg: #f4f6fa;
        --um-green: #059669;
        --um-green-hover: #047857;
        --um-red: #e11d48;
        --um-orange: #d97706;

        color: var(--um-text);
    }

    .um-page * {
        box-sizing: border-box;
    }

    .um-heading {
        margin-bottom: 20px;
    }

    .um-heading h1 {
        margin: 0;

        font-size: 22px;
        font-weight: 700;

        line-height: 1.3;
    }

    .um-heading p {
        margin: 3px 0 0;

        color: var(--um-muted);

        font-size: 13px;
    }

    .um-alert {
        margin-bottom: 14px;

        padding: 11px 13px;

        border: 1px solid #fecaca;
        border-radius: 9px;

        background: #fef2f2;

        color: #b91c1c;

        font-size: 12px;
    }

    .um-alert-success {
        border-color: #bbf7d0;

        background: #f0fdf4;

        color: #166534;
    }

    .um-card {
        margin-bottom: 16px;

        border: 1px solid var(--um-border);
        border-radius: 12px;

        background: var(--um-card);

        overflow: hidden;
    }

    .um-card-header {
        display: flex;

        align-items: center;
        justify-content: space-between;

        gap: 10px;

        padding: 14px 16px;

        border-bottom: 1px solid var(--um-border);
    }

    .um-card-header strong {
        font-size: 15px;
        font-weight: 700;
    }

    .um-count {
        display: inline-flex;

        min-width: 24px;
        height: 24px;

        align-items: center;
        justify-content: center;

        padding: 0 7px;

        border-radius: 999px;

        background: #fff7ed;

        color: var(--um-orange);

        font-size: 11px;
        font-weight: 700;
    }

    .um-card-body {
        padding: 14px 16px;
    }

    .um-filter {
        display: flex;

        gap: 10px;

        padding: 14px;

        flex-wrap: wrap;
    }

    .um-control {
        width: 100%;

        min-height: 40px;

        padding: 8px 12px;

        border: 1px solid var(--um-border);
        border-radius: 9px;

        outline: none;

        background: var(--um-bg);

        color: var(--um-text);

        font: inherit;
        font-size: 13px;
    }

    .um-control:focus {
        border-color: var(--um-green);

        box-shadow:
            0 0 0 1px
            var(--um-green);
    }

    .um-search {
        flex: 1;

        min-width: 260px;
    }

    .um-status-filter {
        width: 210px;
    }

    .um-button {
        display: inline-flex;

        min-height: 40px;

        align-items: center;
        justify-content: center;

        gap: 5px;

        padding: 8px 13px;

        border: 1px solid var(--um-border);
        border-radius: 9px;

        background: #ffffff;

        color: var(--um-text);

        font: inherit;
        font-size: 12px;
        font-weight: 600;

        text-decoration: none;

        cursor: pointer;
    }

    .um-button:hover {
        background: #f8fafc;
    }

    .um-button-primary,
    .um-button-approve {
        border-color: var(--um-green);

        background: var(--um-green);

        color: #ffffff;
    }

    .um-button-primary:hover,
    .um-button-approve:hover {
        background: var(--um-green-hover);
    }

    .um-button-reject {
        border-color: #fecaca;

        background: #fff1f2;

        color: var(--um-red);
    }

    .um-button-danger {
        border-color: var(--um-red);

        background: var(--um-red);

        color: #ffffff;
    }

    .um-pending-list {
        display: grid;

        gap: 10px;
    }

    .um-pending-item {
        display: flex;

        align-items: center;
        justify-content: space-between;

        gap: 14px;

        padding: 12px;

        border: 1px solid var(--um-border);
        border-radius: 10px;

        background: #ffffff;

        flex-wrap: wrap;
    }

    .um-pending-name {
        font-size: 13px;
        font-weight: 700;
    }

    .um-pending-email {
        margin-top: 2px;

        color: var(--um-muted);

        font-size: 12px;
    }

    .um-pending-actions {
        display: flex;

        gap: 7px;

        flex-wrap: wrap;
    }

    .um-create-grid {
        display: grid;

        grid-template-columns:
            1.3fr
            1.4fr
            1fr
            .8fr;

        gap: 10px;
    }

    .um-save {
        width: 100%;

        margin-top: 12px;
    }

    .um-table-wrap {
        overflow-x: auto;
    }

    .um-table {
        width: 100%;

        min-width: 940px;

        border-collapse: collapse;

        font-size: 13px;
    }

    .um-table th {
        padding: 10px 14px;

        background: var(--um-bg);

        color: var(--um-muted);

        font-size: 11px;
        font-weight: 600;

        text-align: left;
        text-transform: uppercase;
    }

    .um-table td {
        padding: 12px 14px;

        border-top: 1px solid var(--um-border);

        vertical-align: middle;
    }

    .um-user-name {
        font-weight: 600;
    }

    .um-email {
        color: #475569;
    }

    .um-role,
    .um-status {
        display: inline-flex;

        align-items: center;

        padding: 4px 9px;

        border-radius: 999px;

        font-size: 10px;
        font-weight: 700;

        text-transform: uppercase;

        white-space: nowrap;
    }

    .um-role-admin {
        background: #ede9fe;

        color: #6d28d9;
    }

    .um-role-guest {
        background: #e0f2fe;

        color: #0369a1;
    }

    .um-status-pending {
        background: #fff7ed;

        color: var(--um-orange);
    }

    .um-status-active {
        background: #ecfdf5;

        color: #047857;
    }

    .um-status-rejected {
        background: #fff1f2;

        color: var(--um-red);
    }

    .um-actions {
        display: flex;

        align-items: center;

        gap: 6px;

        flex-wrap: wrap;
    }

    .um-edit {
        border: 0;

        padding: 0;

        background: transparent;

        color: var(--um-green);

        font: inherit;
        font-size: 12px;
        font-weight: 700;

        cursor: pointer;
    }

    .um-footer {
        display: flex;

        align-items: center;
        justify-content: space-between;

        gap: 10px;

        flex-wrap: wrap;

        padding: 12px 14px;

        border-top: 1px solid var(--um-border);
    }

    .um-info {
        color: var(--um-muted);

        font-size: 12px;
    }

    .um-pagination {
        display: flex;

        gap: 4px;
    }

    .um-page-link {
        display: grid;

        width: 32px;
        height: 32px;

        place-items: center;

        border: 1px solid var(--um-border);
        border-radius: 8px;

        color: var(--um-text);

        font-size: 12px;

        text-decoration: none;
    }

    .um-page-link.active {
        border-color: var(--um-green);

        background: var(--um-green);

        color: #ffffff;
    }

    .um-page-link.disabled {
        color: #cbd5e1;
    }

    .um-overlay {
        position: fixed;

        z-index: 80;

        inset: 0;

        background:
            rgba(
                2,
                6,
                23,
                .50
            );
    }

    .um-drawer {
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

        border-left: 1px solid var(--um-border);

        background: #ffffff;

        transform:
            translateX(100%);

        transition:
            transform .25s ease;
    }

    .um-drawer.open {
        transform: none;
    }

    .um-drawer-header {
        display: flex;

        align-items: center;
        justify-content: space-between;

        gap: 12px;

        margin-bottom: 18px;
    }

    .um-drawer-header strong {
        font-size: 18px;
    }

    .um-close {
        width: 36px;
        height: 36px;

        display: grid;

        place-items: center;

        border: 1px solid var(--um-border);
        border-radius: 8px;

        background: #ffffff;

        color: var(--um-muted);

        cursor: pointer;
    }

    .um-field {
        margin-bottom: 13px;
    }

    .um-field label {
        display: block;

        margin-bottom: 5px;

        color: var(--um-muted);

        font-size: 12px;
    }

    .um-drawer-actions {
        display: flex;

        gap: 8px;

        margin-top: 18px;
    }

    .um-drawer-actions .um-button:first-child {
        flex: 1;
    }

    .um-drawer-actions .um-button-primary {
        flex: 2;
    }

    @media (max-width: 900px) {
        .um-create-grid {
            grid-template-columns:
                repeat(
                    2,
                    minmax(0, 1fr)
                );
        }
    }

    @media (max-width: 600px) {
        .um-filter {
            display: grid;

            grid-template-columns: 1fr;
        }

        .um-search,
        .um-status-filter {
            width: 100%;
            min-width: 0;
        }

        .um-create-grid {
            grid-template-columns: 1fr;
        }
    }
</style>


<div
    class="um-page"

    x-data="userManagementPage(
        @js($editItems),
        @js($editError),
        @js($editingId)
    )"

    @keydown.escape.window="closeEdit()"
>

    <div class="um-heading">

        <h1>
            User Management
        </h1>

        <p>
            Kelola pengajuan akun, role, dan pengguna yang dapat mengakses sistem.
        </p>

    </div>


    @if (session('success'))

        <div class="um-alert um-alert-success">
            {{ session('success') }}
        </div>

    @endif


    @if (session('error'))

        <div class="um-alert">
            {{ session('error') }}
        </div>

    @endif


    @if ($errors->any())

        <div class="um-alert">

            @foreach ($errors->all() as $error)

                <div>
                    {{ $error }}
                </div>

            @endforeach

        </div>

    @endif


    {{-- =====================================================
         FILTER
    ====================================================== --}}

    <div class="um-card">

        <form
            action="{{ route('user-management.index') }}"
            method="GET"
            class="um-filter"
        >

            <input
                type="search"
                name="search"
                value="{{ $search }}"
                class="um-control um-search"
                placeholder="🔍 Cari nama, email, username..."
                autocomplete="off"
            >


            <select
                name="status"
                class="um-control um-status-filter"
            >

                <option value="">
                    Semua status
                </option>

                <option
                    value="pending"
                    @selected($status === 'pending')
                >
                    Menunggu Persetujuan
                </option>

                <option
                    value="active"
                    @selected($status === 'active')
                >
                    Aktif
                </option>

                <option
                    value="rejected"
                    @selected($status === 'rejected')
                >
                    Ditolak
                </option>

            </select>


            <button
                type="submit"
                class="um-button um-button-primary"
            >
                Cari
            </button>


            <a
                href="{{ route('user-management.index') }}"
                class="um-button"
            >
                Reset
            </a>

        </form>

    </div>


    {{-- =====================================================
         PENGAJUAN AKUN
    ====================================================== --}}

    <div class="um-card">

        <div class="um-card-header">

            <strong>
                Pengajuan Akun
            </strong>

            <span class="um-count">
                {{ $totalPending }}
            </span>

        </div>


        <div class="um-card-body">

            @forelse ($pendingApprovals as $pending)

                <div
                    class="um-pending-item"
                    style="
                        margin-bottom:
                        {{ $loop->last ? '0' : '10px' }};
                    "
                >

                    <div>

                        <div class="um-pending-name">
                            {{ $pending->name }}
                        </div>

                        <div class="um-pending-email">
                            {{ $pending->email }}
                        </div>

                    </div>


                    <div class="um-pending-actions">

                        <form
                            action="{{ route('user-management.approve', $pending) }}"
                            method="POST"
                        >
                            @csrf
                            @method('PATCH')

                            <button
                                type="submit"
                                class="um-button um-button-approve"
                            >
                                ✓ Setujui
                            </button>
                        </form>


                        <form
                            action="{{ route('user-management.reject', $pending) }}"
                            method="POST"
                            onsubmit="return confirm('Tolak pengajuan akun ini?')"
                        >
                            @csrf
                            @method('PATCH')

                            <button
                                type="submit"
                                class="um-button um-button-reject"
                            >
                                Tolak
                            </button>
                        </form>

                    </div>

                </div>

            @empty

                <div
                    style="
                        color: #64748b;
                        font-size: 12px;
                    "
                >
                    Tidak ada pengajuan akun baru.
                </div>

            @endforelse

        </div>

    </div>


    {{-- =====================================================
         TAMBAH USER MANUAL
    ====================================================== --}}

    <div class="um-card">

        <div class="um-card-header">

            <strong>
                Tambah User
            </strong>

        </div>


        <div class="um-card-body">

            <form
                action="{{ route('user-management.store') }}"
                method="POST"
            >

                @csrf


                <div class="um-create-grid">

                    <input
                        type="text"
                        name="name"
                        class="um-control"
                        placeholder="Nama"
                        required
                    >


                    <input
                        type="email"
                        name="email"
                        class="um-control"
                        placeholder="Email"
                        required
                    >


                    <input
                        type="password"
                        name="password"
                        class="um-control"
                        placeholder="Password"
                        required
                    >


                    <select
                        name="role"
                        class="um-control"
                        required
                    >

                        <option value="guest">
                            guest
                        </option>

                        <option value="admin">
                            admin
                        </option>

                    </select>

                </div>


                <button
                    type="submit"
                    class="um-button um-button-primary um-save"
                >
                    Simpan
                </button>

            </form>

        </div>

    </div>


    {{-- =====================================================
         SEMUA USER
    ====================================================== --}}

    <div class="um-card">

        <div class="um-table-wrap">

            <table class="um-table">

                <thead>

                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>

                </thead>


                <tbody>

                    @forelse ($users as $item)

                        <tr>

                            <td>
                                <span class="um-user-name">
                                    {{ $item->name }}
                                </span>
                            </td>


                            <td>
                                <span class="um-email">
                                    {{ $item->email ?? '-' }}
                                </span>
                            </td>


                            <td>
                                {{ $item->username }}
                            </td>


                            <td>

                                <span
                                    class="
                                        um-role

                                        {{
                                            $item->role === 'admin'
                                                ? 'um-role-admin'
                                                : 'um-role-guest'
                                        }}
                                    "
                                >
                                    {{ $item->role }}
                                </span>

                            </td>


                            <td>

                                <span
                                    class="
                                        um-status

                                        @if ($item->account_status === 'active')
                                            um-status-active
                                        @elseif ($item->account_status === 'pending')
                                            um-status-pending
                                        @else
                                            um-status-rejected
                                        @endif
                                    "
                                >
                                    @if ($item->account_status === 'active')
                                        Aktif
                                    @elseif ($item->account_status === 'pending')
                                        Menunggu
                                    @else
                                        Ditolak
                                    @endif
                                </span>

                            </td>


                            <td>

                                <div class="um-actions">

                                    @if ($item->account_status !== 'active')

                                        <form
                                            action="{{ route('user-management.approve', $item) }}"
                                            method="POST"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <button
                                                type="submit"
                                                class="um-button um-button-approve"
                                            >
                                                Setujui
                                            </button>
                                        </form>

                                    @endif


                                    <button
                                        type="button"
                                        class="um-edit"

                                        @click="
                                            openEditById(
                                                {{ (int) $item->id }}
                                            )
                                        "
                                    >
                                        ✎ Edit
                                    </button>


                                    @if (auth()->id() !== $item->id)

                                        <form
                                            action="{{ route('user-management.destroy', $item) }}"
                                            method="POST"

                                            onsubmit="
                                                return confirm(
                                                    'Hapus akun ini secara permanen?'
                                                )
                                            "
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="um-button"
                                            >
                                                Hapus
                                            </button>

                                        </form>

                                    @endif

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="6"
                                style="
                                    padding: 30px;
                                    text-align: center;
                                    color: #64748b;
                                "
                            >
                                Tidak ada data user.
                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        <div class="um-footer">

            <span class="um-info">

                Menampilkan

                {{ $users->firstItem() ?? 0 }}

                –

                {{ $users->lastItem() ?? 0 }}

                dari

                {{ $users->total() }}

                user

            </span>


            @if ($users->lastPage() > 1)

                <div class="um-pagination">

                    @if ($users->onFirstPage())

                        <span class="um-page-link disabled">
                            ‹
                        </span>

                    @else

                        <a
                            href="{{ $users->previousPageUrl() }}"
                            class="um-page-link"
                        >
                            ‹
                        </a>

                    @endif


                    @for (
                        $page = 1;
                        $page <= $users->lastPage();
                        $page++
                    )

                        <a
                            href="{{ $users->url($page) }}"

                            class="
                                um-page-link

                                {{
                                    $page === $users->currentPage()
                                        ? 'active'
                                        : ''
                                }}
                            "
                        >
                            {{ $page }}
                        </a>

                    @endfor


                    @if ($users->hasMorePages())

                        <a
                            href="{{ $users->nextPageUrl() }}"
                            class="um-page-link"
                        >
                            ›
                        </a>

                    @else

                        <span class="um-page-link disabled">
                            ›
                        </span>

                    @endif

                </div>

            @endif

        </div>

    </div>


    {{-- =====================================================
         DRAWER EDIT
    ====================================================== --}}

    <div
        x-show="editOpen"
        x-cloak
        x-transition.opacity
        class="um-overlay"
        @click="closeEdit()"
    ></div>


    <aside
        class="um-drawer"

        :class="{
            'open':
                editOpen
        }"
    >

        <div class="um-drawer-header">

            <strong>
                Edit User
            </strong>


            <button
                type="button"
                class="um-close"
                @click="closeEdit()"
            >
                ✕
            </button>

        </div>


        <form
            :action="editForm.update_url"
            method="POST"
        >

            @csrf
            @method('PUT')


            <input
                type="hidden"
                name="editing_id"
                :value="editForm.id"
            >


            <div class="um-field">

                <label>
                    Nama
                </label>

                <input
                    type="text"
                    name="name"
                    x-model="editForm.name"
                    class="um-control"
                    required
                >

            </div>


            <div class="um-field">

                <label>
                    Email
                </label>

                <input
                    type="email"
                    name="email"
                    x-model="editForm.email"
                    class="um-control"
                    required
                >

            </div>


            <div class="um-field">

                <label>
                    Role
                </label>

                <select
                    name="role"
                    x-model="editForm.role"
                    class="um-control"
                    required
                >

                    <option value="guest">
                        guest
                    </option>

                    <option value="admin">
                        admin
                    </option>

                </select>

            </div>


            <div class="um-field">

                <label>
                    Password baru (opsional)
                </label>

                <input
                    type="password"
                    name="password"
                    x-model="editForm.password"
                    class="um-control"
                    placeholder="Kosongkan jika tidak diubah"
                >

            </div>


            <div class="um-drawer-actions">

                <button
                    type="button"
                    class="um-button"
                    @click="closeEdit()"
                >
                    Batal
                </button>


                <button
                    type="submit"
                    class="um-button um-button-primary"
                >
                    Simpan Perubahan
                </button>

            </div>

        </form>

    </aside>

</div>


<script>
    function userManagementPage(
        editItems,
        editError,
        editingId
    ) {
        return {
            editItems:
                editItems,

            editOpen:
                false,

            editForm: {
                id:
                    null,

                name:
                    '',

                email:
                    '',

                role:
                    'guest',

                password:
                    '',

                update_url:
                    '',
            },


            init()
            {
                if (
                    editError
                    &&
                    editingId
                ) {
                    this.openEditById(
                        editingId
                    );

                    this.editForm.name =
                        @js(
                            old(
                                'name',
                                ''
                            )
                        );

                    this.editForm.email =
                        @js(
                            old(
                                'email',
                                ''
                            )
                        );

                    this.editForm.role =
                        @js(
                            old(
                                'role',
                                'guest'
                            )
                        );
                }
            },


            openEditById(id)
            {
                const item =
                    this.editItems.find(
                        user =>
                            Number(
                                user.id
                            )
                            ===
                            Number(
                                id
                            )
                    );

                if (! item) {
                    return;
                }

                this.editForm = {
                    id:
                        item.id,

                    name:
                        item.name
                        ??
                        '',

                    email:
                        item.email
                        ??
                        '',

                    role:
                        item.role
                        ??
                        'guest',

                    password:
                        '',

                    update_url:
                        item.update_url
                        ??
                        '',
                };

                this.editOpen =
                    true;
            },


            closeEdit()
            {
                this.editOpen =
                    false;

                this.editForm.password =
                    '';
            },
        };
    }
</script>

@endsection

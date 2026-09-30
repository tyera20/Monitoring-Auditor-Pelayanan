@extends('layouts.app', ['title' => 'Penugasan'])

@section('content')

@php
    $currentUser = auth()->user();

    $isAdmin =
        $currentUser
        &&
        $currentUser->role === 'admin';

    /*
    |--------------------------------------------------------------------------
    | FORM AWAL
    |--------------------------------------------------------------------------
    */

    $initialForm = [
        'petugas_ids' => collect(old('petugas_ids', []))
            ->map(fn ($id) => (string) $id)
            ->values()
            ->all(),

        'layanan_id' => (string) old('layanan_id', ''),

        'tempat' => old('tempat', ''),

        'komoditi' => old('komoditi', ''),

        'task_detail' => old('task_detail', ''),

        'tanggal_mulai' => old('tanggal_mulai', ''),

        'tanggal_selesai' => old('tanggal_selesai', ''),
    ];

    /*
    |--------------------------------------------------------------------------
    | DATA EDIT
    |--------------------------------------------------------------------------
    */

    $editItems = $penugasans
        ->getCollection()
        ->map(function ($item) {
            return [
                'id' => $item->id,

                'update_url' => route(
                    'penugasan.update',
                    $item
                ),

                'destroy_url' => route(
                    'penugasan.destroy',
                    $item
                ),

                'petugas' => $item->petugas
                    ->map(function ($petugas) {
                        return [
                            'id' => $petugas->id,
                            'name' => $petugas->name,
                            'nip' => $petugas->nip,
                        ];
                    })
                    ->values()
                    ->all(),

                'layanan_id' => $item->layanan_id,

                'layanan_name' => $item->layanan?->nama_layanan,

                'tempat' => $item->tempat,

                'komoditi' => $item->komoditi,

                'task_detail' => $item->task_detail,

                'tanggal_mulai' => $item->tanggal_mulai?->format('Y-m-d'),

                'tanggal_selesai' => $item->tanggal_selesai?->format('Y-m-d'),
            ];
        })
        ->values()
        ->all();

    /*
    |--------------------------------------------------------------------------
    | ERROR MODE
    |--------------------------------------------------------------------------
    */

    $errorMode = null;

    if ($errors->any()) {
        $errorMode =
            old('_method') === 'PUT'
                ? 'edit'
                : 'create';
    }

    $editingId = old('editing_id');

    /*
    |--------------------------------------------------------------------------
    | WARNING JADWAL BENTROK
    |--------------------------------------------------------------------------
    */

    $scheduleConflicts = session('schedule_conflicts', []);

    $scheduleWarningMode = session('schedule_warning_mode');

    if (!empty($scheduleConflicts)) {
        $errorMode =
            $scheduleWarningMode
            ??
            (
                old('_method') === 'PUT'
                    ? 'edit'
                    : 'create'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | PALETTE TABEL
    |--------------------------------------------------------------------------
    |
    | Palette ini tetap digunakan di tabel utama.
    | Riwayat akan menggunakan hijau seragam.
    |
    */

    $palette = [
        '#94a3b8',
        '#f59e0b',
        '#ec4899',
        '#14b8a6',
        '#22c55e',
        '#3b82f6',
        '#8b5cf6',
        '#06b6d4',
        '#84cc16',
        '#f97316',
    ];
@endphp


<style>
    [x-cloak] {
        display: none !important;
    }

    .pen-page {
        --card: #ffffff;
        --text: #0f172a;
        --muted: #64748b;
        --border: #e2e8f0;
        --background: #f4f6fa;

        --green: #059669;
        --green-hover: #047857;
        --green-light: #ecfdf5;
        --green-soft: #dcfce7;
        --green-text: #15803d;

        --red: #e11d48;

        color: var(--text);

        font-family:
            Inter,
            system-ui,
            -apple-system,
            BlinkMacSystemFont,
            "Segoe UI",
            sans-serif;
    }

    .pen-page * {
        box-sizing: border-box;
    }


    /* =========================================================
       HEADER
    ========================================================= */

    .pen-header {
        display: flex;
        align-items: center;
        justify-content: space-between;

        gap: 16px;
        flex-wrap: wrap;

        margin-bottom: 20px;
    }

    .pen-title {
        margin: 0;

        font-size: 22px;
        font-weight: 700;
        line-height: 1.3;
    }

    .pen-subtitle {
        margin: 3px 0 0;

        color: var(--muted);

        font-size: 13px;
    }

    .pen-header-actions {
        display: flex;
        align-items: center;

        gap: 8px;
        flex-wrap: wrap;
    }


    /* =========================================================
       BUTTON
    ========================================================= */

    .pen-button {
        display: inline-flex;

        min-height: 40px;

        align-items: center;
        justify-content: center;

        gap: 7px;

        padding: 8px 14px;

        border: 1px solid var(--border);
        border-radius: 9px;

        background: #ffffff;
        color: var(--text);

        font: inherit;
        font-size: 13px;
        font-weight: 600;

        text-decoration: none;

        cursor: pointer;

        transition:
            background .15s ease,
            border-color .15s ease;
    }

    .pen-button:hover {
        background: #f1f5f9;
    }

    .pen-button-primary {
        border-color: var(--green);

        background: var(--green);
        color: #ffffff;
    }

    .pen-button-primary:hover {
        background: var(--green-hover);
    }

    .pen-button-danger {
        border-color: var(--red);

        background: var(--red);
        color: #ffffff;
    }


    /* =========================================================
       ALERT
    ========================================================= */

    .pen-alert {
        margin-bottom: 16px;

        padding: 12px 14px;

        border-radius: 10px;

        font-size: 13px;
    }

    .pen-alert-success {
        border: 1px solid #bbf7d0;

        background: #f0fdf4;
        color: #166534;
    }

    .pen-alert-error {
        border: 1px solid #fecaca;

        background: #fef2f2;
        color: #b91c1c;
    }


    /* =========================================================
       STATS
    ========================================================= */

    .pen-stats {
        display: grid;

        grid-template-columns:
            repeat(
                4,
                minmax(0, 1fr)
            );

        gap: 14px;

        margin-bottom: 16px;
    }

    .pen-stat {
        padding: 14px 16px;

        border: 1px solid var(--border);
        border-radius: 12px;

        background: #ffffff;
    }

    .pen-stat span {
        display: block;

        color: var(--muted);

        font-size: 12px;
    }

    .pen-stat strong {
        display: block;

        margin-top: 4px;

        font-size: 24px;
        font-weight: 700;
    }


    /* =========================================================
       CARD
    ========================================================= */

    .pen-card {
        border: 1px solid var(--border);
        border-radius: 12px;

        background: #ffffff;

        overflow: visible;
    }


    /* =========================================================
       FILTER
    ========================================================= */

    .pen-filter-bar {
        display: flex;

        align-items: center;

        gap: 10px;
        flex-wrap: wrap;

        padding: 14px;

        border-bottom: 1px solid var(--border);
    }

    .pen-filter-form {
        display: flex;

        flex: 1;

        min-width: 0;

        align-items: center;

        gap: 10px;
        flex-wrap: wrap;
    }

    .pen-control {
        min-height: 40px;

        padding: 8px 12px;

        border: 1px solid var(--border);
        border-radius: 9px;

        outline: none;

        background: var(--background);
        color: var(--text);

        font: inherit;
        font-size: 13px;
    }

    .pen-control:focus {
        border-color: var(--green);

        box-shadow:
            0 0 0 1px
            var(--green);
    }

    .pen-search {
        flex: 1;

        min-width: 260px;
    }

    .pen-layanan-filter {
        width: 230px;
    }


    /* =========================================================
       COLUMN MENU
    ========================================================= */

    .pen-column-dropdown {
        position: relative;

        margin-left: auto;
    }

    .pen-column-menu {
        position: absolute;

        z-index: 50;

        top: calc(100% + 6px);
        right: 0;

        width: 190px;

        padding: 8px;

        border: 1px solid var(--border);
        border-radius: 10px;

        background: #ffffff;

        box-shadow:
            0 10px 30px
            rgba(15, 23, 42, .12);
    }

    .pen-column-option {
        display: flex;

        align-items: center;

        gap: 8px;

        padding: 7px 8px;

        border-radius: 7px;

        font-size: 13px;

        cursor: pointer;
    }

    .pen-column-option:hover {
        background: #f1f5f9;
    }

    .pen-column-option input {
        accent-color: var(--green);
    }


    /* =========================================================
       JUMLAH DATA PER HALAMAN
    ========================================================= */

    .pen-table-toolbar {
        display: flex;
        align-items: center;
        justify-content: flex-start;

        padding: 10px 14px;

        border-bottom: 1px solid var(--border);

        background: #ffffff;
    }

    .pen-length-form {
        display: inline-flex;
        align-items: center;

        gap: 7px;

        color: var(--muted);

        font-size: 12px;
        font-weight: 500;
    }

    .pen-length-select {
        min-width: 70px;
        height: 34px;

        padding: 5px 28px 5px 10px;

        border: 1px solid var(--border);
        border-radius: 8px;

        outline: none;

        background: var(--background);
        color: var(--text);

        font: inherit;
        font-size: 12px;
        font-weight: 600;

        cursor: pointer;
    }

    .pen-length-select:focus {
        border-color: var(--green);

        box-shadow:
            0 0 0 1px
            var(--green);
    }


    /* =========================================================
       TABLE
    ========================================================= */

    .pen-table-wrapper {
        width: 100%;

        overflow-x: auto;
    }

    .pen-table {
        width: 100%;

        min-width: 1100px;

        border-collapse: collapse;

        font-size: 13px;
    }

    .pen-table thead {
        background: var(--background);
    }

    .pen-table th {
        padding: 10px 14px;

        color: var(--muted);

        font-size: 11px;
        font-weight: 600;
        letter-spacing: .04em;

        text-align: left;
        text-transform: uppercase;

        white-space: nowrap;
    }

    .pen-table td {
        padding: 12px 14px;

        border-top: 1px solid var(--border);

        color: #334155;

        vertical-align: middle;
    }

    .pen-table tbody tr:hover {
        background: #f8fafc;
    }


    /* =========================================================
       PETUGAS
    ========================================================= */

    .pen-petugas-list {
        display: flex;

        flex-wrap: wrap;

        gap: 5px;
    }

    .pen-petugas-chip {
        display: inline-flex;

        align-items: center;

        gap: 6px;

        padding: 2px 9px 2px 3px;

        border-radius: 999px;

        background: #eef2ff;

        color: #3730a3;

        font-size: 11px;

        white-space: nowrap;
    }

    .pen-avatar-small {
        display: inline-grid;

        width: 21px;
        height: 21px;

        place-items: center;

        flex-shrink: 0;

        border-radius: 50%;

        color: #ffffff;

        font-size: 8px;
        font-weight: 700;
    }


    /* =========================================================
       SERVICE TABLE
    ========================================================= */

    .pen-service {
        display: inline-flex;

        padding: 4px 9px;

        border-radius: 999px;

        font-size: 11px;
        font-weight: 600;

        white-space: nowrap;
    }


    /* =========================================================
       DATE
    ========================================================= */

    .pen-date {
        white-space: nowrap;
    }

    .pen-date small {
        display: block;

        margin-top: 2px;

        color: var(--muted);

        font-size: 10px;
    }


    /* =========================================================
       ACTION
    ========================================================= */

    .pen-action-button {
        border: 0;

        padding: 0;

        background: transparent;

        font: inherit;
        font-size: 12px;
        font-weight: 700;

        cursor: pointer;
    }

    .pen-history-button {
        display: inline-flex;
        align-items: center;
        gap: 5px;

        margin-right: 10px;

        color: #475569;
    }

    .pen-history-button svg {
        width: 14px;
        height: 14px;

        flex-shrink: 0;

        stroke: currentColor;
    }

    .pen-edit-button {
        color: var(--green);
    }

    .pen-action-button:hover {
        text-decoration: underline;
    }


    /* =========================================================
       FOOTER
    ========================================================= */

    .pen-footer {
        display: flex;

        align-items: center;
        justify-content: space-between;

        gap: 10px;
        flex-wrap: wrap;

        padding: 12px 14px;

        border-top: 1px solid var(--border);
    }

    .pen-result-info {
        color: var(--muted);

        font-size: 12px;
    }

    .pen-pagination {
        display: flex;

        align-items: center;

        gap: 4px;
    }

    .pen-page-link {
        display: inline-grid;

        width: 32px;
        height: 32px;

        place-items: center;

        border: 1px solid var(--border);
        border-radius: 8px;

        background: #ffffff;

        color: var(--text);

        font-size: 12px;

        text-decoration: none;
    }

    .pen-page-link.active {
        border-color: var(--green);

        background: var(--green);
        color: #ffffff;
    }

    .pen-page-link.disabled {
        color: #cbd5e1;

        pointer-events: none;
    }


    /* =========================================================
       OVERLAY
    ========================================================= */

    .pen-overlay {
        position: fixed;

        z-index: 80;

        inset: 0;

        background:
            rgba(
                2,
                6,
                23,
                .55
            );
    }


    /* =========================================================
       DRAWER
    ========================================================= */

    .pen-drawer {
        position: fixed;

        z-index: 90;

        top: 0;
        right: 0;
        bottom: 0;

        width:
            min(
                460px,
                100%
            );

        padding: 22px;

        overflow-y: auto;

        border-left: 1px solid var(--border);

        background: #ffffff;

        transform:
            translateX(100%);

        transition:
            transform .25s ease;
    }

    .pen-drawer.is-open {
        transform:
            translateX(0);
    }

    .pen-drawer-header {
        display: flex;

        align-items: center;
        justify-content: space-between;

        gap: 12px;

        margin-bottom: 20px;
    }

    .pen-drawer-title {
        margin: 0;

        font-size: 18px;
        font-weight: 700;
    }

    .pen-drawer-close {
        display: grid;

        width: 36px;
        height: 36px;

        place-items: center;

        border: 1px solid var(--border);
        border-radius: 8px;

        background: #ffffff;

        color: var(--muted);

        cursor: pointer;
    }


    /* =========================================================
       FORM
    ========================================================= */

    .pen-field {
        margin-bottom: 14px;
    }

    .pen-field label {
        display: block;

        margin-bottom: 5px;

        color: var(--muted);

        font-size: 12px;
        font-weight: 500;
    }

    .pen-form-control {
        width: 100%;

        min-height: 42px;

        padding: 9px 11px;

        border: 1px solid var(--border);
        border-radius: 9px;

        outline: none;

        background: #ffffff;
        color: var(--text);

        font: inherit;
        font-size: 13px;
    }

    textarea.pen-form-control {
        min-height: 88px;

        resize: vertical;
    }

    .pen-form-control:focus {
        border-color: var(--green);

        box-shadow:
            0 0 0 1px
            var(--green);
    }

    .pen-form-grid {
        display: grid;

        grid-template-columns:
            repeat(
                2,
                minmax(0, 1fr)
            );

        gap: 10px;
    }


    /* =========================================================
       MULTI SELECT
    ========================================================= */

    .pen-select-wrap {
        position: relative;
    }

    .pen-multi-select {
        display: flex;

        min-height: 42px;

        flex-wrap: wrap;

        align-items: center;

        gap: 5px;

        padding: 5px 7px;

        border: 1px solid var(--border);
        border-radius: 9px;

        background: #ffffff;
    }

    .pen-selected-chip {
        display: inline-flex;

        align-items: center;

        gap: 5px;

        padding: 3px 7px;

        border-radius: 999px;

        background: var(--green-light);
        color: var(--green-hover);

        font-size: 11px;
        font-weight: 600;
    }

    .pen-selected-chip button {
        border: 0;

        padding: 0;

        background: transparent;

        color: inherit;

        cursor: pointer;
    }

    .pen-multi-input {
        flex: 1;

        min-width: 120px;

        padding: 5px;

        border: 0;
        outline: 0;

        font: inherit;
        font-size: 13px;
    }

    .pen-option-menu {
        position: absolute;

        z-index: 100;

        top: calc(100% + 5px);
        left: 0;
        right: 0;

        max-height: 220px;

        overflow-y: auto;

        border: 1px solid var(--border);
        border-radius: 9px;

        background: #ffffff;

        box-shadow:
            0 12px 30px
            rgba(15, 23, 42, .12);
    }

    .pen-option {
        display: block;

        width: 100%;

        padding: 9px 11px;

        border: 0;
        border-bottom: 1px solid #f1f5f9;

        background: #ffffff;

        color: #334155;

        font: inherit;
        font-size: 12px;

        text-align: left;

        cursor: pointer;
    }

    .pen-option:hover {
        background: #f8fafc;
    }

    .pen-drawer-actions {
        display: flex;

        gap: 8px;

        margin-top: 20px;
    }

    .pen-drawer-actions .pen-button {
        flex: 1;
    }

    .pen-client-error {
        margin-bottom: 14px;

        padding: 10px 12px;

        border: 1px solid #fecaca;
        border-radius: 8px;

        background: #fef2f2;
        color: #b91c1c;

        font-size: 12px;
    }


    /* =========================================================
       HISTORY HEADER
    ========================================================= */

    .history-header {
        display: flex;

        align-items: center;
        justify-content: space-between;

        gap: 12px;

        margin-bottom: 18px;
    }

    .history-title {
        margin: 0;

        color: #0f172a;

        font-size: 14px;
        font-weight: 700;
    }

    .history-back-button {
        display: inline-flex;

        min-height: 36px;

        align-items: center;

        gap: 6px;

        padding: 7px 10px;

        border: 1px solid var(--border);
        border-radius: 8px;

        background: #ffffff;
        color: #475569;

        font: inherit;
        font-size: 12px;
        font-weight: 600;

        cursor: pointer;
    }

    .history-back-button:hover {
        background: #f8fafc;
        color: var(--green-hover);
    }


    /* =========================================================
       HISTORY OVERVIEW
    ========================================================= */

    .history-overview-summary {
        margin-bottom: 16px;
    }

    .history-overview-service {
        margin: 0;

        color: #0f172a;

        font-size: 17px;
        font-weight: 700;
        line-height: 1.4;
    }

    .history-overview-meta {
        margin-top: 3px;

        color: #64748b;

        font-size: 12px;
        line-height: 1.5;
    }

    .history-person-list {
        display: grid;

        gap: 14px;
    }

    .history-person-card {
        padding: 12px;

        border: 1px solid var(--border);
        border-radius: 12px;

        background: #ffffff;
    }

    .history-person-top {
        display: flex;

        align-items: center;
        justify-content: space-between;

        gap: 10px;
    }

    .history-person-identity {
        display: flex;

        min-width: 0;

        align-items: center;

        gap: 9px;
    }

    .history-person-avatar {
        display: grid;

        width: 28px;
        height: 28px;

        place-items: center;

        flex-shrink: 0;

        border-radius: 50%;

        color: #ffffff;

        font-size: 9px;
        font-weight: 700;
    }

    .history-person-name {
        overflow: hidden;

        color: #0f172a;

        font-size: 12px;
        font-weight: 700;

        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .history-person-badge {
        flex-shrink: 0;

        padding: 4px 8px;

        border-radius: 999px;

        background: #f1f5f9;
        color: #64748b;

        font-size: 9px;
        font-weight: 700;
    }

    .history-person-badge.has-history {
        background: var(--green-soft);
        color: var(--green-text);
    }

    .history-person-description {
        margin: 10px 0 11px;

        color: #64748b;

        font-size: 11px;
        line-height: 1.55;
    }

    .history-view-button {
        display: flex;

        width: 100%;
        min-height: 34px;

        align-items: center;
        justify-content: center;

        padding: 7px 10px;

        border: 1px solid var(--border);
        border-radius: 8px;

        background: #ffffff;
        color: #0f172a;

        font: inherit;
        font-size: 11px;
        font-weight: 700;

        cursor: pointer;

        transition:
            border-color .15s ease,
            background .15s ease,
            color .15s ease;
    }

    .history-view-button:hover {
        border-color: #a7f3d0;

        background: var(--green-light);
        color: var(--green-hover);
    }

    .history-overview-empty {
        padding: 24px 12px;

        border: 1px dashed var(--border);
        border-radius: 10px;

        color: #64748b;

        font-size: 12px;

        text-align: center;
    }

    /* =========================================================
       HISTORY PROFILE
    ========================================================= */

    .history-profile {
        margin-bottom: 24px;

        text-align: center;
    }

    .history-avatar {
        display: grid;

        width: 64px;
        height: 64px;

        place-items: center;

        margin: 0 auto;

        border: 1px solid #a7f3d0;
        border-radius: 50%;

        background: var(--green) !important;
        color: #ffffff;

        box-shadow:
            0 0 0 5px
            var(--green-light);

        font-size: 21px;
        font-weight: 700;
    }

    .history-name {
        margin: 13px 0 3px;

        color: #020617;

        font-size: 21px;
        font-weight: 700;
    }

    .history-position {
        display: inline-flex;

        padding: 3px 9px;

        border-radius: 999px;

        background: var(--green-light);
        color: var(--green-hover);

        font-size: 11px;
        font-weight: 600;
    }

    .history-label {
        margin-top: 15px;

        color: var(--muted);

        font-size: 12px;
    }

    .history-nip {
        display: flex;

        align-items: center;

        gap: 7px;

        margin-top: 2px;

        color: #0f172a;

        font-size: 13px;
    }

    .history-copy {
        display: inline-grid;

        width: 26px;
        height: 26px;

        place-items: center;

        padding: 0;

        border: 0;
        border-radius: 6px;

        background: transparent;
        color: #64748b;

        cursor: pointer;

        transition:
            background .15s ease,
            color .15s ease;
    }

    .history-copy:hover {
        background: #f1f5f9;
        color: #0f172a;
    }

    .history-copy svg {
        width: 15px;
        height: 15px;

        fill: none;
        stroke: currentColor;
    }


    /* =========================================================
       EXPERIENCE
    ========================================================= */

    .history-experience-title {
        margin-top: 20px;

        color: #64748b;

        font-size: 12px;
    }

    .history-progress {
        height: 12px;

        margin: 7px 0 9px;

        overflow: hidden;

        border-radius: 999px;

        background: #eef2f7;
    }

    .history-progress-bar {
        height: 100%;

        border-radius: 999px;

        background: var(--green);

        transition:
            width .2s ease;
    }


    /* =========================================================
       DAFTAR SEMUA LAYANAN
    ========================================================= */

    .history-service-row {
        display: flex;

        min-height: 36px;

        align-items: center;
        justify-content: space-between;

        gap: 10px;

        border-bottom: 1px solid #e2e8f0;
    }

    .history-service-left {
        display: flex;

        min-width: 0;

        align-items: center;

        gap: 7px;

        color: #0f172a;

        font-size: 12px;
    }

    /*
    |--------------------------------------------------------------------------
    | SEMUA DOT RIWAYAT SAMA WARNA
    |--------------------------------------------------------------------------
    */

    .history-dot {
        width: 6px;
        height: 6px;

        flex-shrink: 0;

        border-radius: 50%;

        background: var(--green) !important;
    }

    .history-service-row .history-dot {
        background: var(--green) !important;
    }

    .history-company-service .history-dot {
        background: var(--green) !important;
    }

    .history-status {
        flex-shrink: 0;

        padding: 3px 8px;

        border-radius: 999px;

        background: #f1f5f9;
        color: #64748b;

        font-size: 10px;
        font-weight: 700;
    }

    .history-status.done {
        background: var(--green-soft);
        color: var(--green-text);
    }


    /* =========================================================
       RIWAYAT BERDASARKAN PT
    ========================================================= */

    .history-company-title {
        margin-top: 24px;

        padding-bottom: 8px;

        border-bottom: 1px solid var(--border);

        color: #64748b;

        font-size: 12px;
        font-weight: 600;
    }

    .history-company-card {
        margin-top: 10px;

        overflow: hidden;

        border: 1px solid var(--border);
        border-radius: 10px;

        background: #ffffff;
    }


    /* =========================================================
       HEADER PT / DROPDOWN
    ========================================================= */

    .history-company-header {
        display: flex;

        width: 100%;

        min-height: 50px;

        align-items: center;
        justify-content: space-between;

        gap: 10px;

        padding: 10px 12px;

        border: 0;

        background: #f8fafc;
        color: inherit;

        font: inherit;

        text-align: left;

        cursor: pointer;

        transition:
            background .15s ease;
    }

    .history-company-header:hover {
        background: #f1f5f9;
    }

    .history-company-name {
        display: flex;

        min-width: 0;

        align-items: center;

        gap: 8px;

        color: #0f172a;

        font-size: 12px;
        font-weight: 700;
    }

    .history-company-arrow {
        display: inline-block;

        width: 12px;

        flex-shrink: 0;

        color: var(--green);

        font-size: 10px;

        transform: rotate(0deg);

        transition:
            transform .18s ease;
    }

    .history-company-arrow.open {
        transform: rotate(90deg);
    }


    /* =========================================================
       ICON PT - HIJAU SERAGAM
    ========================================================= */

    .history-company-icon {
        display: grid;

        width: 28px;
        height: 28px;

        place-items: center;

        flex-shrink: 0;

        border-radius: 8px;

        background: var(--green-light) !important;
        color: var(--green) !important;

        font-size: 14px;
    }

    .history-company-icon svg {
        width: 15px;
        height: 15px;

        stroke: var(--green) !important;
    }

    .history-company-count {
        flex-shrink: 0;

        padding: 4px 8px;

        border-radius: 999px;

        background: var(--green-soft);
        color: var(--green-text);

        font-size: 9px;
        font-weight: 700;
    }


    /* =========================================================
       BODY PT
    ========================================================= */

    .history-company-body {
        border-top: 1px solid var(--border);

        background: #ffffff;
    }


    /* =========================================================
       LAYANAN DI DALAM PT
    ========================================================= */

    .history-company-service {
        padding: 11px 12px;

        border-bottom: 1px solid #f1f5f9;
    }

    .history-company-service:last-child {
        border-bottom: 0;
    }

    .history-company-service-head {
        display: flex;

        align-items: center;
        justify-content: space-between;

        gap: 8px;
    }

    .history-company-service-name {
        display: flex;

        min-width: 0;

        align-items: center;

        gap: 7px;

        color: #0f172a;

        font-size: 12px;
        font-weight: 600;
    }

    .history-company-service-count {
        flex-shrink: 0;

        padding: 3px 8px;

        border-radius: 999px;

        background: var(--green-soft);
        color: var(--green-text);

        font-size: 9px;
        font-weight: 700;
    }


    /* =========================================================
       DETAIL KEGIATAN PT
    ========================================================= */

    .history-company-activity {
        margin-top: 9px;

        padding-left: 13px;

        border-left: 2px solid #a7f3d0;
    }

    .history-company-task {
        color: #475569;

        font-size: 11px;

        line-height: 1.5;
    }

    .history-company-meta {
        margin-top: 4px;

        color: #94a3b8;

        font-size: 10px;
    }

    .history-company-empty {
        padding: 12px 0;

        color: #64748b;

        font-size: 12px;
    }


    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 1000px) {
        .pen-stats {
            grid-template-columns:
                repeat(
                    2,
                    minmax(0, 1fr)
                );
        }
    }

    @media (max-width: 700px) {
        .pen-search {
            width: 100%;
            min-width: 100%;
        }

        .pen-layanan-filter {
            width: 100%;
        }

        .pen-form-grid {
            grid-template-columns: 1fr;
        }
    }

    /* =========================================================
       WARNING JADWAL BENTROK
    ========================================================= */

    .pen-schedule-warning-backdrop {
        position: fixed;
        z-index: 120;
        inset: 0;
        display: grid;
        place-items: center;
        padding: 20px;
        background: rgba(15, 23, 42, .48);
        backdrop-filter: blur(2px);
    }

    .pen-schedule-warning {
        width: min(560px, 100%);
        max-height: min(720px, calc(100vh - 40px));
        overflow-y: auto;
        border: 1px solid #fde68a;
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 24px 64px rgba(15, 23, 42, .22);
    }

    .pen-schedule-warning-head {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 20px 20px 14px;
    }

    .pen-schedule-warning-icon {
        display: grid;
        width: 40px;
        height: 40px;
        flex: 0 0 40px;
        place-items: center;
        border-radius: 12px;
        background: #fffbeb;
        color: #b45309;
    }

    .pen-schedule-warning-icon svg {
        width: 22px;
        height: 22px;
        stroke: currentColor;
    }

    .pen-schedule-warning-title {
        margin: 0;
        font-size: 17px;
        font-weight: 750;
        color: #0f172a;
    }

    .pen-schedule-warning-subtitle {
        margin: 5px 0 0;
        font-size: 13px;
        line-height: 1.55;
        color: #64748b;
    }

    .pen-schedule-warning-body {
        padding: 0 20px 18px;
    }

    .pen-schedule-warning-list {
        display: grid;
        gap: 10px;
    }

    .pen-schedule-warning-item {
        padding: 13px 14px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #f8fafc;
    }

    .pen-schedule-warning-name {
        font-size: 13px;
        font-weight: 750;
        color: #0f172a;
    }

    .pen-schedule-warning-service {
        margin-top: 4px;
        font-size: 12px;
        font-weight: 650;
        color: #475569;
    }

    .pen-schedule-warning-meta {
        margin-top: 4px;
        font-size: 12px;
        line-height: 1.5;
        color: #64748b;
    }

    .pen-schedule-warning-note {
        margin: 14px 0 0;
        padding: 11px 12px;
        border-radius: 10px;
        background: #fffbeb;
        font-size: 12px;
        line-height: 1.55;
        color: #92400e;
    }

    .pen-schedule-warning-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 14px 20px 20px;
        border-top: 1px solid #f1f5f9;
    }

    @media (max-width: 520px) {
        .pen-stats {
            grid-template-columns: 1fr;
        }

        .pen-schedule-warning-actions {
            flex-direction: column-reverse;
        }

        .pen-schedule-warning-actions .pen-button {
            width: 100%;
            justify-content: center;
        }
    }
</style>


<div
    class="pen-page"

    x-data="penugasanPage(
        @js($petugasOptions),
        @js($layananOptions),
        @js($editItems),
        @js($initialForm),
        @js($errorMode),
        @js($editingId),
        @js($historyByPetugas),
        @js($rowPetugasIds),
        @js(route('penugasan.store')),
        @js($scheduleConflicts)
    )"

    x-init="init()"

    @keydown.escape.window="handleEscape()"
>


    {{-- =====================================================
         HEADER
    ====================================================== --}}

    <div class="pen-header">

        <div>

            <h1 class="pen-title">
                Transaksi Penugasan
            </h1>

            <p class="pen-subtitle">
                Kelola dan pantau seluruh penugasan petugas
            </p>

        </div>


        <div class="pen-header-actions">

            <a
                href="{{ route('penugasan.export', request()->query()) }}"
                class="pen-button"
            >
                ↓ Export Excel
            </a>


            <button
                type="button"
                class="pen-button pen-button-primary"
                @click="openCreate()"
            >
                ＋ Tambah Penugasan
            </button>

        </div>

    </div>


    {{-- =====================================================
         ALERT
    ====================================================== --}}

    @if(session('success'))

        <div class="pen-alert pen-alert-success">
            {{ session('success') }}
        </div>

    @endif


    @if($errors->any())

        <div class="pen-alert pen-alert-error">

            <strong>
                Data belum dapat disimpan.
            </strong>

            <ul>

                @foreach($errors->all() as $error)

                    <li>
                        {{ $error }}
                    </li>

                @endforeach

            </ul>

        </div>

    @endif


    {{-- =====================================================
         STATISTIK
    ====================================================== --}}

    <div class="pen-stats">

        <div class="pen-stat">

            <span>
                Total Penugasan
            </span>

            <strong>
                {{ $totalPenugasan }}
            </strong>

        </div>


        <div class="pen-stat">

            <span>
                Ditampilkan
            </span>

            <strong>
                {{ $ditampilkan }}
            </strong>

        </div>


        <div class="pen-stat">

            <span>
                Petugas Terlibat
            </span>

            <strong>
                {{ $petugasTerlibat }}
            </strong>

        </div>


        <div class="pen-stat">

            <span>
                Jenis Layanan
            </span>

            <strong>
                {{ $jenisLayanan }}
            </strong>

        </div>

    </div>


    {{-- =====================================================
         CARD
    ====================================================== --}}

    <div class="pen-card">


        {{-- =================================================
             FILTER
        ================================================== --}}

        <div class="pen-filter-bar">

            <form
                action="{{ route('penugasan.index') }}"
                method="GET"
                class="pen-filter-form"
            >

                <input
                    type="hidden"
                    name="per_page"
                    value="{{ request('per_page', 10) }}"
                >


                <input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    class="pen-control pen-search"
                    placeholder="🔍 Cari petugas, tempat, atau komoditi..."
                    autocomplete="off"
                >


                <select
                    name="layanan_id"
                    class="pen-control pen-layanan-filter"
                >

                    <option value="">
                        Semua layanan
                    </option>


                    @foreach($layananOptions as $layanan)

                        <option
                            value="{{ $layanan->id }}"

                            {{
                                (string) request('layanan_id')
                                ===
                                (string) $layanan->id
                                    ? 'selected'
                                    : ''
                            }}
                        >
                            {{ $layanan->nama_layanan }}
                        </option>

                    @endforeach

                </select>


                <input
                    type="date"
                    name="dari"
                    value="{{ request('dari') }}"
                    class="pen-control"
                >


                <span
                    style="
                        color:#64748b;
                        font-size:12px;
                    "
                >
                    s/d
                </span>


                <input
                    type="date"
                    name="sampai"
                    value="{{ request('sampai') }}"
                    class="pen-control"
                >


                <button
                    type="submit"
                    class="pen-button pen-button-primary"
                >
                    Cari
                </button>

            </form>


            {{-- =================================================
                 KOLOM
            ================================================== --}}

            <div
                class="pen-column-dropdown"

                @click.outside="
                    columnOpen = false
                "
            >

                <button
                    type="button"
                    class="pen-button"

                    @click="
                        columnOpen =
                            !columnOpen
                    "
                >
                    ⚙ Kolom ▾
                </button>


                <div
                    x-show="columnOpen"
                    x-cloak
                    class="pen-column-menu"
                >

                    <label class="pen-column-option">

                        <input
                            type="checkbox"
                            x-model="columns.detail"
                        >

                        Detail
                    </label>


                    <label class="pen-column-option">

                        <input
                            type="checkbox"
                            x-model="columns.tempat"
                        >

                        Tempat
                    </label>


                    <label class="pen-column-option">

                        <input
                            type="checkbox"
                            x-model="columns.komoditi"
                        >

                        Komoditi
                    </label>


                    <label class="pen-column-option">

                        <input
                            type="checkbox"
                            x-model="columns.date"
                        >

                        Tanggal
                    </label>


                    <label class="pen-column-option">

                        <input
                            type="checkbox"
                            x-model="columns.action"
                        >

                        Aksi
                    </label>

                </div>

            </div>

        </div>


        {{-- =================================================
             JUMLAH DATA PER HALAMAN
        ================================================== --}}

        <div class="pen-table-toolbar">

            <form
                action="{{ route('penugasan.index') }}"
                method="GET"
                class="pen-length-form"
            >

                <input
                    type="hidden"
                    name="search"
                    value="{{ request('search') }}"
                >

                <input
                    type="hidden"
                    name="layanan_id"
                    value="{{ request('layanan_id') }}"
                >

                <input
                    type="hidden"
                    name="dari"
                    value="{{ request('dari') }}"
                >

                <input
                    type="hidden"
                    name="sampai"
                    value="{{ request('sampai') }}"
                >

                @if(request()->filled('bulan'))
                    <input
                        type="hidden"
                        name="bulan"
                        value="{{ request('bulan') }}"
                    >
                @endif

                @if(request()->filled('tahun'))
                    <input
                        type="hidden"
                        name="tahun"
                        value="{{ request('tahun') }}"
                    >
                @endif


                <span>
                    Tampilkan
                </span>


                <select
                    name="per_page"
                    class="pen-length-select"
                    aria-label="Jumlah data per halaman"
                    onchange="this.form.submit()"
                >

                    @foreach([10, 25, 50, 100] as $size)

                        <option
                            value="{{ $size }}"
                            {{
                                (int) request('per_page', 10) === $size
                                    ? 'selected'
                                    : ''
                            }}
                        >
                            {{ $size }}
                        </option>

                    @endforeach

                </select>


                <span>
                    data
                </span>

            </form>

        </div>


        {{-- =================================================
             TABLE
        ================================================== --}}

        <div class="pen-table-wrapper">

            <table class="pen-table">

                <thead>

                    <tr>

                        <th>
                            Petugas
                        </th>

                        <th>
                            Layanan
                        </th>

                        <th x-show="columns.detail">
                            Detail
                        </th>

                        <th x-show="columns.tempat">
                            Tempat
                        </th>

                        <th x-show="columns.komoditi">
                            Komoditi
                        </th>

                        <th x-show="columns.date">
                            Tanggal
                        </th>

                        <th x-show="columns.action">
                            Aksi
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @if($penugasans->count() > 0)

                        @foreach($penugasans as $item)

                            @php
                                $serviceIndex =
                                    max(
                                        0,
                                        ((int) $item->layanan_id) - 1
                                    )
                                    %
                                    count($palette);

                                $serviceColor =
                                    $palette[
                                        $serviceIndex
                                    ];

                                $editPayload =
                                    collect($editItems)
                                        ->firstWhere(
                                            'id',
                                            $item->id
                                        );

                                $duration = null;

                                if (
                                    $item->tanggal_mulai
                                    &&
                                    $item->tanggal_selesai
                                ) {
                                    $duration =
                                        (int) $item
                                            ->tanggal_mulai
                                            ->diffInDays(
                                                $item->tanggal_selesai
                                            )
                                        +
                                        1;
                                }
                            @endphp


                            <tr>


                                {{-- PETUGAS --}}

                                <td>

                                    <div class="pen-petugas-list">

                                        @if($item->petugas->isNotEmpty())

                                            @foreach($item->petugas as $petugas)

                                                @php
                                                    $parts =
                                                        preg_split(
                                                            '/\s+/',
                                                            trim(
                                                                $petugas->name
                                                            )
                                                        );

                                                    $initial =
                                                        strtoupper(
                                                            mb_substr(
                                                                $parts[0] ?? '',
                                                                0,
                                                                1
                                                            )
                                                            .
                                                            mb_substr(
                                                                $parts[1] ?? '',
                                                                0,
                                                                1
                                                            )
                                                        );

                                                    $avatarColor =
                                                        $palette[
                                                            ((int) $petugas->id)
                                                            %
                                                            count($palette)
                                                        ];
                                                @endphp


                                                <span class="pen-petugas-chip">

                                                    <span
                                                        class="pen-avatar-small"

                                                        style="
                                                            background:
                                                                {{ $avatarColor }};
                                                        "
                                                    >
                                                        {{ $initial }}
                                                    </span>


                                                    {{ $petugas->name }}

                                                </span>

                                            @endforeach

                                        @else

                                            <span style="color:#94a3b8;">
                                                -
                                            </span>

                                        @endif

                                    </div>

                                </td>


                                {{-- LAYANAN --}}

                                <td>

                                    <span
                                        class="pen-service"

                                        style="
                                            color:
                                                {{ $serviceColor }};

                                            background:
                                                {{ $serviceColor }}18;
                                        "
                                    >
                                        {{
                                            $item
                                                ->layanan
                                                ?->nama_layanan
                                            ??
                                            '-'
                                        }}
                                    </span>

                                </td>


                                {{-- DETAIL --}}

                                <td
                                    x-show="columns.detail"

                                    style="
                                        color:#64748b;
                                        min-width:280px;
                                    "
                                >
                                    {{ $item->task_detail ?: '-' }}
                                </td>


                                {{-- TEMPAT --}}

                                <td
                                    x-show="columns.tempat"

                                    style="
                                        min-width:190px;
                                    "
                                >
                                    {{ $item->tempat ?: '-' }}
                                </td>


                                {{-- KOMODITI --}}

                                <td
                                    x-show="columns.komoditi"

                                    style="
                                        min-width:160px;
                                    "
                                >
                                    {{ $item->komoditi ?: '-' }}
                                </td>


                                {{-- TANGGAL --}}

                                <td
                                    x-show="columns.date"
                                    class="pen-date"
                                >

                                    @if(
                                        $item->tanggal_mulai
                                        &&
                                        $item->tanggal_selesai
                                    )

                                        @if(
                                            $item->tanggal_mulai->format('Y-m-d')
                                            ===
                                            $item->tanggal_selesai->format('Y-m-d')
                                        )

                                            {{
                                                $item
                                                    ->tanggal_mulai
                                                    ->format('d M Y')
                                            }}

                                        @else

                                            {{
                                                $item
                                                    ->tanggal_mulai
                                                    ->format('d M Y')
                                            }}

                                            –

                                            {{
                                                $item
                                                    ->tanggal_selesai
                                                    ->format('d M Y')
                                            }}

                                        @endif


                                        @if($duration)

                                            <small>
                                                {{ $duration }} hari
                                            </small>

                                        @endif

                                    @else

                                        -

                                    @endif

                                </td>


                                {{-- AKSI --}}

                                <td
                                    x-show="columns.action"

                                    style="
                                        min-width:160px;
                                        white-space:nowrap;
                                    "
                                >

                                    <button
                                        type="button"

                                        class="
                                            pen-action-button
                                            pen-history-button
                                        "

                                        @click="
                                            openHistory(
                                                {{ (int) $item->id }}
                                            )
                                        "
                                    >
                                        <svg
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            xmlns="http://www.w3.org/2000/svg"
                                            aria-hidden="true"
                                        >
                                            <path
                                                d="M3 12a9 9 0 1 0 3-6.708"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            ></path>

                                            <path
                                                d="M3 4v6h6"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            ></path>

                                            <path
                                                d="M12 7v5l3 2"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            ></path>
                                        </svg>

                                        <span>Riwayat</span>
                                    </button>


                                    <button
                                        type="button"

                                        class="
                                            pen-action-button
                                            pen-edit-button
                                        "

                                        @click='openEdit(
                                            @json(
                                                $editPayload,
                                                JSON_HEX_APOS
                                            )
                                        )'
                                    >
                                        ✎ Edit
                                    </button>

                                </td>

                            </tr>

                        @endforeach

                    @else

                        <tr>

                            <td
                                colspan="7"

                                style="
                                    padding:32px;
                                    text-align:center;
                                    color:#64748b;
                                "
                            >
                                Tidak ada penugasan yang cocok dengan filter.
                            </td>

                        </tr>

                    @endif

                </tbody>

            </table>

        </div>


        {{-- =================================================
             FOOTER
        ================================================== --}}

        <div class="pen-footer">

            <div class="pen-result-info">

                Menampilkan

                {{
                    $penugasans->firstItem()
                    ??
                    0
                }}

                –

                {{
                    $penugasans->lastItem()
                    ??
                    0
                }}

                dari

                {{ $penugasans->total() }}

                hasil

            </div>


            @if($penugasans->lastPage() > 1)

                <div class="pen-pagination">


                    {{-- PREVIOUS --}}

                    @if($penugasans->onFirstPage())

                        <span class="pen-page-link disabled">
                            ‹
                        </span>

                    @else

                        <a
                            href="{{ $penugasans->previousPageUrl() }}"
                            class="pen-page-link"
                        >
                            ‹
                        </a>

                    @endif


                    {{-- PAGE NUMBER --}}

                    @php
                        $startPage =
                            max(
                                1,
                                $penugasans->currentPage() - 2
                            );

                        $endPage =
                            min(
                                $penugasans->lastPage(),
                                $penugasans->currentPage() + 2
                            );
                    @endphp


                    @for($page = $startPage; $page <= $endPage; $page++)

                        <a
                            href="{{ $penugasans->url($page) }}"

                            class="
                                pen-page-link

                                {{
                                    $page === $penugasans->currentPage()
                                        ? 'active'
                                        : ''
                                }}
                            "
                        >
                            {{ $page }}
                        </a>

                    @endfor


                    {{-- NEXT --}}

                    @if($penugasans->hasMorePages())

                        <a
                            href="{{ $penugasans->nextPageUrl() }}"
                            class="pen-page-link"
                        >
                            ›
                        </a>

                    @else

                        <span class="pen-page-link disabled">
                            ›
                        </span>

                    @endif

                </div>

            @endif

        </div>

    </div>


    {{-- =====================================================
         FORM OVERLAY
    ====================================================== --}}

    <div
        x-show="drawerOpen"

        x-cloak

        class="pen-overlay"

        @click="closeDrawer()"
    ></div>


    {{-- =====================================================
         FORM DRAWER
    ====================================================== --}}

    <aside
        class="pen-drawer"

        :class="{
            'is-open':
                drawerOpen
        }"
    >

        <div class="pen-drawer-header">

            <h2
                class="pen-drawer-title"

                x-text="
                    drawerMode === 'edit'
                        ? 'Edit Penugasan'
                        : 'Tambah Penugasan'
                "
            ></h2>


            <button
                type="button"

                class="pen-drawer-close"

                @click="closeDrawer()"
            >
                ✕
            </button>

        </div>


        <div
            x-show="formError"

            x-cloak

            class="pen-client-error"

            x-text="formError"
        ></div>


        <form
            x-ref="penugasanForm"

            :action="
                drawerMode === 'edit'
                    ? updateUrl
                    : createUrl
            "

            method="POST"

            @submit="preparePetugasInputs($event)"
        >

            @csrf

            <input
                type="hidden"
                name="allow_overlap"
                :value="allowOverlap ? 1 : 0"
            >


            <template x-if="drawerMode === 'edit'">

                <input
                    type="hidden"
                    name="_method"
                    value="PUT"
                >

            </template>


            <template x-if="drawerMode === 'edit'">

                <input
                    type="hidden"
                    name="editing_id"
                    :value="editId"
                >

            </template>


            {{-- =================================================
                 PETUGAS
            ================================================== --}}

            <div class="pen-field">

                <label>
                    Petugas
                </label>


                <div
                    class="pen-select-wrap"

                    @click.outside="
                        petugasOpen = false
                    "
                >

                    <div class="pen-multi-select">

                        <template
                            x-for="
                                petugas
                                in
                                selectedPetugas
                            "

                            :key="petugas.id"
                        >

                            <span class="pen-selected-chip">

                                <span
                                    x-text="
                                        petugas.name
                                    "
                                ></span>


                                <button
                                    type="button"

                                    @click.prevent.stop="
                                        removePetugas(
                                            petugas.id
                                        )
                                    "
                                >
                                    ×
                                </button>

                            </span>

                        </template>


                        <input
                            type="text"

                            x-model="
                                petugasSearch
                            "

                            @focus="
                                petugasOpen = true
                            "

                            @input="
                                petugasOpen = true
                            "

                            class="pen-multi-input"

                            placeholder="Cari petugas..."
                        >

                    </div>


                    <div
                        x-show="
                            petugasOpen
                        "

                        x-cloak

                        class="pen-option-menu"
                    >

                        <template
                            x-for="
                                item
                                in
                                filteredPetugas
                            "

                            :key="item.id"
                        >

                            <button
                                type="button"

                                class="pen-option"

                                @click.prevent.stop="
                                    selectPetugas(
                                        item
                                    )
                                "

                                x-text="
                                    item.name
                                    +
                                    (
                                        item.nip
                                            ?
                                            ' (' +
                                            item.nip +
                                            ')'
                                            :
                                            ''
                                    )
                                "
                            ></button>

                        </template>


                        <div
                            x-show="
                                filteredPetugas.length === 0
                            "

                            style="
                                padding:12px;
                                color:#94a3b8;
                                font-size:12px;
                            "
                        >
                            Petugas tidak ditemukan.
                        </div>

                    </div>

                </div>

            </div>


            <div
                data-petugas-hidden-inputs
                style="display:none;"
            ></div>


            {{-- =================================================
                 LAYANAN
            ================================================== --}}

            <div class="pen-field">

                <label>
                    Layanan
                </label>


                <div
                    class="pen-select-wrap"

                    @click.outside="
                        layananOpen = false
                    "
                >

                    <input
                        type="text"

                        class="pen-form-control"

                        x-model="
                            layananSearch
                        "

                        @focus="
                            layananOpen = true
                        "

                        @input="
                            onLayananInput()
                        "

                        placeholder="Cari layanan..."
                    >


                    <div
                        x-show="
                            layananOpen
                        "

                        x-cloak

                        class="pen-option-menu"
                    >

                        <template
                            x-for="
                                item
                                in
                                filteredLayanan
                            "

                            :key="item.id"
                        >

                            <button
                                type="button"

                                class="pen-option"

                                @click.prevent.stop="
                                    selectLayanan(
                                        item
                                    )
                                "

                                x-text="
                                    item.nama_layanan
                                "
                            ></button>

                        </template>

                    </div>

                </div>


                <input
                    type="hidden"

                    name="layanan_id"

                    :value="
                        form.layanan_id
                        ??
                        ''
                    "
                >

            </div>


            {{-- =================================================
                 TEMPAT
            ================================================== --}}

            <div class="pen-field">

                <label>
                    Tempat
                </label>

                <input
                    type="text"

                    name="tempat"

                    x-model="
                        form.tempat
                    "

                    class="pen-form-control"

                    placeholder="Nama perusahaan / lokasi"

                    required
                >

            </div>


            {{-- =================================================
                 KOMODITI
            ================================================== --}}

            <div class="pen-field">

                <label>
                    Komoditi
                </label>

                <input
                    type="text"

                    name="komoditi"

                    x-model="
                        form.komoditi
                    "

                    class="pen-form-control"

                    placeholder="Komoditi"

                    required
                >

            </div>


            {{-- =================================================
                 DETAIL
            ================================================== --}}

            <div class="pen-field">

                <label>
                    Detail Tugas
                </label>

                <textarea
                    name="task_detail"

                    x-model="
                        form.task_detail
                    "

                    class="pen-form-control"

                    placeholder="Detail tugas..."

                    required
                ></textarea>

            </div>


            {{-- =================================================
                 DATE
            ================================================== --}}

            <div class="pen-form-grid">

                <div class="pen-field">

                    <label>
                        Tanggal Mulai
                    </label>

                    <input
                        type="date"

                        name="tanggal_mulai"

                        x-model="
                            form.tanggal_mulai
                        "

                        class="pen-form-control"

                        required
                    >

                </div>


                <div class="pen-field">

                    <label>
                        Tanggal Selesai
                    </label>

                    <input
                        type="date"

                        name="tanggal_selesai"

                        x-model="
                            form.tanggal_selesai
                        "

                        class="pen-form-control"

                        required
                    >

                </div>

            </div>


            {{-- =================================================
                 BUTTON
            ================================================== --}}

            <div class="pen-drawer-actions">

                <button
                    type="button"

                    class="pen-button"

                    @click="
                        closeDrawer()
                    "
                >
                    Batal
                </button>


                <button
                    type="submit"

                    class="
                        pen-button
                        pen-button-primary
                    "

                    x-text="
                        drawerMode === 'edit'
                            ? 'Update Penugasan'
                            : 'Simpan Penugasan'
                    "
                ></button>

            </div>

        </form>


        {{-- =================================================
             DELETE ADMIN ONLY
        ================================================== --}}

        @if($isAdmin)

            <form
                x-show="
                    drawerMode === 'edit'
                "

                x-cloak

                :action="
                    destroyUrl
                "

                method="POST"

                style="
                    margin-top:10px;
                "

                onsubmit="
                    return confirm(
                        'Hapus data penugasan ini?'
                    )
                "
            >

                @csrf
                @method('DELETE')


                <button
                    type="submit"

                    class="
                        pen-button
                        pen-button-danger
                    "

                    style="
                        width:100%;
                    "
                >
                    Hapus Penugasan
                </button>

            </form>

        @endif

    </aside>


    {{-- =====================================================
         WARNING JADWAL BENTROK
    ====================================================== --}}

    <div
        x-show="scheduleWarningOpen"
        x-cloak
        class="pen-schedule-warning-backdrop"
        @click.self="cancelScheduleOverlap()"
    >
        <div
            class="pen-schedule-warning"
            role="dialog"
            aria-modal="true"
            aria-labelledby="schedule-warning-title"
        >
            <div class="pen-schedule-warning-head">
                <div class="pen-schedule-warning-icon">
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                        aria-hidden="true"
                    >
                        <path d="M12 9v4" stroke-width="2" stroke-linecap="round"></path>
                        <path d="M12 17h.01" stroke-width="2.5" stroke-linecap="round"></path>
                        <path
                            d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        ></path>
                    </svg>
                </div>

                <div>
                    <h3 id="schedule-warning-title" class="pen-schedule-warning-title">
                        Jadwal Petugas Bertabrakan
                    </h3>
                    <p class="pen-schedule-warning-subtitle">
                        Ada petugas yang sudah memiliki penugasan pada tanggal yang sama atau rentang tanggal yang beririsan.
                    </p>
                </div>
            </div>

            <div class="pen-schedule-warning-body">
                <div class="pen-schedule-warning-list">
                    <template
                        x-for="conflict in scheduleConflicts"
                        :key="`${conflict.penugasan_id}-${conflict.petugas_id}`"
                    >
                        <div class="pen-schedule-warning-item">
                            <div
                                class="pen-schedule-warning-name"
                                x-text="conflict.petugas_name"
                            ></div>

                            <div
                                class="pen-schedule-warning-service"
                                x-text="conflict.layanan"
                            ></div>

                            <div class="pen-schedule-warning-meta">
                                <span x-text="conflict.tempat"></span>
                                <span> · </span>
                                <span
                                    x-text="formatAssignmentDate(conflict.tanggal_mulai, conflict.tanggal_selesai)"
                                ></span>

                                <template x-if="conflict.komoditi && conflict.komoditi !== '-'">
                                    <span>
                                        · <span x-text="conflict.komoditi"></span>
                                    </span>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <p class="pen-schedule-warning-note">
                    Data belum disimpan. Pilih <strong>Tetap Simpan</strong> jika penugasan ganda pada periode tersebut memang benar.
                </p>
            </div>

            <div class="pen-schedule-warning-actions">
                <button
                    type="button"
                    class="pen-button"
                    @click="cancelScheduleOverlap()"
                >
                    Kembali
                </button>

                <button
                    type="button"
                    class="pen-button pen-button-primary"
                    @click="confirmScheduleOverlap()"
                >
                    Tetap Simpan
                </button>
            </div>
        </div>
    </div>


    {{-- =====================================================
         HISTORY OVERLAY
    ====================================================== --}}

    <div
        x-show="
            historyOpen
        "

        x-cloak

        class="pen-overlay"

        @click="
            closeHistory()
        "
    ></div>


    {{-- =====================================================
         HISTORY DRAWER
    ====================================================== --}}

    <aside
        class="pen-drawer"

        :class="{
            'is-open':
                historyOpen
        }"
    >


        {{-- =================================================
             HEADER
        ================================================== --}}

        <div class="history-header">

            <template x-if="historyView === 'overview'">

                <h2 class="history-title">
                    Riwayat Layanan
                </h2>

            </template>


            <template x-if="historyView === 'detail'">

                <button
                    type="button"
                    class="history-back-button"
                    @click="backToHistoryOverview()"
                >
                    ← Kembali
                </button>

            </template>


            <button
                type="button"

                class="pen-drawer-close"

                @click="
                    closeHistory()
                "
            >
                ✕
            </button>

        </div>


        {{-- =================================================
             OVERVIEW RIWAYAT
        ================================================== --}}

        <template x-if="historyView === 'overview'">

            <div>

                <div class="history-overview-summary">

                    <h3
                        class="history-overview-service"
                        x-text="historyServiceName"
                    ></h3>

                    <div
                        class="history-overview-meta"
                        x-text="historyAssignmentMeta"
                    ></div>

                </div>


                <template x-if="historyPeople.length > 0">

                    <div class="history-person-list">

                        <template
                            x-for="person in historyPeople"
                            :key="person.id"
                        >

                            <div class="history-person-card">

                                <div class="history-person-top">

                                    <div class="history-person-identity">

                                        <div
                                            class="history-person-avatar"
                                            :style="`
                                                background:
                                                ${historyAvatarColor(person.id)};
                                            `"
                                            x-text="initials(person.name)"
                                        ></div>


                                        <div
                                            class="history-person-name"
                                            x-text="person.name"
                                        ></div>

                                    </div>


                                    <span
                                        class="history-person-badge"

                                        :class="{
                                            'has-history':
                                                previousServiceCount(person) > 0
                                        }"

                                        x-text="
                                            historyStatusText(person)
                                        "
                                    ></span>

                                </div>


                                <p
                                    class="history-person-description"
                                    x-text="
                                        historyDescription(person)
                                    "
                                ></p>


                                <button
                                    type="button"

                                    class="history-view-button"

                                    @click="
                                        openHistoryDetail(
                                            person.id
                                        )
                                    "

                                    x-text="
                                        `Lihat semua ${historyTotalServices(person)} layanan`
                                    "
                                ></button>

                            </div>

                        </template>

                    </div>

                </template>


                <template x-if="historyPeople.length === 0">

                    <div class="history-overview-empty">
                        Belum ada data petugas pada penugasan ini.
                    </div>

                </template>

            </div>

        </template>


        {{-- =================================================
             DETAIL RIWAYAT PETUGAS
        ================================================== --}}

        <template x-if="historyView === 'detail'">

            <div>


                {{-- =================================================
                     DETAIL PETUGAS
                ================================================== --}}

                <template
                    x-if="
                        activeHistory
                    "
                >

                    <div>


                        {{-- =============================================
                             PROFILE
                        ============================================== --}}

                        <div class="history-profile">

                            <div
                                class="history-avatar"

                                x-text="
                                    initials(
                                        activeHistory.name
                                    )
                                "
                            ></div>


                            <h2
                                class="history-name"

                                x-text="
                                    activeHistory.name
                                "
                            ></h2>


                            <span
                                class="history-position"

                                x-text="
                                    activeHistory.position
                                "
                            ></span>

                        </div>


                        {{-- =============================================
                             NIP
                        ============================================== --}}

                        <div class="history-label">
                            NIP
                        </div>


                        <div class="history-nip">

                            <span
                                x-text="
                                    activeHistory.nip
                                "
                            ></span>


                            <button
                                type="button"

                                class="history-copy"

                                title="Salin NIP"

                                @click="
                                    copyNip(
                                        activeHistory.nip
                                    )
                                "
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <rect x="9" y="9" width="11" height="11" rx="2"></rect>
                                    <path d="M15 9V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h3"></path>
                                </svg>
                            </button>

                        </div>


                        {{-- =============================================
                             EXPERIENCE
                        ============================================== --}}

                        <div class="history-experience-title">

                            Pengalaman layanan ·

                            <span
                                x-text="
                                    activeHistory
                                        .experienced_service_count
                                "
                            ></span>

                            dari

                            <span
                                x-text="
                                    activeHistory
                                        .total_services
                                "
                            ></span>

                        </div>


                        <div class="history-progress">

                            <div
                                class="history-progress-bar"

                                :style="`
                                    width:
                                    ${experiencePercentage}%
                                `"
                            ></div>

                        </div>


                        {{-- =============================================
                             SEMUA LAYANAN
                        ============================================== --}}

                        <template
                            x-for="
                                service
                                in
                                activeHistory.services
                            "

                            :key="
                                service.id
                            "
                        >

                            <div class="history-service-row">

                                <div class="history-service-left">

                                    <span class="history-dot"></span>


                                    <span
                                        x-text="
                                            service.name
                                        "
                                    ></span>

                                </div>


                                <span
                                    class="history-status"

                                    :class="{
                                        'done':
                                            service.count > 0
                                    }"

                                    x-text="
                                        service.count > 0
                                            ?
                                            `Sudah ${service.count}x`
                                            :
                                            'Belum'
                                    "
                                ></span>

                            </div>

                        </template>


                        {{-- =============================================
                             RIWAYAT BERDASARKAN PT
                        ============================================== --}}

                        <div class="history-company-title">

                            Riwayat berdasarkan PT

                            (<span
                                x-text="
                                    companyHistoryGroups.length
                                "
                            ></span>)

                        </div>


                        <template
                            x-if="
                                companyHistoryGroups.length === 0
                            "
                        >

                            <div class="history-company-empty">
                                Belum ada riwayat penugasan.
                            </div>

                        </template>


                        <template
                            x-for="
                                company
                                in
                                companyHistoryGroups
                            "

                            :key="
                                company.name
                            "
                        >

                            <div class="history-company-card">


                                <button
                                    type="button"

                                    class="history-company-header"

                                    @click="
                                        toggleCompany(
                                            company.name
                                        )
                                    "
                                >

                                    <div class="history-company-name">

                                        <span
                                            class="history-company-arrow"

                                            :class="{
                                                'open':
                                                    isCompanyOpen(
                                                        company.name
                                                    )
                                            }"
                                        >
                                            ▶
                                        </span>


                                        <span class="history-company-icon">

                                            <svg
                                                viewBox="0 0 24 24"

                                                fill="none"

                                                stroke="currentColor"

                                                stroke-width="2"

                                                stroke-linecap="round"

                                                stroke-linejoin="round"
                                            >
                                                <path d="M3 21h18"></path>

                                                <path d="M6 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16"></path>

                                                <path d="M9 7h1"></path>

                                                <path d="M14 7h1"></path>

                                                <path d="M9 11h1"></path>

                                                <path d="M14 11h1"></path>

                                                <path d="M9 15h1"></path>

                                                <path d="M14 15h1"></path>
                                            </svg>

                                        </span>


                                        <span
                                            x-text="
                                                company.name
                                            "
                                        ></span>

                                    </div>


                                    <span
                                        class="history-company-count"

                                        x-text="
                                            `${company.total_assignments} penugasan`
                                        "
                                    ></span>

                                </button>


                                <div
                                    x-show="
                                        isCompanyOpen(
                                            company.name
                                        )
                                    "

                                    x-cloak

                                    class="history-company-body"
                                >


                                    <template
                                        x-for="
                                            service
                                            in
                                            company.services
                                        "

                                        :key="
                                            `${company.name}-${service.name}`
                                        "
                                    >

                                        <div class="history-company-service">


                                            <div class="history-company-service-head">

                                                <div class="history-company-service-name">

                                                    <span class="history-dot"></span>


                                                    <span
                                                        x-text="
                                                            service.name
                                                        "
                                                    ></span>

                                                </div>


                                                <span
                                                    class="history-company-service-count"

                                                    x-text="
                                                        `${service.count}x`
                                                    "
                                                ></span>

                                            </div>


                                            <template
                                                x-for="
                                                    activity
                                                    in
                                                    service.items
                                                "

                                                :key="
                                                    activity.id
                                                "
                                            >

                                                <div class="history-company-activity">


                                                    <div
                                                        class="history-company-task"

                                                        x-text="
                                                            activity.task_detail
                                                            &&
                                                            activity.task_detail !== '-'
                                                                ?
                                                                activity.task_detail
                                                                :
                                                                '-'
                                                        "
                                                    ></div>


                                                    <div class="history-company-meta">

                                                        <span
                                                            x-text="
                                                                activity.tanggal_mulai
                                                            "
                                                        ></span>

                                                        –

                                                        <span
                                                            x-text="
                                                                activity.tanggal_selesai
                                                            "
                                                        ></span>


                                                        <template
                                                            x-if="
                                                                activity.komoditi
                                                                &&
                                                                activity.komoditi !== '-'
                                                            "
                                                        >

                                                            <span>

                                                                ·

                                                                <span
                                                                    x-text="
                                                                        activity.komoditi
                                                                    "
                                                                ></span>

                                                            </span>

                                                        </template>

                                                    </div>

                                                </div>

                                            </template>

                                        </div>

                                    </template>

                                </div>

                            </div>

                        </template>

                    </div>

                </template>

            </div>

        </template>

    </aside>

</div>


<script>
    function penugasanPage(
        petugasOptions,
        layananOptions,
        editItems,
        initialForm,
        errorMode,
        editingId,
        historyByPetugas,
        rowPetugasIds,
        createUrl,
        scheduleConflicts
    ) {
        return {

            /*
            |--------------------------------------------------------------------------
            | DATA
            |--------------------------------------------------------------------------
            */

            petugasOptions:
                petugasOptions
                ?? [],

            layananOptions:
                layananOptions
                ?? [],

            editItems:
                editItems
                ?? [],

            historyByPetugas:
                historyByPetugas
                ?? {},

            rowPetugasIds:
                rowPetugasIds
                ?? {},

            createUrl:
                createUrl,


            /*
            |--------------------------------------------------------------------------
            | COLUMN
            |--------------------------------------------------------------------------
            */

            columnOpen:
                false,

            columns: {
                detail: true,
                tempat: true,
                komoditi: true,
                date: true,
                action: true,
            },


            /*
            |--------------------------------------------------------------------------
            | DRAWER FORM
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

            formError:
                '',

            scheduleWarningOpen:
                false,

            scheduleConflicts:
                scheduleConflicts
                ?? [],

            allowOverlap:
                false,


            /*
            |--------------------------------------------------------------------------
            | FORM
            |--------------------------------------------------------------------------
            */

            form: {
                layanan_id: null,
                tempat: '',
                komoditi: '',
                task_detail: '',
                tanggal_mulai: '',
                tanggal_selesai: '',
            },


            /*
            |--------------------------------------------------------------------------
            | PETUGAS
            |--------------------------------------------------------------------------
            */

            selectedPetugas:
                [],

            petugasSearch:
                '',

            petugasOpen:
                false,


            /*
            |--------------------------------------------------------------------------
            | LAYANAN
            |--------------------------------------------------------------------------
            */

            layananSearch:
                '',

            layananOpen:
                false,


            /*
            |--------------------------------------------------------------------------
            | HISTORY
            |--------------------------------------------------------------------------
            */

            historyOpen:
                false,

            historyView:
                'overview',

            historyAssignment:
                null,

            historyPeople:
                [],

            activeHistoryId:
                null,


            /*
            |--------------------------------------------------------------------------
            | PT DROPDOWN
            |--------------------------------------------------------------------------
            */

            openCompanies:
                {},


            /*
            |--------------------------------------------------------------------------
            | INIT
            |--------------------------------------------------------------------------
            */

            init()
            {
                if (
                    errorMode === 'edit'
                    &&
                    editingId
                ) {

                    const item =
                        this.editItems.find(
                            item =>
                                Number(item.id)
                                ===
                                Number(editingId)
                        );


                    if (item) {

                        this.openEdit(
                            item
                        );

                        this.applyOldForm(
                            initialForm
                        );
                    }


                    this.scheduleWarningOpen =
                        this.scheduleConflicts.length > 0;

                    return;
                }


                if (
                    errorMode === 'create'
                ) {

                    this.openCreate(
                        initialForm
                    );
                }


                this.scheduleWarningOpen =
                    this.scheduleConflicts.length > 0;
            },


            /*
            |--------------------------------------------------------------------------
            | FILTER PETUGAS
            |--------------------------------------------------------------------------
            */

            get filteredPetugas()
            {
                const keyword =
                    this.petugasSearch
                        .trim()
                        .toLowerCase();


                return this.petugasOptions
                    .filter(
                        item => {

                            const alreadySelected =
                                this.selectedPetugas
                                    .some(
                                        selected =>
                                            Number(selected.id)
                                            ===
                                            Number(item.id)
                                    );


                            if (
                                alreadySelected
                            ) {
                                return false;
                            }


                            if (
                                keyword === ''
                            ) {
                                return true;
                            }


                            return (
                                `${item.name ?? ''} ${item.nip ?? ''}`
                                    .toLowerCase()
                                    .includes(
                                        keyword
                                    )
                            );
                        }
                    )
                    .slice(
                        0,
                        30
                    );
            },


            /*
            |--------------------------------------------------------------------------
            | FILTER LAYANAN
            |--------------------------------------------------------------------------
            */

            get filteredLayanan()
            {
                const keyword =
                    this.layananSearch
                        .trim()
                        .toLowerCase();


                return this.layananOptions
                    .filter(
                        item => {

                            if (
                                keyword === ''
                            ) {
                                return true;
                            }


                            return item
                                .nama_layanan
                                .toLowerCase()
                                .includes(
                                    keyword
                                );
                        }
                    );
            },


            /*
            |--------------------------------------------------------------------------
            | ACTIVE HISTORY
            |--------------------------------------------------------------------------
            */

            get activeHistory()
            {
                if (
                    this.activeHistoryId
                    ===
                    null
                ) {
                    return null;
                }


                return (
                    this.historyByPetugas[
                        String(
                            this.activeHistoryId
                        )
                    ]
                    ??
                    null
                );
            },


            /*
            |--------------------------------------------------------------------------
            | DATA OVERVIEW RIWAYAT
            |--------------------------------------------------------------------------
            */

            get historyServiceName()
            {
                return (
                    this.historyAssignment
                        ?.layanan_name
                    ??
                    '-'
                );
            },


            get historyAssignmentMeta()
            {
                if (
                    !this.historyAssignment
                ) {
                    return '-';
                }


                const place =
                    this.historyAssignment
                        .tempat
                    ??
                    '-';


                const date =
                    this.formatAssignmentDate(
                        this.historyAssignment
                            .tanggal_mulai,
                        this.historyAssignment
                            .tanggal_selesai
                    );


                return `${place} · ${date}`;
            },


            formatDateIndonesia(
                value
            )
            {
                if (!value) {
                    return '-';
                }


                const date =
                    new Date(
                        `${value}T00:00:00`
                    );


                if (
                    Number.isNaN(
                        date.getTime()
                    )
                ) {
                    return value;
                }


                return new Intl
                    .DateTimeFormat(
                        'id-ID',
                        {
                            day: '2-digit',
                            month: 'long',
                            year: 'numeric',
                        }
                    )
                    .format(
                        date
                    );
            },


            formatAssignmentDate(
                start,
                end
            )
            {
                if (
                    !start
                    &&
                    !end
                ) {
                    return '-';
                }


                if (
                    start
                    &&
                    end
                    &&
                    start !== end
                ) {
                    return (
                        `${this.formatDateIndonesia(start)}`
                        +
                        ' – '
                        +
                        `${this.formatDateIndonesia(end)}`
                    );
                }


                return this.formatDateIndonesia(
                    start
                    ??
                    end
                );
            },


            historyAvatarColor(
                id
            )
            {
                const colors = [
                    '#10b981',
                    '#0ea5e9',
                    '#6366f1',
                    '#8b5cf6',
                    '#f59e0b',
                    '#ec4899',
                    '#14b8a6',
                ];


                const index =
                    Math.abs(
                        Number(id)
                        ||
                        0
                    )
                    %
                    colors.length;


                return colors[index];
            },


            previousServiceCount(
                person
            )
            {
                if (
                    !person
                    ||
                    !Array.isArray(
                        person.history
                    )
                    ||
                    !this.historyAssignment
                ) {
                    return 0;
                }


                const currentId =
                    Number(
                        this.historyAssignment.id
                    );


                const currentService =
                    String(
                        this.historyAssignment
                            .layanan_name
                        ??
                        ''
                    )
                        .trim()
                        .toLowerCase();


                return person.history
                    .filter(
                        item => {

                            const sameService =
                                String(
                                    item.layanan
                                    ??
                                    ''
                                )
                                    .trim()
                                    .toLowerCase()
                                ===
                                currentService;


                            const differentAssignment =
                                Number(item.id)
                                !==
                                currentId;


                            return (
                                sameService
                                &&
                                differentAssignment
                            );
                        }
                    )
                    .length;
            },


            historyStatusText(
                person
            )
            {
                const count =
                    this.previousServiceCount(
                        person
                    );


                return count > 0
                    ?
                    `Pernah ${count}x`
                    :
                    'Pertama kali';
            },


            historyDescription(
                person
            )
            {
                const count =
                    this.previousServiceCount(
                        person
                    );


                const service =
                    this.historyServiceName;


                if (count === 0) {
                    return `Belum pernah melakukan ${service} sebelumnya.`;
                }


                return `Sudah ${count} kali melakukan ${service} sebelumnya.`;
            },


            historyTotalServices(
                person
            )
            {
                return (
                    Number(
                        person
                            ?.total_services
                    )
                    ||
                    this.layananOptions.length
                    ||
                    0
                );
            },


            /*
            |--------------------------------------------------------------------------
            | EXPERIENCE %
            |--------------------------------------------------------------------------
            */

            get experiencePercentage()
            {
                if (
                    !this.activeHistory
                    ||
                    !this.activeHistory.total_services
                ) {
                    return 0;
                }


                return Math.min(
                    100,

                    Math.round(
                        (
                            this.activeHistory
                                .experienced_service_count
                            /
                            this.activeHistory
                                .total_services
                        )
                        *
                        100
                    )
                );
            },


            /*
            |--------------------------------------------------------------------------
            | GROUP RIWAYAT BERDASARKAN PT
            |--------------------------------------------------------------------------
            */

            get companyHistoryGroups()
            {
                if (
                    !this.activeHistory
                    ||
                    !Array.isArray(
                        this.activeHistory.history
                    )
                ) {
                    return [];
                }


                const companies =
                    new Map();


                this.activeHistory
                    .history
                    .forEach(
                        item => {

                            /*
                            |--------------------------------------------------------------------------
                            | NAMA PT
                            |--------------------------------------------------------------------------
                            */

                            const companyName =
                                String(
                                    item.tempat
                                    ??
                                    'Tanpa Tempat'
                                )
                                    .trim()
                                ||
                                'Tanpa Tempat';


                            /*
                            |--------------------------------------------------------------------------
                            | BUAT PT
                            |--------------------------------------------------------------------------
                            */

                            if (
                                !companies.has(
                                    companyName
                                )
                            ) {

                                companies.set(
                                    companyName,
                                    {
                                        name:
                                            companyName,

                                        total_assignments:
                                            0,

                                        services:
                                            new Map(),
                                    }
                                );
                            }


                            const company =
                                companies.get(
                                    companyName
                                );


                            company
                                .total_assignments++;


                            /*
                            |--------------------------------------------------------------------------
                            | LAYANAN
                            |--------------------------------------------------------------------------
                            */

                            const serviceName =
                                String(
                                    item.layanan
                                    ??
                                    '-'
                                );


                            /*
                            |--------------------------------------------------------------------------
                            | BUAT SERVICE
                            |--------------------------------------------------------------------------
                            */

                            if (
                                !company
                                    .services
                                    .has(
                                        serviceName
                                    )
                            ) {

                                company
                                    .services
                                    .set(
                                        serviceName,
                                        {
                                            name:
                                                serviceName,

                                            /*
                                            |--------------------------------------------------------------------------
                                            | Warna riwayat disamakan
                                            |--------------------------------------------------------------------------
                                            */

                                            color:
                                                '#059669',

                                            count:
                                                0,

                                            items:
                                                [],
                                        }
                                    );
                            }


                            const service =
                                company
                                    .services
                                    .get(
                                        serviceName
                                    );


                            service.count++;


                            service.items.push(
                                item
                            );
                        }
                    );


                return Array
                    .from(
                        companies.values()
                    )
                    .map(
                        company => ({
                            name:
                                company.name,

                            total_assignments:
                                company.total_assignments,

                            services:
                                Array.from(
                                    company
                                        .services
                                        .values()
                                ),
                        })
                    );
            },


            /*
            |--------------------------------------------------------------------------
            | TOGGLE PT
            |--------------------------------------------------------------------------
            */

            toggleCompany(
                companyName
            )
            {
                this.openCompanies[
                    companyName
                ] =
                    !this.openCompanies[
                        companyName
                    ];
            },


            /*
            |--------------------------------------------------------------------------
            | CEK PT TERBUKA
            |--------------------------------------------------------------------------
            */

            isCompanyOpen(
                companyName
            )
            {
                return Boolean(
                    this.openCompanies[
                        companyName
                    ]
                );
            },


            /*
            |--------------------------------------------------------------------------
            | RESET FORM
            |--------------------------------------------------------------------------
            */

            resetForm()
            {
                this.selectedPetugas =
                    [];

                this.petugasSearch =
                    '';

                this.petugasOpen =
                    false;

                this.layananSearch =
                    '';

                this.layananOpen =
                    false;

                this.formError =
                    '';

                this.allowOverlap =
                    false;

                this.form = {
                    layanan_id: null,
                    tempat: '',
                    komoditi: '',
                    task_detail: '',
                    tanggal_mulai: '',
                    tanggal_selesai: '',
                };
            },


            /*
            |--------------------------------------------------------------------------
            | APPLY OLD FORM
            |--------------------------------------------------------------------------
            */

            applyOldForm(
                data
            )
            {
                this.selectedPetugas =
                    (
                        data.petugas_ids
                        ??
                        []
                    )
                        .map(
                            id =>
                                this.petugasOptions
                                    .find(
                                        item =>
                                            Number(item.id)
                                            ===
                                            Number(id)
                                    )
                        )
                        .filter(
                            Boolean
                        )
                        .map(
                            item => ({
                                id:
                                    item.id,

                                name:
                                    item.name,

                                nip:
                                    item.nip,
                            })
                        );


                this.form.tempat =
                    data.tempat
                    ??
                    '';

                this.form.komoditi =
                    data.komoditi
                    ??
                    '';

                this.form.task_detail =
                    data.task_detail
                    ??
                    '';

                this.form.tanggal_mulai =
                    data.tanggal_mulai
                    ??
                    '';

                this.form.tanggal_selesai =
                    data.tanggal_selesai
                    ??
                    '';


                const layananId =
                    data.layanan_id
                        ?
                        Number(
                            data.layanan_id
                        )
                        :
                        null;


                this.form.layanan_id =
                    layananId;


                const layanan =
                    this.layananOptions
                        .find(
                            item =>
                                Number(item.id)
                                ===
                                Number(layananId)
                        );


                this.layananSearch =
                    layanan
                        ?.nama_layanan
                    ??
                    '';
            },


            /*
            |--------------------------------------------------------------------------
            | CREATE
            |--------------------------------------------------------------------------
            */

            openCreate(
                data = null
            )
            {
                this.closeHistory();


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

                    this.applyOldForm(
                        data
                    );
                }


                this.drawerOpen =
                    true;
            },


            /*
            |--------------------------------------------------------------------------
            | EDIT
            |--------------------------------------------------------------------------
            */

            openEdit(
                item
            )
            {
                if (!item) {
                    return;
                }


                this.closeHistory();


                this.drawerMode =
                    'edit';

                this.editId =
                    item.id;

                this.updateUrl =
                    item.update_url;

                this.destroyUrl =
                    item.destroy_url;

                this.formError =
                    '';


                this.selectedPetugas =
                    (
                        item.petugas
                        ??
                        []
                    )
                        .map(
                            petugas => ({
                                id:
                                    petugas.id,

                                name:
                                    petugas.name,

                                nip:
                                    petugas.nip,
                            })
                        );


                this.form = {
                    layanan_id:
                        item.layanan_id
                        ??
                        null,

                    tempat:
                        item.tempat
                        ??
                        '',

                    komoditi:
                        item.komoditi
                        ??
                        '',

                    task_detail:
                        item.task_detail
                        ??
                        '',

                    tanggal_mulai:
                        item.tanggal_mulai
                        ??
                        '',

                    tanggal_selesai:
                        item.tanggal_selesai
                        ??
                        '',
                };


                this.layananSearch =
                    item.layanan_name
                    ??
                    '';


                this.petugasSearch =
                    '';

                this.petugasOpen =
                    false;

                this.layananOpen =
                    false;

                this.drawerOpen =
                    true;
            },


            /*
            |--------------------------------------------------------------------------
            | CLOSE DRAWER
            |--------------------------------------------------------------------------
            */

            closeDrawer()
            {
                this.drawerOpen =
                    false;

                this.petugasOpen =
                    false;

                this.layananOpen =
                    false;

                this.formError =
                    '';
            },


            /*
            |--------------------------------------------------------------------------
            | SELECT PETUGAS
            |--------------------------------------------------------------------------
            */

            selectPetugas(
                item
            )
            {
                const exists =
                    this.selectedPetugas
                        .some(
                            selected =>
                                Number(selected.id)
                                ===
                                Number(item.id)
                        );


                if (!exists) {

                    this.selectedPetugas
                        .push({
                            id:
                                item.id,

                            name:
                                item.name,

                            nip:
                                item.nip,
                        });
                }


                this.petugasSearch =
                    '';

                this.petugasOpen =
                    false;

                this.formError =
                    '';
            },


            /*
            |--------------------------------------------------------------------------
            | REMOVE PETUGAS
            |--------------------------------------------------------------------------
            */

            removePetugas(
                id
            )
            {
                this.selectedPetugas =
                    this.selectedPetugas
                        .filter(
                            item =>
                                Number(item.id)
                                !==
                                Number(id)
                        );
            },


            /*
            |--------------------------------------------------------------------------
            | INPUT LAYANAN
            |--------------------------------------------------------------------------
            */

            onLayananInput()
            {
                const selected =
                    this.layananOptions
                        .find(
                            item =>
                                Number(item.id)
                                ===
                                Number(
                                    this.form
                                        .layanan_id
                                )
                        );


                if (
                    !selected
                    ||
                    selected.nama_layanan
                    !==
                    this.layananSearch
                ) {

                    this.form.layanan_id =
                        null;
                }


                this.layananOpen =
                    true;
            },


            /*
            |--------------------------------------------------------------------------
            | SELECT LAYANAN
            |--------------------------------------------------------------------------
            */

            selectLayanan(
                item
            )
            {
                this.form.layanan_id =
                    item.id;

                this.layananSearch =
                    item.nama_layanan;

                this.layananOpen =
                    false;

                this.formError =
                    '';
            },


            /*
            |--------------------------------------------------------------------------
            | SUBMIT PETUGAS
            |--------------------------------------------------------------------------
            */

            preparePetugasInputs(
                event
            )
            {
                if (
                    this.selectedPetugas
                        .length
                    ===
                    0
                ) {

                    event.preventDefault();

                    this.formError =
                        'Pilih minimal satu petugas.';

                    return;
                }


                if (
                    !this.form
                        .layanan_id
                ) {

                    event.preventDefault();

                    this.formError =
                        'Pilih layanan terlebih dahulu.';

                    return;
                }


                if (
                    this.form
                        .tanggal_mulai
                    &&
                    this.form
                        .tanggal_selesai
                    &&
                    this.form
                        .tanggal_selesai
                    <
                    this.form
                        .tanggal_mulai
                ) {

                    event.preventDefault();

                    this.formError =
                        'Tanggal selesai tidak boleh sebelum tanggal mulai.';

                    return;
                }


                const container =
                    event.currentTarget
                        .querySelector(
                            '[data-petugas-hidden-inputs]'
                        );


                if (!container) {
                    return;
                }


                container.replaceChildren();


                this.selectedPetugas
                    .forEach(
                        petugas => {

                            const input =
                                document
                                    .createElement(
                                        'input'
                                    );


                            input.type =
                                'hidden';

                            input.name =
                                'petugas_ids[]';

                            input.value =
                                petugas.id;


                            container
                                .appendChild(
                                    input
                                );
                        }
                    );


                this.formError =
                    '';
            },


            /*
            |--------------------------------------------------------------------------
            | BATAL WARNING BENTROK
            |--------------------------------------------------------------------------
            */

            cancelScheduleOverlap()
            {
                this.scheduleWarningOpen =
                    false;

                this.allowOverlap =
                    false;
            },


            /*
            |--------------------------------------------------------------------------
            | TETAP SIMPAN MESKI BENTROK
            |--------------------------------------------------------------------------
            */

            confirmScheduleOverlap()
            {
                this.allowOverlap =
                    true;

                this.scheduleWarningOpen =
                    false;

                this.$nextTick(
                    () => {
                        if (
                            this.$refs
                                .penugasanForm
                        ) {
                            this.$refs
                                .penugasanForm
                                .requestSubmit();
                        }
                    }
                );
            },


            /*
            |--------------------------------------------------------------------------
            | OPEN HISTORY
            |--------------------------------------------------------------------------
            */

            openHistory(
                penugasanId
            )
            {
                this.closeDrawer();


                const assignment =
                    this.editItems
                        .find(
                            item =>
                                Number(item.id)
                                ===
                                Number(
                                    penugasanId
                                )
                        )
                    ??
                    null;


                this.historyAssignment =
                    assignment;


                const ids =
                    this.rowPetugasIds[
                        String(
                            penugasanId
                        )
                    ]
                    ??
                    [];


                this.historyPeople =
                    ids
                        .map(
                            id =>
                                this.historyByPetugas[
                                    String(id)
                                ]
                                ??
                                null
                        )
                        .filter(
                            Boolean
                        );


                this.historyView =
                    'overview';


                this.activeHistoryId =
                    null;


                this.openCompanies =
                    {};


                this.historyOpen =
                    true;
            },


            /*
            |--------------------------------------------------------------------------
            | BUKA DETAIL PETUGAS
            |--------------------------------------------------------------------------
            */

            openHistoryDetail(
                id
            )
            {
                this.activeHistoryId =
                    id;


                this.historyView =
                    'detail';


                this.openCompanies =
                    {};
            },


            /*
            |--------------------------------------------------------------------------
            | KEMBALI KE OVERVIEW
            |--------------------------------------------------------------------------
            */

            backToHistoryOverview()
            {
                this.historyView =
                    'overview';


                this.activeHistoryId =
                    null;


                this.openCompanies =
                    {};
            },


            /*
            |--------------------------------------------------------------------------
            | CLOSE HISTORY
            |--------------------------------------------------------------------------
            */

            closeHistory()
            {
                this.historyOpen =
                    false;

                this.historyView =
                    'overview';

                this.historyAssignment =
                    null;

                this.historyPeople =
                    [];

                this.activeHistoryId =
                    null;

                this.openCompanies =
                    {};
            },


            /*
            |--------------------------------------------------------------------------
            | INITIALS
            |--------------------------------------------------------------------------
            */

            initials(
                name
            )
            {
                const parts =
                    String(
                        name
                        ??
                        ''
                    )
                        .trim()
                        .split(
                            /\s+/
                        )
                        .filter(
                            Boolean
                        );


                if (
                    parts.length === 0
                ) {
                    return '?';
                }


                return (
                    (
                        parts[0][0]
                        ??
                        ''
                    )
                    +
                    (
                        parts[1]?.[0]
                        ??
                        ''
                    )
                )
                    .toUpperCase();
            },


            /*
            |--------------------------------------------------------------------------
            | COPY NIP
            |--------------------------------------------------------------------------
            */

            async copyNip(
                nip
            )
            {
                if (
                    !nip
                    ||
                    nip === '-'
                ) {
                    return;
                }


                try {

                    await navigator
                        .clipboard
                        .writeText(
                            nip
                        );

                } catch (error) {

                    console.error(
                        'Gagal menyalin NIP:',
                        error
                    );
                }
            },


            /*
            |--------------------------------------------------------------------------
            | ESC
            |--------------------------------------------------------------------------
            */

            handleEscape()
            {
                if (
                    this.scheduleWarningOpen
                ) {
                    this.cancelScheduleOverlap();

                    return;
                }


                if (
                    this.historyOpen
                ) {

                    this.closeHistory();

                    return;
                }


                if (
                    this.drawerOpen
                ) {

                    this.closeDrawer();
                }
            },
        };
    }
</script>

@endsection
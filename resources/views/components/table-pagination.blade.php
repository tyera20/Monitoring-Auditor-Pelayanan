@props([
    'paginator',
])

@php
    /*
    |--------------------------------------------------------------------------
    | PER PAGE SEBENARNYA DARI BACKEND
    |--------------------------------------------------------------------------
    */

    $currentPerPage =
        (int) $paginator->perPage();


    /*
    |--------------------------------------------------------------------------
    | PAGINATION
    |--------------------------------------------------------------------------
    */

    $currentPage =
        $paginator->currentPage();

    $lastPage =
        $paginator->lastPage();


    /*
    |--------------------------------------------------------------------------
    | MAKSIMAL 3 NOMOR
    |--------------------------------------------------------------------------
    */

    if ($lastPage <= 3) {

        $startPage = 1;

        $endPage = $lastPage;

    } elseif ($currentPage <= 2) {

        $startPage = 1;

        $endPage = 3;

    } elseif ($currentPage >= $lastPage - 1) {

        $startPage =
            $lastPage - 2;

        $endPage =
            $lastPage;

    } else {

        $startPage =
            $currentPage - 1;

        $endPage =
            $currentPage + 1;

    }


    /*
    |--------------------------------------------------------------------------
    | QUERY PARAMETER YANG HARUS DIPERTAHANKAN
    |--------------------------------------------------------------------------
    */

    $queryParameters =
        request()->except([
            'page',
            'per_page',
        ]);
@endphp


<style>
    .shared-pagination {
        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 14px;

        flex-wrap: wrap;

        padding: 12px 14px;

        border-top: 1px solid #e2e8f0;

        background: #ffffff;
    }


    .shared-pagination-left {
        display: flex;

        align-items: center;

        gap: 18px;

        flex-wrap: wrap;
    }


    .shared-pagination-info {
        color: #64748b;

        font-size: 12px;
    }


    .shared-pagination-per-page {
        display: flex;

        align-items: center;

        gap: 7px;

        color: #64748b;

        font-size: 12px;
    }


    .shared-pagination-select {
        min-width: 74px;

        height: 36px;

        padding: 0 10px;

        border: 1px solid #cbd5e1;

        border-radius: 9px;

        outline: none;

        background: #ffffff;

        color: #0f172a;

        font: inherit;

        cursor: pointer;
    }


    .shared-pagination-select:focus {
        border-color: #059669;

        box-shadow:
            0 0 0 1px
            #059669;
    }


    .shared-pagination-pages {
        display: flex;

        align-items: center;

        gap: 4px;
    }


    .shared-pagination-link {
        display: inline-grid;

        min-width: 34px;

        height: 34px;

        place-items: center;

        padding: 0 8px;

        border: 1px solid #e2e8f0;

        border-radius: 8px;

        background: #ffffff;

        color: #0f172a;

        font-size: 12px;

        font-weight: 600;

        text-decoration: none;
    }


    .shared-pagination-link:hover {
        background: #f1f5f9;
    }


    .shared-pagination-link.active {
        border-color: #059669;

        background: #059669;

        color: #ffffff;
    }


    .shared-pagination-link.disabled {
        color: #cbd5e1;

        background: #f8fafc;

        pointer-events: none;
    }


    @media (max-width: 650px) {

        .shared-pagination {
            align-items: flex-start;

            flex-direction: column;
        }

    }
</style>


<div class="shared-pagination">


    {{-- =====================================================
         KIRI
    ====================================================== --}}

    <div class="shared-pagination-left">


        {{-- INFO --}}

        <div class="shared-pagination-info">

            Menampilkan

            {{
                $paginator->firstItem()
                ?? 0
            }}

            –

            {{
                $paginator->lastItem()
                ?? 0
            }}

            dari

            {{ $paginator->total() }}

            data

        </div>


        {{-- =================================================
             PER PAGE FORM
        ================================================== --}}

        <form
            action="{{ url()->current() }}"
            method="GET"
            class="shared-pagination-per-page"
        >


            {{-- Pertahankan filter --}}

            @foreach ($queryParameters as $key => $value)

                @if (! is_array($value))

                    <input
                        type="hidden"
                        name="{{ $key }}"
                        value="{{ $value }}"
                    >

                @endif

            @endforeach


            <span>
                Tampilkan
            </span>


            <select
                name="per_page"
                class="shared-pagination-select"
                onchange="
                    this.form.submit();
                "
            >

                <option
                    value="5"
                    @selected(
                        $currentPerPage === 5
                    )
                >
                    5
                </option>


                <option
                    value="10"
                    @selected(
                        $currentPerPage === 10
                    )
                >
                    10
                </option>


                <option
                    value="20"
                    @selected(
                        $currentPerPage === 20
                    )
                >
                    20
                </option>

            </select>


            <span>
                data
            </span>

        </form>

    </div>


    {{-- =====================================================
         PAGE
    ====================================================== --}}

    @if ($lastPage > 1)

        <div class="shared-pagination-pages">


            {{-- PREVIOUS --}}

            @if ($paginator->onFirstPage())

                <span
                    class="
                        shared-pagination-link
                        disabled
                    "
                    title="Sudah di halaman pertama"
                >
                    ‹
                </span>

            @else

                <a
                    href="{{
                        $paginator
                            ->previousPageUrl()
                    }}"
                    class="shared-pagination-link"
                    title="Halaman sebelumnya"
                >
                    ‹
                </a>

            @endif


            {{-- NUMBER --}}

            @for (
                $page = $startPage;
                $page <= $endPage;
                $page++
            )

                <a
                    href="{{
                        $paginator
                            ->url(
                                $page
                            )
                    }}"
                    class="
                        shared-pagination-link

                        {{
                            $page === $currentPage
                                ? 'active'
                                : ''
                        }}
                    "
                >
                    {{ $page }}
                </a>

            @endfor


            {{-- NEXT --}}

            @if ($paginator->hasMorePages())

                <a
                    href="{{
                        $paginator
                            ->nextPageUrl()
                    }}"
                    class="shared-pagination-link"
                    title="Halaman berikutnya"
                >
                    ›
                </a>

            @else

                <span
                    class="
                        shared-pagination-link
                        disabled
                    "
                    title="Sudah di halaman terakhir"
                >
                    ›
                </span>

            @endif

        </div>

    @endif

</div>
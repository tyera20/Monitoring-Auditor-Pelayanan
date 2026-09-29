<?php

namespace App\Http\Controllers;

use App\Models\Layanan;
use App\Models\Penugasan;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        /*
        |--------------------------------------------------------------------------
        | Validasi Filter Dashboard
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'from' => [
                'nullable',
                'date',
                'required_with:to',
            ],
            'to' => [
                'nullable',
                'date',
                'required_with:from',
                'after_or_equal:from',
            ],
            'search' => [
                'nullable',
                'string',
                'max:255',
            ],
            'reset' => [
                'nullable',
                'boolean',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Reset Filter Tanggal
        |--------------------------------------------------------------------------
        |
        | Reset menghapus tanggal yang tersimpan di session lalu kembali
        | menggunakan periode default bulan berjalan.
        |--------------------------------------------------------------------------
        */

        if ($request->boolean('reset')) {
            $request->session()->forget([
                'dashboard_from',
                'dashboard_to',
            ]);

            return redirect()->route('dashboard');
        }

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        |
        | Search tidak disimpan ke session. Yang dipertahankan hanya tanggal.
        |--------------------------------------------------------------------------
        */

        $search = trim(
            (string) $request->string('search')
        );

        /*
        |--------------------------------------------------------------------------
        | Periode Default
        |--------------------------------------------------------------------------
        */

        $defaultFrom =
            Carbon::now()
                ->startOfMonth()
                ->toDateString();

        $defaultTo =
            Carbon::now()
                ->endOfMonth()
                ->toDateString();

        /*
        |--------------------------------------------------------------------------
        | Simpan Periode Baru ke Session
        |--------------------------------------------------------------------------
        |
        | Saat user mengisi FROM dan TO lalu menekan Cari, tanggal tersebut
        | disimpan. Jadi ketika pindah menu lalu kembali ke Dashboard,
        | periode yang sama tetap digunakan.
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled('from')
            &&
            $request->filled('to')
        ) {
            $request->session()->put([
                'dashboard_from' =>
                    $request
                        ->date('from')
                        ->toDateString(),

                'dashboard_to' =>
                    $request
                        ->date('to')
                        ->toDateString(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Ambil Periode
        |--------------------------------------------------------------------------
        |
        | Prioritas:
        | 1. Tanggal yang baru dikirim dari form.
        | 2. Tanggal yang tersimpan di session.
        | 3. Default awal/akhir bulan berjalan.
        |--------------------------------------------------------------------------
        */

        $from =
            $request->filled('from')
                ? $request
                    ->date('from')
                    ->toDateString()
                : $request->session()->get(
                    'dashboard_from',
                    $defaultFrom
                );

        $to =
            $request->filled('to')
                ? $request
                    ->date('to')
                    ->toDateString()
                : $request->session()->get(
                    'dashboard_to',
                    $defaultTo
                );

        /*
        |--------------------------------------------------------------------------
        | Query Utama
        |--------------------------------------------------------------------------
        */

        $baseQuery =
            $this->filteredPenugasanQuery(
                $from,
                $to,
                $search
            )
            ->with([
                'petugas:id,name',
                'layanan:id,nama_layanan',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Statistik
        |--------------------------------------------------------------------------
        */

        $totalAssignments =
            (clone $baseQuery)
                ->count();

        $frequencyByStaff =
            (clone $baseQuery)
                ->join(
                    'penugasan_petugas',
                    'tr_penugasan.id',
                    '=',
                    'penugasan_petugas.penugasan_id'
                )
                ->join(
                    'ms_petugas',
                    'penugasan_petugas.petugas_id',
                    '=',
                    'ms_petugas.id'
                )
                ->select(
                    'ms_petugas.name',
                    DB::raw(
                        'COUNT(DISTINCT tr_penugasan.id) as frequency'
                    )
                )
                ->groupBy(
                    'ms_petugas.name'
                )
                ->orderByDesc(
                    'frequency'
                )
                ->get();

        $totalPetugasInPeriod =
            $frequencyByStaff->count();

        $averageAssignments =
            $totalPetugasInPeriod > 0
                ? round(
                    $totalAssignments
                    /
                    $totalPetugasInPeriod,
                    2
                )
                : 0;

        $mostActiveStaff =
            $frequencyByStaff
                ->first()
                ?->name
            ??
            '-';

        /*
        |--------------------------------------------------------------------------
        | Grafik
        |--------------------------------------------------------------------------
        */

        $layananCharacteristics =
            $this->buildLayananCharacteristicsChart(
                $from,
                $to,
                $search
            );

        $topPetugasDistribution =
            $this->buildTopPetugasDistributionChart(
                $from,
                $to,
                $search
            );

        /*
        |--------------------------------------------------------------------------
        | View
        |--------------------------------------------------------------------------
        */

        return view(
            'dashboard',
            [
                'search' =>
                    $search,

                'from' =>
                    $from,

                'to' =>
                    $to,

                'totalAssignments' =>
                    $totalAssignments,

                'totalPetugasInPeriod' =>
                    $totalPetugasInPeriod,

                'averageAssignments' =>
                    $averageAssignments,

                'mostActiveStaff' =>
                    $mostActiveStaff,

                'layananCharacteristicsCategories' =>
                    $layananCharacteristics[
                        'categories'
                    ],

                'layananCharacteristicsSeries' =>
                    $layananCharacteristics[
                        'series'
                    ],

                'topPetugasDistributionCategories' =>
                    $topPetugasDistribution[
                        'categories'
                    ],

                'topPetugasDistributionSeries' =>
                    $topPetugasDistribution[
                        'series'
                    ],
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Filter Query
    |--------------------------------------------------------------------------
    */

    private function filteredPenugasanQuery(
        string $from,
        string $to,
        string $search
    ): Builder {
        return Penugasan::query()
            ->where(
                'tanggal_mulai',
                '<=',
                $to
            )
            ->where(
                'tanggal_selesai',
                '>=',
                $from
            )
            ->when(
                $search !== '',
                function ($query) use ($search) {
                    $query->where(
                        function ($nested) use ($search) {
                            $nested
                                ->whereHas(
                                    'petugas',
                                    function ($petugas) use ($search) {
                                        $petugas->where(
                                            function ($match) use ($search) {
                                                $match
                                                    ->where(
                                                        'name',
                                                        'like',
                                                        "%{$search}%"
                                                    )
                                                    ->orWhere(
                                                        'nip',
                                                        'like',
                                                        "%{$search}%"
                                                    );
                                            }
                                        );
                                    }
                                )
                                ->orWhereHas(
                                    'layanan',
                                    fn ($layanan) =>
                                        $layanan->where(
                                            'nama_layanan',
                                            'like',
                                            "%{$search}%"
                                        )
                                );
                        }
                    );
                }
            );
    }

    /**
     * @return array{
     *     categories: Collection<int, string>,
     *     series: array<int, array{name: string, data: array<int, int>}>
     * }
     */
    private function buildLayananCharacteristicsChart(
        string $from,
        string $to,
        string $search
    ): array {
        $assignments =
            $this->filteredPenugasanQuery(
                $from,
                $to,
                $search
            )
            ->with([
                'layanan:id,nama_layanan',
            ])
            ->withCount(
                'petugas'
            )
            ->get();

        $categories =
            $assignments
                ->pluck(
                    'layanan.nama_layanan'
                )
                ->filter()
                ->unique()
                ->sort()
                ->values();

        $groupedByLayanan =
            $assignments->groupBy(
                fn ($assignment) =>
                    $assignment
                        ->layanan
                        ->nama_layanan
            );

        $mandiriData = [];
        $timKecilData = [];
        $timBesarData = [];

        foreach (
            $categories
            as
            $namaLayanan
        ) {
            $group =
                $groupedByLayanan->get(
                    $namaLayanan,
                    collect()
                );

            $mandiriData[] =
                $group
                    ->where(
                        'petugas_count',
                        1
                    )
                    ->count();

            $timKecilData[] =
                $group
                    ->where(
                        'petugas_count',
                        '>=',
                        2
                    )
                    ->where(
                        'petugas_count',
                        '<=',
                        3
                    )
                    ->count();

            $timBesarData[] =
                $group
                    ->where(
                        'petugas_count',
                        '>',
                        3
                    )
                    ->count();
        }

        return [
            'categories' =>
                $categories,

            'series' => [
                [
                    'name' =>
                        'Mandiri',

                    'data' =>
                        $mandiriData,
                ],
                [
                    'name' =>
                        'Tim Kecil',

                    'data' =>
                        $timKecilData,
                ],
                [
                    'name' =>
                        'Tim Besar',

                    'data' =>
                        $timBesarData,
                ],
            ],
        ];
    }

    /**
     * @return array{
     *     categories: Collection<int, string>,
     *     series: array<int, array{name: string, data: array<int, int>}>
     * }
     */
    private function buildTopPetugasDistributionChart(
        string $from,
        string $to,
        string $search
    ): array {
        $topStaff =
            $this->filteredPenugasanQuery(
                $from,
                $to,
                $search
            )
            ->join(
                'penugasan_petugas',
                'tr_penugasan.id',
                '=',
                'penugasan_petugas.penugasan_id'
            )
            ->join(
                'ms_petugas',
                'penugasan_petugas.petugas_id',
                '=',
                'ms_petugas.id'
            )
            ->select(
                'ms_petugas.id',
                'ms_petugas.name',
                DB::raw(
                    'COUNT(DISTINCT tr_penugasan.id) as assignment_count'
                )
            )
            ->groupBy(
                'ms_petugas.id',
                'ms_petugas.name'
            )
            ->orderByDesc(
                'assignment_count'
            )
            ->limit(10)
            ->get();

        $categories =
            $topStaff
                ->pluck('name')
                ->values();

        $allLayananNames =
            Layanan::query()
                ->orderBy(
                    'nama_layanan'
                )
                ->pluck(
                    'nama_layanan'
                );

        if ($topStaff->isEmpty()) {
            return [
                'categories' =>
                    $categories,

                'series' =>
                    $allLayananNames
                        ->map(
                            fn ($namaLayanan) => [
                                'name' =>
                                    $namaLayanan,

                                'data' =>
                                    [],
                            ]
                        )
                        ->values()
                        ->all(),
            ];
        }

        $distributionRows =
            $this->filteredPenugasanQuery(
                $from,
                $to,
                $search
            )
            ->join(
                'penugasan_petugas',
                'tr_penugasan.id',
                '=',
                'penugasan_petugas.penugasan_id'
            )
            ->join(
                'ms_layanan',
                'tr_penugasan.layanan_id',
                '=',
                'ms_layanan.id'
            )
            ->whereIn(
                'penugasan_petugas.petugas_id',
                $topStaff->pluck('id')
            )
            ->select(
                'penugasan_petugas.petugas_id',
                'ms_layanan.nama_layanan',
                DB::raw(
                    'COUNT(DISTINCT tr_penugasan.id) as frequency'
                )
            )
            ->groupBy(
                'penugasan_petugas.petugas_id',
                'ms_layanan.nama_layanan'
            )
            ->get()
            ->groupBy(
                'petugas_id'
            );

        $series =
            $allLayananNames
                ->map(
                    function (
                        $namaLayanan
                    ) use (
                        $topStaff,
                        $distributionRows
                    ) {
                        $data =
                            $topStaff
                                ->map(
                                    function (
                                        $staff
                                    ) use (
                                        $namaLayanan,
                                        $distributionRows
                                    ) {
                                        $staffDistribution =
                                            $distributionRows->get(
                                                $staff->id,
                                                collect()
                                            );

                                        return (int) (
                                            $staffDistribution
                                                ->firstWhere(
                                                    'nama_layanan',
                                                    $namaLayanan
                                                )
                                                ?->frequency
                                            ??
                                            0
                                        );
                                    }
                                )
                                ->values()
                                ->all();

                        return [
                            'name' =>
                                $namaLayanan,

                            'data' =>
                                $data,
                        ];
                    }
                )
                ->values()
                ->all();

        return [
            'categories' =>
                $categories,

            'series' =>
                $series,
        ];
    }
}

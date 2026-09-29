<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePenugasanRequest;
use App\Http\Requests\UpdatePenugasanRequest;
use App\Models\Layanan;
use App\Models\Penugasan;
use App\Models\Petugas;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenugasanController extends Controller
{
    /**
     * ============================================================
     * INDEX
     * ============================================================
     */
    public function index(Request $request): View
    {
        /*
        |--------------------------------------------------------------------------
        | VALIDASI FILTER
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:255',
            ],

            'layanan_id' => [
                'nullable',
                'integer',
                'exists:ms_layanan,id',
            ],

            'dari' => [
                'nullable',
                'date',
            ],

            'sampai' => [
                'nullable',
                'date',
                'after_or_equal:dari',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | SEARCH
        |--------------------------------------------------------------------------
        */

        $search = trim(
            (string) $request->input(
                'search',
                ''
            )
        );


        /*
        |--------------------------------------------------------------------------
        | QUERY FILTER
        |--------------------------------------------------------------------------
        */

        $filteredQuery = $this->filteredQuery(
            $request,
            $search
        );


        /*
        |--------------------------------------------------------------------------
        | STATISTIK
        |--------------------------------------------------------------------------
        */

        $totalPenugasan = Penugasan::query()
            ->count();


        $ditampilkan = (clone $filteredQuery)
            ->count();


        $petugasTerlibat = (clone $filteredQuery)
            ->join(
                'penugasan_petugas',
                'tr_penugasan.id',
                '=',
                'penugasan_petugas.penugasan_id'
            )
            ->distinct()
            ->count(
                'penugasan_petugas.petugas_id'
            );


        $jenisLayanan = (clone $filteredQuery)
            ->whereNotNull('layanan_id')
            ->distinct()
            ->count('layanan_id');


        /*
        |--------------------------------------------------------------------------
        | DATA TABEL
        |--------------------------------------------------------------------------
        |
        | Balik ke versi stabil:
        |
        | - terbaru di atas
        | - 5 data per halaman
        |
        */

        $penugasans = (clone $filteredQuery)
            ->with([
                'petugas:id,name,nip,position',
                'layanan:id,nama_layanan',
            ])
            ->latest('id')
            ->paginate(5)
            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | MASTER PETUGAS
        |--------------------------------------------------------------------------
        */

        $petugasOptions = Petugas::query()
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'nip',
                'position',
            ]);


        /*
        |--------------------------------------------------------------------------
        | MASTER LAYANAN
        |--------------------------------------------------------------------------
        */

        $layananOptions = Layanan::query()
            ->orderBy('id')
            ->get([
                'id',
                'nama_layanan',
            ]);


        /*
        |--------------------------------------------------------------------------
        | PETUGAS YANG ADA DI HALAMAN SAAT INI
        |--------------------------------------------------------------------------
        */

        $visiblePetugasIds = $penugasans
            ->getCollection()
            ->flatMap(function (Penugasan $penugasan) {
                return $penugasan
                    ->petugas
                    ->pluck('id');
            })
            ->map(
                fn ($id) => (int) $id
            )
            ->unique()
            ->values();


        /*
        |--------------------------------------------------------------------------
        | HUBUNGKAN PENUGASAN DENGAN PETUGAS
        |--------------------------------------------------------------------------
        |
        | Digunakan ketika tombol Riwayat diklik.
        |
        */

        $rowPetugasIds = $penugasans
            ->getCollection()
            ->mapWithKeys(function (Penugasan $penugasan) {
                return [
                    (string) $penugasan->id =>
                        $penugasan
                            ->petugas
                            ->pluck('id')
                            ->map(
                                fn ($id) => (int) $id
                            )
                            ->values()
                            ->all(),
                ];
            })
            ->all();


        /*
        |--------------------------------------------------------------------------
        | DATA RIWAYAT PETUGAS
        |--------------------------------------------------------------------------
        */

        $historyByPetugas = [];


        if ($visiblePetugasIds->isNotEmpty()) {

            /*
            |--------------------------------------------------------------------------
            | Data petugas
            |--------------------------------------------------------------------------
            */

            $visiblePetugas = Petugas::query()
                ->whereIn(
                    'id',
                    $visiblePetugasIds
                )
                ->get([
                    'id',
                    'name',
                    'nip',
                    'position',
                ])
                ->keyBy('id');


            /*
            |--------------------------------------------------------------------------
            | Semua penugasan milik petugas yang sedang terlihat
            |--------------------------------------------------------------------------
            */

            $historyAssignments = Penugasan::query()
                ->with([
                    'petugas:id,name,nip,position',
                    'layanan:id,nama_layanan',
                ])
                ->whereHas(
                    'petugas',
                    function (Builder $query) use ($visiblePetugasIds) {
                        $query->whereIn(
                            'ms_petugas.id',
                            $visiblePetugasIds
                        );
                    }
                )
                ->orderByDesc('tanggal_mulai')
                ->orderByDesc('id')
                ->get();


            /*
            |--------------------------------------------------------------------------
            | Warna layanan
            |--------------------------------------------------------------------------
            */

            $serviceColors = [
                '#f97316',
                '#84cc16',
                '#14b8a6',
                '#3b82f6',
                '#8b5cf6',
                '#22c55e',
                '#10b981',
                '#ec4899',
                '#f59e0b',
                '#059669',
            ];


            /*
            |--------------------------------------------------------------------------
            | Susun riwayat per petugas
            |--------------------------------------------------------------------------
            */

            foreach ($visiblePetugasIds as $petugasId) {

                $petugas = $visiblePetugas->get(
                    $petugasId
                );


                if (! $petugas) {
                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | Penugasan petugas tersebut
                |--------------------------------------------------------------------------
                */

                $petugasHistory = $historyAssignments
                    ->filter(function (Penugasan $penugasan) use ($petugasId) {
                        return $penugasan
                            ->petugas
                            ->contains(
                                'id',
                                (int) $petugasId
                            );
                    })
                    ->values();


                /*
                |--------------------------------------------------------------------------
                | Daftar pengalaman semua layanan
                |--------------------------------------------------------------------------
                */

                $serviceExperience = $layananOptions
                    ->map(function ($layanan, $index) use (
                        $petugasHistory,
                        $serviceColors
                    ) {

                        $count = $petugasHistory
                            ->where(
                                'layanan_id',
                                $layanan->id
                            )
                            ->count();


                        return [
                            'id' =>
                                (int) $layanan->id,

                            'name' =>
                                $layanan->nama_layanan,

                            'count' =>
                                $count,

                            'color' =>
                                $serviceColors[
                                    $index
                                    %
                                    count(
                                        $serviceColors
                                    )
                                ],
                        ];
                    })
                    ->values();


                /*
                |--------------------------------------------------------------------------
                | Berapa jenis layanan yang pernah dilakukan
                |--------------------------------------------------------------------------
                */

                $experiencedServiceCount = $serviceExperience
                    ->where(
                        'count',
                        '>',
                        0
                    )
                    ->count();


                /*
                |--------------------------------------------------------------------------
                | Riwayat detail
                |--------------------------------------------------------------------------
                */

                $historyItems = $petugasHistory
                    ->map(function (Penugasan $penugasan) use (
                        $serviceColors
                    ) {

                        $layananId = (int) $penugasan->layanan_id;

                        $colorIndex = max(
                            0,
                            $layananId - 1
                        ) % count(
                            $serviceColors
                        );


                        return [
                            'id' =>
                                (int) $penugasan->id,

                            'layanan' =>
                                $penugasan
                                    ->layanan
                                    ?->nama_layanan
                                ?? '-',

                            'tempat' =>
                                $penugasan->tempat
                                ?: '-',

                            'task_detail' =>
                                $penugasan->task_detail
                                ?: '-',

                            'komoditi' =>
                                $penugasan->komoditi
                                ?: '-',

                            'tanggal_mulai' =>
                                $penugasan->tanggal_mulai
                                    ?->format(
                                        'd M Y'
                                    )
                                ?? '-',

                            'tanggal_selesai' =>
                                $penugasan->tanggal_selesai
                                    ?->format(
                                        'd M Y'
                                    )
                                ?? '-',

                            'color' =>
                                $serviceColors[
                                    $colorIndex
                                ],
                        ];
                    })
                    ->values()
                    ->all();


                /*
                |--------------------------------------------------------------------------
                | Data final petugas
                |--------------------------------------------------------------------------
                */

                $historyByPetugas[
                    (string) $petugasId
                ] = [

                    'id' =>
                        (int) $petugas->id,

                    'name' =>
                        $petugas->name,

                    'nip' =>
                        $petugas->nip
                        ?: '-',

                    'position' =>
                        $petugas->position
                        ?: '-',

                    'experienced_service_count' =>
                        $experiencedServiceCount,

                    'total_services' =>
                        $layananOptions->count(),

                    'services' =>
                        $serviceExperience
                            ->all(),

                    'history_count' =>
                        $petugasHistory
                            ->count(),

                    'history' =>
                        $historyItems,
                ];
            }
        }


        /*
        |--------------------------------------------------------------------------
        | VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'penugasan.index',
            compact(
                'penugasans',
                'petugasOptions',
                'layananOptions',
                'totalPenugasan',
                'ditampilkan',
                'petugasTerlibat',
                'jenisLayanan',
                'search',
                'historyByPetugas',
                'rowPetugasIds'
            )
        );
    }


    /**
     * ============================================================
     * STORE
     * ============================================================
     */
    public function store(
        StorePenugasanRequest $request
    ): RedirectResponse {

        $validated =
            $request->validated();


        /*
        |--------------------------------------------------------------------------
        | Ambil petugas
        |--------------------------------------------------------------------------
        */

        $petugasIds =
            $validated[
                'petugas_ids'
            ];


        /*
        |--------------------------------------------------------------------------
        | Hapus data pivot dari payload tr_penugasan
        |--------------------------------------------------------------------------
        */

        unset(
            $validated[
                'petugas_ids'
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Kalau request versi baru masih punya force_conflict,
        | jangan simpan field tersebut.
        |--------------------------------------------------------------------------
        */

        unset(
            $validated[
                'force_conflict'
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Balik ke logic lama:
        | langsung simpan, tanpa popup bentrok.
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use (
                $validated,
                $petugasIds
            ) {

                $penugasan =
                    Penugasan::create(
                        $validated
                    );


                $penugasan
                    ->petugas()
                    ->sync(
                        $petugasIds
                    );
            }
        );


        return back()->with(
            'success',
            'Penugasan berhasil ditambahkan.'
        );
    }


    /**
     * ============================================================
     * UPDATE
     * ============================================================
     */
    public function update(
        UpdatePenugasanRequest $request,
        Penugasan $penugasan
    ): RedirectResponse {

        $validated =
            $request->validated();


        $petugasIds =
            $validated[
                'petugas_ids'
            ];


        unset(
            $validated[
                'petugas_ids'
            ]
        );


        unset(
            $validated[
                'force_conflict'
            ]
        );


        DB::transaction(
            function () use (
                $penugasan,
                $validated,
                $petugasIds
            ) {

                $penugasan->update(
                    $validated
                );


                $penugasan
                    ->petugas()
                    ->sync(
                        $petugasIds
                    );
            }
        );


        return back()->with(
            'success',
            'Penugasan berhasil diperbarui.'
        );
    }


    /**
     * ============================================================
     * DELETE
     * ============================================================
     */
    public function destroy(
        Penugasan $penugasan
    ): RedirectResponse {

        $penugasan->delete();


        return back()->with(
            'success',
            'Penugasan berhasil dihapus.'
        );
    }


    /**
     * ============================================================
     * QUERY FILTER
     * ============================================================
     */
    private function filteredQuery(
        Request $request,
        string $search
    ): Builder {

        return Penugasan::query()

            /*
            |--------------------------------------------------------------------------
            | SEARCH
            |--------------------------------------------------------------------------
            */

            ->when(
                $search !== '',
                function (
                    Builder $query
                ) use ($search) {

                    $query->where(
                        function (
                            Builder $nested
                        ) use ($search) {

                            $nested
                                ->where(
                                    'task_detail',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'tempat',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'komoditi',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhereHas(
                                    'petugas',
                                    function (
                                        Builder $petugas
                                    ) use ($search) {

                                        $petugas->where(
                                            function (
                                                Builder $match
                                            ) use ($search) {

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
                                    function (
                                        Builder $layanan
                                    ) use ($search) {

                                        $layanan->where(
                                            'nama_layanan',
                                            'like',
                                            "%{$search}%"
                                        );
                                    }
                                );
                        }
                    );
                }
            )


            /*
            |--------------------------------------------------------------------------
            | LAYANAN
            |--------------------------------------------------------------------------
            */

            ->when(
                $request->filled(
                    'layanan_id'
                ),
                fn (Builder $query) =>
                    $query->where(
                        'layanan_id',
                        (int) $request->input(
                            'layanan_id'
                        )
                    )
            )


            /*
            |--------------------------------------------------------------------------
            | TANGGAL MULAI FILTER
            |--------------------------------------------------------------------------
            */

            ->when(
                $request->filled(
                    'dari'
                ),
                fn (Builder $query) =>
                    $query->whereDate(
                        'tanggal_selesai',
                        '>=',
                        $request->input(
                            'dari'
                        )
                    )
            )


            /*
            |--------------------------------------------------------------------------
            | TANGGAL AKHIR FILTER
            |--------------------------------------------------------------------------
            */

            ->when(
                $request->filled(
                    'sampai'
                ),
                fn (Builder $query) =>
                    $query->whereDate(
                        'tanggal_mulai',
                        '<=',
                        $request->input(
                            'sampai'
                        )
                    )
            );
    }
}
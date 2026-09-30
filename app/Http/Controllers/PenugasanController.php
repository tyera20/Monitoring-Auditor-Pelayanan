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

class PenugasanController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'layanan_id' => ['nullable', 'integer', 'exists:ms_layanan,id'],
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
            'bulan' => ['nullable', 'integer', 'between:1,12'],
            'tahun' => ['nullable', 'integer', 'between:2000,2100'],
            'per_page' => ['nullable', 'integer', 'in:10,25,50,100'],
        ]);

        $search = trim((string) $request->input('search', ''));

        $bulan = $request->filled('bulan')
            ? (int) $request->input('bulan')
            : null;

        $tahun = $request->filled('tahun')
            ? (int) $request->input('tahun')
            : null;

        $perPage = (int) $request->input('per_page', 10);

        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $filteredQuery = $this->filteredPenugasanQuery(
            $request,
            $search,
            $bulan,
            $tahun
        );

        /*
        |--------------------------------------------------------------------------
        | STATISTIK
        |--------------------------------------------------------------------------
        */

        $totalPenugasan = Penugasan::query()->count();

        $ditampilkan = (clone $filteredQuery)->count();

        $petugasTerlibat = (clone $filteredQuery)
            ->join(
                'penugasan_petugas',
                'tr_penugasan.id',
                '=',
                'penugasan_petugas.penugasan_id'
            )
            ->distinct()
            ->count('penugasan_petugas.petugas_id');

        $jenisLayanan = Penugasan::query()
            ->whereNotNull('layanan_id')
            ->distinct()
            ->count('layanan_id');

        /*
        |--------------------------------------------------------------------------
        | DATA TABEL
        |--------------------------------------------------------------------------
        |
        | Data terbaru ditampilkan terlebih dahulu.
        | Jumlah data per halaman mengikuti pilihan user.
        |
        */

        $penugasans = (clone $filteredQuery)
            ->with([
                'petugas:id,name,nip',
                'layanan:id,nama_layanan',
            ])
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | PILIHAN PETUGAS & LAYANAN
        |--------------------------------------------------------------------------
        */

        $petugasOptions = Petugas::query()
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'nip',
            ]);

        $layananOptions = Layanan::query()
            ->orderBy('nama_layanan')
            ->get([
                'id',
                'nama_layanan',
            ]);

        /*
        |--------------------------------------------------------------------------
        | PETUGAS PER BARIS PENUGASAN
        |--------------------------------------------------------------------------
        */

        $rowPetugasIds = $penugasans
            ->getCollection()
            ->mapWithKeys(function (Penugasan $penugasan) {
                return [
                    (string) $penugasan->id => $penugasan
                        ->petugas
                        ->pluck('id')
                        ->map(fn ($id) => (int) $id)
                        ->values()
                        ->all(),
                ];
            })
            ->all();

        /*
        |--------------------------------------------------------------------------
        | RIWAYAT PETUGAS
        |--------------------------------------------------------------------------
        |
        | Riwayat hanya disiapkan untuk petugas yang muncul pada halaman aktif.
        | Detail riwayat tetap mencakup seluruh penugasan petugas tersebut.
        |
        */

        $pagePetugasIds = collect($rowPetugasIds)
            ->flatten()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $historyByPetugas = [];

        if ($pagePetugasIds->isNotEmpty()) {
            $historyPetugas = Petugas::query()
                ->whereIn('id', $pagePetugasIds)
                ->get([
                    'id',
                    'name',
                    'nip',
                    'position',
                ])
                ->keyBy('id');

            $historyAssignments = Penugasan::query()
                ->with([
                    'layanan:id,nama_layanan',
                    'petugas:id',
                ])
                ->whereHas(
                    'petugas',
                    fn (Builder $query) => $query
                        ->whereIn('ms_petugas.id', $pagePetugasIds)
                )
                ->latest('tanggal_mulai')
                ->latest('id')
                ->get();

            foreach ($pagePetugasIds as $petugasId) {
                $petugas = $historyPetugas->get($petugasId);

                if (! $petugas) {
                    continue;
                }

                $personAssignments = $historyAssignments
                    ->filter(
                        fn (Penugasan $penugasan) => $penugasan
                            ->petugas
                            ->contains(
                                fn ($item) => (int) $item->id === (int) $petugasId
                            )
                    )
                    ->values();

                $services = $layananOptions
                    ->map(function (Layanan $layanan) use ($personAssignments) {
                        $count = $personAssignments
                            ->where('layanan_id', $layanan->id)
                            ->count();

                        return [
                            'id' => $layanan->id,
                            'name' => $layanan->nama_layanan,
                            'count' => $count,
                        ];
                    })
                    ->values();

                $history = $personAssignments
                    ->map(function (Penugasan $penugasan) {
                        return [
                            'id' => $penugasan->id,
                            'layanan' => $penugasan->layanan?->nama_layanan ?? '-',
                            'tempat' => $penugasan->tempat ?: '-',
                            'komoditi' => $penugasan->komoditi ?: '-',
                            'task_detail' => $penugasan->task_detail ?: '-',
                            'tanggal_mulai' => $penugasan->tanggal_mulai
                                ?->format('d M Y') ?? '-',
                            'tanggal_selesai' => $penugasan->tanggal_selesai
                                ?->format('d M Y') ?? '-',
                        ];
                    })
                    ->values();

                $historyByPetugas[(string) $petugasId] = [
                    'id' => $petugas->id,
                    'name' => $petugas->name,
                    'nip' => $petugas->nip ?: '-',
                    'position' => $petugas->position ?: '-',
                    'experienced_service_count' => $services
                        ->where('count', '>', 0)
                        ->count(),
                    'total_services' => $layananOptions->count(),
                    'services' => $services->all(),
                    'history' => $history->all(),
                ];
            }
        }

        return view(
            'penugasan.index',
            compact(
                'penugasans',
                'search',
                'petugasOptions',
                'layananOptions',
                'totalPenugasan',
                'ditampilkan',
                'petugasTerlibat',
                'jenisLayanan',
                'historyByPetugas',
                'rowPetugasIds',
                'perPage',
            )
        );
    }

    private function filteredPenugasanQuery(
        Request $request,
        string $search,
        ?int $bulan,
        ?int $tahun
    ): Builder {
        return Penugasan::query()
            ->when(
                $search !== '',
                function (Builder $query) use ($search) {
                    $query->where(
                        function (Builder $nested) use ($search) {
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
                                    function (Builder $petugas) use ($search) {
                                        $petugas->where(
                                            function (Builder $match) use ($search) {
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
                                    fn (Builder $layanan) => $layanan
                                        ->where(
                                            'nama_layanan',
                                            'like',
                                            "%{$search}%"
                                        )
                                );
                        }
                    );
                }
            )
            ->when(
                $bulan !== null,
                fn (Builder $query) => $query
                    ->whereMonth('tanggal_mulai', $bulan)
            )
            ->when(
                $tahun !== null,
                fn (Builder $query) => $query
                    ->whereYear('tanggal_mulai', $tahun)
            )
            ->when(
                $request->filled('layanan_id'),
                fn (Builder $query) => $query
                    ->where(
                        'layanan_id',
                        (int) $request->input('layanan_id')
                    )
            )
            ->when(
                $request->filled('dari'),
                fn (Builder $query) => $query
                    ->whereDate(
                        'tanggal_selesai',
                        '>=',
                        $request->input('dari')
                    )
            )
            ->when(
                $request->filled('sampai'),
                fn (Builder $query) => $query
                    ->whereDate(
                        'tanggal_mulai',
                        '<=',
                        $request->input('sampai')
                    )
            );
    }

    /**
     * Cari penugasan petugas yang tanggalnya bertabrakan.
     *
     * Rentang tanggal dianggap bentrok jika:
     * tanggal_mulai_lama <= tanggal_selesai_baru
     * DAN
     * tanggal_selesai_lama >= tanggal_mulai_baru.
     */
    private function findScheduleConflicts(
        array $petugasIds,
        string $tanggalMulai,
        string $tanggalSelesai,
        ?int $excludePenugasanId = null
    ): array {
        $petugasIds = collect($petugasIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($petugasIds)) {
            return [];
        }

        $assignments = Penugasan::query()
            ->with([
                'layanan:id,nama_layanan',
                'petugas:id,name,nip',
            ])
            ->whereDate('tanggal_mulai', '<=', $tanggalSelesai)
            ->whereDate('tanggal_selesai', '>=', $tanggalMulai)
            ->when(
                $excludePenugasanId !== null,
                fn (Builder $query) => $query
                    ->where('id', '!=', $excludePenugasanId)
            )
            ->whereHas(
                'petugas',
                fn (Builder $query) => $query
                    ->whereIn('ms_petugas.id', $petugasIds)
            )
            ->orderBy('tanggal_mulai')
            ->orderBy('id')
            ->get();

        $conflicts = [];

        foreach ($assignments as $assignment) {
            foreach ($assignment->petugas as $petugas) {
                if (! in_array((int) $petugas->id, $petugasIds, true)) {
                    continue;
                }

                $conflicts[] = [
                    'penugasan_id' => (int) $assignment->id,
                    'petugas_id' => (int) $petugas->id,
                    'petugas_name' => $petugas->name,
                    'nip' => $petugas->nip,
                    'layanan' => $assignment->layanan?->nama_layanan ?? '-',
                    'tempat' => $assignment->tempat ?: '-',
                    'komoditi' => $assignment->komoditi ?: '-',
                    'tanggal_mulai' => (string) $assignment
                        ->getRawOriginal('tanggal_mulai'),
                    'tanggal_selesai' => (string) $assignment
                        ->getRawOriginal('tanggal_selesai'),
                ];
            }
        }

        return $conflicts;
    }

    public function store(
        StorePenugasanRequest $request
    ): RedirectResponse {
        $validated = $request->validated();

        $petugasIds = $validated['petugas_ids'];

        /*
        |--------------------------------------------------------------------------
        | PERINGATAN JADWAL BENTROK
        |--------------------------------------------------------------------------
        |
        | Pengecekan hanya dilewati setelah user menekan "Tetap Simpan".
        | Data belum disimpan selama user belum memberikan konfirmasi.
        |
        */

        if (! $request->boolean('allow_overlap')) {
            $conflicts = $this->findScheduleConflicts(
                $petugasIds,
                $validated['tanggal_mulai'],
                $validated['tanggal_selesai']
            );

            if (! empty($conflicts)) {
                return back()
                    ->withInput()
                    ->with(
                        'schedule_conflicts',
                        $conflicts
                    )
                    ->with(
                        'schedule_warning_mode',
                        'create'
                    );
            }
        }

        unset($validated['petugas_ids']);

        $penugasan = Penugasan::create($validated);

        $penugasan
            ->petugas()
            ->sync($petugasIds);

        return back()->with(
            'success',
            'Penugasan berhasil ditambahkan.'
        );
    }

    public function update(
        UpdatePenugasanRequest $request,
        Penugasan $penugasan
    ): RedirectResponse {
        $validated = $request->validated();

        $petugasIds = $validated['petugas_ids'];

        /*
        |--------------------------------------------------------------------------
        | PERINGATAN JADWAL BENTROK SAAT EDIT
        |--------------------------------------------------------------------------
        |
        | Penugasan yang sedang diedit tidak dibandingkan dengan dirinya sendiri.
        |
        */

        if (! $request->boolean('allow_overlap')) {
            $conflicts = $this->findScheduleConflicts(
                $petugasIds,
                $validated['tanggal_mulai'],
                $validated['tanggal_selesai'],
                (int) $penugasan->id
            );

            if (! empty($conflicts)) {
                return back()
                    ->withInput()
                    ->with(
                        'schedule_conflicts',
                        $conflicts
                    )
                    ->with(
                        'schedule_warning_mode',
                        'edit'
                    );
            }
        }

        unset($validated['petugas_ids']);

        $penugasan->update($validated);

        $penugasan
            ->petugas()
            ->sync($petugasIds);

        return back()->with(
            'success',
            'Penugasan berhasil diperbarui.'
        );
    }

    public function destroy(
        Penugasan $penugasan
    ): RedirectResponse {
        $penugasan->delete();

        return back()->with(
            'success',
            'Penugasan berhasil dihapus.'
        );
    }
}

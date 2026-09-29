<?php

namespace App\Services;

use App\Models\Penugasan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PenugasanFilters
{
    public static function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'bulan' => ['nullable', 'integer', 'between:1,12'],
            'tahun' => ['nullable', 'integer', 'between:2000,2100'],
            'tanggal_penugasan' => ['nullable', 'date'],
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
            'layanan_id' => ['nullable', 'integer', 'exists:ms_layanan,id'],
            'petugas_id' => ['nullable', 'integer', 'exists:ms_petugas,id'],
        ];
    }

    public function query(Request $request): Builder
    {
        $search = trim((string) $request->input('search', ''));

        return Penugasan::query()
            ->when($search !== '', fn (Builder $query) =>
                $query->where(function (Builder $nested) use ($search) {
                    $nested
                        ->where('task_detail', 'like', "%{$search}%")
                        ->orWhere('tempat', 'like', "%{$search}%")
                        ->orWhere('komoditi', 'like', "%{$search}%")
                        ->orWhereHas('petugas', fn (Builder $staff) =>
                            $staff->where(function (Builder $match) use ($search) {
                                $match
                                    ->where('name', 'like', "%{$search}%")
                                    ->orWhere('nip', 'like', "%{$search}%");
                            })
                        )
                        ->orWhereHas('layanan', fn (Builder $service) =>
                            $service->where('nama_layanan', 'like', "%{$search}%")
                        );
                })
            )
            ->when($request->filled('bulan'), fn (Builder $query) =>
                $query->whereMonth('tanggal_mulai', (int) $request->input('bulan'))
            )
            ->when($request->filled('tahun'), fn (Builder $query) =>
                $query->whereYear('tanggal_mulai', (int) $request->input('tahun'))
            )
            ->when($request->filled('tanggal_penugasan'), fn (Builder $query) =>
                $query
                    ->whereDate(
                        'tanggal_mulai',
                        '<=',
                        $request->input('tanggal_penugasan')
                    )
                    ->whereDate(
                        'tanggal_selesai',
                        '>=',
                        $request->input('tanggal_penugasan')
                    )
            )
            ->when($request->filled('dari'), fn (Builder $query) =>
                $query->whereDate(
                    'tanggal_selesai',
                    '>=',
                    $request->input('dari')
                )
            )
            ->when($request->filled('sampai'), fn (Builder $query) =>
                $query->whereDate(
                    'tanggal_mulai',
                    '<=',
                    $request->input('sampai')
                )
            )
            ->when($request->filled('layanan_id'), fn (Builder $query) =>
                $query->where('layanan_id', (int) $request->input('layanan_id'))
            )
            ->when($request->filled('petugas_id'), fn (Builder $query) =>
                $query->whereHas('petugas', fn (Builder $staff) =>
                    $staff->where(
                        'ms_petugas.id',
                        (int) $request->input('petugas_id')
                    )
                )
            );
    }
}
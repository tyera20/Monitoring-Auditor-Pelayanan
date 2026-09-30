<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePetugasRequest;
use App\Http\Requests\UpdatePetugasRequest;
use App\Models\Penugasan;
use App\Models\Petugas;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PetugasController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'jenjang' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'in:10,25,50,100'],
        ]);

        $search = trim((string) $request->input('search', ''));
        $jenjang = trim((string) $request->input('jenjang', ''));

        $perPage = (int) $request->input('per_page', 10);

        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $jenjangOptions = collect([
            'Calon',
            'Pemula',
            'Terampil',
            'Mahir',
            'Ahli',
        ]);

        $filteredQuery = Petugas::query()
            ->withCount('penugasans')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    $nested
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('nip', 'like', "%{$search}%")
                        ->orWhere('position', 'like', "%{$search}%");
                });
            })
            ->when($jenjang !== '', function ($query) use ($jenjang) {
                $query->where('position', 'like', "%{$jenjang}%");
            });

        $totalPetugas = Petugas::query()->count();

        $ditampilkan = (clone $filteredQuery)->count();

        $petugasDitugaskan = Petugas::query()
            ->has('penugasans')
            ->count();

        $totalPenugasan = Penugasan::query()->count();

        $petugas = (clone $filteredQuery)
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        return view(
            'petugas.index',
            compact(
                'petugas',
                'search',
                'jenjang',
                'jenjangOptions',
                'totalPetugas',
                'ditampilkan',
                'petugasDitugaskan',
                'totalPenugasan',
                'perPage',
            )
        );
    }

    public function store(StorePetugasRequest $request): RedirectResponse
    {
        Petugas::create($request->validated());

        return back()->with(
            'success',
            'Data petugas berhasil ditambahkan.'
        );
    }

    public function update(
        UpdatePetugasRequest $request,
        Petugas $petuga
    ): RedirectResponse {
        $petuga->update($request->validated());

        return back()->with(
            'success',
            'Data petugas berhasil diperbarui.'
        );
    }

    public function destroy(Petugas $petuga): RedirectResponse
    {
        $petuga->delete();

        return back()->with(
            'success',
            'Data petugas berhasil dihapus.'
        );
    }
}
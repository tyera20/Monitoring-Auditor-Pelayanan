<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePetugasRequest;
use App\Http\Requests\UpdatePetugasRequest;
use App\Models\Petugas;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PetugasController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */
    public function index(Request $request): View
    {
        /*
        |--------------------------------------------------------------------------
        | Pilihan Jenjang
        |--------------------------------------------------------------------------
        */

        $jenjangOptions = [
            'Pemula',
            'Terampil',
            'Mahir',
            'Penyelia',
            'Ahli Pertama',
            'Ahli Muda',
            'Ahli Madya',
            'Ahli Utama',
        ];


        /*
        |--------------------------------------------------------------------------
        | Validasi Filter
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:255',
            ],

            'jenjang' => [
                'nullable',
                'string',
                Rule::in($jenjangOptions),
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Ambil Filter
        |--------------------------------------------------------------------------
        */

        $search = trim(
            (string) $request->string('search')
        );

        $jenjang = trim(
            (string) $request->string('jenjang')
        );


        /*
        |--------------------------------------------------------------------------
        | Query Dasar
        |--------------------------------------------------------------------------
        */

        $filteredQuery = Petugas::query()

            /*
            |--------------------------------------------------------------------------
            | Search
            |--------------------------------------------------------------------------
            */

            ->when(
                $search !== '',
                function ($query) use ($search) {
                    $query->where(
                        function ($nested) use ($search) {
                            $nested
                                ->where(
                                    'name',
                                    'like',
                                    "%{$search}%"
                                )

                                ->orWhere(
                                    'nip',
                                    'like',
                                    "%{$search}%"
                                )

                                ->orWhere(
                                    'position',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
                }
            )


            /*
            |--------------------------------------------------------------------------
            | Filter Jenjang
            |--------------------------------------------------------------------------
            */

            ->when(
                $jenjang !== '',
                function ($query) use ($jenjang) {
                    $query->where(
                        'position',
                        'like',
                        "%{$jenjang}%"
                    );
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Statistik
        |--------------------------------------------------------------------------
        */

        $totalPetugas = Petugas::query()->count();

        $ditampilkan =
            (clone $filteredQuery)
                ->count();

        $petugasDitugaskan =
            DB::table('penugasan_petugas')
                ->distinct()
                ->count('petugas_id');

        $totalPenugasan =
            DB::table('penugasan_petugas')
                ->distinct()
                ->count('penugasan_id');


        /*
        |--------------------------------------------------------------------------
        | Nama Tabel Petugas
        |--------------------------------------------------------------------------
        */

        $petugasTable =
            (new Petugas())
                ->getTable();


        /*
        |--------------------------------------------------------------------------
        | Data Petugas
        |--------------------------------------------------------------------------
        */

        $petugas =
            (clone $filteredQuery)

                /*
                |--------------------------------------------------------------------------
                | Ambil Seluruh Kolom Petugas
                |--------------------------------------------------------------------------
                */

                ->select(
                    "{$petugasTable}.*"
                )


                /*
                |--------------------------------------------------------------------------
                | Jumlah Penugasan per Petugas
                |--------------------------------------------------------------------------
                */

                ->selectSub(
                    function ($query) use ($petugasTable) {
                        $query
                            ->from('penugasan_petugas')
                            ->selectRaw('COUNT(*)')
                            ->whereColumn(
                                'penugasan_petugas.petugas_id',
                                "{$petugasTable}.id"
                            );
                    },
                    'penugasan_count'
                )


                /*
                |--------------------------------------------------------------------------
                | Urutan Nama
                |--------------------------------------------------------------------------
                */

                ->orderBy(
                    'name',
                    'asc'
                )


                /*
                |--------------------------------------------------------------------------
                | Pagination
                |--------------------------------------------------------------------------
                */

                ->paginate(5)

                ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | Return View
        |--------------------------------------------------------------------------
        */

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
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */
    public function store(
        StorePetugasRequest $request
    ): RedirectResponse {
        Petugas::create(
            $request->validated()
        );

        return back()->with(
            'success',
            'Data petugas berhasil ditambahkan.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */
    public function update(
        UpdatePetugasRequest $request,
        Petugas $petuga
    ): RedirectResponse {
        $petuga->update(
            $request->validated()
        );

        return back()->with(
            'success',
            'Data petugas berhasil diperbarui.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */
    public function destroy(
        Petugas $petuga
    ): RedirectResponse {
        $petuga->delete();

        return back()->with(
            'success',
            'Data petugas berhasil dihapus.'
        );
    }
}
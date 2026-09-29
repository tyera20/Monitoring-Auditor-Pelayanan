<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLayananRequest;
use App\Http\Requests\UpdateLayananRequest;
use App\Models\Layanan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LayananController extends Controller
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
        | Validasi Filter
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Ambil Search
        |--------------------------------------------------------------------------
        */

        $search = trim(
            (string) $request->string('search')
        );


        /*
        |--------------------------------------------------------------------------
        | Query Dasar
        |--------------------------------------------------------------------------
        */

        $filteredQuery = Layanan::query()
            ->when(
                $search !== '',
                function ($query) use ($search) {
                    $query->where(
                        'nama_layanan',
                        'like',
                        "%{$search}%"
                    );
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Statistik
        |--------------------------------------------------------------------------
        */

        $totalLayanan =
            Layanan::query()
                ->count();


        $ditampilkan =
            (clone $filteredQuery)
                ->count();


        $layananDigunakan =
            DB::table('tr_penugasan')
                ->whereNotNull('layanan_id')
                ->distinct()
                ->count('layanan_id');


        $totalPenugasan =
            DB::table('tr_penugasan')
                ->count();


        /*
        |--------------------------------------------------------------------------
        | Nama Tabel Layanan
        |--------------------------------------------------------------------------
        */

        $layananTable =
            (new Layanan())
                ->getTable();


        /*
        |--------------------------------------------------------------------------
        | Data Tabel
        |--------------------------------------------------------------------------
        */

        $layanans =
            (clone $filteredQuery)

                /*
                |--------------------------------------------------------------------------
                | Ambil Semua Kolom
                |--------------------------------------------------------------------------
                */

                ->select(
                    "{$layananTable}.*"
                )


                /*
                |--------------------------------------------------------------------------
                | Hitung Penugasan per Layanan
                |--------------------------------------------------------------------------
                */

                ->selectSub(
                    function ($query) use ($layananTable) {
                        $query
                            ->from('tr_penugasan')
                            ->selectRaw('COUNT(*)')
                            ->whereColumn(
                                'tr_penugasan.layanan_id',
                                "{$layananTable}.id"
                            );
                    },
                    'penugasan_count'
                )


                /*
                |--------------------------------------------------------------------------
                | Urutkan Nama
                |--------------------------------------------------------------------------
                */

                ->orderBy(
                    'nama_layanan',
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
            'layanan.index',
            compact(
                'layanans',
                'search',
                'totalLayanan',
                'ditampilkan',
                'layananDigunakan',
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
        StoreLayananRequest $request
    ): RedirectResponse {
        Layanan::create(
            $request->validated()
        );


        return back()->with(
            'success',
            'Data layanan berhasil ditambahkan.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */
    public function update(
        UpdateLayananRequest $request,
        Layanan $layanan
    ): RedirectResponse {
        $layanan->update(
            $request->validated()
        );


        return back()->with(
            'success',
            'Data layanan berhasil diperbarui.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */
    public function destroy(
        Layanan $layanan
    ): RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | Cek Apakah Sedang Digunakan
        |--------------------------------------------------------------------------
        */

        $digunakan =
            DB::table('tr_penugasan')
                ->where(
                    'layanan_id',
                    $layanan->id
                )
                ->exists();


        /*
        |--------------------------------------------------------------------------
        | Jangan Hapus Jika Sudah Dipakai
        |--------------------------------------------------------------------------
        */

        if ($digunakan) {
            return back()->with(
                'error',
                'Layanan tidak dapat dihapus karena masih digunakan pada data penugasan.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Hapus
        |--------------------------------------------------------------------------
        */

        $layanan->delete();


        return back()->with(
            'success',
            'Data layanan berhasil dihapus.'
        );
    }
}
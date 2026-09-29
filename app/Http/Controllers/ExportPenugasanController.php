<?php

namespace App\Http\Controllers;

use App\Models\Penugasan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportPenugasanController extends Controller
{
    public function __invoke(
        Request $request
    ): BinaryFileResponse {
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
        | QUERY PENUGASAN
        |--------------------------------------------------------------------------
        |
        | Query export dibuat sama dengan filter
        | pada halaman Penugasan.
        |--------------------------------------------------------------------------
        */

        $penugasans =
            Penugasan::query()

                ->with([
                    'petugas:id,name,nip',

                    'layanan:id,nama_layanan',
                ])


                /*
                |--------------------------------------------------------------------------
                | SEARCH
                |--------------------------------------------------------------------------
                */

                ->when(
                    $search !== '',
                    function (
                        Builder $query
                    ) use (
                        $search
                    ) {
                        $query->where(
                            function (
                                Builder $nested
                            ) use (
                                $search
                            ) {
                                $nested

                                    /*
                                    |--------------------------------------------------------------------------
                                    | Detail Tugas
                                    |--------------------------------------------------------------------------
                                    */

                                    ->where(
                                        'task_detail',
                                        'like',
                                        "%{$search}%"
                                    )


                                    /*
                                    |--------------------------------------------------------------------------
                                    | Tempat
                                    |--------------------------------------------------------------------------
                                    */

                                    ->orWhere(
                                        'tempat',
                                        'like',
                                        "%{$search}%"
                                    )


                                    /*
                                    |--------------------------------------------------------------------------
                                    | Komoditi
                                    |--------------------------------------------------------------------------
                                    */

                                    ->orWhere(
                                        'komoditi',
                                        'like',
                                        "%{$search}%"
                                    )


                                    /*
                                    |--------------------------------------------------------------------------
                                    | Petugas
                                    |--------------------------------------------------------------------------
                                    */

                                    ->orWhereHas(
                                        'petugas',
                                        function (
                                            Builder $petugas
                                        ) use (
                                            $search
                                        ) {
                                            $petugas->where(
                                                function (
                                                    Builder $match
                                                ) use (
                                                    $search
                                                ) {
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


                                    /*
                                    |--------------------------------------------------------------------------
                                    | Layanan
                                    |--------------------------------------------------------------------------
                                    */

                                    ->orWhereHas(
                                        'layanan',
                                        function (
                                            Builder $layanan
                                        ) use (
                                            $search
                                        ) {
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
                | FILTER LAYANAN
                |--------------------------------------------------------------------------
                */

                ->when(
                    $request->filled(
                        'layanan_id'
                    ),
                    function (
                        Builder $query
                    ) use (
                        $request
                    ) {
                        $query->where(
                            'layanan_id',
                            (int) $request->input(
                                'layanan_id'
                            )
                        );
                    }
                )


                /*
                |--------------------------------------------------------------------------
                | FILTER DARI TANGGAL
                |--------------------------------------------------------------------------
                |
                | Penugasan akan masuk apabila
                | tanggal selesai >= tanggal awal filter.
                |--------------------------------------------------------------------------
                */

                ->when(
                    $request->filled(
                        'dari'
                    ),
                    function (
                        Builder $query
                    ) use (
                        $request
                    ) {
                        $query->whereDate(
                            'tanggal_selesai',
                            '>=',
                            $request->input(
                                'dari'
                            )
                        );
                    }
                )


                /*
                |--------------------------------------------------------------------------
                | FILTER SAMPAI TANGGAL
                |--------------------------------------------------------------------------
                |
                | Penugasan akan masuk apabila
                | tanggal mulai <= tanggal akhir filter.
                |--------------------------------------------------------------------------
                */

                ->when(
                    $request->filled(
                        'sampai'
                    ),
                    function (
                        Builder $query
                    ) use (
                        $request
                    ) {
                        $query->whereDate(
                            'tanggal_mulai',
                            '<=',
                            $request->input(
                                'sampai'
                            )
                        );
                    }
                )


                /*
                |--------------------------------------------------------------------------
                | TERBARU DULU
                |--------------------------------------------------------------------------
                */

                ->latest('id')


                /*
                |--------------------------------------------------------------------------
                | BATAS MAKSIMAL
                |--------------------------------------------------------------------------
                */

                ->limit(5001)

                ->get();


        /*
        |--------------------------------------------------------------------------
        | BATAS 5000 DATA
        |--------------------------------------------------------------------------
        */

        abort_if(
            $penugasans->count() > 5000,
            422,
            'Data lebih dari 5.000 baris. Gunakan filter tanggal atau layanan untuk mempersempit ekspor.'
        );


        /*
        |--------------------------------------------------------------------------
        | PERIODE EXPORT
        |--------------------------------------------------------------------------
        */

        $tanggalAwal =
            $request->filled('dari')
                ? $request->input('dari')
                : null;


        $tanggalAkhir =
            $request->filled('sampai')
                ? $request->input('sampai')
                : null;


        /*
        |--------------------------------------------------------------------------
        | Jika tidak ada tanggal dari filter,
        | cari tanggal terkecil / terbesar dari data.
        |--------------------------------------------------------------------------
        */

        if (
            !$tanggalAwal &&
            $penugasans->isNotEmpty()
        ) {
            $tanggalAwal =
                $penugasans
                    ->pluck(
                        'tanggal_mulai'
                    )
                    ->filter()
                    ->min();
        }


        if (
            !$tanggalAkhir &&
            $penugasans->isNotEmpty()
        ) {
            $tanggalAkhir =
                $penugasans
                    ->pluck(
                        'tanggal_selesai'
                    )
                    ->filter()
                    ->max();
        }


        /*
        |--------------------------------------------------------------------------
        | TEXT PERIODE
        |--------------------------------------------------------------------------
        */

        if (
            $tanggalAwal &&
            $tanggalAkhir
        ) {
            $periodeText =
                Carbon::parse(
                    $tanggalAwal
                )
                    ->locale('id')
                    ->translatedFormat(
                        'd F Y'
                    )

                .
                ' - '
                .

                Carbon::parse(
                    $tanggalAkhir
                )
                    ->locale('id')
                    ->translatedFormat(
                        'd F Y'
                    );
        } elseif (
            $tanggalAwal
        ) {
            $periodeText =
                'Mulai '
                .
                Carbon::parse(
                    $tanggalAwal
                )
                    ->locale('id')
                    ->translatedFormat(
                        'd F Y'
                    );
        } elseif (
            $tanggalAkhir
        ) {
            $periodeText =
                'Sampai '
                .
                Carbon::parse(
                    $tanggalAkhir
                )
                    ->locale('id')
                    ->translatedFormat(
                        'd F Y'
                    );
        } else {
            $periodeText =
                'Semua Periode';
        }


        /*
        |--------------------------------------------------------------------------
        | TAHUN JUDUL
        |--------------------------------------------------------------------------
        */

        $tahunData =
            $penugasans

                ->map(
                    fn (
                        Penugasan $item
                    ) =>
                        $item
                            ->tanggal_mulai
                            ?->format(
                                'Y'
                            )
                )

                ->filter()

                ->unique()

                ->sort()

                ->values();


        $tahunJudul =
            match (
                $tahunData->count()
            ) {
                0 =>
                    'Semua Tahun',

                1 =>
                    $tahunData->first(),

                default =>
                    $tahunData->first()
                    .
                    '–'
                    .
                    $tahunData->last(),
            };


        /*
        |--------------------------------------------------------------------------
        | BUAT EXCEL
        |--------------------------------------------------------------------------
        */

        $excel =
            new Spreadsheet();


        $sheet =
            $excel->getActiveSheet();


        $sheet->setTitle(
            'Rekap Penugasan'
        );


        $sheet->setShowGridlines(
            false
        );


        $sheet
            ->getSheetView()
            ->setZoomScale(
                85
            );


        /*
        |--------------------------------------------------------------------------
        | DEFAULT FONT
        |--------------------------------------------------------------------------
        */

        $excel
            ->getDefaultStyle()
            ->getFont()
            ->setName(
                'Calibri'
            )
            ->setSize(
                10
            );


        /*
        |--------------------------------------------------------------------------
        | JUDUL LAPORAN
        |--------------------------------------------------------------------------
        */

        $sheet->mergeCells(
            'A1:I2'
        );


        $sheet->setCellValue(
            'A1',
            'REKAP SURAT TUGAS - TAHUN '
            .
            $tahunJudul
        );


        $sheet
            ->getStyle(
                'A1:I2'
            )
            ->applyFromArray([
                'font' => [
                    'bold' =>
                        true,

                    'size' =>
                        17,

                    'color' => [
                        'rgb' =>
                            'FFFFFF',
                    ],
                ],

                'fill' => [
                    'fillType' =>
                        Fill::FILL_SOLID,

                    'startColor' => [
                        'rgb' =>
                            '17365D',
                    ],
                ],

                'alignment' => [
                    'horizontal' =>
                        Alignment::HORIZONTAL_CENTER,

                    'vertical' =>
                        Alignment::VERTICAL_CENTER,
                ],
            ]);


        $sheet
            ->getRowDimension(
                1
            )
            ->setRowHeight(
                25
            );


        $sheet
            ->getRowDimension(
                2
            )
            ->setRowHeight(
                25
            );


        /*
        |--------------------------------------------------------------------------
        | INFORMASI PERIODE
        |--------------------------------------------------------------------------
        */

        $sheet->mergeCells(
            'A3:I3'
        );


        $sheet->setCellValue(
            'A3',

            'Periode: '
            .
            $periodeText

            .
            '   |   Jumlah Penugasan: '
            .
            $penugasans->count()

            .
            '   |   Diekspor: '
            .
            now()->format(
                'd/m/Y H:i'
            )
        );


        $sheet
            ->getStyle(
                'A3:I3'
            )
            ->applyFromArray([
                'font' => [
                    'size' =>
                        10,

                    'color' => [
                        'rgb' =>
                            '475569',
                    ],
                ],

                'fill' => [
                    'fillType' =>
                        Fill::FILL_SOLID,

                    'startColor' => [
                        'rgb' =>
                            'EAF0F7',
                    ],
                ],

                'alignment' => [
                    'horizontal' =>
                        Alignment::HORIZONTAL_CENTER,

                    'vertical' =>
                        Alignment::VERTICAL_CENTER,
                ],
            ]);


        $sheet
            ->getRowDimension(
                3
            )
            ->setRowHeight(
                28
            );


        $sheet
            ->getRowDimension(
                4
            )
            ->setRowHeight(
                12
            );


        $sheet
            ->getRowDimension(
                5
            )
            ->setRowHeight(
                8
            );


        /*
        |--------------------------------------------------------------------------
        | HEADER TABEL
        |--------------------------------------------------------------------------
        */

        $headers = [
            'No',
            'Nama Petugas',
            'NIP',
            'Layanan',
            'Detail Tugas',
            'Tempat',
            'Komoditi',
            'Tanggal Mulai',
            'Tanggal Selesai',
        ];


        foreach (
            $headers
            as
            $index =>
            $header
        ) {
            $column =
                chr(
                    65 +
                    $index
                );


            $sheet->setCellValue(
                $column
                .
                '6',

                $header
            );
        }


        /*
        |--------------------------------------------------------------------------
        | STYLE HEADER
        |--------------------------------------------------------------------------
        */

        $sheet
            ->getStyle(
                'A6:I6'
            )
            ->applyFromArray([
                'font' => [
                    'bold' =>
                        true,

                    'color' => [
                        'rgb' =>
                            'FFFFFF',
                    ],
                ],

                'fill' => [
                    'fillType' =>
                        Fill::FILL_SOLID,

                    'startColor' => [
                        'rgb' =>
                            '27558A',
                    ],
                ],

                'alignment' => [
                    'horizontal' =>
                        Alignment::HORIZONTAL_CENTER,

                    'vertical' =>
                        Alignment::VERTICAL_CENTER,

                    'wrapText' =>
                        true,
                ],

                'borders' => [
                    'allBorders' => [
                        'borderStyle' =>
                            Border::BORDER_THIN,

                        'color' => [
                            'rgb' =>
                                'C8D5E3',
                        ],
                    ],
                ],
            ]);


        $sheet
            ->getRowDimension(
                6
            )
            ->setRowHeight(
                34
            );


        /*
        |--------------------------------------------------------------------------
        | LEBAR KOLOM
        |--------------------------------------------------------------------------
        */

        foreach ([
            'A' => 8,
            'B' => 38,
            'C' => 35,
            'D' => 30,
            'E' => 55,
            'F' => 32,
            'G' => 28,
            'H' => 20,
            'I' => 20,
        ] as $column => $width) {
            $sheet
                ->getColumnDimension(
                    $column
                )
                ->setWidth(
                    $width
                );
        }


        /*
        |--------------------------------------------------------------------------
        | ISI DATA
        |--------------------------------------------------------------------------
        */

        $row = 7;


        foreach (
            $penugasans
            as
            $index =>
            $penugasan
        ) {
            /*
            |--------------------------------------------------------------------------
            | Nama Petugas
            |--------------------------------------------------------------------------
            */

            $names =
                $penugasan
                    ->petugas
                    ->pluck(
                        'name'
                    )
                    ->join(
                        "\n"
                    );


            /*
            |--------------------------------------------------------------------------
            | NIP
            |--------------------------------------------------------------------------
            */

            $nips =
                $penugasan
                    ->petugas
                    ->pluck(
                        'nip'
                    )
                    ->join(
                        "\n"
                    );


            /*
            |--------------------------------------------------------------------------
            | Nomor
            |--------------------------------------------------------------------------
            */

            $sheet->setCellValue(
                'A'
                .
                $row,

                $index +
                1
            );


            /*
            |--------------------------------------------------------------------------
            | Data Teks
            |--------------------------------------------------------------------------
            */

            foreach ([
                'B' => $names,

                'C' => $nips,

                'D' =>
                    $penugasan
                        ->layanan
                        ?->nama_layanan
                    ??
                    '',

                'E' =>
                    $penugasan
                        ->task_detail
                    ??
                    '',

                'F' =>
                    $penugasan
                        ->tempat
                    ??
                    '',

                'G' =>
                    $penugasan
                        ->komoditi
                    ??
                    '',
            ] as $column => $value) {
                /*
                |--------------------------------------------------------------------------
                | Paksa menjadi TEXT
                |--------------------------------------------------------------------------
                |
                | Supaya NIP tidak berubah format dan
                | teks yang dimulai "=" tidak menjadi formula.
                |--------------------------------------------------------------------------
                */

                $sheet->setCellValueExplicit(
                    $column
                    .
                    $row,

                    (string) $value,

                    DataType::TYPE_STRING
                );
            }


            /*
            |--------------------------------------------------------------------------
            | TANGGAL MULAI
            |--------------------------------------------------------------------------
            */

            if (
                $penugasan
                    ->tanggal_mulai
            ) {
                $sheet->setCellValue(
                    'H'
                    .
                    $row,

                    Date::PHPToExcel(
                        $penugasan
                            ->tanggal_mulai
                    )
                );
            }


            /*
            |--------------------------------------------------------------------------
            | TANGGAL SELESAI
            |--------------------------------------------------------------------------
            */

            if (
                $penugasan
                    ->tanggal_selesai
            ) {
                $sheet->setCellValue(
                    'I'
                    .
                    $row,

                    Date::PHPToExcel(
                        $penugasan
                            ->tanggal_selesai
                    )
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Tinggi Baris
            |--------------------------------------------------------------------------
            */

            $jumlahBaris =
                max(
                    1,

                    $penugasan
                        ->petugas
                        ->count(),

                    (int) ceil(
                        mb_strlen(
                            (string)
                            $penugasan
                                ->task_detail
                        )
                        /
                        58
                    ),

                    (int) ceil(
                        mb_strlen(
                            (string)
                            $penugasan
                                ->tempat
                        )
                        /
                        35
                    ),

                    (int) ceil(
                        mb_strlen(
                            (string)
                            $penugasan
                                ->komoditi
                        )
                        /
                        30
                    )
                );


            $sheet
                ->getRowDimension(
                    $row
                )
                ->setRowHeight(
                    min(
                        180,

                        max(
                            30,

                            $jumlahBaris
                            *
                            18
                            +
                            10
                        )
                    )
                );


            /*
            |--------------------------------------------------------------------------
            | Zebra Row
            |--------------------------------------------------------------------------
            */

            if (
                (
                    $row -
                    7
                )
                %
                2
                ===
                1
            ) {
                $sheet
                    ->getStyle(
                        'A'
                        .
                        $row
                        .
                        ':I'
                        .
                        $row
                    )
                    ->getFill()
                    ->setFillType(
                        Fill::FILL_SOLID
                    )
                    ->getStartColor()
                    ->setRGB(
                        'F3F7FB'
                    );
            }


            $row++;
        }


        /*
        |--------------------------------------------------------------------------
        | LAST ROW
        |--------------------------------------------------------------------------
        */

        $lastRow =
            max(
                6,
                $row -
                1
            );


        /*
        |--------------------------------------------------------------------------
        | STYLE DATA
        |--------------------------------------------------------------------------
        */

        if (
            $lastRow >
            6
        ) {
            $sheet
                ->getStyle(
                    'A7:I'
                    .
                    $lastRow
                )
                ->applyFromArray([
                    'alignment' => [
                        'vertical' =>
                            Alignment::VERTICAL_TOP,

                        'wrapText' =>
                            true,
                    ],

                    'borders' => [
                        'allBorders' => [
                            'borderStyle' =>
                                Border::BORDER_THIN,

                            'color' => [
                                'rgb' =>
                                    'E2E8F0',
                            ],
                        ],
                    ],
                ]);


            /*
            |--------------------------------------------------------------------------
            | Format Tanggal
            |--------------------------------------------------------------------------
            */

            $sheet
                ->getStyle(
                    'H7:I'
                    .
                    $lastRow
                )
                ->getNumberFormat()
                ->setFormatCode(
                    'dd/mm/yyyy'
                );


            /*
            |--------------------------------------------------------------------------
            | Nomor Tengah
            |--------------------------------------------------------------------------
            */

            $sheet
                ->getStyle(
                    'A7:A'
                    .
                    $lastRow
                )
                ->getAlignment()
                ->setHorizontal(
                    Alignment::HORIZONTAL_CENTER
                );


            /*
            |--------------------------------------------------------------------------
            | Tanggal Tengah
            |--------------------------------------------------------------------------
            */

            $sheet
                ->getStyle(
                    'H7:I'
                    .
                    $lastRow
                )
                ->getAlignment()
                ->setHorizontal(
                    Alignment::HORIZONTAL_CENTER
                );
        } else {
            /*
            |--------------------------------------------------------------------------
            | Tidak Ada Data
            |--------------------------------------------------------------------------
            */

            $sheet->mergeCells(
                'A7:I7'
            );


            $sheet->setCellValue(
                'A7',
                'Tidak ada penugasan sesuai filter.'
            );


            $sheet
                ->getRowDimension(
                    7
                )
                ->setRowHeight(
                    28
                );
        }


        /*
        |--------------------------------------------------------------------------
        | FREEZE HEADER
        |--------------------------------------------------------------------------
        */

        $sheet->freezePane(
            'A7'
        );


        /*
        |--------------------------------------------------------------------------
        | AUTO FILTER
        |--------------------------------------------------------------------------
        */

        $sheet->setAutoFilter(
            'A6:I'
            .
            $lastRow
        );


        /*
        |--------------------------------------------------------------------------
        | PAGE SETUP
        |--------------------------------------------------------------------------
        */

        $sheet
            ->getPageSetup()
            ->setOrientation(
                PageSetup::ORIENTATION_LANDSCAPE
            );


        $sheet
            ->getPageSetup()
            ->setFitToWidth(
                1
            );


        $sheet
            ->getPageSetup()
            ->setFitToHeight(
                0
            );


        /*
        |--------------------------------------------------------------------------
        | FILE TEMPORARY
        |--------------------------------------------------------------------------
        */

        $file =
            tempnam(
                sys_get_temp_dir(),
                'penugasan_'
            );


        abort_unless(
            $file !== false,
            500,
            'Gagal menyiapkan file Excel.'
        );


        /*
        |--------------------------------------------------------------------------
        | SIMPAN EXCEL
        |--------------------------------------------------------------------------
        */

        try {
            (
                new Xlsx(
                    $excel
                )
            )->save(
                $file
            );
        } catch (
            \Throwable $error
        ) {
            @unlink(
                $file
            );

            throw $error;
        } finally {
            $excel
                ->disconnectWorksheets();
        }


        /*
        |--------------------------------------------------------------------------
        | DOWNLOAD
        |--------------------------------------------------------------------------
        */

        return response()
            ->download(
                $file,

                'rekap-surat-tugas-'
                .
                now()->format(
                    'Ymd-His'
                )
                .
                '.xlsx',

                [
                    'Content-Type' =>
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ]
            )
            ->deleteFileAfterSend(
                true
            );
    }
}
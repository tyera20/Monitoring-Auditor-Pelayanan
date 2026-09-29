@extends('layouts.app', ['title' => 'Dashboard'])
@section('content')
<style>
    .design-dashboard {
        --dash-card: #ffffff;
        --dash-text: #0f172a;
        --dash-muted: #64748b;
        --dash-border: #e2e8f0;
        --dash-green: #059669;
        color: var(--dash-text);
        font-family:
            Inter,
            system-ui,
            -apple-system,
            "Segoe UI",
            sans-serif;
    }
    .design-dashboard * {
        box-sizing: border-box;
    }
    .dash-heading {
        margin-bottom: 20px;
    }
    .dash-heading h1 {
        margin: 0;
        color: var(--dash-text);
        font-size: 22px;
        font-weight: 700;
        line-height: 1.3;
    }
    .dash-card,
    .dash-stat {
        background: var(--dash-card);
        border: 1px solid var(--dash-border);
        border-radius: 10px;
    }
    .dash-filter-card {
        margin-bottom: 16px;
    }
    .dash-filter {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
        padding: 14px;
    }
    .dash-filter input {
        height: 40px;
        min-width: 0;
        padding: 8px 12px;
        border: 1px solid var(--dash-border);
        border-radius: 9px;
        background: #f4f6fa;
        color: var(--dash-text);
        font: inherit;
        font-size: 13px;
    }
    .dash-filter input[type="date"] {
        width: 170px;
    }
    .dash-filter input[name="search"] {
        flex: 1;
        min-width: 210px;
    }
    .dash-filter-separator {
        color: var(--dash-muted);
        font-size: 13px;
    }
    .dash-button {
        display: inline-flex;
        height: 40px;
        align-items: center;
        justify-content: center;
        padding: 0 16px;
        border: 1px solid var(--dash-border);
        border-radius: 9px;
        background: #ffffff;
        color: var(--dash-text);
        font: inherit;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
    }
    .dash-button:hover {
        background: #f1f5f9;
    }
    .dash-button-primary {
        border-color: var(--dash-green);
        background: var(--dash-green);
        color: #ffffff;
    }
    .dash-button-primary:hover {
        background: #047857;
    }
    .dash-stats {
        display: grid;
        grid-template-columns:
            repeat(
                4,
                minmax(0, 1fr)
            );
        gap: 14px;
        margin-bottom: 16px;
    }
    .dash-stat {
        display: block;
        min-width: 0;
        padding: 14px 16px;
        color: inherit;
        text-decoration: none;
    }
    a.dash-stat:hover {
        border-color: var(--dash-green);
    }
    .dash-stat span {
        display: block;
        color: var(--dash-muted);
        font-size: 12px;
    }
    .dash-stat strong {
        display: block;
        margin-top: 4px;
        font-size: 24px;
        font-weight: 700;
        line-height: 1.3;
        overflow-wrap: anywhere;
    }
    .dash-stat strong.staff-name {
        font-size: 18px;
    }
    .dash-chart-card {
        margin-bottom: 16px;
        overflow: hidden;
    }
    .dash-chart-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 8px;
        padding: 14px 16px;
        border-bottom: 1px solid var(--dash-border);
    }
    .dash-chart-header h2 {
        margin: 0;
        color: var(--dash-text);
        font-size: 18px;
        font-weight: 600;
        line-height: 1.3;
    }
    .dash-chart-header span {
        color: var(--dash-muted);
        font-size: 11px;
    }
    .dash-chart-body {
        width: 100%;
        overflow-x: auto;
        padding:
            16px
            16px
            12px;
    }
    .dash-chart {
        width: 100%;
        min-width: 650px;
    }
    #layananCharacteristicsChart {
        cursor: pointer;
    }
    .dash-empty {
        display: grid;
        min-height: 220px;
        place-items: center;
        color: var(--dash-muted);
        font-size: 12px;
        text-align: center;
    }
    @media (max-width: 1000px) {
        .dash-stats {
            grid-template-columns:
                repeat(
                    2,
                    minmax(0, 1fr)
                );
        }
    }
    @media (max-width: 600px) {
        .dash-heading h1 {
            font-size: 20px;
        }
        .dash-stats {
            grid-template-columns: 1fr;
        }
        .dash-filter input[name="search"] {
            flex-basis: 100%;
        }
        .dash-filter .dash-button {
            flex: 1;
        }
        .dash-chart-header h2 {
            font-size: 16px;
        }
        .dash-chart-header span {
            font-size: 10px;
        }
        .dash-chart-header {
            padding: 12px 14px;
        }
        .dash-chart-body {
            padding:
                12px
                10px
                10px;
        }
        .dash-chart {
            min-width: 600px;
        }
    }
</style>
<div class="design-dashboard">
    <div class="dash-heading">
        <h1>
            Dashboard Frekuensi Penugasan
        </h1>
    </div>
    <div class="dash-card dash-filter-card">
        <form
            action="{{ route('dashboard') }}"
            method="GET"
            class="dash-filter"
        >
            <input
                type="date"
                name="from"
                value="{{ $from }}"
                aria-label="Tanggal awal"
            >
            <span class="dash-filter-separator">
                s/d
            </span>
            <input
                type="date"
                name="to"
                value="{{ $to }}"
                aria-label="Tanggal akhir"
            >
            <input
                type="search"
                name="search"
                value="{{ $search }}"
                placeholder="Cari nama atau layanan..."
                aria-label="Cari nama atau layanan"
            >
            <button
                type="submit"
                class="dash-button dash-button-primary"
            >
                Cari
            </button>
            <a
                href="{{ route('dashboard', ['reset' => 1]) }}"
                class="dash-button"
            >
                Reset
            </a>
        </form>
    </div>
    <div class="dash-stats">
        <a
            href="{{ route('penugasan.index') }}"
            class="dash-stat"
        >
            <span>
                Total Penugasan
            </span>
            <strong>
                {{ $totalAssignments }}
            </strong>
        </a>
        <a
            href="{{ route('petugas.index') }}"
            class="dash-stat"
        >
            <span>
                Total Petugas Aktif
            </span>
            <strong>
                {{ $totalPetugasInPeriod }}
            </strong>
        </a>
        <div class="dash-stat">
            <span>
                Rata-rata Penugasan per Petugas
            </span>
            <strong>
                {{ $averageAssignments }}
            </strong>
        </div>
        <div class="dash-stat">
            <span>
                Petugas Paling Aktif
            </span>
            <strong class="staff-name">
                {{ $mostActiveStaff }}
            </strong>
        </div>
    </div>
    <section class="dash-card dash-chart-card">
        <div class="dash-chart-header">
            <h2>
                Beban Kerja per Layanan
            </h2>
            <span id="layananFilterHint">
                Klik batang untuk memfilter Top 10 petugas
            </span>
        </div>
        <div class="dash-chart-body">
            <div
                id="layananCharacteristicsChart"
                class="dash-chart"
            ></div>
        </div>
    </section>
    <section class="dash-card dash-chart-card">
        <div class="dash-chart-header">
            <h2 id="topPetugasTitle">
                Top 10 Petugas Paling Aktif &amp; Distribusi Bidang Kerja
            </h2>
        </div>
        <div class="dash-chart-body">
            <div
                id="topPetugasDistributionChart"
                class="dash-chart"
            ></div>
        </div>
    </section>
</div>
<script>
window.addEventListener(
    'load',
    () => {
        if (!window.ApexCharts) {
            return;
        }
        const layananCategories =
            @json(
                $layananCharacteristicsCategories
            );
        const layananSeries =
            @json(
                $layananCharacteristicsSeries
            );
        const petugasCategories =
            @json(
                $topPetugasDistributionCategories
            );
        const petugasSeries =
            @json(
                $topPetugasDistributionSeries
            );
        let selectedLayanan = null;
        const layananRows =
            layananCategories
                .map(
                    (name, index) => ({
                        name,
                        total:
                            layananSeries.reduce(
                                (
                                    sum,
                                    item
                                ) =>
                                    sum +
                                    Number(
                                        item.data?.[
                                            index
                                        ] ?? 0
                                    ),
                                0
                            )
                    })
                )
                .sort(
                    (a, b) =>
                        b.total -
                        a.total ||
                        a.name.localeCompare(
                            b.name
                        )
                );
        const layananNames =
            layananRows.map(
                item =>
                    item.name
            );
        const layananTotals = [
            {
                name:
                    'Jumlah Penugasan',
                data:
                    layananRows.map(
                        item =>
                            item.total
                    )
            }
        ];
        const palette = [
            '#94a3b8',
            '#f59e0b',
            '#ec4899',
            '#14b8a6',
            '#22c55e',
            '#3b82f6',
            '#8b5cf6',
            '#06b6d4',
            '#84cc16',
            '#f97316'
        ];
        const colorForLayanan =
            (name) => {
                const index =
                    petugasSeries.findIndex(
                        item =>
                            item.name === name
                    );
                return palette[
                    (
                        index < 0
                            ? 0
                            : index
                    )
                    %
                    palette.length
                ];
            };
        const integerXAxis =
            (
                categories,
                series
            ) => {
                const highest =
                    categories.reduce(
                        (
                            maximum,
                            _,
                            index
                        ) => {
                            const total =
                                series.reduce(
                                    (
                                        sum,
                                        item
                                    ) =>
                                        sum +
                                        Number(
                                            item.data?.[
                                                index
                                            ] ?? 0
                                        ),
                                    0
                                );
                            return Math.max(
                                maximum,
                                total
                            );
                        },
                        0
                    );
                const maximum =
                    Math.max(
                        3,
                        Math.ceil(
                            highest
                        )
                    );
                return {
                    categories,
                    min: 0,
                    max: maximum,
                    tickAmount:
                        maximum,
                    stepSize: 1,
                    decimalsInFloat: 0,
                    title: {
                        text:
                            'Jumlah Penugasan',
                        style: {
                            fontSize:
                                '11px',
                            fontWeight:
                                500,
                            color:
                                '#64748b'
                        }
                    },
                    labels: {
                        style: {
                            fontSize:
                                '11px',
                            colors:
                                '#64748b'
                        },
                        formatter:
                            (value) => {
                                const number =
                                    Number(
                                        value
                                    );
                                if (
                                    !Number.isFinite(
                                        number
                                    )
                                ) {
                                    return '';
                                }
                                const rounded =
                                    Math.round(
                                        number
                                    );
                                return (
                                    Math.abs(
                                        number -
                                        rounded
                                    )
                                    <
                                    0.000001
                                )
                                    ? String(
                                        rounded
                                    )
                                    : '';
                            }
                    }
                };
            };
        const commonConfig = {
            chart: {
                type:
                    'bar',
                stacked:
                    true,
                toolbar: {
                    show:
                        false
                },
                fontFamily:
                    'Inter, system-ui, sans-serif',
                parentHeightOffset:
                    0
            },
            plotOptions: {
                bar: {
                    horizontal:
                        true,
                    borderRadius:
                        4,
                    barHeight:
                        '65%'
                }
            },
            dataLabels: {
                enabled:
                    true,
                style: {
                    fontSize:
                        '11px',
                    fontWeight:
                        600
                },
                formatter:
                    (value) => (
                        Number(
                            value
                        ) > 0
                            ?
                            String(
                                Math.round(
                                    Number(
                                        value
                                    )
                                )
                            )
                            :
                            ''
                    )
            },
            grid: {
                borderColor:
                    '#e2e8f0',
                strokeDashArray:
                    4,
                padding: {
                    left:
                        8,
                    right:
                        14,
                    top:
                        4,
                    bottom:
                        4
                }
            },
            tooltip: {
                y: {
                    formatter:
                        (value) =>
                            `${value} penugasan`
                }
            }
        };
        const allStaffRows =
            petugasCategories.map(
                (name, staffIndex) => {
                    const layanan = {};
                    petugasSeries.forEach(
                        series => {
                            layanan[
                                series.name
                            ] =
                                Number(
                                    series.data?.[
                                        staffIndex
                                    ] ?? 0
                                );
                        }
                    );
                    const total =
                        Object.values(
                            layanan
                        )
                        .reduce(
                            (
                                sum,
                                value
                            ) =>
                                sum +
                                Number(
                                    value
                                ),
                            0
                        );
                    return {
                        name,
                        total,
                        layanan
                    };
                }
            );
        const buildTopPetugas =
            (
                namaLayanan = null
            ) => {
                const rows =
                    allStaffRows
                        .map(
                            item => ({
                                ...item,
                                selectedTotal:
                                    namaLayanan
                                        ? Number(
                                            item
                                                .layanan[
                                                    namaLayanan
                                                ] ?? 0
                                        )
                                        : item.total
                            })
                        )
                        .filter(
                            item =>
                                item.selectedTotal > 0
                        )
                        .sort(
                            (a, b) => {
                                if (
                                    b.selectedTotal !==
                                    a.selectedTotal
                                ) {
                                    return (
                                        b.selectedTotal -
                                        a.selectedTotal
                                    );
                                }
                                if (
                                    b.total !==
                                    a.total
                                ) {
                                    return (
                                        b.total -
                                        a.total
                                    );
                                }
                                return (
                                    a.name.localeCompare(
                                        b.name
                                    )
                                );
                            }
                        )
                        .slice(
                            0,
                            10
                        );
                const categories =
                    rows.map(
                        item =>
                            item.name
                    );
                let series;
                if (
                    namaLayanan
                ) {
                    series = [
                        {
                            name:
                                namaLayanan,
                            data:
                                rows.map(
                                    item =>
                                        Number(
                                            item
                                                .layanan[
                                                    namaLayanan
                                                ] ?? 0
                                        )
                                )
                        }
                    ];
                } else {
                    series =
                        petugasSeries
                            .map(
                                layananItem => ({
                                    name:
                                        layananItem.name,
                                    data:
                                        rows.map(
                                            staff =>
                                                Number(
                                                    staff
                                                        .layanan[
                                                            layananItem.name
                                                        ] ?? 0
                                                )
                                        )
                                })
                            )
                            .filter(
                                layananItem =>
                                    layananItem.data.some(
                                        value =>
                                            Number(
                                                value
                                            ) > 0
                                    )
                            );
                }
                return {
                    rows,
                    categories,
                    series
                };
            };
        const petugasElement =
            document.querySelector(
                '#topPetugasDistributionChart'
            );
        const topPetugasTitle =
            document.querySelector(
                '#topPetugasTitle'
            );
        const layananFilterHint =
            document.querySelector(
                '#layananFilterHint'
            );
        const initialPetugas =
            buildTopPetugas();
        let petugasChart =
            null;
        if (
            initialPetugas
                .categories
                .length === 0
        ) {
            petugasElement.innerHTML = `
                <div class="dash-empty">
                    Belum ada petugas
                    pada periode ini.
                </div>
            `;
        } else {
            petugasChart =
                new window.ApexCharts(
                    petugasElement,
                    {
                        ...commonConfig,
                        chart: {
                            ...commonConfig.chart,
                            height:
                                Math.max(
                                    420,
                                    initialPetugas
                                        .categories
                                        .length
                                    *
                                    42
                                    +
                                    140
                                )
                        },
                        series:
                            initialPetugas.series,
                        colors:
                            initialPetugas
                                .series
                                .map(
                                    item =>
                                        colorForLayanan(
                                            item.name
                                        )
                                ),
                        legend: {
                            show:
                                true,
                            position:
                                'bottom',
                            horizontalAlign:
                                'left',
                            fontSize:
                                '11px',
                            labels: {
                                colors:
                                    '#475569'
                            }
                        },
                        xaxis:
                            integerXAxis(
                                initialPetugas
                                    .categories,
                                initialPetugas
                                    .series
                            ),
                        yaxis: {
                            labels: {
                                minWidth:
                                    125,
                                maxWidth:
                                    180,
                                style: {
                                    fontSize:
                                        '11px',
                                    fontWeight:
                                        400,
                                    colors:
                                        '#475569'
                                },
                                offsetX:
                                    -2
                            }
                        }
                    }
                );
            petugasChart.render();
        }
        const updatePetugasChart =
            (
                namaLayanan = null
            ) => {
                if (
                    !petugasChart
                ) {
                    return;
                }
                const result =
                    buildTopPetugas(
                        namaLayanan
                    );
                if (
                    namaLayanan
                ) {
                    topPetugasTitle.textContent =
                        `Top 10 Petugas – ${namaLayanan}`;
                    layananFilterHint.textContent =
                        `Filter aktif: ${namaLayanan} • Klik batang yang sama untuk reset`;
                } else {
                    topPetugasTitle.textContent =
                        'Top 10 Petugas Paling Aktif & Distribusi Bidang Kerja';
                    layananFilterHint.textContent =
                        'Klik batang untuk memfilter Top 10 petugas';
                }
                petugasChart.updateOptions(
                    {
                        chart: {
                            height:
                                Math.max(
                                    420,
                                    result
                                        .categories
                                        .length
                                    *
                                    42
                                    +
                                    140
                                )
                        },
                        xaxis:
                            integerXAxis(
                                result
                                    .categories,
                                result
                                    .series
                            ),
                        colors:
                            result
                                .series
                                .map(
                                    item =>
                                        colorForLayanan(
                                            item.name
                                        )
                                ),
                        legend: {
                            show:
                                !namaLayanan,
                            position:
                                'bottom',
                            horizontalAlign:
                                'left'
                        }
                    },
                    false,
                    true
                );
                petugasChart.updateSeries(
                    result.series,
                    true
                );
            };
        const layananElement =
            document.querySelector(
                '#layananCharacteristicsChart'
            );
        if (
            layananRows.length === 0
        ) {
            layananElement.innerHTML = `
                <div class="dash-empty">
                    Belum ada penugasan
                    pada periode ini.
                </div>
            `;
        } else {
            const layananChart =
                new window.ApexCharts(
                    layananElement,
                    {
                        ...commonConfig,
                        chart: {
                            ...commonConfig.chart,
                            height:
                                Math.max(
                                    320,
                                    layananRows.length
                                    *
                                    40
                                    +
                                    110
                                ),
                            events: {
                                dataPointSelection:
                                    (
                                        _event,
                                        _chart,
                                        config
                                    ) => {
                                        const namaLayanan =
                                            layananNames[
                                                config
                                                    .dataPointIndex
                                            ];
                                        if (
                                            !namaLayanan
                                        ) {
                                            return;
                                        }
                                        selectedLayanan =
                                            (
                                                selectedLayanan
                                                ===
                                                namaLayanan
                                            )
                                                ? null
                                                : namaLayanan;
                                        updatePetugasChart(
                                            selectedLayanan
                                        );
                                    }
                            }
                        },
                        plotOptions: {
                            bar: {
                                horizontal:
                                    true,
                                distributed:
                                    true,
                                borderRadius:
                                    4,
                                barHeight:
                                    '65%'
                            }
                        },
                        series:
                            layananTotals,
                        colors:
                            layananNames.map(
                                colorForLayanan
                            ),
                        legend: {
                            show:
                                false
                        },
                        xaxis:
                            integerXAxis(
                                layananNames,
                                layananTotals
                            ),
                        yaxis: {
                            labels: {
                                minWidth:
                                    125,
                                maxWidth:
                                    180,
                                style: {
                                    fontSize:
                                        '11px',
                                    fontWeight:
                                        400,
                                    colors:
                                        '#475569'
                                },
                                offsetX:
                                    -2
                            }
                        }
                    }
                );
            layananChart.render();
        }
    }
);
</script>
@endsection

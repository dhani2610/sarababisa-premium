@section('title')
    Dashboard Kepala Toko
@endsection

<x-toko-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        <div class="mb-6 flex gap-4 items-end">

            <div>
                <label class="block text-sm font-medium mb-1">Dari</label>
                <input id="start" type="month" class="border rounded px-3 py-2">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Sampai</label>
                <input id="end" type="month" class="border rounded px-3 py-2">
            </div>

            <button id="btnApply"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Apply
            </button>

        </div>

        <div class="grid  gap-6 mt-4">
            <div id="chart-pencapaian" style="height: 400px;width:100%"></div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
            <div id="chart-anggaran" style="height: 380px;"></div>
            <div id="chart-omset" style="height: 380px;"></div>

            <div id="chart-omset-service" style="height: 380px;"></div>
            <div id="chart-omset-produk" style="height: 380px;"></div>

            <div id="chart-profit" style="height: 380px;"></div>
            <div id="chart-profit-service" style="height: 380px;"></div>

            <div id="chart-profit-produk" style="height: 380px;"></div>
            <div id="chart-pengeluaran" style="height: 380px;"></div>

            <div id="chart-insiden" style="height: 380px;"></div>
            <div id="chart-refund" style="height: 380px;"></div>
        </div>

    </div>
</x-toko-layout>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://code.highcharts.com/highcharts.js"></script>

<script>
$(document).ready(function () {

    // Ambil tahun berjalan
    let yearNow = new Date().getFullYear();

    // Start = Januari tahun ini
    let startDefault = `${yearNow}-01`;

    // End = bulan berjalan (format YYYY-MM)
    let monthNow = new Date().toISOString().slice(0, 7);

    // Set ke input kalau belum ada value
    $("#start").val(startDefault);
    $("#end").val(monthNow);


   function renderColumnChart(id, title, categories, categoriesIndo, series, isPercent = false) {

        Highcharts.chart(id, {
            chart: { type: 'column' },
            title: { text: title },

            xAxis: { categories: categoriesIndo },

            yAxis: {
                title: { text: isPercent ? 'Persentase (%)' : 'Nominal (Rp)' },
                labels: {
                    formatter: function () {
                        return isPercent
                            ? this.value + '%'
                            : formatRupiahShort(this.value);
                    }
                }
            },

            tooltip: {
                shared: true,
                formatter: function () {
                    let s = `<b>${this.x}</b><br>`;
                    this.points.forEach(p => {
                        if (isPercent) {
                            s += `${p.series.name}: <b>${p.y}%</b><br>`;
                        } else {
                            s += `${p.series.name}: <b>${formatRupiahShort(p.y)}</b><br>`;
                        }
                    });
                    return s;
                }
            },

            plotOptions: {
                series: {
                    dataLabels: {
                        enabled: true,
                        formatter: function () {
                            return isPercent
                                ? this.y + '%'
                                : formatRupiahShort(this.y);
                        },
                        style: { fontSize: "11px", fontWeight: "bold" }
                    }
                }
            },

            exporting: { enabled: false },
            credits: { enabled: false },

            series
        });

    }


    // Set default bulan berjalan
    let current = new Date().toISOString().slice(0, 7);
    if (!$("#start").val()) $("#start").val(current);
    if (!$("#end").val()) $("#end").val(current);

    // Apply filter
    $("#btnApply").click(function () {
        loadDashboard();
    });

    // Format angka ke Rupiah singkat (Ribuan, Jutaan, Miliar)
    function formatRupiahShort(value) {
        if (value >= 1_000_000_000)
            return "Rp " + (value / 1_000_000_000).toFixed(1).replace('.0','') + " M";

        if (value >= 1_000_000)
            return "Rp " + (value / 1_000_000).toFixed(1).replace('.0','') + " Jt";

        if (value >= 1_000)
            return "Rp " + (value / 1_000).toFixed(1).replace('.0','') + " Rb";

        return "Rp " + value;
    }



    function loadDashboard() {
        $.ajax({
            url: "/dashboard-cabang-json",
            method: "GET",
            data: {
                start_month_year: $("#start").val(),
                end_month_year: $("#end").val()
            },
            success: function (res) {

                const range = res.range;
                const rangeIndo = res.rangeIndo;

                // ===================================
                // 1. CHART PENCAPAIAN
                // ===================================
                let seriesPencapaian = [];
                Object.keys(res.dataPencapaian).forEach(cabang => {
                    seriesPencapaian.push({
                        name: cabang,
                        data: range.map(r => res.dataAnggaran[cabang][r] || 0)
                    });
                });
                renderColumnChart("chart-pencapaian", "Pencapaian", range, rangeIndo, seriesPencapaian, true);

                // ===================================
                // 1. CHART ANGGARAN
                // ===================================
                let seriesAnggaran = [];
                Object.keys(res.dataAnggaran).forEach(cabang => {
                    seriesAnggaran.push({
                        name: cabang,
                        data: range.map(r => res.dataAnggaran[cabang][r] || 0)
                    });
                });
                renderColumnChart("chart-anggaran", "Anggaran ", range,rangeIndo, seriesAnggaran);


                // ===================================
                // 2. CHART OMSET
                // ===================================
                let seriesOmset = [];
                Object.keys(res.dataOmset).forEach(cabang => {
                    seriesOmset.push({
                        name: cabang,
                        data: range.map(r => res.dataOmset[cabang][r]?.omset || 0)
                    });
                });
                renderColumnChart("chart-omset", "Omset ", range,rangeIndo, seriesOmset);


                // ===================================
                // 3. CHART OMSET SERVICE
                // ===================================
                let seriesOmsetService = [];
                Object.keys(res.dataOmsetService).forEach(cabang => {
                    seriesOmsetService.push({
                        name: cabang,
                        data: range.map(r => res.dataOmsetService[cabang][r]?.omset || 0)
                    });
                });
                renderColumnChart("chart-omset-service", "Omset Service ", range,rangeIndo, seriesOmsetService);

                // ===================================
                // 4. CHART OMSET PRODUK (CABANG + KATEGORI)
                // ===================================

                let kategoriList = ["Handphone", "Sparepart", "Aksesoris", "Tools"];

                // X-Axis hanya nama cabang
                let xCategoriesOmsetProduk = Object.keys(res.dataOmsetProduk);

                // Series per kategori → tiap kategori punya array berisi total omset
                let seriesOmsetProduk = kategoriList.map((katName, idxKat) => {
                    return {
                        name: katName,   // legend = kategori
                        data: Object.keys(res.dataOmsetProduk).map(cabang => {

                            // total omset kategori pada masing-masing cabang
                            let total = range.map(r =>
                                res.dataOmsetProduk[cabang][r]?.[idxKat + 1]?.total_omset || 0
                            ).reduce((a, b) => a + b, 0);

                            return total;
                        })
                    };
                });

              Highcharts.chart("chart-omset-produk", {
                chart: { type: "column" },
                title: { text: "Omset Produk " },
                xAxis: {
                    categories: xCategoriesOmsetProduk,
                    title: { text: "Cabang" }
                },
                yAxis: {
                    title: { text: "Rp" },
                    labels: {
                        formatter: function () {
                            return formatRupiahShort(this.value);
                        }
                    }
                },

                tooltip: {
                    shared: true,
                    formatter: function () {
                        let s = `<b>${this.x}</b><br>`;
                        this.points.forEach(p => {
                            s += `${p.series.name}: <b>${formatRupiahShort(p.y)}</b><br>`;
                        });
                        return s;
                    }
                },

                plotOptions: {
                    column: { grouping: true },
                    series: {
                        dataLabels: {
                            enabled: true,
                            formatter: function () {
                                return formatRupiahShort(this.y);
                            },
                            style: { fontSize: "11px", fontWeight: "bold" }
                        }
                    }
                },

                exporting: { enabled: false },
                credits: { enabled: false },

                series: seriesOmsetProduk
            });



                // ===================================
                // 5. PROFIT
                // ===================================
                let seriesProfit = [];
                Object.keys(res.dataProfit).forEach(cabang => {
                    seriesProfit.push({
                        name: cabang,
                        data: range.map(r => res.dataProfit[cabang][r]?.profit || 0)
                    });
                });
                renderColumnChart("chart-profit", "Profit ", range,rangeIndo, seriesProfit);


                // ===================================
                // 6. PROFIT SERVICE
                // ===================================
                let seriesProfitService = [];
                Object.keys(res.dataProfitService).forEach(cabang => {
                    seriesProfitService.push({
                        name: cabang,
                        data: range.map(r => res.dataProfitService[cabang][r]?.omset || 0)
                    });
                });
                renderColumnChart("chart-profit-service", "Profit Service ", range,rangeIndo, seriesProfitService);


                // ===================================
                // 7. CHART PROFIT PRODUK (CABANG + KATEGORI)
                // ===================================

                // let kategoriList = ["Handphone", "Sparepart", "Aksesoris", "Tools"];

                // X-Axis hanya nama cabang
                let xCategoriesProfitProduk = Object.keys(res.dataProdukProduk);

                // Series per kategori → tiap kategori punya array berisi total profit
                let seriesProfitProduk = kategoriList.map((katName, idxKat) => {
                    return {
                        name: katName,   // legend = kategori
                        data: Object.keys(res.dataProdukProduk).map(cabang => {

                            // total profit kategori
                            let total = range.map(r =>
                                res.dataProdukProduk[cabang][r]?.[idxKat + 1]?.total_profit || 0
                            ).reduce((a, b) => a + b, 0);

                            return total;
                        })
                    };
                });

                Highcharts.chart("chart-profit-produk", {
                    chart: { type: "column" },
                    title: { text: "Profit Produk" },
                    xAxis: {
                        categories: xCategoriesProfitProduk,
                        title: { text: "Cabang" }
                    },
                    yAxis: {
                        title: { text: "Rp" },
                        labels: {
                            formatter: function () {
                                return formatRupiahShort(this.value);
                            }
                        }
                    },

                    tooltip: {
                        shared: true,
                        formatter: function () {
                            let s = `<b>${this.x}</b><br>`;
                            this.points.forEach(p => {
                                s += `${p.series.name}: <b>${formatRupiahShort(p.y)}</b><br>`;
                            });
                            return s;
                        }
                    },

                    plotOptions: {
                        column: { grouping: true },
                        series: {
                            dataLabels: {
                                enabled: true,
                                formatter: function () {
                                    return formatRupiahShort(this.y);
                                },
                                style: { fontSize: "11px", fontWeight: "bold" }
                            }
                        }
                    },

                    exporting: { enabled: false },
                    credits: { enabled: false },

                    series: seriesProfitProduk
                });



                // ===================================
                // 8. PENGELUARAN
                // ===================================
                let seriesPengeluaran = [];
                Object.keys(res.dataPengeluaran).forEach(cabang => {
                    seriesPengeluaran.push({
                        name: cabang,
                        data: range.map(r => res.dataPengeluaran[cabang][r] || 0)
                    });
                });
                renderColumnChart("chart-pengeluaran", "Pengeluaran ", range,rangeIndo, seriesPengeluaran);


                // ===================================
                // 9. INSIDEN
                // ===================================
                let seriesInsiden = [];
                Object.keys(res.dataInsiden).forEach(cabang => {
                    seriesInsiden.push({
                        name: cabang,
                        data: range.map(r => res.dataInsiden[cabang][r] || 0)
                    });
                });
                renderColumnChart("chart-insiden", "Insiden ", range,rangeIndo, seriesInsiden);


                // ===================================
                // 10. REFUND
                // ===================================
                let seriesRefund = [];
                Object.keys(res.dataRefund).forEach(cabang => {
                    seriesRefund.push({
                        name: cabang,
                        data: range.map(r => res.dataRefund[cabang][r] || 0)
                    });
                });
                renderColumnChart("chart-refund", "Pengembalian Dana ", range,rangeIndo, seriesRefund);

            }
        });
    }

    loadDashboard();

});
</script>

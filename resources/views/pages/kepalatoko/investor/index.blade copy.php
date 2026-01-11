@section('title')
    Pembagian Hasil Investor
@endsection

<x-toko-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        <div class="sm:flex sm:justify-between sm:items-center mb-5">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Pembagian Hasil</h1>
            </div>
        </div>

        <div class="bg-white shadow-lg rounded-sm border border-slate-200 mb-6 p-4">
            <h3 class="font-semibold text-slate-800 mb-3">Filter Periode</h3>

            <div class="flex items-end gap-4">

                <div>
                    <label class="block text-sm font-medium mb-1" for="start_month">Dari Bulan</label>
                    <input id="start_month" type="month" class="form-input border-slate-300 rounded-md" />
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1" for="end_month">Sampai Bulan</label>
                    <input id="end_month" type="month" class="form-input border-slate-300 rounded-md" />
                </div>

                <div class="mb-[2px]"> <button id="btn-filter" class="btn bg-indigo-500 hover:bg-indigo-600 text-white whitespace-nowrap">
                        <svg class="w-4 h-4 fill-current opacity-50 shrink-0 mr-2" viewBox="0 0 16 16">
                            <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                        </svg>
                        Tampilkan Data
                    </button>
                </div>

            </div>
        </div>

        <div class="bg-white shadow-lg rounded-sm border border-slate-200">
            <div class="overflow-x-auto p-4">
                <table id="investor-table" class="table-auto w-full border-collapse border border-slate-300">
                    <thead class="text-xs font-bold uppercase text-slate-700 bg-slate-100 border-b border-slate-300">
                        <tr>
                            <th class="px-2 py-3 border border-slate-300 text-center">Bulan</th>
                            <th class="px-2 py-3 border border-slate-300 text-center">Jumlah<br>Transaksi</th>
                            <th class="px-2 py-3 border border-slate-300 text-right">Omset</th>
                            <th class="px-2 py-3 border border-slate-300 text-right">Pengeluaran<br>Modal Sparepart</th>
                            <th class="px-2 py-3 border border-slate-300 text-right">Profit</th>
                            <th class="px-2 py-3 border border-slate-300 text-right">Pengeluaran<br>Ops Toko & Refund</th>
                            <th class="px-2 py-3 border border-slate-300 text-right">Insiden</th>
                            <th class="px-2 py-3 border border-slate-300 text-right">Sisa Profit</th>
                            <th class="px-2 py-3 border border-slate-300 text-right">Target</th>
                            <th class="px-2 py-3 border border-slate-300 text-center">Result</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-200 text-slate-700">
                        </tbody>

                    <tfoot class="text-sm font-bold bg-slate-50 border-t border-slate-300">
                        <tr class="bg-yellow-50">
                            <td class="px-2 py-3 border border-slate-300">Total</td>
                            <td class="px-2 py-3 border border-slate-300 text-center" id="sum-transaksi">-</td>
                            <td class="px-2 py-3 border border-slate-300 text-right" id="sum-omset">-</td>
                            <td class="px-2 py-3 border border-slate-300 text-right" id="sum-modal">-</td>
                            <td class="px-2 py-3 border border-slate-300 text-right" id="sum-profit">-</td>
                            <td class="px-2 py-3 border border-slate-300 text-right" id="sum-operasional">-</td>
                            <td class="px-2 py-3 border border-slate-300 text-right" id="sum-insiden">-</td>
                            <td class="px-2 py-3 border border-slate-300 text-right bg-yellow-200 text-slate-900" id="sum-sisa-profit">-</td>
                            <td class="px-2 py-3 border border-slate-300 text-right"></td>
                            <td class="px-2 py-3 border border-slate-300 text-right"></td>
                        </tr>

                        <tr>
                            <td colspan="2" class="border-none"></td>
                            <td class="px-2 py-2 border border-slate-300 text-center bg-white">TOKO</td>
                            <td class="px-2 py-2 border border-slate-300 text-center bg-white">INVESTOR</td>
                            <td colspan="6" class="border-none"></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="border-none"></td>
                            <td class="px-2 py-1 border border-slate-300 text-center bg-white font-normal persen-toko">60%</td>
                            <td class="px-2 py-1 border border-slate-300 text-center bg-white font-normal persen-investor">40%</td>
                            <td colspan="6" class="border-none"></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="border-none"></td>
                            <td class="px-2 py-2 border border-slate-300 text-right bg-white" id="val-hf">-</td>
                            <td class="px-2 py-2 border border-slate-300 text-right bg-white" id="val-investor">-</td>
                            <td colspan="6" class="border-none"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>

    <style>
        /* Override border styles for table cells to look like Excel */
        #investor-table td, #investor-table th {
            border: 1px solid #cbd5e1; /* slate-300 */
        }
        .dataTables_processing {
            z-index: 50;
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            var table = $('#investor-table').DataTable({
                processing: true,
                serverSide: true,
                searching: false, // Matikan search box standar jika hanya ingin filter tanggal
                paging: false,    // Matikan paging agar terlihat seperti sheet Excel penuh
                info: false,
                ajax: {
                    url: "{{ route('investor.data') }}",
                    data: function (d) {
                        d.start_month = $('#start_month').val();
                        d.end_month = $('#end_month').val();
                    }
                },
                columns: [
                    { data: 'bulan', name: 'bulan', className: 'text-center' },
                    { data: 'transaksi', name: 'transaksi', className: 'text-center' },
                    { data: 'omset', name: 'omset', className: 'text-right' },
                    { data: 'modal', name: 'modal', className: 'text-right' },
                    { data: 'profit', name: 'profit', className: 'text-right' },
                    { data: 'operasional', name: 'operasional', className: 'text-right' },
                    { data: 'insiden', name: 'insiden', className: 'text-right' },
                    { data: 'sisa_profit', name: 'sisa_profit', className: 'text-right font-bold' },
                    { data: 'target', name: 'target', className: 'text-right' },
                    { data: 'result', name: 'result', className: 'text-center' },
                ],
                order: [], // Disable default sort
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json',
                    emptyTable: "Tidak ada data transaksi pada periode ini"
                },
                // Footer Callback untuk mengisi nilai Total dari Response JSON
                drawCallback: function (settings) {
                    var api = this.api();
                    var json = api.ajax.json();

                    if(json) {
                        // Mengisi Baris Total
                        $('#sum-transaksi').text(json.total_transaksi);
                        $('#sum-omset').text('Rp ' + json.total_omset);
                        $('#sum-modal').text('Rp ' + json.total_modal);
                        $('#sum-profit').text('Rp ' + json.total_profit);
                        $('#sum-operasional').text('Rp ' + json.total_operasional);
                        $('#sum-insiden').text(json.total_insiden === '0' ? '-' : 'Rp ' + json.total_insiden);
                        $('#sum-sisa-profit').text('Rp ' + json.total_sisa_profit);

                        // Mengisi Baris Bagi Hasil (HF & Investor)
                        $('#val-hf').text('Rp ' + json.share_hf);
                        $('#val-investor').text('Rp ' + json.share_investor);

                        $('.persen-toko').text(json.pembagiPersen+'%');
                        $('.persen-investor').text(json.persenInvestor+'%');
                    }
                }
            });

            // Tombol Filter Klik
            $('#btn-filter').click(function() {
                table.draw();
            });
        });
    </script>
</x-toko-layout>

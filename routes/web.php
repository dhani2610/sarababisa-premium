<?php

use App\Http\Controllers\KepalaToko\ProdukController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\RincianInvestController;
use Illuminate\Support\Facades\Route;
use App\Http\Livewire\MasterAbsensi;

use App\Http\Controllers\DefaultController;

use App\Http\Controllers\GaransiController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\HakAksesController;
use App\Http\Controllers\TrackingController;
// Kepala Toko
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AutoBiayaServisController;
use App\Http\Controllers\AutoModalSparepartController;
use App\Http\Controllers\AutoHargaJualController;
use App\Http\Controllers\KepalaToko\TransferStokController;
use App\Http\Controllers\KepalaToko\RefundController;
use App\Http\Controllers\KepalaToko\DataServisController;
use App\Http\Controllers\KepalaToko\DataTargetController;
use App\Http\Controllers\KepalaToko\RecycleBinController;
use App\Http\Controllers\KepalaToko\DataPenjualanController;
use App\Http\Controllers\KepalaToko\DataPengeluaranController;
use App\Http\Controllers\KepalaToko\DataTargetPersenController;
use App\Http\Controllers\KepalaToko\AttendanceController;
use App\Http\Controllers\Sales\PosController as SalesPosController;
use App\Http\Controllers\Sales\KasbonController as SalesKasbonController;
use App\Http\Controllers\Sales\ProdukController as SalesProdukController;
use App\Http\Controllers\AdminToko\PosController as AdminTokoPosController;
use App\Http\Controllers\Sales\ExpenseController as SalesExpenseController;
use App\Http\Controllers\KepalaToko\PosController as KepalaTokoPosController;
use App\Http\Controllers\Sales\KategoriController as SalesKategoriController;
use App\Http\Controllers\Teknisi\KasbonController as TeknisiKasbonController;
use App\Http\Controllers\Teknisi\ProdukController as TeknisiProdukController;
use App\Http\Controllers\KepalaToko\AkunController as KepalaTokoAkunController;
use App\Http\Controllers\KepalaToko\GajiController as KepalaTokoGajiController;
use App\Http\Controllers\KepalaToko\TermController as KepalaTokoTermController;
use App\Http\Controllers\KepalaToko\SistemController as KepalaTokoSistemController;
use App\Http\Controllers\Sales\DashboardController as SalesDashboardController;
use App\Http\Controllers\Sales\PelangganController as SalesPelangganController;
use App\Http\Controllers\Teknisi\ExpenseController as TeknisiExpenseController;
use App\Http\Controllers\AdminToko\KasbonController as AdminTokoKasbonController;
use App\Http\Controllers\AdminToko\ProdukController as AdminTokoProdukController;
use App\Http\Controllers\Sales\ProdukToolController as SalesProdukToolController;
use App\Http\Controllers\AdminToko\ExpenseController as AdminTokoExpenseController;
use App\Http\Controllers\AdminToko\InsidenController as AdminTokoInsidenController;
use App\Http\Controllers\KepalaToko\KasbonController as KepalaTokoKasbonController;
use App\Http\Controllers\KepalaToko\ProdukController as KepalaTokoProdukController;
use App\Http\Controllers\KepalaToko\TargetController as KepalaTokoTargetController;
use App\Http\Controllers\Sales\SubKategoriController as SalesSubKategoriController;
use App\Http\Controllers\Teknisi\DashboardController as TeknisiDashboardController;
use App\Http\Controllers\Teknisi\PelangganController as TeknisiPelangganController;
use App\Http\Controllers\AdminToko\KategoriController as AdminTokoKategoriController;
use App\Http\Controllers\AdminToko\SupplierController as AdminTokoSupplierController;
use App\Http\Controllers\KepalaToko\ApproveController as KepalaTokoApproveController;
use App\Http\Controllers\KepalaToko\ExpenseController as KepalaTokoExpenseController;
use App\Http\Controllers\KepalaToko\InsidenController as KepalaTokoInsidenController;
use App\Http\Controllers\AdminToko\DashboardController as AdminTokoDashboardController;
use App\Http\Controllers\AdminToko\PelangganController as AdminTokoPelangganController;
use App\Http\Controllers\KepalaToko\AnggaranController as KepalaTokoAnggaranController;
use App\Http\Controllers\KepalaToko\KaryawanController as KepalaTokoKaryawanController;
use App\Http\Controllers\KepalaToko\KategoriController as KepalaTokoKategoriController;
use App\Http\Controllers\KepalaToko\SupplierController as KepalaTokoSupplierController;
use App\Http\Controllers\AdminToko\ProdukToolController as AdminTokoProdukToolController;
use App\Http\Controllers\KepalaToko\DashboardController as KepalaTokoDashboardController;
use App\Http\Controllers\KepalaToko\DashboardCabangController as KepalaTokoDashboardCabangController;
use App\Http\Controllers\KepalaToko\LogServisController as KepalaTokoLogServisController;
use App\Http\Controllers\KepalaToko\PelangganController as KepalaTokoPelangganController;
use App\Http\Controllers\AdminToko\BisaDiambilController as AdminTokoBisaDiambilController;
use App\Http\Controllers\AdminToko\MasterMerekController as AdminTokoMasterMerekController;
use App\Http\Controllers\AdminToko\MasterWarnaController as AdminTokoMasterWarnaController;
use App\Http\Controllers\AdminToko\ProdukHabisController as AdminTokoProdukHabisController;
use App\Http\Controllers\AdminToko\SubKategoriController as AdminTokoSubKategoriController;
use App\Http\Controllers\KepalaToko\ProdukToolController as KepalaTokoProdukToolController;
use App\Http\Controllers\KepalaToko\InventarisController as KepalaTokoInventarisController;
use App\Http\Controllers\Sales\ProdukAksesorisController as SalesProdukAksesorisController;
use App\Http\Controllers\Sales\ProdukHandphoneController as SalesProdukHandphoneController;
use App\Http\Controllers\Sales\ProdukSparepartController as SalesProdukSparepartController;
use App\Http\Controllers\KepalaToko\TukarTambahController as KepalaTokoTukarTambahController;
// Admin Toko
use App\Http\Controllers\Sales\TransaksiProdukController as SalesTransaksiProdukController;
use App\Http\Controllers\AdminToko\SudahDiambilController as AdminTokoSudahDiambilController;
use App\Http\Controllers\AdminToko\InventarisController as AdminTokoInventarisController;
use App\Http\Controllers\KepalaToko\BisaDiambilController as KepalaTokoBisaDiambilController;
use App\Http\Controllers\KepalaToko\MasterMerekController as KepalaTokoMasterMerekController;
use App\Http\Controllers\KepalaToko\MasterWarnaController as KepalaTokoMasterWarnaController;
use App\Http\Controllers\KepalaToko\SubKategoriController as KepalaTokoSubKategoriController;
use App\Http\Controllers\KepalaToko\TargetSalesController as KepalaTokoTargetSalesController;
use App\Http\Controllers\Sales\LaporanPenjualanController as SalesLaporanPenjualanController;
use App\Http\Controllers\Teknisi\LaporanTeknisiController as TeknisiLaporanTeknisiController;
use App\Http\Controllers\Teknisi\TindakanServisController as TeknisiTindakanServisController;
use App\Http\Controllers\AdminToko\LaporanServisController as AdminTokoLaporanServisController;

use App\Http\Controllers\KepalaToko\LaporanAdminController as KepalaTokoLaporanAdminController;
use App\Http\Controllers\KepalaToko\LaporanSalesController as KepalaTokoLaporanSalesController;

use App\Http\Controllers\KepalaToko\ReturProductController as KepalaTokoReturProductController;
use App\Http\Controllers\KepalaToko\SudahDiambilController as KepalaTokoSudahDiambilController;
use App\Http\Controllers\Teknisi\MasterModelSeriController as TeknisiMasterModelSeriController;
use App\Http\Controllers\Teknisi\TransaksiServisController as TeknisiTransaksiServisController;
use App\Http\Controllers\Teknisi\UbahBisaDiambilController as TeknisiUbahBisaDiambilController;
use App\Http\Controllers\AdminToko\ProdukTersediaController as AdminTokoProdukTersediaController;
use App\Http\Controllers\AdminToko\TindakanServisController as AdminTokoTindakanServisController;
use App\Http\Controllers\KepalaToko\InformasiTokoController as KepalaTokoInformasiTokoController;
use App\Http\Controllers\KepalaToko\LaporanHarianController as KepalaTokoLaporanHarianController;
use App\Http\Controllers\KepalaToko\LaporanServisController as KepalaTokoLaporanServisController;
use App\Http\Controllers\KepalaToko\TargetTeknisiController as KepalaTokoTargetTeknisiController;
use App\Http\Controllers\Sales\TransaksiProdukDueController as SalesTransaksiProdukDueController;
use App\Http\Controllers\AdminToko\MasterKapasitasController as AdminTokoMasterKapasitasController;
use App\Http\Controllers\AdminToko\MasterModelSeriController as AdminTokoMasterModelSeriController;
use App\Http\Controllers\AdminToko\ProdukAksesorisController as AdminTokoProdukAksesorisController;
use App\Http\Controllers\AdminToko\ProdukHandphoneController as AdminTokoProdukHandphoneController;
use App\Http\Controllers\AdminToko\ProdukSparepartController as AdminTokoProdukSparepartController;
use App\Http\Controllers\AdminToko\TransaksiProdukController as AdminTokoTransaksiProdukController;
use App\Http\Controllers\AdminToko\TransaksiServisController as AdminTokoTransaksiServisController;
use App\Http\Controllers\AdminToko\UbahBisaDiambilController as AdminTokoUbahBisaDiambilController;
use App\Http\Controllers\KepalaToko\LaporanTeknisiController as KepalaTokoLaporanTeknisiController;
use App\Http\Controllers\KepalaToko\TindakanServisController as KepalaTokoTindakanServisController;
use App\Http\Controllers\Sales\TransaksiProdukPaidController as SalesTransaksiProdukPaidController;
use App\Http\Controllers\AdminToko\LaporanPenjualanController as AdminTokoLaporanPenjualanController;
use App\Http\Controllers\AdminToko\UbahSudahDiambilController as AdminTokoUbahSudahDiambilController;
use App\Http\Controllers\KepalaToko\MasterKapasitasController as KepalaTokoMasterKapasitasController;
use App\Http\Controllers\AdminToko\PurchaseProductController as AdminTokoPurchaseProductController;
use App\Http\Controllers\AdminToko\ReturProductController as AdminTokoReturProductController;
use App\Http\Controllers\AdminToko\TukarTambahController as AdminTokoTukarTambahController;
// Teknisi
use App\Http\Controllers\KepalaToko\MasterModelSeriController as KepalaTokoMasterModelSeriController;
use App\Http\Controllers\KepalaToko\ProdukAksesorisController as KepalaTokoProdukAksesorisController;
use App\Http\Controllers\KepalaToko\ProdukHandphoneController as KepalaTokoProdukHandphoneController;
use App\Http\Controllers\KepalaToko\ProdukSparepartController as KepalaTokoProdukSparepartController;
use App\Http\Controllers\KepalaToko\PurchaseProductController as KepalaTokoPurchaseProductController;
use App\Http\Controllers\KepalaToko\TransaksiProdukController as KepalaTokoTransaksiProdukController;
use App\Http\Controllers\KepalaToko\TransaksiServisController as KepalaTokoTransaksiServisController;
// Sales
use App\Http\Controllers\KepalaToko\UbahBisaDiambilController as KepalaTokoUbahBisaDiambilController;
use App\Http\Controllers\AdminToko\MasterJenisBarangController as AdminTokoMasterJenisBarangController;
use App\Http\Controllers\KepalaToko\ApprovePenjualanController as KepalaTokoApprovePenjualanController;
use App\Http\Controllers\KepalaToko\LaporanPenjualanController as KepalaTokoLaporanPenjualanController;
use App\Http\Controllers\KepalaToko\UbahSudahDiambilController as KepalaTokoUbahSudahDiambilController;
use App\Http\Controllers\AdminToko\TransaksiProdukDueController as AdminTokoTransaksiProdukDueController;
use App\Http\Controllers\KepalaToko\MasterJenisBarangController as KepalaTokoMasterJenisBarangController;
use App\Http\Controllers\AdminToko\TransaksiProdukPaidController as AdminTokoTransaksiProdukPaidController;
use App\Http\Controllers\KepalaToko\ApprovePengeluaranController as KepalaTokoApprovePengeluaranController;
use App\Http\Controllers\KepalaToko\TransaksiProdukDueController as KepalaTokoTransaksiProdukDueController;
use App\Http\Controllers\KepalaToko\TransaksiProdukPaidController as KepalaTokoTransaksiProdukPaidController;
use App\Http\Controllers\Teknisi\UbahStatusProsesServisController as TeknisiUbahStatusProsesServisController;
use App\Http\Controllers\KepalaToko\BonusBulanSebelumnyaController as KepalaTokoBonusBulanSebelumnyaController;
use App\Http\Controllers\KepalaToko\ServisBelumDisetujuiController as KepalaTokoServisBelumDisetujuiController;
use App\Http\Controllers\AdminToko\UbahStatusProsesServisController as AdminTokoUbahStatusProsesServisController;
use App\Http\Controllers\KepalaToko\TargetBulanSebelumnyaController as KepalaTokoTargetBulanSebelumnyaController;
use App\Http\Controllers\AdminToko\TransaksiServisLangsungController as AdminTokoTransaksiServisLangsungController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\HistoryGaransiController;
use App\Http\Controllers\KepalaToko\UbahStatusProsesServisController as KepalaTokoUbahStatusProsesServisController;
use App\Http\Controllers\KepalaToko\TransaksiServisLangsungController as KepalaTokoTransaksiServisLangsungController;
use App\Http\Controllers\Teknisi\TransaksiServisLangsungController as TeknisiTransaksiServisLangsungController;
use App\Http\Controllers\KepalaToko\ServisBelumDisetujuiApproveController as KepalaTokoServisBelumDisetujuiApproveController;
use App\Http\Controllers\TipeOsController;
use App\Http\Controllers\CabangController;
use App\Http\Controllers\DirectPasswordResetController;
use App\Http\Controllers\KepalaToko\MasterIzinController;
use App\Http\Controllers\KepalaToko\MasterOvertimeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', [PortalController::class, 'index'])->name('portal');
Route::get('/', [PortalController::class, 'indexHome'])->name('portal.home');

// Halaman Portal per Cabang (Show Produk dll)
Route::get('/cabang/{id}', [PortalController::class, 'index'])->name('portal.branch');
Route::get('/detail-produk/{id}', [PortalController::class, 'index'])->name('portal.detail-produk');
Route::get('/install-app-ios', [PortalController::class, 'installAppIOS'])->name('portal.install-app-ios');
Route::get('/pembayaran', [PaymentController::class, 'index'])->name('payment');
Route::get('/hak-akses', [HakAksesController::class, 'index'])->name('hak-akses');
Route::get('/tracking', [TrackingController::class, 'index'])->name('tracking');
Route::get('/tracking-data', [TrackingController::class, 'data'])->name('tracking-data');
Route::get('/garansi', [GaransiController::class, 'index'])->name('garansi');
Route::get('/garansi-data', [GaransiController::class, 'data'])->name('garansi-data');
Route::get('/garansi-servis', [GaransiController::class, 'indexServis'])->name('garansi-servis');
Route::get('/garansi-servis-data', [GaransiController::class, 'dataServis'])->name('garansi-servis-data');
Route::get('/servis-detail/{id}', [GaransiController::class, 'dataServisTeknisi'])->name('servis-detail');
Route::get('/get-action/{service_actions_id}', [AutoBiayaServisController::class, 'getAction']);
Route::get('/get-sparepart/{products_id}', [AutoModalSparepartController::class, 'getSparepart']);
Route::get('/get-product/{products_id}', [AutoHargaJualController::class, 'getProduct']);

// Default All Route
Route::controller(DefaultController::class)->group(function () {
    Route::get('/get-modelserie', 'GetModelSerie')->name('get-modelserie');
    Route::get('/get-product', 'GetProduct')->name('get-product');
});

Route::get('/test-mail', function () {
    $data = [
        'name' => 'Test User',
        'url' => 'https://example.com/verify-link'
    ];

    Mail::send('mail.password', $data, function ($message) {
        $message->to('andakaramdhanisantoso@gmail.com')
                ->subject('Test Email with View');
    });

    return 'HTML mail sent using Blade view!';
});


// Keranjang Sampah
Route::get('/keranjang-servis', [RecycleBinController::class, 'service'])->name('keranjang-servis');
Route::delete('/{id}/hapus-keranjang-servis', [RecycleBinController::class, 'permanentlyDelete'])->name('hapus-keranjang-servis');
Route::get('/{id}/restore-keranjang-servis', [RecycleBinController::class, 'restore'])->name('restore-keranjang-servis');
Route::post('bersihkan-keranjang-servis', [RecycleBinController::class, 'cleanService'])->name('bersihkan-keranjang-servis');
Route::get('/keranjang-akun', [RecycleBinController::class, 'account'])->name('keranjang-akun');
Route::delete('/{id}/hapus-keranjang-akun', [RecycleBinController::class, 'permanentlyDeleteAccount'])->name('hapus-keranjang-akun');
Route::get('/{id}/restore-keranjang-akun', [RecycleBinController::class, 'restoreAccount'])->name('restore-keranjang-akun');
Route::post('bersihkan-keranjang-akun', [RecycleBinController::class, 'cleanAccount'])->name('bersihkan-keranjang-akun');
Route::get('/keranjang-pelanggan', [RecycleBinController::class, 'customer'])->name('keranjang-pelanggan');
Route::delete('/{id}/hapus-keranjang-pelanggan', [RecycleBinController::class, 'permanentlyDeleteCustomer'])->name('hapus-keranjang-pelanggan');
Route::get('/{id}/restore-keranjang-pelanggan', [RecycleBinController::class, 'restoreCustomer'])->name('restore-keranjang-pelanggan');
Route::post('bersihkan-keranjang-pelanggan', [RecycleBinController::class, 'cleanCustomer'])->name('bersihkan-keranjang-pelanggan');
Route::get('/keranjang-produk', [RecycleBinController::class, 'product'])->name('keranjang-produk');
Route::delete('/{id}/hapus-keranjang-produk', [RecycleBinController::class, 'permanentlyDeleteProduct'])->name('hapus-keranjang-produk');
Route::get('/{id}/restore-keranjang-produk', [RecycleBinController::class, 'restoreProduct'])->name('restore-keranjang-produk');
Route::post('bersihkan-keranjang-produk', [RecycleBinController::class, 'cleanProduct'])->name('bersihkan-keranjang-produk');
Route::get('/keranjang-insiden', [RecycleBinController::class, 'incident'])->name('keranjang-insiden');
Route::delete('/{id}/hapus-keranjang-insiden', [RecycleBinController::class, 'permanentlyDeleteIncident'])->name('hapus-keranjang-insiden');
Route::get('/{id}/restore-keranjang-insiden', [RecycleBinController::class, 'restoreIncident'])->name('restore-keranjang-insiden');
Route::post('bersihkan-keranjang-insiden', [RecycleBinController::class, 'cleanIncident'])->name('bersihkan-keranjang-insiden');
Route::get('/keranjang-kasbon', [RecycleBinController::class, 'debt'])->name('keranjang-kasbon');
Route::delete('/{id}/hapus-keranjang-kasbon', [RecycleBinController::class, 'permanentlyDeleteDebt'])->name('hapus-keranjang-kasbon');
Route::get('/{id}/restore-keranjang-kasbon', [RecycleBinController::class, 'restoreDebt'])->name('restore-keranjang-kasbon');
Route::post('bersihkan-keranjang-kasbon', [RecycleBinController::class, 'cleanDebt'])->name('bersihkan-keranjang-kasbon');
Route::get('/keranjang-pengeluaran', [RecycleBinController::class, 'expense'])->name('keranjang-pengeluaran');
Route::delete('/{id}/hapus-keranjang-pengeluaran', [RecycleBinController::class, 'permanentlyDeleteExpense'])->name('hapus-keranjang-pengeluaran');
Route::get('/{id}/restore-keranjang-pengeluaran', [RecycleBinController::class, 'restoreExpense'])->name('restore-keranjang-pengeluaran');
Route::post('bersihkan-keranjang-pengeluaran', [RecycleBinController::class, 'cleanExpense'])->name('bersihkan-keranjang-pengeluaran');
Route::get('/keranjang-penjualan', [RecycleBinController::class, 'order'])->name('keranjang-penjualan');
Route::delete('/{id}/hapus-keranjang-penjualan', [RecycleBinController::class, 'permanentlyDeleteOrder'])->name('hapus-keranjang-penjualan');
Route::get('/{id}/restore-keranjang-penjualan', [RecycleBinController::class, 'restoreOrder'])->name('restore-keranjang-penjualan');
Route::post('bersihkan-keranjang-penjualan', [RecycleBinController::class, 'cleanOrder'])->name('bersihkan-keranjang-penjualan');

Route::delete('/customers/delete', [KepalaTokoPelangganController::class, 'deleteSelected']);
Route::delete('/service-actions/delete', [KepalaTokoTindakanServisController::class, 'deleteSelected']);
Route::delete('/categories/delete', [KepalaTokoKategoriController::class, 'deleteSelected']);
Route::delete('/sub-categories/delete', [KepalaTokoSubKategoriController::class, 'deleteSelected']);
Route::delete('/suppliers/delete', [KepalaTokoSupplierController::class, 'deleteSelected']);
Route::delete('/products/delete', [KepalaTokoProdukController::class, 'deleteSelected']);
Route::delete('/types/delete', [KepalaTokoMasterJenisBarangController::class, 'deleteSelected'])->name('master-jenis-barang.deleteSelected');
Route::delete('/brands/delete', [KepalaTokoMasterMerekController::class, 'deleteSelected']);
Route::delete('/model-series/delete', [KepalaTokoMasterModelSeriController::class, 'deleteSelected']);
Route::delete('/capacities/delete', [KepalaTokoMasterKapasitasController::class, 'deleteSelected']);
Route::delete('/incidents/delete', [KepalaTokoInsidenController::class, 'deleteSelected']);
Route::delete('/debts/delete', [KepalaTokoKasbonController::class, 'deleteSelected']);
Route::delete('/expenses/delete', [KepalaTokoExpenseController::class, 'deleteSelected']);
Route::delete('/target/delete', [KepalaTokoTargetController::class, 'deleteSelected']);
Route::delete('/target-sales/delete', [KepalaTokoTargetSalesController::class, 'deleteSelected']);
Route::delete('/target-teknisi/delete', [KepalaTokoTargetTeknisiController::class, 'deleteSelected']);

Route::get('produk/item/{id}/download-barcode', [ProdukController::class, 'downloadBarcode'])->name('download-barcode');
Route::delete('master/master-gallery/delete-selected', [GalleryController::class, 'deleteSelected'])
        ->name('master-gallery.deleteSelected');

Route::resource('gaji/karyawan', KepalaTokoKaryawanController::class);
Route::get('slip-gaji/{id}', [KepalaTokoKaryawanController::class, 'cetak'])->name('cetak-slip-gaji');
Route::get('/history-garansi/cetak', [HistoryGaransiController::class, 'cetak'])
    ->name('history-garansi.cetak');
Route::get('/history-garansi/cetak-inject/{id}', [HistoryGaransiController::class, 'cetakinkjet'])
    ->name('history-garansi.cetak-inject');
Route::resource('history-garansi', HistoryGaransiController::class);
Route::patch('/history-garansi/{id}/toggle-status', [HistoryGaransiController::class, 'toggleStatus'])
    ->name('history-garansi.toggleStatus');
Route::get('/history-garansi/list-data/{id}', [HistoryGaransiController::class, 'getDetailHistory'])->name('history-garansi.list-data');
Route::post('/history-garansi/bulk-delete', [HistoryGaransiController::class, 'bulkDelete'])->name('history-garansi.bulkDelete');

Route::delete('/master/master-izin/delete-selected', [MasterIzinController::class, 'deleteSelected'])
    ->name('master-izin.deleteSelected');
Route::resource('master/master-izin', MasterIzinController::class);

Route::post('master-overtime/{id}/approve', [\App\Http\Controllers\KepalaToko\MasterOvertimeController::class, 'approve'])->name('master-overtime.approve');

Route::delete('master/master-overtime/delete-selected', [MasterOvertimeController::class, 'deleteSelected'])
    ->name('master-overtime.deleteSelected');

Route::post('master/master-overtime/approve-selected', [MasterOvertimeController::class, 'approveSelected'])
    ->name('master-overtime.approveSelected');

Route::post('master/master-overtime/reject-selected', [MasterOvertimeController::class, 'rejectSelected'])
    ->name('master-overtime.rejectSelected');

Route::resource('master/master-overtime', MasterOvertimeController::class);


Route::post('/set-cabang', function () {
    $id = request('cabang_id');

    $user = Auth::user();
    $user->cabang_id = $id;
    $user->save();

    return back();
})->name('set.cabang');

Route::post('/change-cabang', function () {
    $id = request('cabang_id');

    $user = Auth::user();
    $user->cabang_id = $id;
    $user->save();

    return redirect('hak-akses');
})->name('change-cabang');


// Livewire page (index)
// Route::get('master/master-absensi', MasterAbsensi::class)->name('master-absensi.index');

// Controller endpoints for store & deletes (Livewire only displays)
Route::get('master/master-absensi', [AttendanceController::class, 'index'])->name('master-absensi.index');
Route::post('master/master-absensi/store', [AttendanceController::class, 'store'])->name('master-absensi.store');
Route::delete('master/master-absensi/delete-selected', [AttendanceController::class, 'deleteSelected'])->name('master-absensi.deleteSelected');
Route::delete('master/master-absensi/{id}', [AttendanceController::class, 'destroy'])->name('master-absensi.destroy');
Route::get('master/master-absensi/export', [AttendanceController::class, 'export'])->name('master-absensi.export');

// routes/web.php
// Route::get('/service-transaction/{id}', [App\Http\Controllers\HistoryGaransiController::class, 'yyy'])->name('service.show');
    Route::get('/update-expired', [KepalaTokoAkunController::class, 'updateExpDateJson'])->name('update-expired');
    Route::get('/get-total-cabang', [KepalaTokoAkunController::class, 'getDataTotalCabang'])->name('get-total-cabang');
    Route::get('/update-total-cabang', [KepalaTokoAkunController::class, 'updateTotalCabang'])->name('update-total-cabang');

// 1. Halaman Input Email (Awal)
    Route::get('/lupa-password', [DirectPasswordResetController::class, 'showRequestForm'])->name('direct.reset.request');

    // 2. Proses Cek Email (POST) -> Akan me-redirect ke halaman ganti
    Route::post('/lupa-password/cek', [DirectPasswordResetController::class, 'checkEmail'])->name('direct.reset.check');

    // 3. Halaman Input Password Baru (Halaman Berbeda / GET)
    Route::get('/lupa-password/ganti', [DirectPasswordResetController::class, 'showChangePasswordForm'])->name('direct.reset.form');

    // 4. Proses Simpan Password (POST)
    Route::post('/lupa-password/update', [DirectPasswordResetController::class, 'updatePassword'])->name('direct.reset.update');
Route::middleware(['ensureUserRole:KepalaToko', 'checkSubscription','jam_kerja'])->group(function () {
    Route::get('top-produk-kepala-toko', [KepalaTokoProdukController::class, 'indexTopNew'])->name('top-produk-kepala-toko');

    Route::get('/dashboard', [KepalaTokoDashboardController::class, 'index'])->name('kepalatoko-dashboard');
    Route::get('/dashboard-cabang', [KepalaTokoDashboardCabangController::class, 'index'])->name('kepalatoko-dashboard-cabang');
    Route::get('/dashboard-cabang-json', [KepalaTokoDashboardCabangController::class, 'getJsonChart'])->name('kepalatoko-dashboard-cabang-json');
    Route::get('/json-data-servis', [DataServisController::class, 'getDataServis'])->name('json_data_servis');
    Route::get('/json-data-penjualan', [DataPenjualanController::class, 'getDataPenjualan'])->name('json_data_penjualan');
    Route::get('/json-data-target', [DataTargetController::class, 'getDataTarget'])->name('json_data_target');
    Route::get('/json-data-target-persen', [DataTargetPersenController::class, 'getDataTargetPersen'])->name('json_data_target-persen');
    Route::get('/json-data-pengeluaran', [DataPengeluaranController::class, 'getDataPengeluaran'])->name('pengeluaran');


    Route::get('/akun/data', [KepalaTokoAkunController::class, 'getData'])->name('akun.data');

    // Route Bulk Delete
    Route::post('/akun/delete-batch', [KepalaTokoAkunController::class, 'deleteBatch'])->name('akun.delete-batch');
    Route::get('/akun', [KepalaTokoAkunController::class, 'index'])->name('akun');
    Route::get('/akun/setting', [KepalaTokoAkunController::class, 'setting'])->name('setting');
    Route::post('/akun/setting/update-exp-date', [KepalaTokoAkunController::class, 'updateExpDate'])
    ->name('akun.update-exp-date');


    Route::post('/akun', [KepalaTokoAkunController::class, 'store'])->name('akun-store');
    Route::get('/akun/{id}', [KepalaTokoAkunController::class, 'edit'])->name('akun-edit');
    Route::post('/akun{id}', [KepalaTokoAkunController::class, 'update'])->name('akun-update');
    Route::delete('/akun/{id}', [KepalaTokoAkunController::class, 'destroy'])->name('akun-destroy');
    Route::delete('/accounts/delete', [KepalaTokoAkunController::class, 'deleteSelected']);
    Route::post('/servis/transaksi-servis-langsung', [KepalaTokoTransaksiServisLangsungController::class, 'store'])->name('servis-langsung');
    Route::resource('servis/tindakan-servis', KepalaTokoTindakanServisController::class);

    Route::get('pelanggan/data', [KepalaTokoPelangganController::class, 'getData'])->name('pelanggan.data');
    Route::post('pelanggan/import-chunk', [KepalaTokoPelangganController::class, 'importChunk'])->name('pelanggan.import-chunk');
    Route::resource('pelanggan', KepalaTokoPelangganController::class);
    Route::post('pelanggan-broadcast', [KepalaTokoPelangganController::class,'broadcast'])->name('pelanggan.broadcast');

    Route::get('/transaksi-servis/data', [KepalaTokoTransaksiServisController::class, 'getData'])->name('transaksi-servis.data');
    Route::get('/transaksi-servis/get-pin-pola/{id}', [KepalaTokoTransaksiServisController::class, 'getPinPola'])->name('transaksi-servis.get-pin-pola');
    Route::post('/transaksi-servis/update-pin-pola/{id}', [KepalaTokoTransaksiServisController::class, 'updatePinPolaNew'])->name('transaksi-servis.update-pin-pola');
    // Route untuk Fitur Foto Servis (AJAX)
    Route::get('servis/transaksi-servis/{id}/get-foto', [KepalaTokoTransaksiServisController::class, 'getFoto']);
    Route::post('servis/transaksi-servis/{id}/upload-foto', [KepalaTokoTransaksiServisController::class, 'uploadFoto']);
    Route::delete('servis/transaksi-servis/{id}/delete-foto', [KepalaTokoTransaksiServisController::class, 'deleteFoto']);
    Route::resource('servis/transaksi-servis', KepalaTokoTransaksiServisController::class);
    Route::post('servis/transaksi-servis/{id}/update-pin-pola', [KepalaTokoTransaksiServisController::class, 'updatePinPola'])
    ->name('transaksi-servis.update-pin-pola');


    Route::get('rincian-invest', [RincianInvestController::class, 'index'])->name('rincian-invest.index');
    Route::post('rincian-invest', [RincianInvestController::class, 'store'])->name('rincian-invest.store');
    Route::put('rincian-invest/{id}', [RincianInvestController::class, 'update'])->name('rincian-invest.update');
    Route::delete('rincian-invest/{id}', [RincianInvestController::class, 'destroy'])->name('rincian-invest.destroy');
    Route::post('/rincian-invest/bulk-delete', [RincianInvestController::class, 'bulkDelete'])->name('rincian-invest.bulkDelete');


    Route::delete('/services/delete', [KepalaTokoTransaksiServisController::class, 'deleteSelected']);
    Route::patch('/services/update', [KepalaTokoSudahDiambilController::class, 'approveSelected']);
    Route::patch('/services/reject', [KepalaTokoSudahDiambilController::class, 'rejectSelected']);
    Route::resource('servis/transaksi-servis-approve', KepalaTokoApproveController::class);
    Route::resource('servis/servis-belum-disetujui-approve', KepalaTokoServisBelumDisetujuiApproveController::class);
    Route::get('servis/transaksi-servis-bisa-diambil/data', [KepalaTokoBisaDiambilController::class,'getData'])->name('transaksi-servis-bisa-diambil.data');
    Route::resource('servis/transaksi-servis-bisa-diambil', KepalaTokoBisaDiambilController::class);

    Route::get('servis/transaksi-servis-sudah-diambil/data', [KepalaTokoSudahDiambilController::class,'getData'])->name('transaksi-servis-sudah-diambil.data');
    Route::resource('servis/transaksi-servis-sudah-diambil', KepalaTokoSudahDiambilController::class);

    Route::get('servis/transaksi-servis-belum-disetujui/data', [KepalaTokoServisBelumDisetujuiController::class,'getData'])->name('transaksi-servis-belum-disetujui.data');
    Route::resource('servis/transaksi-servis-belum-disetujui', KepalaTokoServisBelumDisetujuiController::class);
    Route::get('servis/log-servis', [KepalaTokoLogServisController::class, 'index'])->name('log-servis');
    Route::post('servis/log-servis-destroy/{model}', [KepalaTokoLogServisController::class, 'destroy'])->name('log-servis-destroy');

    Route::get('master-jenis-barang/data', [KepalaTokoMasterJenisBarangController::class, 'getData'])->name('master-jenis-barang.data');
    Route::resource('master/master-jenis-barang', KepalaTokoMasterJenisBarangController::class);


    Route::get('master-warna/data', [KepalaTokoMasterWarnaController::class, 'getData'])->name('master-warna.data');

    Route::post('master-warna/delete-batch', [KepalaTokoMasterWarnaController::class, 'deleteBatch'])->name('master-warna.delete-batch');
    Route::resource('master/master-warna', KepalaTokoMasterWarnaController::class);


    Route::get('master-tipe-os/data', [TipeOsController::class, 'getData'])->name('master-tipe-os.data');
    Route::post('master-tipe-os/delete-batch', [TipeOsController::class, 'deleteBatch'])->name('master-tipe-os.delete-batch');
    Route::resource('master/master-tipe-os', TipeOsController::class);
    Route::resource('master/master-gallery', GalleryController::class);


    Route::get('master-cabang/data', [CabangController::class, 'getData'])->name('master-cabang.data');

    Route::post('master-cabang/delete-batch', [CabangController::class, 'deleteBatch'])->name('master-cabang.delete-batch');
    Route::resource('master/master-cabang', CabangController::class);


    Route::resource('refund', RefundController::class)->names('refund');
    Route::post('refund/delete-selected', [RefundController::class, 'deleteSelected'])->name('refund.deleteSelected');
    Route::get('refund/service/{id}', [RefundController::class, 'serviceDetail'])->name('refund.serviceDetail');
    Route::get('/refund-cetak', [RefundController::class, 'cetak'])->name('refunds.cetak');


    Route::resource('transfer-stok', TransferStokController::class)->names('transfer-stok');
    Route::get('/api/products-by-cabang/{cabang}/{kategori}', [TransferStokController::class, 'productsByCabang'])
    ->name('api.productsByCabang');
    Route::post('transfer-stok/delete-selected', [TransferStokController::class, 'deleteSelected'])->name('transfer-stok.deleteSelected');
    Route::get('transfer-stok-cetak', [TransferStokController::class, 'cetak'])->name('transfer-stok.cetak');
    Route::post('/transfer-stok/{id}/approve', [TransferStokController::class, 'approve'])
        ->name('transfer-stok.approve');


    Route::resource('shift', \App\Http\Controllers\KepalaToko\ShiftController::class)->names('shift');
    Route::post('shift/delete-selected', [\App\Http\Controllers\KepalaToko\ShiftController::class, 'deleteSelected'])->name('shift.deleteSelected');


    Route::get('master-merek/data', [KepalaTokoMasterMerekController::class, 'getData'])->name('master-merek.data');
    Route::post('master-merek/delete-batch', [KepalaTokoMasterMerekController::class, 'deleteBatch'])->name('master-merek.delete-batch');
    Route::resource('master/master-merek', KepalaTokoMasterMerekController::class);


    Route::get('master-kapasitas/data', [KepalaTokoMasterKapasitasController::class, 'getData'])->name('master-kapasitas.data');
    Route::post('master-kapasitas/delete-batch', [KepalaTokoMasterKapasitasController::class, 'deleteBatch'])->name('master-kapasitas.delete-batch');
    Route::resource('master/master-kapasitas', KepalaTokoMasterKapasitasController::class);

    Route::get('master-model-seri/data', [KepalaTokoMasterModelSeriController::class, 'getData'])->name('master-model-seri.data');
    Route::post('master-model-seri/delete-batch', [KepalaTokoMasterModelSeriController::class, 'deleteBatch'])->name('master-model-seri.delete-batch');
    Route::resource('master/master-model-seri', KepalaTokoMasterModelSeriController::class);
    Route::get('manajemen/anggaran/data', [KepalaTokoAnggaranController::class, 'getData'])->name('anggaran.data');
    Route::post('manajemen/anggaran/delete-batch', [KepalaTokoAnggaranController::class, 'deleteBatch'])->name('anggaran.delete-batch');
    Route::resource('manajemen/anggaran', KepalaTokoAnggaranController::class);
    Route::resource('target', KepalaTokoTargetController::class);
    Route::resource('target-sales', KepalaTokoTargetSalesController::class);
    Route::resource('target-teknisi', KepalaTokoTargetTeknisiController::class);
    Route::delete('/budgets/delete', [KepalaTokoAnggaranController::class, 'deleteSelected']);
    Route::post('/target/bulan-sebelumnya', [KepalaTokoTargetBulanSebelumnyaController::class, 'store'])->name('target-bulan-sebelumnya');
    Route::resource('manajemen/insiden', KepalaTokoInsidenController::class);
    Route::resource('manajemen/kasbon', KepalaTokoKasbonController::class);
    Route::patch('/debts/update', [KepalaTokoKasbonController::class, 'approveSelected']);
    Route::patch('/debts/reject', [KepalaTokoKasbonController::class, 'rejectSelected']);
    Route::delete('/workers/delete', [KepalaTokoKaryawanController::class, 'deleteSelected']);
    Route::resource('gaji/bonus', KepalaTokoGajiController::class);
    Route::delete('/bonus/delete', [KepalaTokoGajiController::class, 'deleteSelected']);
    Route::post('/gaji/bonus/bulan-sebelumnya', [KepalaTokoBonusBulanSebelumnyaController::class, 'store'])->name('bonus-bulan-sebelumnya');
    Route::resource('manajemen/pengeluaran', KepalaTokoExpenseController::class);
    Route::patch('/expenses/update', [KepalaTokoExpenseController::class, 'approveSelected']);
    Route::patch('/expenses/reject', [KepalaTokoExpenseController::class, 'rejectSelected']);
    Route::resource('approve-pengeluaran', KepalaTokoApprovePengeluaranController::class);
    Route::get('manajemen/inventaris/data', [KepalaTokoInventarisController::class, 'getData'])->name('inventaris.data');
Route::post('manajemen/inventaris/delete-batch', [KepalaTokoInventarisController::class, 'deleteBatch'])->name('inventaris.delete-batch');
Route::post('manajemen/inventaris/print-selected', [KepalaTokoInventarisController::class, 'printSelected'])->name('inventaris.print-selected');
    Route::resource('manajemen/inventaris', KepalaTokoInventarisController::class);
    Route::delete('/inventories/delete', [KepalaTokoInventarisController::class, 'deleteSelected']);

    Route::resource('produk/kategori', KepalaTokoKategoriController::class);
    Route::get('produk/sub-kategori/data', [KepalaTokoSubKategoriController::class, 'getData'])->name('sub-kategori.data');
    Route::post('produk/sub-kategori/delete-batch', [KepalaTokoSubKategoriController::class, 'deleteBatch'])->name('sub-kategori.delete-batch');
    Route::resource('produk/sub-kategori', KepalaTokoSubKategoriController::class);

    Route::resource('produk/supplier', KepalaTokoSupplierController::class);

    Route::get('produk/item/data', [ProdukController::class, 'getData'])->name('produk-item.data');
    Route::post('produk/item/upload-foto', [ProdukController::class, 'uploadFotoAjax'])->name('produk-item.upload-foto');
    // Route Bulk Actions
    Route::post('produk/item/delete-batch', [ProdukController::class, 'deleteBatch'])->name('produk-item.delete-batch');
    Route::post('produk/item/update-portal', [ProdukController::class, 'updatePortal'])->name('produk-item.update-portal');

    Route::resource('produk/item', KepalaTokoProdukController::class);
    Route::post('/products/update-portal', [KepalaTokoProdukController::class, 'updatePortal'])->name('products.updatePortal');
    Route::get('top-produk', [KepalaTokoProdukController::class, 'indexTop'])->name('top-produk');

    Route::resource('produk/handphone', KepalaTokoProdukHandphoneController::class);
    Route::resource('produk/sparepart', KepalaTokoProdukSparepartController::class);
    Route::resource('produk/aksesoris', KepalaTokoProdukAksesorisController::class);
    Route::resource('produk/tool', KepalaTokoProdukToolController::class);
    Route::get('produk/purchase/cetak', [KepalaTokoPurchaseProductController::class, 'cetak'])->name('purchase.cetak');
    Route::resource('produk/purchase', KepalaTokoPurchaseProductController::class);
    Route::delete('/purchases/delete', [KepalaTokoPurchaseProductController::class, 'deleteSelected']);
    Route::resource('produk/retur', KepalaTokoReturProductController::class);
    Route::delete('/returs/delete', [KepalaTokoReturProductController::class, 'deleteSelected']);
    Route::resource('produk/tukar-tambah', KepalaTokoTukarTambahController::class);

    Route::get('nota-tukar-tambah/{id}', [KepalaTokoTukarTambahController::class, 'cetakinkjet'])->name('nota-tukar-tambah');
    Route::get('cetak-laporan-tukar-tambah', [KepalaTokoTukarTambahController::class, 'cetak'])->name('cetak-laporan-tukar-tambah');

    Route::resource('produk/pos', KepalaTokoPosController::class);
    Route::resource('produk/transaksi-penjualan-approve', KepalaTokoApprovePenjualanController::class);
    Route::get('transaksi-produk/data', [App\Http\Controllers\KepalaToko\TransaksiProdukController::class, 'data'])->name('transaksi-produk.data');
    Route::get('transaksi-produk/data/lunas', [App\Http\Controllers\KepalaToko\TransaksiProdukController::class, 'dataLunas'])->name('transaksi-produk.data.lunas');
    Route::get('transaksi-produk/data/due', [App\Http\Controllers\KepalaToko\TransaksiProdukController::class, 'dataDue'])->name('transaksi-produk.data.due');
    Route::resource('produk/transaksi-produk', KepalaTokoTransaksiProdukController::class);
    Route::get('/get-qc-data/{product_id?}', [KepalaTokoTransaksiProdukController::class, 'getQcData'])->name('get-qc-data');
    Route::post('/store-qc-data', [KepalaTokoTransaksiProdukController::class, 'storeQcData'])->name('store-qc-data');
    Route::get('qc-produk/{id}', [KepalaTokoTransaksiProdukController::class, 'cetakQc'])->name('qc-produk');

    Route::delete('/product-transactions/delete', [KepalaTokoTransaksiProdukController::class, 'deleteSelected']);
    Route::patch('/product-transactions/update', [KepalaTokoTransaksiProdukController::class, 'approveSelected']);
    Route::patch('/product-transactions/reject', [KepalaTokoTransaksiProdukController::class, 'rejectSelected']);
    Route::resource('produk/transaksi-produk-paid', KepalaTokoTransaksiProdukPaidController::class);
    Route::delete('/paid-product-transactions/delete', [KepalaTokoTransaksiProdukPaidController::class, 'deleteSelected']);
    Route::patch('/paid-product-transactions/update', [KepalaTokoTransaksiProdukPaidController::class, 'approveSelected']);
    Route::patch('/paid-product-transactions/reject', [KepalaTokoTransaksiProdukPaidController::class, 'rejectSelected']);
    Route::resource('produk/transaksi-produk-due', KepalaTokoTransaksiProdukDueController::class);
    Route::delete('/due-product-transactions/delete', [KepalaTokoTransaksiProdukDueController::class, 'deleteSelected']);
    Route::patch('/due-product-transactions/update', [KepalaTokoTransaksiProdukDueController::class, 'approveSelected']);
    Route::patch('/due-product-transactions/reject', [KepalaTokoTransaksiProdukDueController::class, 'rejectSelected']);

    // Route::resource('laporan/harian', KepalaTokoLaporanHarianController::class);

    Route::get('/order/due/{id}', [KepalaTokoTransaksiProdukController::class, 'OrderDueAjax']);
    Route::post('produk/update-due', [KepalaTokoTransaksiProdukController::class, 'UpdateDue'])->name('produk.updateDue');

    Route::get('produk/pos', [KepalaTokoPosController::class, 'index'])->name('pos');
    Route::post('produk/add-cart', [KepalaTokoPosController::class, 'AddCart']);
    Route::post('produk/cart-update/{rowId}', [KepalaTokoPosController::class, 'CartUpdate']);
    Route::post('produk/apply-discount', [KepalaTokoPosController::class, 'ApplyDiscount'])->name('produk.applyDiscount');
    Route::get('produk/cart-remove/{rowId}', [KepalaTokoPosController::class, 'CartRemove']);
    Route::post('produk/create-invoice', [KepalaTokoPosController::class, 'CreateInvoice']);
    Route::post('produk/complete-order', [KepalaTokoPosController::class, 'CompleteOrder']);
    Route::get('produk/pos/{id}', [KepalaTokoPosController::class, 'show'])->name('show-print-order');

    Route::get('transaksi-produk-inkjet/{id}', [KepalaTokoTransaksiProdukController::class, 'cetakinkjet'])->name('lunas-cetak-inkjet');
    Route::get('transaksi-produk-termal/{orders_id}', [KepalaTokoTransaksiProdukController::class, 'cetaktermal'])->name('cetak-termal');

    Route::get('laporan/laporan-servis', [KepalaTokoLaporanServisController::class, 'index'])->name('laporan-servis');
    Route::get('laporan/laporan-pajak-servis', [KepalaTokoLaporanServisController::class, 'indexPajak'])->name('laporan-pajak-servis');
    Route::get('cetak-laporan-proses', [KepalaTokoTransaksiServisController::class, 'cetak'])->name('cetak-laporan-proses');
    Route::get('cetak-laporan-bisa-diambil', [KepalaTokoBisaDiambilController::class, 'cetak'])->name('cetak-laporan-bisa-diambil');
    Route::get('cetak-laporan-servis', [KepalaTokoLaporanServisController::class, 'cetak'])->name('cetak-laporan-servis');
    Route::get('cetak-laporan-pajak-servis', [KepalaTokoLaporanServisController::class, 'cetakPajak'])->name('cetak-laporan-pajak-servis');
    Route::get('cetak-laporan-teknisi', [KepalaTokoLaporanTeknisiController::class, 'cetak'])->name('cetak-laporan-teknisi');
    Route::get('cetak-laporan-pengeluaran', [KepalaTokoExpenseController::class, 'cetak'])->name('cetak-laporan-pengeluaran');
    Route::get('laporan/laporan-teknisi', [KepalaTokoLaporanTeknisiController::class, 'index'])->name('laporan-teknisi');
    Route::get('laporan/laporan-penjualan', [KepalaTokoLaporanPenjualanController::class, 'index'])->name('laporan-penjualan');
    Route::get('laporan/laporan-pajak-penjualan', [KepalaTokoLaporanPenjualanController::class, 'indexPajak'])->name('laporan-pajak-penjualan');
    Route::get('cetak-laporan-penjualan', [KepalaTokoLaporanPenjualanController::class, 'cetak'])->name('cetak-laporan-penjualan');
    Route::get('cetak-laporan-pajak-penjualan', [KepalaTokoLaporanPenjualanController::class, 'cetakPajak'])->name('cetak-laporan-pajak-penjualan');
    Route::get('cetak-laporan-sales', [KepalaTokoLaporanSalesController::class, 'cetak'])->name('cetak-laporan-sales');
    Route::get('laporan/laporan-sales', [KepalaTokoLaporanSalesController::class, 'index'])->name('laporan-sales');
    Route::get('laporan/laporan-admin', [KepalaTokoLaporanAdminController::class, 'index'])->name('laporan-admin');
    Route::get('cetak-laporan-admin', [KepalaTokoLaporanAdminController::class, 'cetak'])->name('cetak-laporan-admin');
    Route::post('cetak-label-inventaris', [KepalaTokoInventarisController::class, 'printSelected'])->name('cetak-label-inventaris');
    Route::get('cetak-laporan-produk', [KepalaTokoProdukController::class, 'cetak'])->name('cetak-laporan-produk');
    Route::get('cetak-laporan-produk-handphone', [KepalaTokoProdukHandphoneController::class, 'cetak'])->name('cetak-laporan-produk-handphone');
    Route::get('cetak-laporan-produk-sparepart', [KepalaTokoProdukSparepartController::class, 'cetak'])->name('cetak-laporan-produk-sparepart');

    Route::get('pengaturan/profil', [KepalaTokoInformasiTokoController::class, 'index'])->name('informasi-toko');
    Route::post('pengaturan/profil', [KepalaTokoInformasiTokoController::class, 'update'])->name('informasi-toko-update');
    Route::get('pengaturan/sistem', [KepalaTokoSistemController::class, 'index'])->name('sistem');
    Route::post('pengaturan/sistem', [KepalaTokoSistemController::class, 'update'])->name('sistem-update');
    Route::get('pengaturan/syarat-ketentuan', [KepalaTokoTermController::class, 'index'])->name('syarat-ketentuan');
    Route::post('pengaturan/syarat-ketentuan-terima', [KepalaTokoTermController::class, 'updateterima'])->name('ketentuan-terima-update');
    Route::post('pengaturan/syarat-ketentuan-pengambilan', [KepalaTokoTermController::class, 'updatepengambilan'])->name('ketentuan-pengambilan-update');
    Route::post('pengaturan/syarat-ketentuan-penjualan', [KepalaTokoTermController::class, 'updatepenjualan'])->name('ketentuan-penjualan-update');

    Route::get('servis/ubah-status-proses/{id}', [KepalaTokoUbahStatusProsesServisController::class, 'edit'])->name('ubah-status-proses-edit');
    Route::post('servis/ubah-status-proses{id}', [KepalaTokoUbahStatusProsesServisController::class, 'update'])->name('ubah-status-proses-update');
    Route::get('servis/multi-teknisi/{id}', [KepalaTokoUbahBisaDiambilController::class, 'multiTeknisi'])->name('multi-teknisi');
    Route::post('servis/multi-teknisi-proses/{id}', [KepalaTokoUbahBisaDiambilController::class, 'multiTeknisiProses'])->name('multi-teknisi-proses');
    Route::get('servis/ubah-bisa-diambil/{id}', [KepalaTokoUbahBisaDiambilController::class, 'edit'])->name('ubah-bisa-diambil-edit');
    Route::post('servis/ubah-bisa-diambil{id}', [KepalaTokoUbahBisaDiambilController::class, 'update'])->name('ubah-bisa-diambil-update');
    Route::get('servis/ubah-sudah-diambil/{id}', [KepalaTokoUbahSudahDiambilController::class, 'edit'])->name('ubah-sudah-diambil-edit');
    Route::post('servis/ubah-sudah-diambil{id}', [KepalaTokoUbahSudahDiambilController::class, 'update'])->name('ubah-sudah-diambil-update');
    Route::post('servis/kembali-proses{id}', [KepalaTokoBisaDiambilController::class, 'back'])->name('kembali-proses');
    Route::post('servis/kembali-bisa-diambil{id}', [KepalaTokoSudahDiambilController::class, 'back'])->name('kembali-bisa-diambil');

    Route::get('nota-terima-termal/{id}', [KepalaTokoTransaksiServisController::class, 'cetaktermal'])->name('kepalatoko-cetak-termal');
    Route::get('nota-pengambilan-inkjet/{id}', [KepalaTokoSudahDiambilController::class, 'cetakinkjet'])->name('kepalatoko-pengambilan-cetak-inkjet');
    Route::get('kepalatoko-nota-pengambilan-termal/{id}', [KepalaTokoSudahDiambilController::class, 'pengambilantermal'])->name('kepalatoko-nota-pengambilan-termal');


    Route::post('/impor-pelanggan', [KepalaTokoPelangganController::class, 'import'])->name('impor-pelanggan');
    Route::get('export-pelanggan', [KepalaTokoPelangganController::class, 'export'])->name('pelanggan-export');
    Route::post('/impor-tindakan-servis', [KepalaTokoTindakanServisController::class, 'import'])->name('impor-tindakan-servis');
    Route::get('export-tindakan', [KepalaTokoTindakanServisController::class, 'export'])->name('tindakan-servis-export');
    Route::post('/impor-brand', [KepalaTokoMasterMerekController::class, 'import'])->name('impor-merek');
    Route::get('export-merek', [KepalaTokoMasterMerekController::class, 'export'])->name('merek-export');
    Route::post('/impor-model', [KepalaTokoMasterModelSeriController::class, 'import'])->name('impor-model');
    Route::get('export-modelseri', [KepalaTokoMasterModelSeriController::class, 'export'])->name('modelseri-export');
    Route::get('ekspor-handphone', [KepalaTokoProdukHandphoneController::class, 'export'])->name('ekspor-handphone');
    Route::post('/impor-handphone', [KepalaTokoProdukHandphoneController::class, 'import'])->name('impor-handphone');
    Route::get('ekspor-sparepart', [KepalaTokoProdukSparepartController::class, 'export'])->name('ekspor-sparepart');
    Route::post('/impor-sparepart', [KepalaTokoProdukSparepartController::class, 'import'])->name('impor-sparepart');
    Route::get('ekspor-aksesoris', [KepalaTokoProdukAksesorisController::class, 'export'])->name('ekspor-aksesoris');
    Route::post('/impor-aksesoris', [KepalaTokoProdukAksesorisController::class, 'import'])->name('impor-aksesoris');
    Route::get('ekspor-tool', [KepalaTokoProdukToolController::class, 'export'])->name('ekspor-tool');
    Route::post('/impor-tool', [KepalaTokoProdukToolController::class, 'import'])->name('impor-tool');
});

// Route::get('izin', [MasterIzinController::class, 'cetakinkjet/{id}'])->name('kepalatoko-cetak-inkjet');
Route::get('nota-qc/{id}', [KepalaTokoTransaksiServisController::class, 'cetakQc'])->name('kepalatoko-cetak-qc');
Route::get('nota-qc-garansi/{id}', [HistoryGaransiController::class, 'cetakQcGaransi'])->name('kepalatoko-cetak-qc-garansi');
Route::get('nota-terima-inkjet/{id}', [KepalaTokoTransaksiServisController::class, 'cetakinkjet'])->name('kepalatoko-cetak-inkjet');
Route::get('nota-pengambilan-inkjet/{id}', [KepalaTokoSudahDiambilController::class, 'cetakinkjet'])->name('kepalatoko-pengambilan-cetak-inkjet');
Route::get('transaksi-produk-inkjet/{id}', [KepalaTokoTransaksiProdukController::class, 'cetakinkjet'])->name('lunas-cetak-inkjet');
Route::post('/servis/admin-transaksi-servis-langsung', [AdminTokoTransaksiServisLangsungController::class, 'store'])->name('admin-servis-langsung');

Route::middleware(['ensureAdminRole:AdminToko', 'checkSubscription','jam_kerja'])->group(function () {
    Route::get('/admin-dashboard', [AdminTokoDashboardController::class, 'index'])->name('admintoko-dashboard');
    Route::resource('servis/admin-tindakan-servis', AdminTokoTindakanServisController::class);
    Route::resource('admin-pelanggan', AdminTokoPelangganController::class);
    Route::post('admin-pelanggan-broadcast', [AdminTokoPelangganController::class,'broadcast'])->name('admin.pelanggan.broadcast');

    // Route::resource('servis/admin-transaksi-servis', AdminTokoTransaksiServisController::class);
    // Route::post('servis/admin-transaksi-servis/{id}/update-pin-pola', [AdminTokoTransaksiServisController::class, 'updatePinPola'])
    // ->name('transaksi-servis.update-pin-pola');

    // Route::post('/servis/admin-transaksi-servis-langsung', [AdminTokoTransaksiServisLangsungController::class, 'store'])->name('admin-servis-langsung');
    // Route::resource('servis/admin-servis-bisa-diambil', AdminTokoBisaDiambilController::class);
    // Route::resource('servis/admin-servis-sudah-diambil', AdminTokoSudahDiambilController::class);
    Route::resource('master/admin-master-jenis-barang', AdminTokoMasterJenisBarangController::class);
    Route::resource('master/admin-master-merek', AdminTokoMasterMerekController::class);
    Route::resource('master/admin-master-kapasitas', AdminTokoMasterKapasitasController::class);
    Route::resource('master/admin-master-model-seri', AdminTokoMasterModelSeriController::class);
    Route::resource('master/admin-master-warna', AdminTokoMasterWarnaController::class);
    Route::resource('admin-insiden', AdminTokoInsidenController::class);
    Route::resource('admin-kasbon', AdminTokoKasbonController::class);
    Route::resource('admin-pengeluaran', AdminTokoExpenseController::class);
    Route::resource('admin-inventaris', AdminTokoInventarisController::class);

    Route::get('tandaterima-inkjet/{id}', [AdminTokoTransaksiServisController::class, 'cetakinkjet'])->name('admin-cetak-tanda-terima');
    Route::get('tandaterima-termal/{id}', [AdminTokoTransaksiServisController::class, 'cetaktermal'])->name('admin-cetak-termal');
    Route::get('admin-nota-pengambilan-inkjet/{id}', [AdminTokoSudahDiambilController::class, 'cetakinkjet'])->name('admin-inkjet-pengambilan');
    Route::get('admin-nota-pengambilan-termal/{id}', [AdminTokoSudahDiambilController::class, 'cetaktermal'])->name('admin-termal-pengambilan');

    Route::get('servis/admin-ubah-status-proses/{id}', [AdminTokoUbahStatusProsesServisController::class, 'edit'])->name('admin-ubah-status-proses-edit');
    Route::post('servis/admin-ubah-status-proses{id}', [AdminTokoUbahStatusProsesServisController::class, 'update'])->name('admin-ubah-status-proses-update');
    Route::get('servis/admin-ubah-bisa-diambil/{id}', [AdminTokoUbahBisaDiambilController::class, 'edit'])->name('admin-ubah-bisa-diambil-edit');
    Route::post('servis/admin-ubah-bisa-diambil{id}', [AdminTokoUbahBisaDiambilController::class, 'update'])->name('admin-ubah-bisa-diambil-update');
    Route::get('servis/admin-ubah-sudah-diambil/{id}', [AdminTokoUbahSudahDiambilController::class, 'edit'])->name('admin-ubah-sudah-diambil-edit');
    Route::post('servis/admin-ubah-sudah-diambil{id}', [AdminTokoUbahSudahDiambilController::class, 'update'])->name('admin-ubah-sudah-diambil-update');

    Route::post('/importindakanservis', [AdminTokoTindakanServisController::class, 'import'])->name('admin-impor-tindakan-servis');
    Route::post('/imporbrand', [AdminTokoMasterMerekController::class, 'import'])->name('admin-impor-merek');
    Route::post('/impormodel', [AdminTokoMasterModelSeriController::class, 'import'])->name('admin-impor-model');
    Route::post('/imporpelanggan', [AdminTokoPelangganController::class, 'import'])->name('admin-impor-pelanggan');

    Route::get('tindakan/export', [AdminTokoTindakanServisController::class, 'export'])->name('admin-tindakan-servis-export');
    Route::get('modelseri/export', [AdminTokoMasterModelSeriController::class, 'export'])->name('admin-modelseri-export');
    Route::get('merek/export', [AdminTokoMasterMerekController::class, 'export'])->name('admin-merek-export');
    Route::get('customer/export', [AdminTokoPelangganController::class, 'export'])->name('admin-pelanggan-export');

    Route::get('admin-ekspor-handphone', [AdminTokoProdukHandphoneController::class, 'export'])->name('admin-ekspor-handphone');
    Route::post('/admin-impor-handphone', [AdminTokoProdukHandphoneController::class, 'import'])->name('admin-impor-handphone');
    Route::get('admin-ekspor-sparepart', [AdminTokoProdukSparepartController::class, 'export'])->name('admin-ekspor-sparepart');
    Route::post('/admin-impor-sparepart', [AdminTokoProdukSparepartController::class, 'import'])->name('admin-impor-sparepart');
    Route::get('admin-ekspor-aksesoris', [AdminTokoProdukAksesorisController::class, 'export'])->name('admin-ekspor-aksesoris');
    Route::post('/admin-impor-aksesoris', [AdminTokoProdukAksesorisController::class, 'import'])->name('admin-impor-aksesoris');
    Route::get('admin-ekspor-tool', [AdminTokoProdukToolController::class, 'export'])->name('admin-ekspor-tool');
    Route::post('/admin-impor-tool', [AdminTokoProdukToolController::class, 'import'])->name('admin-impor-tool');

    Route::resource('produk/admin-kategori', AdminTokoKategoriController::class);
    Route::resource('produk/admin-sub-kategori', AdminTokoSubKategoriController::class);
    Route::resource('produk/admin-item', AdminTokoProdukController::class);

    Route::post('/admin-products/update-portal', [AdminTokoProdukController::class, 'updatePortal'])->name('admin.products.updatePortal');

    Route::get('top-produk', [AdminTokoProdukController::class, 'indexTop'])->name('top-produk');

    Route::resource('produk/admin-handphone', AdminTokoProdukHandphoneController::class);
    Route::resource('produk/admin-sparepart', AdminTokoProdukSparepartController::class);
    Route::resource('produk/admin-aksesoris', AdminTokoProdukAksesorisController::class);
    Route::resource('produk/admin-tool', AdminTokoProdukToolController::class);
    Route::resource('produk/admin-item-tersedia', AdminTokoProdukTersediaController::class);
    Route::resource('produk/admin-item-habis', AdminTokoProdukHabisController::class);
    Route::resource('produk/admin-supplier', AdminTokoSupplierController::class);
    Route::resource('produk/admin-pos', AdminTokoPosController::class);
    Route::resource('produk/admin-transaksi-produk', AdminTokoTransaksiProdukController::class);
    Route::resource('produk/admin-transaksi-produk-paid', AdminTokoTransaksiProdukPaidController::class);
    Route::resource('produk/admin-transaksi-produk-due', AdminTokoTransaksiProdukDueController::class);
    Route::resource('produk/admin-purchase', AdminTokoPurchaseProductController::class);
    Route::resource('produk/admin-retur', AdminTokoReturProductController::class);
    Route::resource('produk/admin-tukar-tambah', AdminTokoTukarTambahController::class);
    Route::get('admin-cetak-laporan-tukar-tambah', [AdminTokoTukarTambahController::class, 'cetak'])->name('admin-cetak-laporan-tukar-tambah');

    Route::get('produk/admin-pos', [AdminTokoPosController::class, 'index'])->name('admin-pos');
    Route::get('produk/admin-allitem', [AdminTokoPosController::class, 'AllItem']);
    Route::post('produk/admin-add-cart', [AdminTokoPosController::class, 'AddCart']);
    Route::post('produk/admin-cart-update/{rowId}', [AdminTokoPosController::class, 'CartUpdate']);
    Route::post('produk/admin-apply-discount', [AdminTokoPosController::class, 'ApplyDiscount'])->name('admin-produk.applyDiscount');
    Route::get('produk/admin-cart-remove/{rowId}', [AdminTokoPosController::class, 'CartRemove']);
    Route::post('produk/admin-create-invoice', [AdminTokoPosController::class, 'CreateInvoice']);
    Route::post('produk/admin-complete-order', [AdminTokoPosController::class, 'CompleteOrder']);
    Route::get('produk/admin-pos/{id}', [AdminTokoPosController::class, 'show'])->name('admin-show-print-order');


    Route::post('/admin-import-produk', [AdminTokoProdukController::class, 'import'])->name('admin-import-produk');
    Route::get('admin-export-produk', [AdminTokoProdukController::class, 'export'])->name('admin-produk-export');

    Route::get('laporan/admin-laporan-servis', [AdminTokoLaporanServisController::class, 'index'])->name('admin-laporan-servis');

    Route::get('laporan/admin-laporan-penjualan', [AdminTokoLaporanPenjualanController::class, 'index'])->name('admin-laporan-penjualan');
    Route::get('admin-cetak-laporan-penjualan', [AdminTokoLaporanPenjualanController::class, 'cetak'])->name('admin-cetak-laporan-penjualan');
});
Route::get('admin-cetak-laporan-servis', [AdminTokoLaporanServisController::class, 'cetak'])->name('admin-cetak-laporan-servis');

Route::middleware(['ensureTeknisiRole:Teknisi', 'checkSubscription','jam_kerja'])->group(function () {
    Route::get('/teknisi-dashboard', [TeknisiDashboardController::class, 'index'])->name('teknisi-dashboard');
    Route::resource('teknisi-pengeluaran', TeknisiExpenseController::class);
    Route::resource('teknisi-pelanggan', TeknisiPelangganController::class);
    Route::resource('teknisi-tindakan-servis', TeknisiTindakanServisController::class);
    Route::resource('teknisi-transaksi-servis', TeknisiTransaksiServisController::class);
    Route::post('/teknisi-transaksi-servis-langsung', [TeknisiTransaksiServisLangsungController::class, 'store'])->name('teknisi-servis-langsung');
    Route::resource('master/teknisi-master-model-seri', TeknisiMasterModelSeriController::class);
    Route::get('teknisi/tandaterima-inkjet/{id}', [TeknisiTransaksiServisController::class, 'cetakinkjet'])->name('teknisi-cetak-tanda-terima');
    Route::get('teknisi/tandaterima-termal/{id}', [TeknisiTransaksiServisController::class, 'cetaktermal'])->name('teknisi-cetak-termal');
    Route::get('/teknisi-laporan', [TeknisiLaporanTeknisiController::class, 'index'])->name('teknisi-laporan');

    Route::get('teknisi/ubah-status-proses/{id}', [TeknisiUbahStatusProsesServisController::class, 'edit'])->name('teknisi-ubah-status-proses-edit');
    Route::post('teknisi/ubah-status-proses{id}', [TeknisiUbahStatusProsesServisController::class, 'update'])->name('teknisi-ubah-status-proses-update');
    Route::get('teknisi/servis-ubah-bisa-diambil/{id}', [TeknisiUbahBisaDiambilController::class, 'edit'])->name('teknisi-ubah-bisa-diambil-edit');
    Route::post('teknisi/servis-ubah-bisa-diambil{id}', [TeknisiUbahBisaDiambilController::class, 'update'])->name('teknisi-ubah-bisa-diambil-update');

    Route::resource('produk/teknisi-item', TeknisiProdukController::class);
    Route::resource('teknisi-kasbon', TeknisiKasbonController::class);
});

Route::middleware(['ensureSalesRole:Sales', 'checkSubscription','jam_kerja'])->group(
    function () {
        Route::get('/sales-dashboard', [SalesDashboardController::class, 'index'])->name('sales-dashboard');
        Route::resource('sales-pelanggan', SalesPelangganController::class);
        Route::resource('sales-pengeluaran', SalesExpenseController::class);

        Route::resource('produk/sales-kategori', SalesKategoriController::class);
        Route::resource('produk/sales-sub-kategori', SalesSubKategoriController::class);
        Route::resource('produk/sales-item', SalesProdukController::class);
        Route::resource('produk/sales-handphone', SalesProdukHandphoneController::class);
        Route::resource('produk/sales-sparepart', SalesProdukSparepartController::class);
        Route::resource('produk/sales-aksesoris', SalesProdukAksesorisController::class);
        Route::resource('produk/sales-tool', SalesProdukToolController::class);
        Route::resource('produk/sales-pos', SalesPosController::class);
        Route::resource('produk/sales-transaksi-produk', SalesTransaksiProdukController::class);
        Route::resource('produk/sales-transaksi-produk-paid', SalesTransaksiProdukPaidController::class);
        Route::resource('produk/sales-transaksi-produk-due', SalesTransaksiProdukDueController::class);

        Route::get('produk/sales-pos', [SalesPosController::class, 'index'])->name('sales-pos');
        Route::get('produk/sales-allitem', [SalesPosController::class, 'AllItem']);
        Route::post('produk/sales-add-cart', [SalesPosController::class, 'AddCart']);
        Route::post('produk/sales-cart-update/{rowId}', [SalesPosController::class, 'CartUpdate']);
        Route::post('produk/sales-apply-discount', [SalesPosController::class, 'ApplyDiscount'])->name('sales-produk.applyDiscount');
        Route::get('produk/sales-cart-remove/{rowId}', [SalesPosController::class, 'CartRemove']);
        Route::post('produk/sales-create-invoice', [SalesPosController::class, 'CreateInvoice']);
        Route::post('produk/sales-complete-order', [SalesPosController::class, 'CompleteOrder']);
        Route::get('produk/sales-pos/{id}', [SalesPosController::class, 'show'])->name('sales-show-print-order');

        Route::get('sales-transaksi-produk-inkjet/{orders_id}', [SalesTransaksiProdukController::class, 'cetakinkjet'])->name('sales-lunas-cetak-inkjet');
        Route::get('sales-transaksi-produk-termal/{orders_id}', [SalesTransaksiProdukController::class, 'cetaktermal'])->name('sales-cetak-termal-produk');

        Route::post('/sales-import-produk', [SalesProdukController::class, 'import'])->name('sales-import-produk');
        Route::get('sales-export-produk', [SalesProdukController::class, 'export'])->name('sales-produk-export');

        Route::get('sales-laporan-penjualan', [SalesLaporanPenjualanController::class, 'index'])->name('sales-laporan-penjualan');

        Route::resource('sales-kasbon', SalesKasbonController::class);
    }
);

Route::get('admin-transaksi-produk-inkjet/{orders_id}', [AdminTokoTransaksiProdukController::class, 'cetakinkjet'])->name('admin-lunas-cetak-inkjet');
Route::get('admin-transaksi-produk-termal/{orders_id}', [AdminTokoTransaksiProdukController::class, 'cetaktermal'])->name('admin-cetak-termal-produk');


Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/old-dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

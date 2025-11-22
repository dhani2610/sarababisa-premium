<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StoreSetting;
use App\Models\ServiceTransaction;
use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class SendDailyTelegramReport extends Command
{
    protected $signature = 'report:telegram';
    protected $description = 'Kirim laporan harian ke Telegram jika jam sudah sesuai';

    public function handle()
    {
        $setting = StoreSetting::where('cabang_id',getCabangId())->first();

        // Skip jika tidak diatur
        if (!$setting || !$setting->token_bot || !$setting->chat_id || !$setting->report_time) {
            return Command::SUCCESS;
        }

        $now = Carbon::now()->format('H:i');

        if ($now !== Carbon::parse($setting->report_time)->format('H:i')) {
            return Command::SUCCESS;
        }

        $nowDate = date('Y-m-d');

        /*
        |--------------------------------------------------------------------------
        | LAPORAN SERVIS
        |--------------------------------------------------------------------------
        */
        $total_servis = ServiceTransaction::where('status_servis', 'Sudah Diambil')
            ->whereDate('tgl_ambil', $nowDate)
            ->count();

        $total_tunai_servis = ServiceTransaction::where('status_servis', 'Sudah Diambil')
            ->whereDate('tgl_ambil', $nowDate)
            ->sum('tunai');

        $total_transfer_servis = ServiceTransaction::where('status_servis', 'Sudah Diambil')
            ->whereDate('tgl_ambil', $nowDate)
            ->sum('transfer');

        $total_kredit_servis = ServiceTransaction::where('status_servis', 'Sudah Diambil')
            ->whereDate('tgl_ambil', $nowDate)
            ->sum('due');

        $total_diskon_servis = ServiceTransaction::where('is_approve', 'Setuju')
            ->whereDate('tgl_ambil', $nowDate)
            ->sum('diskon');

        $total_biaya_servis = ServiceTransaction::where('status_servis', 'Sudah Diambil')
            ->whereDate('tgl_ambil', $nowDate)
            ->sum('biaya');

        $total_modal_servis = ServiceTransaction::where('status_servis', 'Sudah Diambil')
            ->whereDate('tgl_ambil', $nowDate)
            ->sum('modal_sparepart');

        $total_profit_servis = ServiceTransaction::where('status_servis', 'Sudah Diambil')
            ->whereDate('tgl_ambil', $nowDate)
            ->sum('profit');

        /*
        |--------------------------------------------------------------------------
        | LAPORAN PENJUALAN
        |--------------------------------------------------------------------------
        */
        $total_biaya_penjualan = OrderDetail::whereDate('created_at', $nowDate)->sum('total');
        $total_profit_penjualan = OrderDetail::whereDate('created_at', $nowDate)->sum('profit');
        $total_penjualan_item = OrderDetail::whereDate('created_at', $nowDate)->sum('quantity');
        $total_modal_penjualan = OrderDetail::whereDate('created_at', $nowDate)->sum('modal');

        $sub_total_penjualan = OrderDetail::whereDate('created_at', $nowDate)->sum('sub_total');
        $total_diskon_penjualan = $sub_total_penjualan - $total_biaya_penjualan;

        $total_tunai_penjualan = Order::whereDate('created_at', $nowDate)->sum('tunai');
        $total_transfer_penjualan = Order::whereDate('created_at', $nowDate)->sum('transfer');
        $total_kredit_penjualan = Order::whereDate('created_at', $nowDate)->sum('due');

        /*
        |--------------------------------------------------------------------------
        | TOTAL PENGELUARAN & SALDO
        |--------------------------------------------------------------------------
        */
        $total_pengeluaran = Expense::whereDate('created_at', $nowDate)->sum('price');

        // 🔹 SALDO GABUNGAN = profit servis + profit penjualan - pengeluaran
        $saldo_akhir = ($total_profit_servis + $total_profit_penjualan) - $total_pengeluaran;

        /*
        |--------------------------------------------------------------------------
        | FORMAT PESAN TELEGRAM
        |--------------------------------------------------------------------------
        */
        $message =
            "📅 *Laporan Harian - " . Carbon::today()->format('d M Y') . "*\n\n" .

            "🔧 *LAPORAN SERVIS*\n" .
            "🧾 Total Servis: *{$total_servis} item*\n" .
            "💰 Tunai: Rp " . number_format($total_tunai_servis, 0, ',', '.') . "\n" .
            "🏦 Transfer: Rp " . number_format($total_transfer_servis, 0, ',', '.') . "\n" .
            "💳 Kredit: Rp " . number_format($total_kredit_servis, 0, ',', '.') . "\n" .
            "🏷️ Diskon: Rp " . number_format($total_diskon_servis, 0, ',', '.') . "\n" .
            "⚙️ Modal Sparepart: Rp " . number_format($total_modal_servis, 0, ',', '.') . "\n" .
            "💵 Profit Servis: Rp " . number_format($total_profit_servis, 0, ',', '.') . "\n\n" .

            "🛒 *LAPORAN PENJUALAN*\n" .
            "📦 Total Item: *{$total_penjualan_item} item*\n" .
            "💰 Tunai: Rp " . number_format($total_tunai_penjualan, 0, ',', '.') . "\n" .
            "🏦 Transfer: Rp " . number_format($total_transfer_penjualan, 0, ',', '.') . "\n" .
            "💳 Kredit: Rp " . number_format($total_kredit_penjualan, 0, ',', '.') . "\n" .
            "🏷️ Diskon: Rp " . number_format($total_diskon_penjualan, 0, ',', '.') . "\n" .
            "📈 Total Omzet: Rp " . number_format($total_biaya_penjualan, 0, ',', '.') . "\n" .
            "⚙️ Total Modal: Rp " . number_format($total_modal_penjualan, 0, ',', '.') . "\n" .
            "💵 Profit Penjualan: Rp " . number_format($total_profit_penjualan, 0, ',', '.') . "\n\n" .

            "💸 *Total Pengeluaran:* Rp " . number_format($total_pengeluaran, 0, ',', '.') . "\n" .
            "📊 *Saldo Akhir (Servis + Penjualan):* Rp " . number_format($saldo_akhir, 0, ',', '.');

        $this->info($message);

        /*
        |--------------------------------------------------------------------------
        | KIRIM TELEGRAM
        |--------------------------------------------------------------------------
        */
        Http::post("https://api.telegram.org/bot{$setting->token_bot}/sendMessage", [
            'chat_id' => $setting->chat_id,
            'text' => $message,
            'parse_mode' => 'Markdown',
        ]);

        $this->info('✅ Laporan harian Telegram terkirim dengan penjualan.');
        return Command::SUCCESS;
    }
}

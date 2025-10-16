<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StoreSetting;
use App\Models\ServiceTransaction;
use App\Models\Expense;
use App\Models\Incident;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use DB;

class SendDailyTelegramReport extends Command
{
    protected $signature = 'report:telegram';
    protected $description = 'Kirim laporan harian ke Telegram jika jam sudah sesuai';

    public function handle()
    {
        $setting = StoreSetting::find(1);

        // Skip jika tidak diatur
        if (!$setting || !$setting->token_bot || !$setting->chat_id || !$setting->report_time) {
            return Command::SUCCESS;
        }

        $now = Carbon::now()->format('H:i');
        if ($now !== Carbon::parse($setting->report_time)->format('H:i')) {
            return Command::SUCCESS;
        }

        $start_date = Carbon::today();
        $end_date = Carbon::today();

        $total_servis = ServiceTransaction::where('status_servis', 'Sudah Diambil')
            ->whereBetween('tgl_ambil', [$start_date, $end_date])
            ->count();

        $total_tunai = ServiceTransaction::where('status_servis', 'Sudah Diambil')
            ->whereBetween('tgl_ambil', [$start_date, $end_date])
            ->sum('tunai');

        $total_transfer = ServiceTransaction::where('status_servis', 'Sudah Diambil')
            ->whereBetween('tgl_ambil', [$start_date, $end_date])
            ->sum('transfer');

        $total_kredit = ServiceTransaction::where('status_servis', 'Sudah Diambil')
            ->whereBetween('tgl_ambil', [$start_date, $end_date])
            ->sum('due');

        $total_diskon = ServiceTransaction::where('is_approve', 'Setuju')
            ->whereBetween('tgl_ambil', [$start_date, $end_date])
            ->sum('diskon');

        $total_biaya = ServiceTransaction::where('status_servis', 'Sudah Diambil')
            ->whereBetween('tgl_ambil', [$start_date, $end_date])
            ->sum('biaya');

        $total_modal = ServiceTransaction::where('status_servis', 'Sudah Diambil')
            ->whereBetween('tgl_ambil', [$start_date, $end_date])
            ->sum('modal_sparepart');

        $total_profit = ServiceTransaction::where('status_servis', 'Sudah Diambil')
            ->whereBetween('tgl_ambil', [$start_date, $end_date])
            ->sum('profit');

        $total_pengeluaran = Expense::whereBetween('created_at', [$start_date, $end_date])->sum('price');
        $saldo_akhir = $total_profit - $total_pengeluaran;

        $message =
            "📅 *Laporan Harian - " . Carbon::today()->format('d M Y') . "*\n\n" .
            "🧾 Total Servis: *{$total_servis} item*\n" .
            "💰 Total Tunai: Rp " . number_format($total_tunai, 0, ',', '.') . "\n" .
            "🏦 Total Transfer: Rp " . number_format($total_transfer, 0, ',', '.') . "\n" .
            "💳 Total Kredit: Rp " . number_format($total_kredit, 0, ',', '.') . "\n" .
            "🏷️ Total Diskon: Rp " . number_format($total_diskon, 0, ',', '.') . "\n" .
            "🔧 Total Biaya Servis: Rp " . number_format($total_biaya, 0, ',', '.') . "\n" .
            "⚙️ Total Modal Sparepart: Rp " . number_format($total_modal, 0, ',', '.') . "\n" .
            "💵 Total Profit: Rp " . number_format($total_profit, 0, ',', '.') . "\n" .
            "💸 Total Pengeluaran: Rp " . number_format($total_pengeluaran, 0, ',', '.') . "\n\n" .
            "📊 *Saldo Akhir:* Rp " . number_format($saldo_akhir, 0, ',', '.');

        Http::post("https://api.telegram.org/bot{$setting->token_bot}/sendMessage", [
            'chat_id' => $setting->chat_id,
            'text' => $message,
            'parse_mode' => 'Markdown',
        ]);

        $this->info('Laporan harian Telegram terkirim.');
        return Command::SUCCESS;
    }
}

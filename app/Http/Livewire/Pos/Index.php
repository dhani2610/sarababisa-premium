<?php

declare(strict_types=1);

namespace App\Http\Livewire\Pos;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use Livewire\Component;
use App\Models\Customer;
use App\Models\OrderDetail;
use App\Models\SalePayment;
use App\Enums\PaymentStatus;
use App\Models\StoreSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\Http;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TransaksiBaruNotification;
class Index extends Component
{
    use LivewireAlert;

    /** @var array<string> */
    public $listeners = [
        'refreshIndex' => '$refresh',
        'refreshCustomers','updatedCustomerId'
    ];

    public $cart_instance;

    public $discountModal;

    public $global_discount;

    public $global_tax;

    public $quantity;

    public $check_quantity;

    public $price;

    public $discount_type;

    public $item_discount;

    public $data;

    public $users_id;

    public $customers;
    public $customer_id;

    public $total_amount;

    public $checkoutModal;

    public $product;

    public $paid_amount;

    public $tax_percentage;

    public $discount_percentage;

    public $discount_amount;

    public $tax_amount;

    public $grand_total;

    public $note;
    public $tgl_disetujui;

    public $refreshCustomers;

    public $payment_method;
    public $tempo;
    public $tunai = 0;
    public $transfer = 0;

    public $customer_tipe;

    public $is_manual_customer = false; // Checkbox toggle
    public $manual_nama;
    public $manual_kategori;
    public $manual_nomor_hp;
    public $manual_alamat;
    public $tipe_status_pembayaran;

    // Saat ID Customer (Select2) berubah
    public function updatedCustomerId($value)
    {
        if(!$this->is_manual_customer){
            $customer = Customer::find($value);
            if ($customer) {
                $this->customer_tipe = $customer->kategori;
                $this->emit('updateCustomerType', $customer->kategori);
            }
        }
    }

    // === LOGIKA BARU: Saat Checkbox Manual diklik ===
    public function updatedIsManualCustomer($value)
    {
        // Reset semua field terkait customer
        $this->reset(['customer_id', 'manual_nama', 'manual_kategori', 'manual_nomor_hp', 'manual_alamat', 'customer_tipe']);

        // Emit null ke search product biar ke-reset
        $this->emit('updateCustomerType', null);
    }

    // === LOGIKA BARU: Saat Kategori Manual Dipilih ===
    // Ini PENTING agar SearchProduct tetap jalan sesuai kategori yg dipilih manual
    public function updatedManualKategori($value)
    {
        $this->customer_tipe = $value;
        $this->emit('updateCustomerType', $value);
    }
    public function rules(): array
    {
        // Validasi Dinamis
        if ($this->is_manual_customer) {
            return [
                'manual_nama'     => 'required|string|max:255',
                'manual_kategori' => 'required|in:User,Toko',
                'manual_nomor_hp' => 'required|numeric',
                'manual_alamat'   => 'required|string',
                'total_amount'    => 'required|numeric',
                'paid_amount'     => 'nullable|numeric',
                'note'            => 'nullable|string|max:1000',
            ];
        } else {
            return [
                'customer_id'     => 'required|numeric',
                'total_amount'    => 'required|numeric',
                'paid_amount'     => 'nullable|numeric',
                'price'           => 'nullable|numeric',
                'note'            => 'nullable|string|max:1000',
            ];
        }
    }

    public function mount($cartInstance): void
    {
        $this->cart_instance = $cartInstance;
        $this->global_discount = 0;
        $this->global_tax = 0;

        $this->check_quantity = [];
        $this->quantity = [];
        $this->discount_type = [];
        $this->item_discount = [];

        $this->tax_percentage = 0;
        $this->paid_amount = 0;
        $this->tipe_status_pembayaran = 0;

        $this->customers = Customer::where('cabang_id',getCabangId())->get();
    }



    public function updatedPaymentMethod($value): void
    {
        if ($value === 'Kredit') {
            $this->paid_amount = 0;
        } else {
            $this->paid_amount = $this->total_amount;
        }
    }

    // public function updatedCashPayment($value): void
    // {
    //     if ($this->payment_method === 'Split') {
    //         $this->transfer = $this->total_amount - $value;
    //     }
    // }

    // public function updatedTransferPayment($value): void
    // {
    //     if ($this->payment_method === 'Split') {
    //         $this->tunai = $this->total_amount - $value;
    //     }
    // }

    public function hydrate(): void
    {
        $this->total_amount = $this->calculateTotal();
    }

    public function render()
    {
        $cart_items = Cart::instance($this->cart_instance)->content();
        $toko = StoreSetting::where('cabang_id',getCabangId())->first();
        $users = User::forCabang()->where('role', 'Sales')->get();

        return view('livewire.pos.index', [
            'cart_items' => $cart_items,
            'toko' => $toko,
            'users' => $users,
        ]);
    }

    public function store(): void
    {
        DB::transaction(function () {
            $this->validate();

            $this->paid_amount = (int) preg_replace('/\D/', '', $this->paid_amount);
            $this->tunai       = (int) preg_replace('/\D/', '', $this->tunai);
            $this->transfer    = (int) preg_replace('/\D/', '', $this->transfer);


            if ($this->is_manual_customer) {
                $newCustomer = Customer::create([
                    'nama'       => $this->manual_nama,
                    'kategori'   => $this->manual_kategori,
                    'nomor_hp'   => $this->manual_nomor_hp,
                    'alamat'     => $this->manual_alamat,
                    'cabang_id'  => getCabangId(),
                    // Tambahkan field default lain jika perlu (misal: code)
                ]);

                // Set ID customer baru ke variabel utama agar logika di bawah tetap jalan
                $this->customer_id = $newCustomer->id;

                // Refresh list customer lokal agar kalau balik ke menu select, user baru ada
                $this->customers = Customer::where('cabang_id',getCabangId())->get();
            }

            // Determine payment status
            $due_amount = $this->total_amount - $this->paid_amount;

            if ($due_amount === $this->total_amount) {
                $payment_status = PaymentStatus::PENDING;
            } elseif ($due_amount > 0) {
                $payment_status = PaymentStatus::PARTIAL;
            } else {
                $payment_status = PaymentStatus::PAID;
            }

            $nama_pelanggan = Customer::find($this->customer_id);

            $this->transfer = $this->paid_amount;

            if ($this->payment_method === 'Tunai & Transfer') {
                if ($this->tunai != 0) {
                    $this->transfer = $this->total_amount - $this->tunai;
                } else {
                    $this->tunai = $this->total_amount - $this->transfer;
                }
            }

            if ($this->payment_method === 'Tunai') {
                $this->tunai = $this->paid_amount;
            }

            if ($this->payment_method === 'Transfer') {
                $this->transfer = $this->paid_amount;
            }

            if ($this->payment_method === 'Kredit') {
                if ($this->tunai) {
                    $this->tunai = $this->paid_amount;
                    $this->transfer = 0;
                } elseif ($this->transfer) {
                    $this->transfer = $this->paid_amount;
                    $this->tunai = 0;
                }
            }

            $waktu = Carbon::today();
            if ($this->tempo != null) {
                $tempo = $waktu->addDays(
                    $this->tempo
                );
            } else {
                $tempo = null;
            }

            $toko = StoreSetting::where('cabang_id',getCabangId())->first();
            $cartTax = Cart::instance($this->cart_instance)->tax(); // Pajak dari Cart

            $sale = Order::create([
                'order_date'          => \Carbon\Carbon::today()->locale('id')->translatedFormat('d F Y'),
                'customers_id'         => $this->customer_id,
                'users_id'             => $this->users_id,
                'discount_amount'     => Cart::instance('sale')->discount(),
                'pay'             => $this->paid_amount,
                'due'          => $due_amount,
                'tempo'          => $tempo,
                'sub_total'        => $this->total_amount,
                'total_products'          => Cart::instance($this->cart_instance)->count(),
                'invoice_no'          => intval(date('Ymd') . mt_rand(0, 999)),
                'nama_pelanggan'          => $nama_pelanggan->nama,
                'payment_status'      => $payment_status,
                'payment_method'      => $this->payment_method,
                'tipe_status_pembayaran'      => $this->tipe_status_pembayaran,
                'tunai'      => $this->tunai,
                'transfer'      => $this->transfer,
                'note'                => $this->note,
                'is_approve'      => Auth::user()->role == 'Kepala Toko' ? 'Setuju' : null,
                'tgl_disetujui'      => date('Y-m-d'),
                'cabang_id'      => getCabangId(),
            ]);

            // foreach ($this->cart_instance as cart_items) {}
            foreach (Cart::instance('sale')->content() as $cart_item) {

                $persen_sales = User::find($this->users_id);

                $garansi = Carbon::now();
                if ($cart_item->options->garansi != null) {
                    $expired = $garansi->addDays(
                        $cart_item->options->garansi
                    );
                } else {
                    $expired = null;
                }

                $garansi_imei = Carbon::now();
                if ($cart_item->options->garansi_imei != null) {
                    $expired_imei = $garansi_imei->addDays(
                        $cart_item->options->garansi_imei
                    );
                } else {
                    $expired_imei = null;
                }

                OrderDetail::create([
                    'orders_id'                 => $sale->id,
                    'users_id'            => $sale->users_id,
                    'persen_sales'      => $persen_sales->persen,
                    'products_id'              => $cart_item->id,
                    'product_name'                    => $cart_item->name,
                    'quantity'                => $cart_item->qty,
                    'price'                   => $cart_item->price,
                    'ppn'      => $cartTax, // Pajak dari Cart
                    'total'               => $cart_item->options->sub_total,
                    'sub_total'               => $cart_item->options->unit_price * $cart_item->qty,
                    'product_discount_amount' => $cart_item->options->product_discount,
                    'modal'               => $cart_item->options->modal * $cart_item->qty,
                    'profit'               => $cart_item->options->sub_total - ($cart_item->options->modal * $cart_item->qty),
                    'profit_toko'               => ($cart_item->options->sub_total - ($cart_item->options->modal * $cart_item->qty)) - ($cart_item->options->sub_total - ($cart_item->options->modal * $cart_item->qty)) / 100 * $persen_sales->persen,
                    // 'profit_toko'               => ($cart_item->options->sub_total - ($cart_item->options->modal * $cart_item->qty)) - ($cart_item->options->sub_total - ($cart_item->options->modal * $cart_item->qty)) / 100 * ,
                    // 'ppn'      => $cart_item->options->product_tax,
                    'garansi'      => $expired,
                    'garansi_imei'      => $expired_imei,
                    'payment_method'      => $this->payment_method,
                    'is_admin_toko'      => Auth::user()->role == 'Admin Toko' ? 'Admin' : null,
                    'admin_id'      => Auth::user()->role == 'Admin Toko' ? Auth::user()->id : null,
                    'cabang_id'      => getCabangId(),
                ]);

                $product = Product::findOrFail($cart_item->id);

                $new_quantity = $product->stok - $cart_item->qty;

                $product->update([
                    'stok' => $new_quantity,
                ]);
            }

            Cart::instance('sale')->destroy();

            if ($sale->paid_amount > 0) {
                SalePayment::create([
                    'date'           => date('Y-m-d'),
                    'amount'         => $sale->paid_amount,
                    'orders_id'        => $sale->id,
                    'payment_method' => $this->users_id,
                    'users_id'        => Auth::user()->id,
                    'cabang_id'      => getCabangId(),
                ]);
            }

            // === 📢 Kirim Notifikasi Telegram ===
            try {
                $tglTransaksi = Carbon::parse($sale->created_at)->locale('id')->translatedFormat('d F Y');

                $pesan = "🧾 *TRANSAKSI PENJUALAN BARU*\n\n"
                    . "📄 *No. Invoice:* {$sale->invoice_no}\n"
                    . "📅 *Tanggal:* {$tglTransaksi}\n"
                    . "👤 *Customer:* {$nama_pelanggan->nama}\n\n"
                    . "💰 *Modal:* Rp " . number_format($sale->sub_total - $sale->discount_amount, 0, ',', '.') . "\n"
                    . "💸 *Jumlah Pembayaran:* Rp " . number_format((float) $sale->pay, 0, ',', '.') . "\n"
                    . "🏦 *Metode Pembayaran:* {$sale->payment_method}\n\n";

                // Kirim ke Telegram
                $storeSetting = \App\Models\StoreSetting::where('cabang_id',getCabangId())->first();
                if ($storeSetting && $storeSetting->token_bot && $storeSetting->chat_id) {
                    Http::post("https://api.telegram.org/bot{$storeSetting->token_bot}/sendMessage", [
                        'chat_id' => $storeSetting->chat_id,
                        'text' => $pesan,
                        'parse_mode' => 'Markdown',
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error("Gagal kirim Telegram: " . $e->getMessage());
            }

            try {
                $kepalaToko = User::find(1);

                if($kepalaToko) {
                    $judulNotif = 'Penjualan Baru Masuk! 📦';
                    $isiNotif   = "No Invoice: {$sale->invoice_no}\nPelanggan: {$nama_pelanggan->nama}";
                    $linkNotif  = url('produk/transaksi-produk');

                    // Kirim ke notifikasi
                    Notification::send($kepalaToko, new TransaksiBaruNotification($judulNotif, $isiNotif, $linkNotif));
                }

            } catch (\Exception $e) {
                \Log::error("Gagal kirim notifikasi: " . $e->getMessage());
            }


            $this->alert('success', 'Transaksi penjualan berhasil dibuat!');

            $this->checkoutModal = false;

            Cart::instance('sale')->destroy();

            return redirect()->route('show-print-order', $sale->id);
        });
    }

    // can you solve that issue please
    // customer should provoke checkout
    public function proceed(): void
    {
        // PERBAIKAN DISINI:
        // Izinkan lanjut jika customer_id ada ATAU jika sedang input manual dan datanya lengkap
        if ($this->customer_id !== null || ($this->is_manual_customer && $this->manual_nama && $this->manual_kategori)) {
            $this->checkoutModal = true;
            $this->cart_instance = 'sale';
        } else {
            $this->alert('error', 'Pilih pelanggan atau lengkapi data pelanggan manual!');
        }
    }

    public function calculateTotal(): mixed
    {
        return Cart::instance($this->cart_instance)->total();
    }

    public function resetCart(): void
    {
        Cart::instance($this->cart_instance)->destroy();
    }
}

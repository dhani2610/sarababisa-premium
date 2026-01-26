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
use App\Enums\PaymentStatus;
use App\Models\StoreSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\Http;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class SalesIndex extends Component
{
    use LivewireAlert;

    /** @var array<string> */
    public $listeners = [
        'refreshIndex' => '$refresh',
        'refreshCustomers',
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

    public function rules(): array
    {
        return [
            'customer_id'         => 'required|numeric',
            'total_amount'        => 'required|numeric',
            'paid_amount'         => 'nullable|numeric',
            'price'               => 'nullable|numeric',
            'note'                => 'nullable|string|max:1000',
        ];
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

        $this->customers = Customer::all();
    }

    public function updatedCustomerId($value)
    {
        $this->customer_id = $value;
    }

    public function updatedPaymentMethod($value): void
    {
        if ($value === 'Kredit') {
            $this->paid_amount = 0;
        } else {
            $this->paid_amount = $this->total_amount;
        }
    }

    public function hydrate(): void
    {
        $this->total_amount = $this->calculateTotal();
    }

    public function render()
    {
        $cart_items = Cart::instance($this->cart_instance)->content();
        $toko = StoreSetting::where('cabang_id',getCabangId())->first();

        return view('livewire.pos.sales-index', [
            'cart_items' => $cart_items,
            'toko' => $toko,
        ]);
    }

    public function store(): void
    {
        DB::transaction(function () {
            $this->validate();

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
                'users_id'             => Auth::user()->id,
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
                'tunai'      => $this->tunai,
                'transfer'      => $this->transfer,
                'note'                => $this->note,
                'cabang_id'  => getCabangId(),
            ]);

            // foreach ($this->cart_instance as cart_items) {}
            foreach (Cart::instance('sale')->content() as $cart_item) {

                $persen_sales = Auth::user()->persen;

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
                    'users_id'            => Auth::user()->id,
                    'persen_sales'      => $persen_sales,
                    'products_id'              => $cart_item->id,
                    'product_name'                    => $cart_item->name,
                    'quantity'                => $cart_item->qty,
                    'price'                   => $cart_item->price,
                    'ppn'      => $cartTax, // Pajak dari Cart
                    'sub_total'               => $cart_item->price * $cart_item->qty,
                    'total'               => $cart_item->options->sub_total + $cartTax,
                    'product_discount_amount' => $cart_item->options->product_discount,
                    'modal'               => $cart_item->options->modal * $cart_item->qty,
                    'profit'               => $cart_item->options->sub_total - ($cart_item->options->modal * $cart_item->qty),
                    'profit_toko'               => ($cart_item->options->sub_total - ($cart_item->options->modal * $cart_item->qty)) - ($cart_item->options->sub_total - ($cart_item->options->modal * $cart_item->qty)) / 100 * $persen_sales,
                    // 'ppn'      => $cart_item->options->product_tax, // Hapus pajak produk
                    'garansi'      => $expired,
                    'garansi_imei'      => $expired_imei,
                    'payment_method'      => $this->payment_method,
                    'cabang_id'  => getCabangId(),
                ]);

                $product = Product::findOrFail($cart_item->id);

                $new_quantity = $product->stok - $cart_item->qty;

                $product->update([
                    'stok' => $new_quantity,
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

            Cart::instance('sale')->destroy();

            $this->alert('success', 'Transaksi penjualan berhasil dibuat!');

            $this->checkoutModal = false;

            Cart::instance('sale')->destroy();

            return redirect()->route('sales-show-print-order', $sale->id);
        });
    }

    // can you solve that issue please
    // customer should provoke checkout
    public function proceed(): void
    {
        if ($this->customer_id !== null) {
            $this->checkoutModal = true;
            $this->cart_instance = 'sale';
        } else {
            $this->alert('error', 'Pilih pelanggan terlebih dahulu!');
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

    public function getCustomersProperty()
    {
        return Customer::select(['nama', 'id', 'nomor_hp'])->get();
    }
}

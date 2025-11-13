<?php

declare(strict_types=1);

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\StoreSetting;
use Gloudemans\Shoppingcart\Facades\Cart;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class ProductCart extends Component
{
    use LivewireAlert;

    /** @var array<string> */
    public $listeners = [
        'productSelected',
        'discountModalRefresh',
    ];

    public $cart_instance;

    public $global_discount = 0;

    public $global_tax = 0;

    public $discountModal = false;

    public $quantity = [];

    public $price;

    public $check_quantity = [];

    public $discount_type;

    public $item_discount;

    public $data;

    public function mount($cartInstance, $data = null)
    {
        $this->cart_instance = $cartInstance;
        $this->discount_type = [];
        $this->item_discount = [];
        $this->global_tax = optional(StoreSetting::find(1))->is_tax
        ? StoreSetting::find(1)->ppn
        : 0;

        if ($data) {
            $this->data = $data;

            $this->global_discount = $data->discount_percentage;
            $this->global_tax = $data->tax_percentage;

            $this->updatedGlobalTax();
            $this->updatedGlobalDiscount();

            $cart_items = Cart::instance($this->cart_instance)->content();

            foreach ($cart_items as $index => $cart_item) {
                $this->check_quantity[$cart_item->id] = [$cart_item->options->stock];
                $this->quantity[$cart_item->id] = $cart_item->qty;
                $this->discount_type[$cart_item->id] = $cart_item->options->product_discount_type;
                $this->item_discount[$cart_item->id] = ($cart_item->options->product_discount_type === 'fixed')
                    ? $cart_item->options->product_discount
                    : round(100 * $cart_item->options->product_discount / $cart_item->harga_jual);
            }
        } else {
            $this->updatedGlobalTax();
            $this->updatedGlobalDiscount();
        }
    }

    public function productSelected($product): void
    {
        if (empty($product)) {
            $this->alert('error', 'Terjadi kesalahan!');

            return;
        }

        $cart = Cart::instance($this->cart_instance);
        $exists = $cart->search(fn($cartItem) => $cartItem->id === $product['id']);

        if ($exists->isNotEmpty()) {
            $this->alert('error', 'Produk sudah ditambahkan ke keranjang');

            return;
        }

        $cartItem = $this->createCartItem($product);

        $cart->add($cartItem);
        $this->updateQuantityAndCheckQuantity($product['id'], $product['stok']);
    }

    public function calculate($product): array
    {
        $this->updatedGlobalTax();
        return $this->calculatePrices($product);
    }

    // private function calculatePrices($product)
    // {
    //     $price = $product['harga_jual'];
    //     $unit_price = $price;
    //     $sub_total = $price;

    //     return ['price' => $price, 'unit_price' => $unit_price, 'sub_total' => $sub_total];
    // }
    private function calculatePrices($product)
    {
        $kategori = $product['customer_tipe'] ?? ($this->data->tipe ?? 'User');

        switch ($kategori) {
            case 'Toko':
                $price = $product['harga_jual_toko'] ?? $product['harga_jual'];
                break;
            case 'Distributor':
                $price = $product['harga_jual_distributor'] ?? $product['harga_jual'];
                break;
            default:
                $price = $product['harga_jual'];
        }

        $unit_price = $price;
        $sub_total  = $price;

        return [
            'price'      => $price,
            'unit_price' => $unit_price,
            'sub_total'  => $sub_total,
        ];
    }


    private function updateQuantityAndCheckQuantity($productId, $quantity)
    {
        $this->check_quantity[$productId] = $quantity;
        $this->quantity[$productId] = 1;
    }

    private function createCartItem($product)
    {
        $calculation = $this->calculate($product);

        $kategori = $product['customer_tipe'] ?? ($this->data->tipe ?? 'User');

        switch ($kategori) {
            case 'Toko':
                $price = $product['harga_jual_toko'] ?? $product['harga_jual'];
                break;
            case 'Distributor':
                $price = $product['harga_jual_distributor'] ?? $product['harga_jual'];
                break;
            default:
                $price = $product['harga_jual'];
        }

        return [
            'id'      => $product['id'],
            'name'    => $product['product_name'],
            'qty'     => 1,
            'price'   =>$price,
            'weight'  => 1,
            'options' => array_merge($calculation, [
                'product_discount'      => 0,
                'product_discount_type' => 'fixed',
                'code'                  => $product['product_code'],
                'stock'                 => $product['stok'],
                'modal'                 => $product['harga_modal'],
                'garansi'                 => $product['garansi'],
                'garansi_imei'                 => $product['garansi_imei'],
            ]),
        ];
    }

    public function updatePrice($row_id, $product_id)
    {
        Cart::instance($this->cart_instance)->update($row_id, [
            'price' => $this->price[$product_id],
        ]);

        $cart_item = Cart::instance($this->cart_instance)->get($row_id);

        Cart::instance($this->cart_instance)->update($row_id, [
            'options' => [
                'sub_total'             => $cart_item->price * $cart_item->qty,
                'code'                  => $cart_item->options->code,
                'stock'                 => $cart_item->options->stock,
                'unit'                  => $cart_item->options->unit,
                'unit_price'            => $cart_item->price,
                'modal'      => $cart_item->options->modal,
                'product_discount'      => $cart_item->options->product_discount,
                'product_discount_type' => $cart_item->options->product_discount_type,
            ],
        ]);
    }

    public function updatedGlobalTax()
    {
        Cart::instance($this->cart_instance)->setGlobalTax((int) $this->global_tax);
    }

    public function updatedGlobalDiscount()
    {
        Cart::instance($this->cart_instance)->setGlobalDiscount((int) $this->global_discount);
    }

    public function discountModal($product_id, $row_id): void
    {
        $this->updateQuantity($row_id, $product_id);

        $this->discountModal = true;
    }

    public function updateQuantity($row_id, $product_id)
    {
        if ($this->cart_instance === 'sale' || $this->cart_instance === 'purchase_return') {
            if ($this->check_quantity[$product_id] < $this->quantity[$product_id]) {
                $this->alert('error', 'Nilai Jumlah lebih banyak dari stok yang tersedia!');

                return;
            }
        }

        Cart::instance($this->cart_instance)->update($row_id, $this->quantity[$product_id]);

        $cart_item = Cart::instance($this->cart_instance)->get($row_id);

        Cart::instance($this->cart_instance)->update($row_id, [
            'options' => [
                'sub_total'             => $cart_item->price * $cart_item->qty,
                'code'                  => $cart_item->options->code,
                'stock'                 => $cart_item->options->stock,
                'unit'                  => $cart_item->options->unit,
                'unit_price'            => $cart_item->options->unit_price,
                'product_discount'      => $cart_item->options->product_discount,
                'product_discount_type' => $cart_item->options->product_discount_type,
                'modal'      => $cart_item->options->modal,
            ],
        ]);
    }

    public function removeItem($row_id)
    {
        Cart::instance($this->cart_instance)->remove($row_id);
    }

    public function updatedDiscountType($value, $name)
    {
        $this->item_discount[$name] = 0;
    }

    public function productDiscount($row_id, $product_id): void
    {
        $cart_item = Cart::instance($this->cart_instance)->get($row_id);

        if ($this->discount_type[$product_id] === 'fixed') {
            Cart::instance($this->cart_instance)
                ->update($row_id, [
                    'price' => $cart_item->price + $cart_item->options->product_discount - $this->item_discount[$product_id],
                ]);

            $discount_amount = $this->item_discount[$product_id];

            $this->updateCartOptions($row_id, $product_id, $cart_item, $discount_amount);
        } elseif ($this->discount_type[$product_id] === 'percentage') {
            $discount_amount = ($cart_item->price + $cart_item->options->product_discount) * $this->item_discount[$product_id] / 100;

            Cart::instance($this->cart_instance)
                ->update($row_id, [
                    'price' => $cart_item->price + $cart_item->options->product_discount - $discount_amount,
                ]);

            $this->updateCartOptions($row_id, $product_id, $cart_item, $discount_amount);
        }
        $this->alert('success', 'Diskon produk berhasil diterapkan!');

        $this->discountModal = false;
    }

    public function updateCartOptions($row_id, $product_id, $cart_item, $discount_amount)
    {
        Cart::instance($this->cart_instance)->update($row_id, [
            'options' => [
                'sub_total'             => $cart_item->price * $cart_item->qty,
                'code'                  => $cart_item->options->code,
                'stock'                 => $cart_item->options->stock,
                'unit'                  => $cart_item->options->unit,
                'unit_price'            => $cart_item->options->unit_price,
                'product_discount'      => $discount_amount,
                'product_discount_type' => $cart_item->options->product_discount_type,
                'modal'      => $cart_item->options->modal,
            ],
        ]);
    }

    public function render()
    {
        $toko = StoreSetting::find(1);
        $cart_items = Cart::instance($this->cart_instance)->content();

        // foreach ($cart_items as $index => $cart_item) {
        //     $this->discount_type[$cart_item->id] = $cart_item->options->product_discount_type;
        //     $this->item_discount[$cart_item->id] = ($cart_item->options->product_discount_type === 'fixed')
        //         ? $cart_item->options->product_discount
        //         : round(100 * $cart_item->options->product_discount / $cart_item->price);
        // }

        foreach ($cart_items as $index => $cart_item) {
            if (!isset($this->discount_type[$cart_item->id])) {
                $this->discount_type[$cart_item->id] = $cart_item->options->product_discount_type;
            }

            if (!isset($this->item_discount[$cart_item->id])) {
                $this->item_discount[$cart_item->id] = ($cart_item->options->product_discount_type === 'fixed')
                    ? $cart_item->options->product_discount
                    : round(100 * $cart_item->options->product_discount / $cart_item->price);
            }
        }


        return view('livewire.product-cart', [
            'cart_items' => $cart_items,
            'toko' => $toko,
        ]);
    }
}

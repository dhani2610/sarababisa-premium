<?php

namespace App\Models;

use App\Models\Customer;
use App\Models\Product;
use App\Models\OrderDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Order extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Transaksi Penjualan')
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
        // Chain fluent methods for configuration options
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customers_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id', 'id');
    }

    public function detailOrders()
    {
        return $this->hasMany(OrderDetail::class, 'orders_id', 'id');
    }

    public function returnStock()
    {
        foreach ($this->detailOrders as $detail) {
            if ($detail->products_id) {
                $product = Product::find($detail->products_id);
                if ($product) {
                    $qty = (int) ($detail->quantity ?? 1);
                    $product->stok = (int) $product->stok + $qty;
                    $product->save();
                }
            }
        }
    }

    public function deductStock()
    {
        foreach ($this->detailOrders as $detail) {
            if ($detail->products_id) {
                $product = Product::find($detail->products_id);
                if ($product) {
                    $qty = (int) ($detail->quantity ?? 1);
                    $product->stok = max(0, (int) $product->stok - $qty);
                    $product->save();
                }
            }
        }
    }
}

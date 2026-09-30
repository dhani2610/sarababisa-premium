'use client';

import React, { useState, useEffect } from 'react';
import api, { API_BASE_URL } from '@/lib/api';
import Swal from 'sweetalert2';
import {
  ShoppingCart,
  Search,
  Plus,
  Minus,
  Trash2,
  DollarSign,
  Printer,
  Receipt,
  FileText,
  User,
  CreditCard,
  Banknote,
  CheckCircle2,
  AlertCircle,
  Package,
} from 'lucide-react';

interface CartItem {
  row_id: string;
  produk_id: number;
  nama: string;
  kode: string;
  qty: number;
  harga: number;
  subtotal: number;
  diskon: number;
}

export default function PosPage() {
  const [products, setProducts] = useState<any[]>([]);
  const [cart, setCart] = useState<CartItem[]>([]);
  const [search, setSearch] = useState('');
  const [isLoadingProducts, setIsLoadingProducts] = useState(true);

  // Checkout inputs
  const [pelangganNama, setPelangganNama] = useState('Pelanggan Umum');
  const [pelangganHp, setPelangganHp] = useState('');
  const [metodePembayaran, setMetodePembayaran] = useState('Tunai');
  const [diskonGlobal, setDiskonGlobal] = useState(0);
  const [uangBayar, setUangBayar] = useState(0);
  const [catatan, setCatatan] = useState('');
  const [isCheckingOut, setIsCheckingOut] = useState(false);

  // Success state with completed order ID for printing
  const [completedOrder, setCompletedOrder] = useState<any | null>(null);

  const fetchProducts = async () => {
    setIsLoadingProducts(true);
    try {
      const res = await api.get('/pos/produk', {
        params: { search: search || undefined },
      });
      setProducts(res.data || []);
    } catch (err) {
      console.error('Error fetching POS products', err);
    } finally {
      setIsLoadingProducts(false);
    }
  };

  useEffect(() => {
    fetchProducts();
  }, [search]);

  // Cart operations (Local state + synchronizable with POS backend)
  const addToCart = (product: any) => {
    if (product.stok <= 0) {
      Swal.fire('Stok Kosong', 'Produk ini sedang habis stok.', 'warning');
      return;
    }

    setCart((prev) => {
      const existing = prev.find((item) => item.produk_id === product.id);
      if (existing) {
        if (existing.qty >= product.stok) {
          Swal.fire('Batas Stok', `Maksimal stok tersedia: ${product.stok}`, 'info');
          return prev;
        }
        return prev.map((item) =>
          item.produk_id === product.id
            ? {
                ...item,
                qty: item.qty + 1,
                subtotal: (item.qty + 1) * item.harga - item.diskon,
              }
            : item
        );
      } else {
        const newItem: CartItem = {
          row_id: `row_${Date.now()}_${product.id}`,
          produk_id: product.id,
          nama: product.nama || product.product_name,
          kode: product.kode || product.product_code || '',
          qty: 1,
          harga: Number(product.harga_jual || 0),
          subtotal: Number(product.harga_jual || 0),
          diskon: 0,
        };
        return [...prev, newItem];
      }
    });
  };

  const updateQty = (produkId: number, delta: number) => {
    setCart((prev) =>
      prev
        .map((item) => {
          if (item.produk_id === produkId) {
            const newQty = item.qty + delta;
            if (newQty <= 0) return null;
            return {
              ...item,
              qty: newQty,
              subtotal: newQty * item.harga - item.diskon,
            };
          }
          return item;
        })
        .filter(Boolean) as CartItem[]
    );
  };

  const removeFromCart = (produkId: number) => {
    setCart((prev) => prev.filter((item) => item.produk_id !== produkId));
  };

  const clearCart = () => {
    setCart([]);
  };

  // Calculations
  const subtotal = cart.reduce((acc, item) => acc + item.subtotal, 0);
  const total = Math.max(0, subtotal - diskonGlobal);
  const kembalian = Math.max(0, uangBayar - total);
  const sisaKurang = Math.max(0, total - uangBayar);

  // Complete Order
  const handleCheckout = async () => {
    if (cart.length === 0) {
      Swal.fire('Keranjang Kosong', 'Tambahkan produk terlebih dahulu.', 'warning');
      return;
    }

    setIsCheckingOut(true);
    try {
      const payload = {
        pelanggan_nama: pelangganNama,
        pelanggan_hp: pelangganHp,
        metode_pembayaran: metodePembayaran,
        items: cart.map((c) => ({
          produk_id: c.produk_id,
          nama_produk: c.nama,
          qty: c.qty,
          harga: c.harga,
          diskon: c.diskon,
          total: c.subtotal,
        })),
        diskon: Number(diskonGlobal),
        pay: Number(uangBayar),
        catatan,
      };

      const res = await api.post('/pos/complete-order', payload);
      setCompletedOrder({
        order_id: res.data.order_id,
        invoice_no: res.data.invoice_no,
        total,
        pay: uangBayar,
        kembali: kembalian,
      });

      clearCart();
      setDiskonGlobal(0);
      setUangBayar(0);

      Swal.fire({
        icon: 'success',
        title: 'Transaksi Berhasil!',
        text: `Invoice: ${res.data.invoice_no}. Silakan cetak nota transaksi.`,
      });
    } catch (err: any) {
      Swal.fire('Gagal Transaksi', err.response?.data?.detail || 'Terjadi kesalahan sistem', 'error');
    } finally {
      setIsCheckingOut(false);
    }
  };

  const printPdf = (orderId: number, type: 'termal' | 'inkjet') => {
    const token = localStorage.getItem('token');
    const url = `${API_BASE_URL}/pos/${orderId}/cetak/${type}?token=${token}`;
    window.open(url, '_blank');
  };

  const formatRupiah = (val: number = 0) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
    }).format(val);
  };

  return (
    <div className="space-y-4">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
            <ShoppingCart className="w-6 h-6 text-blue-600" />
            <span>Kasir POS (Point of Sale)</span>
          </h1>
          <p className="text-xs text-slate-500">
            Transaksi kasir cepat & pencetakan struk termal standar 80mm / nota A4.
          </p>
        </div>
      </div>

      {/* Main Grid: Catalog vs Cart */}
      <div className="grid grid-cols-1 lg:grid-cols-12 gap-5">
        {/* Left Column: Product Catalog (7 cols) */}
        <div className="lg:col-span-7 space-y-4">
          {/* Search Box */}
          <div className="relative">
            <input
              type="text"
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="Cari nama produk, SKU, barcode..."
              className="w-full pl-10 pr-4 py-2.5 text-xs bg-white border border-slate-200 rounded-xl shadow-xs focus:outline-hidden focus:border-blue-500 transition-all"
            />
            <Search className="w-4 h-4 text-slate-400 absolute left-3.5 top-3 pointer-events-none" />
          </div>

          {/* Product Cards Grid */}
          <div className="grid grid-cols-2 sm:grid-cols-3 gap-3 max-h-[calc(100vh-250px)] overflow-y-auto pr-1">
            {isLoadingProducts ? (
              <div className="col-span-3 py-16 text-center text-slate-400 text-xs">
                Memuat katalog produk...
              </div>
            ) : products.length === 0 ? (
              <div className="col-span-3 py-16 text-center text-slate-400 text-xs">
                Tidak ada produk ditemukan.
              </div>
            ) : (
              products.map((p) => {
                const isOutOfStock = p.stok <= 0;
                return (
                  <button
                    key={p.id}
                    type="button"
                    disabled={isOutOfStock}
                    onClick={() => addToCart(p)}
                    className={`p-3.5 rounded-xl border text-left flex flex-col justify-between transition-all cursor-pointer ${
                      isOutOfStock
                        ? 'bg-slate-100/60 border-slate-200 opacity-60 cursor-not-allowed'
                        : 'bg-white border-slate-200/90 hover:border-blue-500 hover:shadow-md hover:-translate-y-0.5'
                    }`}
                  >
                    <div>
                      <span className="text-[10px] font-mono text-slate-400 block">{p.kode || '-'}</span>
                      <h4 className="text-xs font-bold text-slate-800 line-clamp-2 mt-0.5">
                        {p.nama || p.product_name}
                      </h4>
                    </div>

                    <div className="mt-3 pt-2 border-t border-slate-100 flex items-center justify-between">
                      <span className="text-xs font-black text-blue-700">
                        {formatRupiah(p.harga_jual)}
                      </span>
                      <span
                        className={`text-[10px] font-bold px-1.5 py-0.5 rounded-md ${
                          isOutOfStock
                            ? 'bg-rose-100 text-rose-700'
                            : 'bg-slate-100 text-slate-700'
                        }`}
                      >
                        {isOutOfStock ? 'Habis' : `Stok: ${p.stok}`}
                      </span>
                    </div>
                  </button>
                );
              })
            )}
          </div>
        </div>

        {/* Right Column: Active Cart & Checkout (5 cols) */}
        <div className="lg:col-span-5 bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex flex-col justify-between space-y-4">
          <div>
            <div className="flex items-center justify-between pb-3 border-b border-slate-100">
              <div className="flex items-center space-x-2">
                <ShoppingCart className="w-4 h-4 text-blue-600" />
                <h3 className="text-sm font-bold text-slate-800">Keranjang Transaksi</h3>
              </div>
              {cart.length > 0 && (
                <button
                  onClick={clearCart}
                  className="text-[11px] font-semibold text-rose-600 hover:underline cursor-pointer"
                >
                  Kosongkan
                </button>
              )}
            </div>

            {/* Cart Items List */}
            <div className="divide-y divide-slate-100 max-h-56 overflow-y-auto my-2 pr-1">
              {cart.length === 0 ? (
                <div className="py-12 text-center text-slate-400 text-xs">
                  Keranjang kosong. Klik produk di sebelah kiri untuk menambahkan.
                </div>
              ) : (
                cart.map((item) => (
                  <div key={item.produk_id} className="py-2.5 flex items-center justify-between text-xs">
                    <div className="max-w-[55%]">
                      <p className="font-bold text-slate-800 truncate">{item.nama}</p>
                      <span className="text-[10px] text-slate-400 font-mono">
                        {formatRupiah(item.harga)} x {item.qty}
                      </span>
                    </div>

                    <div className="flex items-center space-x-2">
                      <div className="flex items-center border border-slate-200 rounded-lg">
                        <button
                          onClick={() => updateQty(item.produk_id, -1)}
                          className="p-1 hover:bg-slate-100 text-slate-600 cursor-pointer"
                        >
                          <Minus className="w-3 h-3" />
                        </button>
                        <span className="px-2 font-bold text-slate-800 text-[11px]">{item.qty}</span>
                        <button
                          onClick={() => updateQty(item.produk_id, 1)}
                          className="p-1 hover:bg-slate-100 text-slate-600 cursor-pointer"
                        >
                          <Plus className="w-3 h-3" />
                        </button>
                      </div>

                      <span className="font-bold text-slate-900 w-16 text-right">
                        {formatRupiah(item.subtotal)}
                      </span>

                      <button
                        onClick={() => removeFromCart(item.produk_id)}
                        className="text-slate-400 hover:text-rose-600 cursor-pointer"
                      >
                        <Trash2 className="w-3.5 h-3.5" />
                      </button>
                    </div>
                  </div>
                ))
              )}
            </div>
          </div>

          {/* Checkout Calculations and Forms */}
          <div className="space-y-3 pt-3 border-t border-slate-100 text-xs">
            <div className="grid grid-cols-2 gap-2">
              <div>
                <label className="text-[10px] font-semibold text-slate-500 block mb-0.5">Nama Pelanggan</label>
                <input
                  type="text"
                  value={pelangganNama}
                  onChange={(e) => setPelangganNama(e.target.value)}
                  className="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg focus:outline-hidden"
                />
              </div>
              <div>
                <label className="text-[10px] font-semibold text-slate-500 block mb-0.5">Metode Bayar</label>
                <select
                  value={metodePembayaran}
                  onChange={(e) => setMetodePembayaran(e.target.value)}
                  className="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg focus:outline-hidden bg-white"
                >
                  <option value="Tunai">Tunai / Cash</option>
                  <option value="Transfer">Transfer Bank</option>
                  <option value="QRIS">QRIS / E-Wallet</option>
                </select>
              </div>
            </div>

            <div className="grid grid-cols-2 gap-2">
              <div>
                <label className="text-[10px] font-semibold text-slate-500 block mb-0.5">Diskon Global (Rp)</label>
                <input
                  type="number"
                  value={diskonGlobal}
                  onChange={(e) => setDiskonGlobal(Number(e.target.value))}
                  className="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg focus:outline-hidden"
                />
              </div>
              <div>
                <label className="text-[10px] font-semibold text-slate-500 block mb-0.5">Uang Diterima / Bayar (Rp)</label>
                <input
                  type="number"
                  value={uangBayar}
                  onChange={(e) => setUangBayar(Number(e.target.value))}
                  className="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg focus:outline-hidden font-bold text-slate-900"
                />
              </div>
            </div>

            {/* Total and Change summary */}
            <div className="bg-slate-50 p-3 rounded-xl space-y-1.5">
              <div className="flex justify-between text-slate-600">
                <span>Subtotal:</span>
                <span>{formatRupiah(subtotal)}</span>
              </div>
              <div className="flex justify-between text-sm font-black text-slate-900 pt-1 border-t border-slate-200">
                <span>Total Belanja:</span>
                <span className="text-blue-700">{formatRupiah(total)}</span>
              </div>
              {uangBayar > 0 && (
                <div className="flex justify-between text-xs font-bold pt-1">
                  <span>{kembalian >= 0 ? 'Kembalian:' : 'Kurang (Piutang):'}</span>
                  <span className={kembalian >= 0 ? 'text-emerald-600' : 'text-rose-600'}>
                    {formatRupiah(kembalian >= 0 ? kembalian : sisaKurang)}
                  </span>
                </div>
              )}
            </div>

            {/* Checkout Button */}
            <button
              type="button"
              disabled={cart.length === 0 || isCheckingOut}
              onClick={handleCheckout}
              className="w-full py-3 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white rounded-xl font-bold tracking-wide shadow-md shadow-blue-500/25 transition-all cursor-pointer flex items-center justify-center space-x-2"
            >
              <span>{isCheckingOut ? 'Memproses Order...' : 'Selesaikan Transaksi & Bayar'}</span>
            </button>
          </div>
        </div>
      </div>

      {/* Completed Order Modal with Print buttons */}
      {completedOrder && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4">
          <div className="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4 text-center">
            <div className="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto">
              <CheckCircle2 className="w-7 h-7" />
            </div>

            <div>
              <h3 className="text-lg font-black text-slate-800">Transaksi Berhasil!</h3>
              <p className="text-xs text-slate-500 mt-1 font-mono">Invoice: {completedOrder.invoice_no}</p>
            </div>

            <div className="bg-slate-50 p-4 rounded-xl border border-slate-200 text-xs space-y-1 text-left">
              <div className="flex justify-between">
                <span className="text-slate-500">Total Transaksi:</span>
                <span className="font-bold text-slate-800">{formatRupiah(completedOrder.total)}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-slate-500">Uang Diterima:</span>
                <span className="font-bold text-slate-800">{formatRupiah(completedOrder.pay)}</span>
              </div>
              <div className="flex justify-between font-bold text-emerald-600 pt-1 border-t border-slate-200">
                <span>Kembalian:</span>
                <span>{formatRupiah(completedOrder.kembali)}</span>
              </div>
            </div>

            {/* Print Buttons (Direct ReportLab PDF Streaming) */}
            <div className="grid grid-cols-2 gap-3 pt-2">
              <button
                onClick={() => printPdf(completedOrder.order_id, 'termal')}
                className="flex items-center justify-center space-x-2 py-2.5 px-3 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 transition-all cursor-pointer"
              >
                <Receipt className="w-4 h-4" />
                <span>Nota Termal (80mm)</span>
              </button>
              <button
                onClick={() => printPdf(completedOrder.order_id, 'inkjet')}
                className="flex items-center justify-center space-x-2 py-2.5 px-3 rounded-xl bg-blue-600 text-white font-bold text-xs hover:bg-blue-700 transition-all cursor-pointer"
              >
                <FileText className="w-4 h-4" />
                <span>Nota Inkjet (A4)</span>
              </button>
            </div>

            <button
              onClick={() => setCompletedOrder(null)}
              className="w-full py-2 text-xs font-bold text-slate-500 hover:text-slate-700 cursor-pointer pt-2"
            >
              Tutup & Transaksi Baru
            </button>
          </div>
        </div>
      )}
    </div>
  );
}

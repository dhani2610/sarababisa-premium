'use client';

import React, { useState, useEffect } from 'react';
import api from '@/lib/api';
import { useAuth } from '@/context/AuthContext';
import Swal from 'sweetalert2';
import {
  ArrowLeftRight,
  Plus,
  CheckCircle,
  Building2,
  Package,
  Check,
  X,
  ChevronLeft,
  ChevronRight,
} from 'lucide-react';

export default function TransferStokPage() {
  const { user, cabangList, activeCabangId } = useAuth();

  const [transferList, setTransferList] = useState<any[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [showModalNew, setShowModalNew] = useState(false);

  // Form Fields
  const [cabangTujuan, setCabangTujuan] = useState<number | ''>('');
  const [catatan, setCatatan] = useState('');
  const [availableProducts, setAvailableProducts] = useState<any[]>([]);
  const [selectedItems, setSelectedItems] = useState<{ produk_id: number; qty: number; nama: string; stok_asal: number }[]>([]);
  const [isSubmitting, setIsSubmitting] = useState(false);

  const fetchTransfer = async () => {
    setIsLoading(true);
    try {
      const res = await api.get('/transfer-stok');
      setTransferList(res.data.data || res.data || []);
    } catch (err) {
      console.error('Error fetching transfer stok', err);
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchTransfer();
  }, []);

  // Fetch available products at current branch
  const loadBranchProducts = async () => {
    try {
      const res = await api.get('/produk/item', { params: { per_page: 100 } });
      setAvailableProducts(res.data.data || res.data || []);
    } catch (err) {
      console.error('Error fetching branch products', err);
    }
  };

  const handleOpenNewModal = () => {
    loadBranchProducts();
    setSelectedItems([]);
    setCabangTujuan('');
    setCatatan('');
    setShowModalNew(true);
  };

  const handleAddItem = (produkId: number) => {
    const prod = availableProducts.find((p) => p.id === produkId);
    if (!prod) return;

    if (selectedItems.some((i) => i.produk_id === produkId)) return;

    setSelectedItems((prev) => [
      ...prev,
      {
        produk_id: prod.id,
        nama: prod.nama || prod.product_name,
        qty: 1,
        stok_asal: prod.stok,
      },
    ]);
  };

  const handleUpdateItemQty = (produkId: number, qty: number) => {
    setSelectedItems((prev) =>
      prev.map((item) => (item.produk_id === produkId ? { ...item, qty: Math.max(1, qty) } : item))
    );
  };

  const handleRemoveItem = (produkId: number) => {
    setSelectedItems((prev) => prev.filter((item) => item.produk_id !== produkId));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!cabangTujuan) {
      Swal.fire('Peringatan', 'Pilih cabang tujuan transfer.', 'warning');
      return;
    }
    if (selectedItems.length === 0) {
      Swal.fire('Peringatan', 'Pilih minimal 1 produk yang akan ditransfer.', 'warning');
      return;
    }

    setIsSubmitting(true);
    try {
      const payload = {
        cabang_tujuan: Number(cabangTujuan),
        catatan,
        items: selectedItems.map((i) => ({
          produk_id: i.produk_id,
          qty: i.qty,
        })),
      };

      await api.post('/transfer-stok', payload);
      Swal.fire('Berhasil Diajukan', 'Permintaan transfer stok berhasil dibuat.', 'success');
      setShowModalNew(false);
      fetchTransfer();
    } catch (err: any) {
      Swal.fire('Gagal Transfer', err.response?.data?.detail || 'Terjadi kesalahan', 'error');
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleApprove = async (transferId: number) => {
    const confirm = await Swal.fire({
      title: 'Setujui Transfer Stok?',
      text: 'Stok di cabang asal akan dikurangi dan otomatis ditambahkan ke cabang tujuan.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Ya, Setujui & Mutasi Stok',
      cancelButtonText: 'Batal',
    });
    if (!confirm.isConfirmed) return;

    try {
      await api.post(`/transfer-stok/${transferId}/approve`);
      Swal.fire('Selesai!', 'Stok berhasil dimutasi antar cabang.', 'success');
      fetchTransfer();
    } catch (err: any) {
      Swal.fire('Gagal', err.response?.data?.detail || 'Gagal menyetujui transfer', 'error');
    }
  };

  return (
    <div className="space-y-5">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
          <h1 className="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
            <ArrowLeftRight className="w-6 h-6 text-blue-600" />
            <span>Transfer Stok Antar Cabang</span>
          </h1>
          <p className="text-xs text-slate-500 mt-0.5">
            Mutasi stok produk, pengiriman unit & persetujuan transfer antar cabang.
          </p>
        </div>

        <button
          onClick={handleOpenNewModal}
          className="inline-flex items-center space-x-1.5 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition-all cursor-pointer self-start sm:self-auto"
        >
          <Plus className="w-4 h-4" />
          <span>Kirim Transfer Stok Baru</span>
        </button>
      </div>

      {/* Main Table */}
      <div className="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs">
            <thead className="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase text-[10px] tracking-wider">
              <tr>
                <th className="py-3 px-4">No Transfer / Tgl</th>
                <th className="py-3 px-4">Cabang Asal</th>
                <th className="py-3 px-4">Cabang Tujuan</th>
                <th className="py-3 px-4">Pengirim / Catatan</th>
                <th className="py-3 px-4 text-center">Status</th>
                <th className="py-3 px-4 text-center">Aksi Approval</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {isLoading ? (
                <tr>
                  <td colSpan={6} className="py-12 text-center text-slate-400">
                    Memuat data transfer stok...
                  </td>
                </tr>
              ) : transferList.length === 0 ? (
                <tr>
                  <td colSpan={6} className="py-12 text-center text-slate-400">
                    Belum ada riwayat transfer stok antar cabang.
                  </td>
                </tr>
              ) : (
                transferList.map((item) => {
                  const isDestination = item.cabang_tujuan === activeCabangId;
                  const isPending = item.status === 'menunggu';

                  return (
                    <tr key={item.id} className="hover:bg-slate-50">
                      <td className="py-3 px-4 font-bold text-blue-700">
                        {item.no_transfer}
                        <span className="block text-[10px] text-slate-400 font-normal">
                          {item.created_at ? new Date(item.created_at).toLocaleDateString('id-ID') : '-'}
                        </span>
                      </td>
                      <td className="py-3 px-4 font-semibold text-slate-800">
                        {item.cabang_asal_nama || `Cabang #${item.cabang_asal}`}
                      </td>
                      <td className="py-3 px-4 font-semibold text-slate-800">
                        {item.tujuan_nama || item.cabang_tujuan_nama || `Cabang #${item.cabang_tujuan}`}
                      </td>
                      <td className="py-3 px-4">
                        <p className="font-medium text-slate-800">{item.user_nama || '-'}</p>
                        <span className="text-[10px] text-slate-400">{item.catatan || 'Tanpa catatan'}</span>
                      </td>
                      <td className="py-3 px-4 text-center">
                        <span
                          className={`inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold ${
                            item.status === 'selesai'
                              ? 'bg-emerald-100 text-emerald-800'
                              : 'bg-amber-100 text-amber-800'
                          }`}
                        >
                          {item.status === 'selesai' ? 'Selesai / Dimutasi' : 'Menunggu Approval'}
                        </span>
                      </td>
                      <td className="py-3 px-4 text-center">
                        {isPending && isDestination ? (
                          <button
                            onClick={() => handleApprove(item.id)}
                            className="inline-flex items-center space-x-1 px-3 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs cursor-pointer"
                          >
                            <Check className="w-3.5 h-3.5" />
                            <span>Setujui</span>
                          </button>
                        ) : (
                          <span className="text-[11px] text-slate-400">-</span>
                        )}
                      </td>
                    </tr>
                  );
                })
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* Modal Buat Transfer Stok */}
      {showModalNew && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4">
          <div className="bg-white rounded-2xl max-w-xl w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
            <div className="flex items-center justify-between pb-3 border-b border-slate-100">
              <div className="flex items-center space-x-2">
                <ArrowLeftRight className="w-5 h-5 text-blue-600" />
                <h3 className="text-sm font-bold text-slate-800">Kirim Transfer Stok Baru</h3>
              </div>
              <button
                onClick={() => setShowModalNew(false)}
                className="text-slate-400 hover:text-slate-600 text-lg leading-none cursor-pointer"
              >
                &times;
              </button>
            </div>

            <form onSubmit={handleSubmit} className="space-y-4 text-xs">
              <div>
                <label className="font-semibold text-slate-700 block mb-1">Pilih Cabang Tujuan *</label>
                <select
                  required
                  value={cabangTujuan}
                  onChange={(e) => setCabangTujuan(e.target.value ? Number(e.target.value) : '')}
                  className="w-full px-3 py-2 border border-slate-200 rounded-xl focus:outline-hidden bg-white"
                >
                  <option value="">-- Pilih Cabang Penerima --</option>
                  {cabangList
                    .filter((c) => c.id !== activeCabangId)
                    .map((c) => (
                      <option key={c.id} value={c.id}>
                        {c.nama}
                      </option>
                    ))}
                </select>
              </div>

              {/* Product selector to transfer */}
              <div className="space-y-2">
                <label className="font-semibold text-slate-700 block">Pilih Produk Untuk Ditransfer</label>
                <select
                  onChange={(e) => {
                    if (e.target.value) handleAddItem(Number(e.target.value));
                  }}
                  defaultValue=""
                  className="w-full px-3 py-2 border border-slate-200 rounded-xl focus:outline-hidden bg-white"
                >
                  <option value="">-- Klik untuk memilih produk dari cabang ini --</option>
                  {availableProducts
                    .filter((p) => p.stok > 0)
                    .map((p) => (
                      <option key={p.id} value={p.id}>
                        {p.nama || p.product_name} (Tersedia: {p.stok})
                      </option>
                    ))}
                </select>

                {/* Selected products list */}
                <div className="border border-slate-200 rounded-xl divide-y divide-slate-100 max-h-48 overflow-y-auto mt-2">
                  {selectedItems.length === 0 ? (
                    <p className="p-3 text-center text-slate-400">Belum ada produk yang dipilih.</p>
                  ) : (
                    selectedItems.map((item) => (
                      <div key={item.produk_id} className="p-2.5 flex items-center justify-between">
                        <div>
                          <p className="font-bold text-slate-800">{item.nama}</p>
                          <span className="text-[10px] text-slate-400">Stok Asal: {item.stok_asal}</span>
                        </div>
                        <div className="flex items-center space-x-2">
                          <input
                            type="number"
                            min={1}
                            max={item.stok_asal}
                            value={item.qty}
                            onChange={(e) => handleUpdateItemQty(item.produk_id, Number(e.target.value))}
                            className="w-16 px-2 py-1 border border-slate-200 rounded-lg text-center font-bold"
                          />
                          <button
                            type="button"
                            onClick={() => handleRemoveItem(item.produk_id)}
                            className="text-slate-400 hover:text-rose-600"
                          >
                            <X className="w-4 h-4" />
                          </button>
                        </div>
                      </div>
                    ))
                  )}
                </div>
              </div>

              <div>
                <label className="font-semibold text-slate-700 block mb-1">Catatan Pengiriman</label>
                <input
                  type="text"
                  value={catatan}
                  onChange={(e) => setCatatan(e.target.value)}
                  placeholder="Contoh: Kirim via kurir internal"
                  className="w-full px-3 py-2 border border-slate-200 rounded-xl focus:outline-hidden"
                />
              </div>

              <div className="pt-2 flex justify-end space-x-2">
                <button
                  type="button"
                  onClick={() => setShowModalNew(false)}
                  className="px-4 py-2 border border-slate-200 rounded-xl hover:bg-slate-50 font-bold cursor-pointer"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  disabled={isSubmitting}
                  className="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold cursor-pointer disabled:opacity-50"
                >
                  {isSubmitting ? 'Mengirim...' : 'Kirim Permintaan Transfer'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}

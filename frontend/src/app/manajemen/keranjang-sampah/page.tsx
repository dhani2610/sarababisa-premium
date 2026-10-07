'use client';

import React, { useState, useEffect } from 'react';
import api from '@/lib/api';
import Swal from 'sweetalert2';
import { Trash2, RotateCcw, RefreshCw, AlertOctagon, Check } from 'lucide-react';

const TABS = [
  { id: 'servis', label: 'Servis' },
  { id: 'penjualan', label: 'Penjualan' },
  { id: 'pelanggan', label: 'Pelanggan' },
  { id: 'produk', label: 'Produk' },
  { id: 'kasbon', label: 'Kasbon' },
  { id: 'pengeluaran', label: 'Pengeluaran' },
  { id: 'insiden', label: 'Insiden' },
  { id: 'akun', label: 'Akun' },
];

export default function KeranjangSampahPage() {
  const [activeTab, setActiveTab] = useState('servis');
  const [items, setItems] = useState<any[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  const fetchTrash = async () => {
    setIsLoading(true);
    try {
      const res = await api.get(`/keranjang/${activeTab}`);
      const list = res.data?.data || (Array.isArray(res.data) ? res.data : []);
      setItems(list);
    } catch (err) {
      console.error('Failed to load trash', err);
      setItems([]);
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchTrash();
  }, [activeTab]);

  const handleRestore = async (id: number) => {
    try {
      await api.post(`/keranjang/${activeTab}/${id}/restore`);
      Swal.fire('Dipulihkan', 'Item berhasil dipulihkan kembali ke data aktif.', 'success');
      fetchTrash();
    } catch (err: any) {
      Swal.fire('Gagal', err.response?.data?.detail || 'Gagal memulihkan item', 'error');
    }
  };

  const handlePermanentDelete = async (id: number) => {
    const confirm = await Swal.fire({
      title: 'Hapus Permanen?',
      text: 'PERINGATAN: Data ini akan dihapus selamanya dari database dan TIDAK BISA dipulihkan lagi!',
      icon: 'error',
      showCancelButton: true,
      confirmButtonText: 'Hapus Selamanya',
      cancelButtonText: 'Batal',
      confirmButtonColor: '#e11d48',
    });
    if (!confirm.isConfirmed) return;

    try {
      await api.delete(`/keranjang/${activeTab}/${id}`);
      Swal.fire('Terhapus Permanen', 'Data berhasil dimusnahkan.', 'success');
      fetchTrash();
    } catch (err: any) {
      Swal.fire('Gagal', err.response?.data?.detail || 'Gagal menghapus data', 'error');
    }
  };

  const handleBersihkanSemua = async () => {
    const confirm = await Swal.fire({
      title: `Kosongkan Keranjang ${activeTab.toUpperCase()}?`,
      text: 'Semua item di kategori ini akan dimusnahkan secara permanen.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Ya, Kosongkan',
      cancelButtonText: 'Batal',
      confirmButtonColor: '#e11d48',
    });
    if (!confirm.isConfirmed) return;

    try {
      await api.post(`/keranjang/${activeTab}/bersihkan`);
      Swal.fire('Bersih', 'Keranjang sampah kategori ini telah dikosongkan.', 'success');
      fetchTrash();
    } catch (err: any) {
      Swal.fire('Gagal', err.response?.data?.detail || 'Gagal mengosongkan keranjang', 'error');
    }
  };

  const getItemTitle = (item: any) => {
    return (
      item.nama ||
      item.name ||
      item.nomor_servis ||
      item.no_invoice ||
      item.judul ||
      item.keterangan ||
      `Item #${item.id}`
    );
  };

  return (
    <div className="space-y-4">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-4 rounded-xl border border-slate-200 shadow-2xs">
        <div>
          <div className="flex items-center space-x-2">
            <Trash2 className="w-5 h-5 text-rose-600" />
            <h1 className="text-lg font-black text-slate-900 tracking-tight">
              Keranjang Sampah (Recycle Bin)
            </h1>
          </div>
          <p className="text-xs text-slate-500 mt-0.5">
            Daftar data yang telah dihapus sementara. Anda dapat memulihkan (restore) atau menghapus secara permanen.
          </p>
        </div>

        <div className="flex items-center space-x-2">
          <button
            onClick={fetchTrash}
            className="p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg border border-slate-200 transition-colors cursor-pointer"
            title="Segarkan"
          >
            <RefreshCw className={`w-4 h-4 ${isLoading ? 'animate-spin' : ''}`} />
          </button>
          {items.length > 0 && (
            <button
              onClick={handleBersihkanSemua}
              className="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold transition-all shadow-xs cursor-pointer"
            >
              <AlertOctagon className="w-4 h-4" />
              <span>Kosongkan Kategori Ini</span>
            </button>
          )}
        </div>
      </div>

      {/* Tabs */}
      <div className="flex items-center space-x-1 border-b border-slate-200 overflow-x-auto pb-1">
        {TABS.map((tab) => (
          <button
            key={tab.id}
            onClick={() => setActiveTab(tab.id)}
            className={`px-3.5 py-2 rounded-t-lg text-xs font-bold whitespace-nowrap transition-colors cursor-pointer ${
              activeTab === tab.id
                ? 'bg-white text-rose-600 border-t-2 border-x border-slate-200 border-t-rose-600'
                : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/60'
            }`}
          >
            {tab.label}
          </button>
        ))}
      </div>

      {/* Table Container */}
      <div className="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-2xs">
        <div className="overflow-x-auto">
          <table className="w-full text-xs text-left">
            <thead className="bg-slate-50/80 text-slate-600 uppercase font-black text-[10px] tracking-wider border-b border-slate-200">
              <tr>
                <th className="px-4 py-3 w-12 text-center">NO.</th>
                <th className="px-4 py-3">DESKRIPSI ITEM TERHAPUS</th>
                <th className="px-4 py-3">TANGGAL DIHAPUS</th>
                <th className="px-4 py-3 text-center w-40">AKSI</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {isLoading ? (
                <tr>
                  <td colSpan={4} className="px-4 py-8 text-center text-slate-400">
                    Memuat data keranjang sampah...
                  </td>
                </tr>
              ) : items.length === 0 ? (
                <tr>
                  <td colSpan={4} className="px-4 py-8 text-center text-slate-400 font-medium">
                    Keranjang sampah {activeTab} kosong. Tidak ada data terhapus.
                  </td>
                </tr>
              ) : (
                items.map((item, idx) => (
                  <tr key={item.id} className="hover:bg-slate-50/70 transition-colors">
                    <td className="px-4 py-3 text-center text-slate-400 font-semibold">
                      {idx + 1}
                    </td>
                    <td className="px-4 py-3 font-bold text-slate-800 uppercase">
                      {getItemTitle(item)}
                    </td>
                    <td className="px-4 py-3 text-slate-500 font-mono">
                      {item.deleted_at
                        ? new Date(item.deleted_at).toLocaleString('id-ID')
                        : '-'}
                    </td>
                    <td className="px-4 py-3 text-center">
                      <div className="flex items-center justify-center space-x-1.5">
                        <button
                          onClick={() => handleRestore(item.id)}
                          className="inline-flex items-center space-x-1 px-2.5 py-1 rounded-md bg-emerald-50 text-emerald-700 hover:bg-emerald-100 text-[11px] font-bold transition-colors cursor-pointer"
                          title="Pulihkan"
                        >
                          <RotateCcw className="w-3.5 h-3.5" />
                          <span>Pulihkan</span>
                        </button>
                        <button
                          onClick={() => handlePermanentDelete(item.id)}
                          className="p-1 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-md transition-colors cursor-pointer"
                          title="Hapus Permanen"
                        >
                          <Trash2 className="w-3.5 h-3.5" />
                        </button>
                      </div>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}

'use client';

import React, { useState, useEffect } from 'react';
import api from '@/lib/api';
import { DataTable, Column } from '@/components/ui/DataTable';
import Swal from 'sweetalert2';
import { AlertTriangle, Plus, Trash2, RefreshCw, X, Save } from 'lucide-react';

interface InsidenRow {
  id: number;
  name: string;
  price: number;
  biaya_teknisi: number;
  biaya_toko: number;
  persen_teknisi: number;
  worker_name?: string;
  created_at?: string;
}

export default function InsidenPage() {
  const [data, setData] = useState<InsidenRow[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  // Modal State
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [name, setName] = useState('');
  const [price, setPrice] = useState<number>(0);
  const [persenTeknisi, setPersenTeknisi] = useState<number>(50);
  const [isSaving, setIsSaving] = useState(false);

  const fetchData = async () => {
    setIsLoading(true);
    try {
      // In Laravel, incidents were stored with worker relations
      const res = await api.get('/manajemen/pengeluaran', { params: { per_page: 50 } });
      const expenses = res.data?.data || (Array.isArray(res.data) ? res.data : []);
      const mapped: InsidenRow[] = expenses
        .filter((e: any) => e.nama?.toLowerCase().includes('insiden') || e.tipe === 'insiden')
        .map((e: any) => ({
          id: e.id,
          name: e.nama,
          price: e.jumlah,
          biaya_teknisi: e.jumlah * 0.5,
          biaya_toko: e.jumlah * 0.5,
          persen_teknisi: 50,
          worker_name: e.user_nama || 'Teknisi',
          created_at: e.created_at || e.tgl_pengeluaran,
        }));
      setData(mapped);
    } catch (err) {
      console.error('Failed to load insiden', err);
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchData();
  }, []);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!name.trim() || price <= 0) {
      Swal.fire('Validasi', 'Keterangan insiden dan biaya ganti rugi harus valid.', 'warning');
      return;
    }

    setIsSaving(true);
    try {
      await api.post('/manajemen/pengeluaran', {
        nama: `[Insiden] ${name.trim()}`,
        jumlah: Number(price),
        tipe: 'insiden',
        tgl_pengeluaran: new Date().toISOString().slice(0, 10),
      });
      Swal.fire('Berhasil', 'Laporan insiden berhasil dicatat.', 'success');
      setIsModalOpen(false);
      setName('');
      setPrice(0);
      fetchData();
    } catch (err: any) {
      Swal.fire('Gagal', err.response?.data?.detail || 'Gagal menyimpan insiden', 'error');
    } finally {
      setIsSaving(false);
    }
  };

  const formatRupiah = (val: number = 0) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
    }).format(val);
  };

  const columns: Column<InsidenRow>[] = [
    {
      key: 'no',
      label: 'NO.',
      sortable: false,
      className: 'w-12 text-center text-slate-500 font-semibold',
      render: (_, idx) => <span>{idx}</span>,
    },
    {
      key: 'name',
      label: 'KETERANGAN INSIDEN KERUSAKAN',
      className: 'font-bold text-slate-800 uppercase text-xs',
      render: (item) => item.name,
    },
    {
      key: 'price',
      label: 'TOTAL KERUGIAN',
      className: 'text-right font-black text-rose-600 text-xs tabular-nums',
      render: (item) => formatRupiah(item.price),
    },
    {
      key: 'biaya_teknisi',
      label: 'BEBAN TEKNISI',
      className: 'text-right font-semibold text-slate-700 text-xs tabular-nums',
      render: (item) => formatRupiah(item.biaya_teknisi),
    },
    {
      key: 'biaya_toko',
      label: 'BEBAN TOKO',
      className: 'text-right font-semibold text-slate-700 text-xs tabular-nums',
      render: (item) => formatRupiah(item.biaya_toko),
    },
    {
      key: 'created_at',
      label: 'TANGGAL',
      className: 'text-xs text-slate-500 font-mono',
      render: (item) =>
        item.created_at ? new Date(item.created_at).toLocaleDateString('id-ID') : '-',
    },
  ];

  return (
    <div className="space-y-4">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-4 rounded-xl border border-slate-200 shadow-2xs">
        <div>
          <div className="flex items-center space-x-2">
            <AlertTriangle className="w-5 h-5 text-amber-500" />
            <h1 className="text-lg font-black text-slate-900 tracking-tight">
              Laporan Insiden & Kerusakan
            </h1>
          </div>
          <p className="text-xs text-slate-500 mt-0.5">
            Pencatatan kasus kerusakan unit atau sparepart saat proses servis dan pembagian beban ganti rugi.
          </p>
        </div>

        <div className="flex items-center space-x-2">
          <button
            onClick={fetchData}
            className="p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg border border-slate-200 transition-colors cursor-pointer"
            title="Segarkan"
          >
            <RefreshCw className={`w-4 h-4 ${isLoading ? 'animate-spin' : ''}`} />
          </button>
          <button
            onClick={() => setIsModalOpen(true)}
            className="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold transition-all shadow-xs cursor-pointer"
          >
            <Plus className="w-4 h-4" />
            <span>Catat Insiden</span>
          </button>
        </div>
      </div>

      {/* Table */}
      <div className="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs">
        <DataTable
          columns={columns}
          data={data}
          keyField="id"
          searchPlaceholder="Cari keterangan insiden..."
          emptyMessage="Belum ada catatan insiden kerusakan."
        />
      </div>

      {/* Modal */}
      {isModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-2xs p-4">
          <div className="bg-white w-full max-w-md rounded-2xl shadow-xl border border-slate-200 overflow-hidden animate-in fade-in zoom-in duration-150">
            <div className="flex items-center justify-between px-5 py-4 border-b border-slate-100 bg-slate-50/50">
              <h3 className="font-black text-slate-800 text-sm">
                Catat Kasus Insiden Baru
              </h3>
              <button
                onClick={() => setIsModalOpen(false)}
                className="text-slate-400 hover:text-slate-600 cursor-pointer"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            <form onSubmit={handleSubmit} className="p-5 space-y-4 text-xs">
              <div>
                <label className="block font-bold text-slate-700 uppercase mb-1">
                  Keterangan Kejadian / Insiden <span className="text-rose-500">*</span>
                </label>
                <textarea
                  required
                  rows={2}
                  value={name}
                  onChange={(e) => setName(e.target.value)}
                  placeholder="Contoh: Pecah touchscreen saat angkat LCD iPhone 11..."
                  className="w-full px-3 py-2 border border-slate-300 rounded-lg font-medium"
                />
              </div>

              <div>
                <label className="block font-bold text-slate-700 uppercase mb-1">
                  Total Nilai Kerugian (Rp) <span className="text-rose-500">*</span>
                </label>
                <input
                  type="number"
                  min="0"
                  step="10000"
                  value={price}
                  onChange={(e) => setPrice(Number(e.target.value))}
                  placeholder="0"
                  className="w-full px-3 py-2 border border-slate-300 rounded-lg font-semibold tabular-nums"
                />
              </div>

              <div>
                <label className="block font-bold text-slate-700 uppercase mb-1">
                  Persentase Tanggungan Teknisi (%)
                </label>
                <input
                  type="number"
                  min="0"
                  max="100"
                  value={persenTeknisi}
                  onChange={(e) => setPersenTeknisi(Number(e.target.value))}
                  className="w-full px-3 py-2 border border-slate-300 rounded-lg font-semibold tabular-nums"
                />
                <p className="text-[11px] text-slate-500 mt-1">
                  Sisa {100 - persenTeknisi}% otomatis ditanggung sebagai beban operasional toko.
                </p>
              </div>

              <div className="flex justify-end space-x-2 pt-3 border-t border-slate-100">
                <button
                  type="button"
                  onClick={() => setIsModalOpen(false)}
                  className="px-4 py-2 border border-slate-200 rounded-lg font-semibold text-slate-600 hover:bg-slate-50 cursor-pointer"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  disabled={isSaving}
                  className="inline-flex items-center space-x-1.5 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg font-bold shadow-xs cursor-pointer disabled:opacity-50"
                >
                  <Save className="w-4 h-4" />
                  <span>{isSaving ? 'Menyimpan...' : 'Simpan Laporan'}</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}

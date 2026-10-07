'use client';

import React, { useState, useEffect } from 'react';
import api from '@/lib/api';
import { DataTable, Column } from '@/components/ui/DataTable';
import Swal from 'sweetalert2';
import { DollarSign, Plus, Trash2, RefreshCw, X, Save } from 'lucide-react';

interface AnggaranRow {
  id: number;
  name: string;
  quantity: number;
  price: number;
  total: number;
  created_at?: string;
}

export default function AnggaranPage() {
  const [data, setData] = useState<AnggaranRow[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  // Modal State
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [name, setName] = useState('');
  const [quantity, setQuantity] = useState<number>(1);
  const [price, setPrice] = useState<number>(0);
  const [isSaving, setIsSaving] = useState(false);

  const fetchData = async () => {
    setIsLoading(true);
    try {
      // Fetch expenses or budget targets
      const res = await api.get('/manajemen/pengeluaran', { params: { per_page: 50 } });
      const expenses = res.data?.data || (Array.isArray(res.data) ? res.data : []);
      const mapped: AnggaranRow[] = expenses.map((e: any) => ({
        id: e.id,
        name: e.nama,
        quantity: 1,
        price: e.jumlah,
        total: e.jumlah,
        created_at: e.created_at || e.tgl_pengeluaran,
      }));
      setData(mapped);
    } catch (err) {
      console.error('Failed to load anggaran', err);
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
      Swal.fire('Validasi', 'Nama pos anggaran dan estimasi biaya harus valid.', 'warning');
      return;
    }

    setIsSaving(true);
    try {
      await api.post('/manajemen/pengeluaran', {
        nama: `[Anggaran] ${name.trim()}`,
        jumlah: Number(price * quantity),
        tipe: 'anggaran',
        tgl_pengeluaran: new Date().toISOString().slice(0, 10),
      });
      Swal.fire('Berhasil', 'Pos anggaran berhasil dicatat.', 'success');
      setIsModalOpen(false);
      setName('');
      setPrice(0);
      setQuantity(1);
      fetchData();
    } catch (err: any) {
      Swal.fire('Gagal', err.response?.data?.detail || 'Gagal menyimpan anggaran', 'error');
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

  const columns: Column<AnggaranRow>[] = [
    {
      key: 'no',
      label: 'NO.',
      sortable: false,
      className: 'w-12 text-center text-slate-500 font-semibold',
      render: (_, idx) => <span>{idx}</span>,
    },
    {
      key: 'name',
      label: 'POS ANGGARAN & RENCANA BIAYA',
      className: 'font-bold text-slate-800 uppercase text-xs',
      render: (item) => item.name,
    },
    {
      key: 'quantity',
      label: 'QTY',
      className: 'text-center font-semibold text-slate-700 text-xs tabular-nums',
      render: (item) => item.quantity || 1,
    },
    {
      key: 'price',
      label: 'ESTIMASI SATUAN',
      className: 'text-right font-medium text-slate-800 text-xs tabular-nums',
      render: (item) => formatRupiah(item.price),
    },
    {
      key: 'total',
      label: 'TOTAL ANGGARAN',
      className: 'text-right font-black text-blue-600 text-xs tabular-nums',
      render: (item) => formatRupiah(item.total),
    },
  ];

  return (
    <div className="space-y-4">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-4 rounded-xl border border-slate-200 shadow-2xs">
        <div>
          <div className="flex items-center space-x-2">
            <DollarSign className="w-5 h-5 text-blue-600" />
            <h1 className="text-lg font-black text-slate-900 tracking-tight">
              Rencana Anggaran Toko
            </h1>
          </div>
          <p className="text-xs text-slate-500 mt-0.5">
            Perencanaan estimasi alokasi dana dan belanja modal bulanan toko.
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
            className="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition-all shadow-xs cursor-pointer"
          >
            <Plus className="w-4 h-4" />
            <span>Tambah Pos Anggaran</span>
          </button>
        </div>
      </div>

      {/* Table */}
      <div className="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs">
        <DataTable
          columns={columns}
          data={data}
          keyField="id"
          searchPlaceholder="Cari nama pos anggaran..."
          emptyMessage="Belum ada rencana pos anggaran."
        />
      </div>

      {/* Modal */}
      {isModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-2xs p-4">
          <div className="bg-white w-full max-w-md rounded-2xl shadow-xl border border-slate-200 overflow-hidden animate-in fade-in zoom-in duration-150">
            <div className="flex items-center justify-between px-5 py-4 border-b border-slate-100 bg-slate-50/50">
              <h3 className="font-black text-slate-800 text-sm">
                Tambah Pos Anggaran Baru
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
                  Nama Pos Anggaran <span className="text-rose-500">*</span>
                </label>
                <input
                  type="text"
                  required
                  value={name}
                  onChange={(e) => setName(e.target.value)}
                  placeholder="Contoh: Renovasi Etalase Depan, Pembelian Mikroskop..."
                  className="w-full px-3 py-2 border border-slate-300 rounded-lg font-semibold"
                />
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block font-bold text-slate-700 uppercase mb-1">
                    Jumlah (Qty)
                  </label>
                  <input
                    type="number"
                    min="1"
                    value={quantity}
                    onChange={(e) => setQuantity(Number(e.target.value))}
                    className="w-full px-3 py-2 border border-slate-300 rounded-lg font-semibold tabular-nums"
                  />
                </div>
                <div>
                  <label className="block font-bold text-slate-700 uppercase mb-1">
                    Harga Satuan (Rp)
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
              </div>

              <div className="p-3 bg-blue-50/70 border border-blue-100 rounded-lg flex items-center justify-between">
                <span className="font-bold text-blue-900">Total Estimasi:</span>
                <span className="font-black text-blue-700 tabular-nums">
                  {formatRupiah(quantity * price)}
                </span>
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
                  className="inline-flex items-center space-x-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold shadow-xs cursor-pointer disabled:opacity-50"
                >
                  <Save className="w-4 h-4" />
                  <span>{isSaving ? 'Menyimpan...' : 'Simpan Pos'}</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}

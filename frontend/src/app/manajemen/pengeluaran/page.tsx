'use client';

import React, { useState, useEffect } from 'react';
import api from '@/lib/api';
import { DataTable, Column } from '@/components/ui/DataTable';
import Swal from 'sweetalert2';
import { Receipt, Plus, Trash2, RefreshCw, X, Save, Calendar, CheckCircle2, Clock } from 'lucide-react';

interface PengeluaranRow {
  id: number;
  nama: string;
  jumlah: number;
  tipe?: string;
  tgl_pengeluaran?: string;
  is_approved?: boolean;
  user_nama?: string;
  created_at?: string;
}

export default function PengeluaranPage() {
  const [data, setData] = useState<PengeluaranRow[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  // Modal State
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [nama, setNama] = useState('');
  const [jumlah, setJumlah] = useState<number>(0);
  const [tipe, setTipe] = useState('operasional');
  const [keterangan, setKeterangan] = useState('');
  const [isSaving, setIsSaving] = useState(false);

  const fetchData = async () => {
    setIsLoading(true);
    try {
      const res = await api.get('/manajemen/pengeluaran', { params: { per_page: 50 } });
      const list = res.data?.data || (Array.isArray(res.data) ? res.data : []);
      setData(list);
    } catch (err) {
      console.error('Failed to load pengeluaran', err);
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchData();
  }, []);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!nama.trim() || jumlah <= 0) {
      Swal.fire('Validasi', 'Nama pengeluaran dan jumlah harus valid.', 'warning');
      return;
    }

    setIsSaving(true);
    try {
      await api.post('/manajemen/pengeluaran', {
        nama: nama.trim(),
        jumlah: Number(jumlah),
        tipe,
        keterangan: keterangan.trim(),
        tgl_pengeluaran: new Date().toISOString().slice(0, 10),
      });
      Swal.fire('Berhasil', 'Pengeluaran berhasil dicatat.', 'success');
      setIsModalOpen(false);
      setNama('');
      setJumlah(0);
      setKeterangan('');
      fetchData();
    } catch (err: any) {
      Swal.fire('Gagal', err.response?.data?.detail || 'Gagal menyimpan pengeluaran', 'error');
    } finally {
      setIsSaving(false);
    }
  };

  const handleDelete = async (id: number) => {
    const confirm = await Swal.fire({
      title: 'Hapus Pengeluaran?',
      text: 'Data yang dihapus tidak dapat dipulihkan.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Ya, Hapus',
      cancelButtonText: 'Batal',
      confirmButtonColor: '#ef4444',
    });
    if (!confirm.isConfirmed) return;

    try {
      await api.delete(`/manajemen/pengeluaran/${id}`);
      Swal.fire('Terhapus', 'Pengeluaran berhasil dihapus.', 'success');
      fetchData();
    } catch (err: any) {
      Swal.fire('Gagal', err.response?.data?.detail || 'Gagal menghapus', 'error');
    }
  };

  const formatRupiah = (val: number = 0) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
    }).format(val);
  };

  const columns: Column<PengeluaranRow>[] = [
    {
      key: 'no',
      label: 'NO.',
      sortable: false,
      className: 'w-12 text-center text-slate-500 font-semibold',
      render: (_, idx) => <span>{idx}</span>,
    },
    {
      key: 'nama',
      label: 'NAMA PENGELUARAN / KEPERLUAN',
      className: 'font-bold text-slate-800 uppercase text-xs',
      render: (item) => item.nama,
    },
    {
      key: 'jumlah',
      label: 'NOMINAL',
      className: 'font-bold text-rose-600 text-xs tabular-nums',
      render: (item) => formatRupiah(item.jumlah),
    },
    {
      key: 'tipe',
      label: 'KATEGORI',
      className: 'text-xs font-semibold text-center uppercase',
      render: (item) => (
        <span className="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-black bg-slate-100 text-slate-700">
          {item.tipe || 'Operasional'}
        </span>
      ),
    },
    {
      key: 'tgl_pengeluaran',
      label: 'TANGGAL',
      className: 'text-xs text-slate-500 font-mono',
      render: (item) =>
        item.tgl_pengeluaran
          ? new Date(item.tgl_pengeluaran).toLocaleDateString('id-ID')
          : '-',
    },
    {
      key: 'aksi',
      label: 'AKSI',
      sortable: false,
      className: 'text-center w-20',
      render: (item) => (
        <button
          onClick={() => handleDelete(item.id)}
          className="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
          title="Hapus"
        >
          <Trash2 className="w-4 h-4" />
        </button>
      ),
    },
  ];

  return (
    <div className="space-y-4">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-4 rounded-xl border border-slate-200 shadow-2xs">
        <div>
          <div className="flex items-center space-x-2">
            <Receipt className="w-5 h-5 text-blue-600" />
            <h1 className="text-lg font-black text-slate-900 tracking-tight">
              Manajemen Pengeluaran Toko
            </h1>
          </div>
          <p className="text-xs text-slate-500 mt-0.5">
            Pencatatan beban operasional, biaya utilitas, dan pengeluaran harian toko.
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
            <span>Tambah Pengeluaran</span>
          </button>
        </div>
      </div>

      {/* Table */}
      <div className="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs">
        <DataTable
          columns={columns}
          data={data}
          keyField="id"
          searchPlaceholder="Cari keperluan pengeluaran..."
          emptyMessage="Belum ada catatan pengeluaran."
        />
      </div>

      {/* Modal Tambah Pengeluaran */}
      {isModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-2xs p-4">
          <div className="bg-white w-full max-w-md rounded-2xl shadow-xl border border-slate-200 overflow-hidden animate-in fade-in zoom-in duration-150">
            <div className="flex items-center justify-between px-5 py-4 border-b border-slate-100 bg-slate-50/50">
              <h3 className="font-black text-slate-800 text-sm">
                Catat Pengeluaran Baru
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
                  Keperluan / Nama Pengeluaran <span className="text-rose-500">*</span>
                </label>
                <input
                  type="text"
                  required
                  value={nama}
                  onChange={(e) => setNama(e.target.value)}
                  placeholder="Contoh: Beli Kertas Thermal, Listrik Toko, Konsumsi..."
                  className="w-full px-3 py-2 border border-slate-300 rounded-lg font-semibold"
                />
              </div>

              <div>
                <label className="block font-bold text-slate-700 uppercase mb-1">
                  Nominal Biaya (Rp) <span className="text-rose-500">*</span>
                </label>
                <input
                  type="number"
                  required
                  min="1"
                  step="1000"
                  value={jumlah}
                  onChange={(e) => setJumlah(Number(e.target.value))}
                  placeholder="0"
                  className="w-full px-3 py-2 border border-slate-300 rounded-lg font-semibold tabular-nums"
                />
              </div>

              <div>
                <label className="block font-bold text-slate-700 uppercase mb-1">
                  Tipe / Kategori
                </label>
                <select
                  value={tipe}
                  onChange={(e) => setTipe(e.target.value)}
                  className="w-full px-3 py-2 border border-slate-300 rounded-lg font-semibold uppercase"
                >
                  <option value="operasional">Operasional</option>
                  <option value="konsumsi">Konsumsi</option>
                  <option value="listrik_air">Listrik & Air</option>
                  <option value="sewa">Sewa & Bangunan</option>
                  <option value="lainnya">Lainnya</option>
                </select>
              </div>

              <div>
                <label className="block font-bold text-slate-700 uppercase mb-1">
                  Catatan Keterangan
                </label>
                <textarea
                  rows={2}
                  value={keterangan}
                  onChange={(e) => setKeterangan(e.target.value)}
                  placeholder="Keterangan tambahan atau nomor nota..."
                  className="w-full px-3 py-2 border border-slate-300 rounded-lg font-medium"
                />
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
                  <span>{isSaving ? 'Menyimpan...' : 'Simpan Pengeluaran'}</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}

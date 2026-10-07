'use client';

import React, { useState, useEffect } from 'react';
import api from '@/lib/api';
import { DataTable, Column } from '@/components/ui/DataTable';
import Swal from 'sweetalert2';
import { Sparkles, Plus, Edit2, Trash2, RefreshCw, X, Save } from 'lucide-react';

interface RincianInvestRow {
  id: number;
  nama: string;
  nominal: number;
  persentase: number;
  keterangan?: string;
  created_at?: string;
}

export default function RincianInvestPage() {
  const [data, setData] = useState<RincianInvestRow[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  // Modal State
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingItem, setEditingItem] = useState<RincianInvestRow | null>(null);
  const [nama, setNama] = useState('');
  const [nominal, setNominal] = useState<number>(0);
  const [persentase, setPersentase] = useState<number>(0);
  const [keterangan, setKeterangan] = useState('');
  const [isSaving, setIsSaving] = useState(false);

  const fetchData = async () => {
    setIsLoading(true);
    try {
      const res = await api.get('/rincian-invest');
      const list = res.data?.data || (Array.isArray(res.data) ? res.data : []);
      setData(list);
    } catch (err) {
      console.error('Failed to load rincian invest', err);
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchData();
  }, []);

  const openAddModal = () => {
    setEditingItem(null);
    setNama('');
    setNominal(0);
    setPersentase(0);
    setKeterangan('');
    setIsModalOpen(true);
  };

  const openEditModal = (item: RincianInvestRow) => {
    setEditingItem(item);
    setNama(item.nama);
    setNominal(item.nominal || 0);
    setPersentase(item.persentase || 0);
    setKeterangan(item.keterangan || '');
    setIsModalOpen(true);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!nama.trim()) {
      Swal.fire('Validasi', 'Nama investor / pemodal harus diisi.', 'warning');
      return;
    }

    setIsSaving(true);
    try {
      const payload = {
        nama: nama.trim(),
        nominal: Number(nominal) || 0,
        persentase: Number(persentase) || 0,
        keterangan: keterangan.trim(),
      };

      if (editingItem) {
        await api.put(`/rincian-invest/${editingItem.id}`, payload);
        Swal.fire('Berhasil', 'Data rincian invest berhasil diperbarui.', 'success');
      } else {
        await api.post('/rincian-invest', payload);
        Swal.fire('Berhasil', 'Data invest baru berhasil ditambahkan.', 'success');
      }
      setIsModalOpen(false);
      fetchData();
    } catch (err: any) {
      Swal.fire('Gagal', err.response?.data?.detail || 'Gagal menyimpan data', 'error');
    } finally {
      setIsSaving(false);
    }
  };

  const handleDelete = async (id: number) => {
    const confirm = await Swal.fire({
      title: 'Hapus Rincian Invest?',
      text: 'Data yang dihapus tidak dapat dipulihkan.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Ya, Hapus',
      cancelButtonText: 'Batal',
      confirmButtonColor: '#ef4444',
    });
    if (!confirm.isConfirmed) return;

    try {
      await api.delete(`/rincian-invest/${id}`);
      Swal.fire('Terhapus', 'Rincian invest berhasil dihapus.', 'success');
      fetchData();
    } catch (err: any) {
      Swal.fire('Gagal', err.response?.data?.detail || 'Gagal menghapus data', 'error');
    }
  };

  const formatRupiah = (val: number = 0) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
    }).format(val);
  };

  const columns: Column<RincianInvestRow>[] = [
    {
      key: 'no',
      label: 'NO.',
      sortable: false,
      className: 'w-12 text-center text-slate-500 font-semibold',
      render: (_, idx) => <span>{idx}</span>,
    },
    {
      key: 'nama',
      label: 'NAMA INVESTOR / PEMODAL',
      className: 'font-bold text-slate-800 uppercase text-xs',
      render: (item) => item.nama,
    },
    {
      key: 'nominal',
      label: 'NOMINAL INVESTASI',
      className: 'font-semibold text-slate-900 text-xs tabular-nums',
      render: (item) => formatRupiah(item.nominal),
    },
    {
      key: 'persentase',
      label: 'BAGI HASIL (%)',
      className: 'font-bold text-center text-blue-600 text-xs tabular-nums',
      render: (item) => `${item.persentase || 0}%`,
    },
    {
      key: 'keterangan',
      label: 'KETERANGAN',
      className: 'text-xs text-slate-600',
      render: (item) => item.keterangan || '-',
    },
    {
      key: 'aksi',
      label: 'AKSI',
      sortable: false,
      className: 'text-center w-24',
      render: (item) => (
        <div className="flex items-center justify-center space-x-1">
          <button
            onClick={() => openEditModal(item)}
            className="p-1.5 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors cursor-pointer"
            title="Edit"
          >
            <Edit2 className="w-4 h-4" />
          </button>
          <button
            onClick={() => handleDelete(item.id)}
            className="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
            title="Hapus"
          >
            <Trash2 className="w-4 h-4" />
          </button>
        </div>
      ),
    },
  ];

  return (
    <div className="space-y-4">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-4 rounded-xl border border-slate-200 shadow-2xs">
        <div>
          <div className="flex items-center space-x-2">
            <Sparkles className="w-5 h-5 text-blue-600" />
            <h1 className="text-lg font-black text-slate-900 tracking-tight">
              Rincian Invest
            </h1>
          </div>
          <p className="text-xs text-slate-500 mt-0.5">
            Daftar investor, nominal modal yang ditanamkan, serta persentase pembagian laba.
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
            onClick={openAddModal}
            className="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition-all shadow-xs cursor-pointer"
          >
            <Plus className="w-4 h-4" />
            <span>Tambah Investor</span>
          </button>
        </div>
      </div>

      {/* Table */}
      <div className="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs">
        <DataTable
          columns={columns}
          data={data}
          keyField="id"
          searchPlaceholder="Cari investor, keterangan..."
          emptyMessage="Belum ada data rincian investasi."
        />
      </div>

      {/* Modal */}
      {isModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-2xs p-4">
          <div className="bg-white w-full max-w-md rounded-2xl shadow-xl border border-slate-200 overflow-hidden animate-in fade-in zoom-in duration-150">
            <div className="flex items-center justify-between px-5 py-4 border-b border-slate-100 bg-slate-50/50">
              <h3 className="font-black text-slate-800 text-sm">
                {editingItem ? 'Edit Rincian Invest' : 'Tambah Investor Baru'}
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
                  Nama Investor <span className="text-rose-500">*</span>
                </label>
                <input
                  type="text"
                  required
                  value={nama}
                  onChange={(e) => setNama(e.target.value)}
                  placeholder="Nama pemodal / investor"
                  className="w-full px-3 py-2 border border-slate-300 rounded-lg font-semibold"
                />
              </div>

              <div>
                <label className="block font-bold text-slate-700 uppercase mb-1">
                  Nominal Investasi (Rp)
                </label>
                <input
                  type="number"
                  min="0"
                  step="100000"
                  value={nominal}
                  onChange={(e) => setNominal(Number(e.target.value))}
                  placeholder="0"
                  className="w-full px-3 py-2 border border-slate-300 rounded-lg font-semibold tabular-nums"
                />
              </div>

              <div>
                <label className="block font-bold text-slate-700 uppercase mb-1">
                  Persentase Bagi Hasil (%)
                </label>
                <input
                  type="number"
                  min="0"
                  max="100"
                  step="0.01"
                  value={persentase}
                  onChange={(e) => setPersentase(Number(e.target.value))}
                  placeholder="Contoh: 10"
                  className="w-full px-3 py-2 border border-slate-300 rounded-lg font-semibold tabular-nums"
                />
              </div>

              <div>
                <label className="block font-bold text-slate-700 uppercase mb-1">
                  Keterangan
                </label>
                <textarea
                  rows={2}
                  value={keterangan}
                  onChange={(e) => setKeterangan(e.target.value)}
                  placeholder="Catatan klausul bagi hasil, akad, dll..."
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
                  <span>{isSaving ? 'Menyimpan...' : 'Simpan'}</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}

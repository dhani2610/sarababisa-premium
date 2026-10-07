'use client';

import React, { useState, useEffect } from 'react';
import api from '@/lib/api';
import { DataTable, Column } from '@/components/ui/DataTable';
import Swal from 'sweetalert2';
import { Wrench, Plus, Edit2, Trash2, RefreshCw, X, Save } from 'lucide-react';

interface TindakanRow {
  id: number;
  nama: string;
  harga: number;
  created_at?: string;
}

export default function TindakanServisPage() {
  const [data, setData] = useState<TindakanRow[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [selectedIds, setSelectedIds] = useState<(number | string)[]>([]);

  // Modal State
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingItem, setEditingItem] = useState<TindakanRow | null>(null);
  const [nama, setNama] = useState('');
  const [harga, setHarga] = useState<number>(0);
  const [isSaving, setIsSaving] = useState(false);

  const fetchData = async () => {
    setIsLoading(true);
    try {
      const res = await api.get('/servis/tindakan-servis', {
        params: { per_page: 100 },
      });
      const list = res.data?.data || (Array.isArray(res.data) ? res.data : []);
      setData(list);
    } catch (err) {
      console.error('Failed to load tindakan servis', err);
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
    setHarga(0);
    setIsModalOpen(true);
  };

  const openEditModal = (item: TindakanRow) => {
    setEditingItem(item);
    setNama(item.nama);
    setHarga(item.harga || 0);
    setIsModalOpen(true);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!nama.trim()) {
      Swal.fire('Validasi', 'Nama tindakan harus diisi.', 'warning');
      return;
    }

    setIsSaving(true);
    try {
      const payload = { nama: nama.trim(), harga: Number(harga) || 0 };
      if (editingItem) {
        await api.put(`/servis/tindakan-servis/${editingItem.id}`, payload);
        Swal.fire('Berhasil', 'Tindakan servis berhasil diperbarui.', 'success');
      } else {
        await api.post('/servis/tindakan-servis', payload);
        Swal.fire('Berhasil', 'Tindakan servis baru berhasil ditambahkan.', 'success');
      }
      setIsModalOpen(false);
      fetchData();
    } catch (err: any) {
      Swal.fire('Gagal', err.response?.data?.detail || 'Terjadi kesalahan sistem', 'error');
    } finally {
      setIsSaving(false);
    }
  };

  const handleDelete = async (id: number) => {
    const confirm = await Swal.fire({
      title: 'Hapus Tindakan Servis?',
      text: 'Data yang dihapus tidak dapat dipulihkan.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Ya, Hapus',
      cancelButtonText: 'Batal',
      confirmButtonColor: '#ef4444',
    });
    if (!confirm.isConfirmed) return;

    try {
      await api.delete(`/servis/tindakan-servis/${id}`);
      Swal.fire('Terhapus', 'Tindakan servis berhasil dihapus.', 'success');
      fetchData();
    } catch (err: any) {
      Swal.fire('Gagal', err.response?.data?.detail || 'Gagal menghapus tindakan', 'error');
    }
  };

  const handleBulkDelete = async (ids: (number | string)[]) => {
    const confirm = await Swal.fire({
      title: `Hapus ${ids.length} Tindakan?`,
      text: 'Semua item tindakan yang dipilih akan dihapus.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: `Ya, Hapus (${ids.length})`,
      cancelButtonText: 'Batal',
      confirmButtonColor: '#ef4444',
    });
    if (!confirm.isConfirmed) return;

    try {
      await api.delete('/servis/tindakan-servis/batch', {
        data: { ids: ids.map((i) => Number(i)) },
      });
      Swal.fire('Terhapus', `${ids.length} data berhasil dihapus.`, 'success');
      setSelectedIds([]);
      fetchData();
    } catch (err: any) {
      Swal.fire('Gagal', 'Terjadi kesalahan sistem saat menghapus data.', 'error');
    }
  };

  const formatRupiah = (val: number = 0) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
    }).format(val);
  };

  const columns: Column<TindakanRow>[] = [
    {
      key: 'no',
      label: 'NO.',
      sortable: false,
      className: 'w-12 text-center text-slate-500 font-semibold',
      render: (_, idx) => <span>{idx}</span>,
    },
    {
      key: 'nama',
      label: 'NAMA TINDAKAN SERVIS',
      className: 'font-bold text-slate-800 uppercase text-xs',
      render: (item) => item.nama,
    },
    {
      key: 'harga',
      label: 'BIAYA DEFAULT',
      className: 'font-semibold text-slate-900 text-xs tabular-nums',
      render: (item) => formatRupiah(item.harga),
    },
    {
      key: 'aksi',
      label: 'AKSI',
      sortable: false,
      className: 'text-center w-28',
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
            <Wrench className="w-5 h-5 text-blue-600" />
            <h1 className="text-lg font-black text-slate-900 tracking-tight">
              Tindakan Servis
            </h1>
          </div>
          <p className="text-xs text-slate-500 mt-0.5">
            Daftar jenis tindakan perbaikan teknisi beserta biaya standar.
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
            <span>Tambah Tindakan</span>
          </button>
        </div>
      </div>

      {/* Data Table */}
      <div className="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs">
        <DataTable
          columns={columns}
          data={data}
          keyField="id"
          searchPlaceholder="Cari tindakan servis..."
          emptyMessage="Belum ada data tindakan servis."
          selectable
          selectedIds={selectedIds}
          onSelectionChange={setSelectedIds}
          onBulkDelete={handleBulkDelete}
        />
      </div>

      {/* Modal Form */}
      {isModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-2xs p-4">
          <div className="bg-white w-full max-w-md rounded-2xl shadow-xl border border-slate-200 overflow-hidden animate-in fade-in zoom-in duration-150">
            <div className="flex items-center justify-between px-5 py-4 border-b border-slate-100 bg-slate-50/50">
              <h3 className="font-black text-slate-800 text-sm">
                {editingItem ? 'Edit Tindakan Servis' : 'Tambah Tindakan Servis'}
              </h3>
              <button
                onClick={() => setIsModalOpen(false)}
                className="text-slate-400 hover:text-slate-600 cursor-pointer"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            <form onSubmit={handleSubmit} className="p-5 space-y-4">
              <div>
                <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                  Nama Tindakan Servis <span className="text-rose-500">*</span>
                </label>
                <input
                  type="text"
                  required
                  value={nama}
                  onChange={(e) => setNama(e.target.value)}
                  placeholder="Contoh: Ganti LCD, Reball IC Power, Software..."
                  className="w-full px-3.5 py-2 border border-slate-300 rounded-lg text-xs font-semibold focus:outline-hidden focus:border-blue-500"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                  Biaya Standar (Rp)
                </label>
                <input
                  type="number"
                  min="0"
                  step="1000"
                  value={harga}
                  onChange={(e) => setHarga(Number(e.target.value))}
                  placeholder="0"
                  className="w-full px-3.5 py-2 border border-slate-300 rounded-lg text-xs font-semibold tabular-nums focus:outline-hidden focus:border-blue-500"
                />
              </div>

              <div className="flex justify-end space-x-2 pt-3 border-t border-slate-100">
                <button
                  type="button"
                  onClick={() => setIsModalOpen(false)}
                  className="px-4 py-2 border border-slate-200 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-50 cursor-pointer"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  disabled={isSaving}
                  className="inline-flex items-center space-x-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold shadow-xs transition-colors cursor-pointer disabled:opacity-50"
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

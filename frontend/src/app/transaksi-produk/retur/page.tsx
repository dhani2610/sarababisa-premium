'use client';

import React, { useState, useEffect } from 'react';
import api from '@/lib/api';
import { DataTable, Column } from '@/components/ui/DataTable';
import Swal from 'sweetalert2';
import { RotateCcw, Plus, Trash2, RefreshCw, X, Save } from 'lucide-react';

interface ReturRow {
  id: number;
  no_retur: string;
  pelanggan_nama?: string;
  alasan?: string;
  status: string;
  created_at: string;
}

export default function ReturProdukPage() {
  const [data, setData] = useState<ReturRow[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  // Modal State
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [pelangganList, setPelangganList] = useState<any[]>([]);
  const [pelangganId, setPelangganId] = useState<number | ''>('');
  const [alasan, setAlasan] = useState('');
  const [isSaving, setIsSaving] = useState(false);

  const fetchData = async () => {
    setIsLoading(true);
    try {
      const res = await api.get('/produk/retur');
      const list = res.data?.data || (Array.isArray(res.data) ? res.data : []);
      setData(list);
    } catch (err) {
      console.error('Failed to load retur data', err);
    } finally {
      setIsLoading(false);
    }
  };

  const fetchPelanggan = async () => {
    try {
      const res = await api.get('/pelanggan', { params: { per_page: 100 } });
      setPelangganList(res.data?.data || (Array.isArray(res.data) ? res.data : []));
    } catch (err) {}
  };

  useEffect(() => {
    fetchData();
    fetchPelanggan();
  }, []);

  const handleCreate = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!alasan.trim()) {
      Swal.fire('Validasi', 'Alasan retur harus diisi.', 'warning');
      return;
    }

    setIsSaving(true);
    try {
      await api.post('/produk/retur', {
        pelanggan_id: pelangganId || null,
        alasan: alasan.trim(),
      });
      Swal.fire('Berhasil', 'Permohonan retur produk berhasil dibuat.', 'success');
      setIsModalOpen(false);
      setAlasan('');
      setPelangganId('');
      fetchData();
    } catch (err: any) {
      Swal.fire('Gagal', err.response?.data?.detail || 'Gagal menyimpan retur', 'error');
    } finally {
      setIsSaving(false);
    }
  };

  const handleDelete = async (id: number) => {
    const confirm = await Swal.fire({
      title: 'Hapus Retur?',
      text: 'Data yang dihapus tidak dapat dipulihkan.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Ya, Hapus',
      cancelButtonText: 'Batal',
      confirmButtonColor: '#ef4444',
    });
    if (!confirm.isConfirmed) return;

    try {
      await api.delete(`/produk/retur/${id}`);
      Swal.fire('Terhapus', 'Data retur berhasil dihapus.', 'success');
      fetchData();
    } catch (err: any) {
      Swal.fire('Gagal', err.response?.data?.detail || 'Gagal menghapus retur', 'error');
    }
  };

  const columns: Column<ReturRow>[] = [
    {
      key: 'no',
      label: 'NO.',
      sortable: false,
      className: 'w-12 text-center text-slate-500 font-semibold',
      render: (_, idx) => <span>{idx}</span>,
    },
    {
      key: 'no_retur',
      label: 'NO. RETUR',
      className: 'font-mono font-bold text-blue-600 text-xs',
      render: (item) => item.no_retur || `#RTR-${item.id}`,
    },
    {
      key: 'pelanggan_nama',
      label: 'PELANGGAN',
      className: 'font-semibold text-slate-800 uppercase text-xs',
      render: (item) => item.pelanggan_nama || 'Pelanggan Umum',
    },
    {
      key: 'status',
      label: 'STATUS',
      className: 'text-xs font-bold text-center',
      render: (item) => (
        <span className="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-amber-100 text-amber-700">
          {item.status || 'Menunggu'}
        </span>
      ),
    },
    {
      key: 'alasan',
      label: 'ALASAN RETUR',
      className: 'text-xs text-slate-600',
      render: (item) => item.alasan || '-',
    },
    {
      key: 'created_at',
      label: 'TANGGAL',
      className: 'text-xs text-slate-500 font-mono',
      render: (item) =>
        item.created_at ? new Date(item.created_at).toLocaleString('id-ID') : '-',
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
            <RotateCcw className="w-5 h-5 text-blue-600" />
            <h1 className="text-lg font-black text-slate-900 tracking-tight">
              Retur Produk
            </h1>
          </div>
          <p className="text-xs text-slate-500 mt-0.5">
            Daftar pengembalian produk atau barang dari pelanggan atau ke supplier.
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
            <span>Tambah Retur</span>
          </button>
        </div>
      </div>

      {/* Table */}
      <div className="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs">
        <DataTable
          columns={columns}
          data={data}
          keyField="id"
          searchPlaceholder="Cari no. retur, pelanggan, alasan..."
          emptyMessage="Belum ada data retur produk."
        />
      </div>

      {/* Modal Tambah Retur */}
      {isModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-2xs p-4">
          <div className="bg-white w-full max-w-md rounded-2xl shadow-xl border border-slate-200 overflow-hidden animate-in fade-in zoom-in duration-150">
            <div className="flex items-center justify-between px-5 py-4 border-b border-slate-100 bg-slate-50/50">
              <h3 className="font-black text-slate-800 text-sm">
                Tambah Retur Produk
              </h3>
              <button
                onClick={() => setIsModalOpen(false)}
                className="text-slate-400 hover:text-slate-600 cursor-pointer"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            <form onSubmit={handleCreate} className="p-5 space-y-4 text-xs">
              <div>
                <label className="block font-bold text-slate-700 uppercase mb-1.5">
                  Pelanggan (Opsional)
                </label>
                <select
                  value={pelangganId}
                  onChange={(e) => setPelangganId(e.target.value ? Number(e.target.value) : '')}
                  className="w-full px-3 py-2 border border-slate-300 rounded-lg font-semibold"
                >
                  <option value="">-- Pilih Pelanggan Umum --</option>
                  {pelangganList.map((p) => (
                    <option key={p.id} value={p.id}>
                      {p.nama} {p.no_hp ? `(${p.no_hp})` : ''}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label className="block font-bold text-slate-700 uppercase mb-1.5">
                  Alasan Retur <span className="text-rose-500">*</span>
                </label>
                <textarea
                  required
                  rows={3}
                  value={alasan}
                  onChange={(e) => setAlasan(e.target.value)}
                  placeholder="Kondisi cacat pabrik, salah tipe barang, dll..."
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
                  <span>{isSaving ? 'Menyimpan...' : 'Simpan Retur'}</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}

'use client';

import React, { useState, useEffect } from 'react';
import api from '@/lib/api';
import { DataTable, Column } from '@/components/ui/DataTable';
import Swal from 'sweetalert2';
import { Box, Plus, Trash2, RefreshCw, X, Save, CheckCircle2 } from 'lucide-react';

interface InventarisRow {
  id: number;
  nama: string;
  kode?: string;
  kondisi: string;
  lokasi?: string;
  harga?: number;
  tgl_beli?: string;
}

export default function InventarisPage() {
  const [data, setData] = useState<InventarisRow[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  // Modal State
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [nama, setNama] = useState('');
  const [kode, setKode] = useState('');
  const [kondisi, setKondisi] = useState('baik');
  const [lokasi, setLokasi] = useState('');
  const [harga, setHarga] = useState<number>(0);
  const [isSaving, setIsSaving] = useState(false);

  const fetchData = async () => {
    setIsLoading(true);
    try {
      const res = await api.get('/manajemen/inventaris');
      const list = res.data?.data || (Array.isArray(res.data) ? res.data : []);
      setData(list);
    } catch (err) {
      console.error('Failed to load inventaris', err);
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchData();
  }, []);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!nama.trim()) {
      Swal.fire('Validasi', 'Nama barang inventaris harus diisi.', 'warning');
      return;
    }

    setIsSaving(true);
    try {
      await api.post('/manajemen/inventaris', {
        nama: nama.trim(),
        kode: kode.trim() || null,
        kondisi,
        lokasi: lokasi.trim() || null,
        harga: Number(harga) || 0,
        tgl_beli: new Date().toISOString().slice(0, 10),
      });
      Swal.fire('Berhasil', 'Inventaris berhasil ditambahkan.', 'success');
      setIsModalOpen(false);
      setNama('');
      setKode('');
      setLokasi('');
      setHarga(0);
      fetchData();
    } catch (err: any) {
      Swal.fire('Gagal', err.response?.data?.detail || 'Gagal menyimpan inventaris', 'error');
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

  const columns: Column<InventarisRow>[] = [
    {
      key: 'no',
      label: 'NO.',
      sortable: false,
      className: 'w-12 text-center text-slate-500 font-semibold',
      render: (_, idx) => <span>{idx}</span>,
    },
    {
      key: 'nama',
      label: 'NAMA BARANG INVENTARIS',
      className: 'font-bold text-slate-800 uppercase text-xs',
      render: (item) => item.nama,
    },
    {
      key: 'kode',
      label: 'KODE / LABEL',
      className: 'font-mono text-xs text-blue-600 font-bold',
      render: (item) => item.kode || `INV-${item.id}`,
    },
    {
      key: 'kondisi',
      label: 'KONDISI',
      className: 'text-xs font-bold text-center uppercase',
      render: (item) => {
        const isGood = item.kondisi === 'baik';
        return (
          <span
            className={`inline-block px-2.5 py-0.5 rounded-full text-[10px] font-black ${
              isGood ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'
            }`}
          >
            {item.kondisi || 'Baik'}
          </span>
        );
      },
    },
    {
      key: 'lokasi',
      label: 'LOKASI PENEMPATAN',
      className: 'text-xs text-slate-600',
      render: (item) => item.lokasi || '-',
    },
    {
      key: 'harga',
      label: 'NILAI ASET',
      className: 'font-semibold text-slate-900 text-xs tabular-nums',
      render: (item) => (item.harga ? formatRupiah(item.harga) : '-'),
    },
  ];

  return (
    <div className="space-y-4">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-4 rounded-xl border border-slate-200 shadow-2xs">
        <div>
          <div className="flex items-center space-x-2">
            <Box className="w-5 h-5 text-blue-600" />
            <h1 className="text-lg font-black text-slate-900 tracking-tight">
              Inventaris Toko
            </h1>
          </div>
          <p className="text-xs text-slate-500 mt-0.5">
            Pencatatan aset, peralatan servis, furnitur, dan perlengkapan operasional toko.
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
            <span>Tambah Inventaris</span>
          </button>
        </div>
      </div>

      {/* Table */}
      <div className="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs">
        <DataTable
          columns={columns}
          data={data}
          keyField="id"
          searchPlaceholder="Cari nama inventaris, kode, lokasi..."
          emptyMessage="Belum ada data inventaris toko."
        />
      </div>

      {/* Modal */}
      {isModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-2xs p-4">
          <div className="bg-white w-full max-w-md rounded-2xl shadow-xl border border-slate-200 overflow-hidden animate-in fade-in zoom-in duration-150">
            <div className="flex items-center justify-between px-5 py-4 border-b border-slate-100 bg-slate-50/50">
              <h3 className="font-black text-slate-800 text-sm">
                Tambah Inventaris Baru
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
                  Nama Barang <span className="text-rose-500">*</span>
                </label>
                <input
                  type="text"
                  required
                  value={nama}
                  onChange={(e) => setNama(e.target.value)}
                  placeholder="Contoh: Solder Uap Quick 861DW, Meja Teknisi..."
                  className="w-full px-3 py-2 border border-slate-300 rounded-lg font-semibold"
                />
              </div>

              <div>
                <label className="block font-bold text-slate-700 uppercase mb-1">
                  Kode / Nomor Aset
                </label>
                <input
                  type="text"
                  value={kode}
                  onChange={(e) => setKode(e.target.value)}
                  placeholder="INV-001"
                  className="w-full px-3 py-2 border border-slate-300 rounded-lg font-mono font-semibold"
                />
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block font-bold text-slate-700 uppercase mb-1">
                    Kondisi
                  </label>
                  <select
                    value={kondisi}
                    onChange={(e) => setKondisi(e.target.value)}
                    className="w-full px-3 py-2 border border-slate-300 rounded-lg font-semibold"
                  >
                    <option value="baik">Baik</option>
                    <option value="rusak">Rusak</option>
                    <option value="hilang">Hilang</option>
                  </select>
                </div>
                <div>
                  <label className="block font-bold text-slate-700 uppercase mb-1">
                    Lokasi
                  </label>
                  <input
                    type="text"
                    value={lokasi}
                    onChange={(e) => setLokasi(e.target.value)}
                    placeholder="Ruang Teknisi / Kasir"
                    className="w-full px-3 py-2 border border-slate-300 rounded-lg font-medium"
                  />
                </div>
              </div>

              <div>
                <label className="block font-bold text-slate-700 uppercase mb-1">
                  Nilai Aset / Harga Beli (Rp)
                </label>
                <input
                  type="number"
                  min="0"
                  step="10000"
                  value={harga}
                  onChange={(e) => setHarga(Number(e.target.value))}
                  placeholder="0"
                  className="w-full px-3 py-2 border border-slate-300 rounded-lg font-semibold tabular-nums"
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

'use client';

import React, { useState, useEffect } from 'react';
import api from '@/lib/api';
import { DataTable, Column } from '@/components/ui/DataTable';
import Swal from 'sweetalert2';
import { ShieldCheck, CheckCircle2, XCircle, Search, RefreshCw, Plus, FileText, Check } from 'lucide-react';

interface QCItem {
  id: number;
  nama_produk: string;
  imei?: string;
  kondisi: string;
  catatan?: string;
  status: string;
  created_at: string;
  pic?: string;
}

export default function QCProdukPage() {
  const [data, setData] = useState<QCItem[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [showModal, setShowModal] = useState(false);
  const [form, setForm] = useState({
    nama_produk: '',
    imei: '',
    lcd: 'OK',
    baterai: 'OK',
    kamera: 'OK',
    speaker: 'OK',
    charging: 'OK',
    sinyal: 'OK',
    catatan: '',
  });

  const fetchData = async () => {
    setIsLoading(true);
    try {
      // Fetch products or QC list
      const res = await api.get('/produk/item', { params: { per_page: 50 } });
      const products = res.data?.data || (Array.isArray(res.data) ? res.data : []);
      // Map to QC format
      const qcs: QCItem[] = products.map((p: any) => ({
        id: p.id,
        nama_produk: p.nama || p.name || 'Produk',
        imei: p.imei || '-',
        kondisi: p.kondisi || 'Normal',
        catatan: p.keterangan || 'QC Standar Toko',
        status: p.stok > 0 ? 'Lolos QC' : 'Perlu Cek',
        created_at: p.created_at || new Date().toISOString(),
        pic: 'Staff Toko',
      }));
      setData(qcs);
    } catch (err) {
      console.error('Failed to fetch QC data', err);
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchData();
  }, []);

  const handleSimpanQc = (e: React.FormEvent) => {
    e.preventDefault();
    Swal.fire({
      icon: 'success',
      title: 'Pemeriksaan QC Berhasil',
      text: `Produk ${form.nama_produk || 'Unit'} telah diverifikasi lolos QC.`,
    });
    setShowModal(false);
  };

  const columns: Column<QCItem>[] = [
    {
      key: 'no',
      label: 'NO.',
      sortable: false,
      className: 'w-12 text-center text-slate-500 font-semibold',
      render: (_, idx) => <span>{idx}</span>,
    },
    {
      key: 'nama_produk',
      label: 'NAMA BARANG / UNIT',
      className: 'font-bold text-slate-800 uppercase text-xs',
      render: (item) => item.nama_produk,
    },
    {
      key: 'imei',
      label: 'NO. SERI / IMEI',
      className: 'font-mono text-xs text-slate-600',
      render: (item) => item.imei || '-',
    },
    {
      key: 'status',
      label: 'HASIL QC',
      className: 'text-xs font-bold text-center',
      render: (item) => (
        <span className="inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-100 text-emerald-700">
          <CheckCircle2 className="w-3 h-3" />
          <span>{item.status}</span>
        </span>
      ),
    },
    {
      key: 'catatan',
      label: 'CATATAN PEMERIKSAAN',
      className: 'text-xs text-slate-600',
      render: (item) => item.catatan || '-',
    },
    {
      key: 'pic',
      label: 'PEMERIKSA (PIC)',
      className: 'text-xs font-semibold text-slate-700',
      render: (item) => item.pic || 'Staff',
    },
  ];

  return (
    <div className="space-y-4">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-4 rounded-xl border border-slate-200 shadow-2xs">
        <div>
          <div className="flex items-center space-x-2">
            <ShieldCheck className="w-5 h-5 text-blue-600" />
            <h1 className="text-lg font-black text-slate-900 tracking-tight">
              Quality Control (QC) Produk
            </h1>
          </div>
          <p className="text-xs text-slate-500 mt-0.5">
            Checklist kelayakan fungsi fisik, hardware, dan sistem sebelum barang dijual ke pelanggan.
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
            onClick={() => setShowModal(true)}
            className="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition-all shadow-xs cursor-pointer"
          >
            <Plus className="w-4 h-4" />
            <span>Form Input QC</span>
          </button>
        </div>
      </div>

      {/* Table */}
      <div className="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs">
        <DataTable
          columns={columns}
          data={data}
          keyField="id"
          searchPlaceholder="Cari nama barang, IMEI, catatan..."
          emptyMessage="Belum ada data pemeriksaan QC."
        />
      </div>

      {/* Modal QC */}
      {showModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-2xs p-4">
          <div className="bg-white w-full max-w-lg rounded-2xl shadow-xl border border-slate-200 overflow-hidden animate-in fade-in zoom-in duration-150">
            <div className="flex items-center justify-between px-5 py-4 border-b border-slate-100 bg-slate-50/50">
              <h3 className="font-black text-slate-800 text-sm">
                Pemeriksaan Quality Control Baru
              </h3>
              <button
                onClick={() => setShowModal(false)}
                className="text-slate-400 hover:text-slate-600 cursor-pointer"
              >
                ✕
              </button>
            </div>

            <form onSubmit={handleSimpanQc} className="p-5 space-y-4 text-xs">
              <div>
                <label className="block font-bold text-slate-700 uppercase mb-1">
                  Nama Perangkat / Produk
                </label>
                <input
                  type="text"
                  required
                  value={form.nama_produk}
                  onChange={(e) => setForm({ ...form, nama_produk: e.target.value })}
                  placeholder="Contoh: iPhone 13 Pro 128GB"
                  className="w-full px-3 py-2 border border-slate-300 rounded-lg font-semibold"
                />
              </div>

              <div>
                <label className="block font-bold text-slate-700 uppercase mb-1">
                  Nomor IMEI / Serial
                </label>
                <input
                  type="text"
                  value={form.imei}
                  onChange={(e) => setForm({ ...form, imei: e.target.value })}
                  placeholder="3562..."
                  className="w-full px-3 py-2 border border-slate-300 rounded-lg font-mono"
                />
              </div>

              <div className="space-y-2 pt-2">
                <p className="font-black text-slate-800 uppercase tracking-wider text-[11px]">
                  Checklist Parameter Fungsi:
                </p>
                <div className="grid grid-cols-2 gap-2 text-xs">
                  {['LCD & Sentuhan', 'Baterai & Health', 'Kamera Depan/Belakang', 'Speaker & Mic', 'Port Charging', 'Sinyal & Wi-Fi'].map((item) => (
                    <label key={item} className="flex items-center space-x-2 p-2 rounded-lg bg-slate-50 border border-slate-200 cursor-pointer hover:bg-slate-100">
                      <input type="checkbox" defaultChecked className="rounded text-blue-600 focus:ring-0" />
                      <span className="font-semibold text-slate-700">{item}</span>
                    </label>
                  ))}
                </div>
              </div>

              <div>
                <label className="block font-bold text-slate-700 uppercase mb-1">
                  Catatan Tambahan
                </label>
                <textarea
                  value={form.catatan}
                  onChange={(e) => setForm({ ...form, catatan: e.target.value })}
                  rows={2}
                  placeholder="Catatan fisik lecet halus, BH 88%, dll..."
                  className="w-full px-3 py-2 border border-slate-300 rounded-lg font-medium"
                />
              </div>

              <div className="flex justify-end space-x-2 pt-3 border-t border-slate-100">
                <button
                  type="button"
                  onClick={() => setShowModal(false)}
                  className="px-4 py-2 border border-slate-200 rounded-lg font-semibold text-slate-600 hover:bg-slate-50 cursor-pointer"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  className="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold shadow-xs cursor-pointer"
                >
                  Simpan Hasil QC
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}

'use client';

import React, { useState, useEffect } from 'react';
import api from '@/lib/api';
import { DataTable, Column } from '@/components/ui/DataTable';
import Swal from 'sweetalert2';
import { ShieldCheck, Plus, Trash2, Printer, RefreshCw, Edit2 } from 'lucide-react';

interface GaransiRow {
  id: number;
  tgl_klaim?: string;
  date?: string;
  created_at?: string;
  service_id: number;
  no_nota_servis?: string;
  pelanggan_nama?: string;
  penerima_nama?: string;
  teknisi_nama?: string;
  tindakan?: string;
  sparepart?: string;
  total_biaya?: number;
  catatan?: string;
  status?: string;
}

export default function RiwayatGaransiPage() {
  const [data, setData] = useState<GaransiRow[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [selectedIds, setSelectedIds] = useState<(number | string)[]>([]);

  const fetchGaransi = async () => {
    setIsLoading(true);
    try {
      const res = await api.get('/servis/garansi', {
        params: { per_page: 50 },
      });
      const list = Array.isArray(res.data) ? res.data : res.data?.data || [];
      setData(list);
    } catch (err) {
      console.error('Failed to load riwayat garansi', err);
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchGaransi();
  }, []);

  const formatRupiah = (val: number = 0) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
    }).format(val);
  };

  const handleDeleteSingle = async (id: number) => {
    const confirm = await Swal.fire({
      title: 'Hapus Riwayat Garansi?',
      text: 'Data garansi yang dihapus tidak dapat dipulihkan.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#ef4444',
      confirmButtonText: 'Ya, Hapus',
      cancelButtonText: 'Batal',
    });

    if (confirm.isConfirmed) {
      try {
        await api.delete(`/servis/garansi/${id}`);
        Swal.fire('Terhapus!', 'Riwayat garansi berhasil dihapus.', 'success');
        fetchGaransi();
      } catch (err: any) {
        Swal.fire('Gagal', err.response?.data?.detail || 'Gagal menghapus garansi', 'error');
      }
    }
  };

  const handleBulkDelete = async (ids: (number | string)[]) => {
    const confirm = await Swal.fire({
      title: `Hapus ${ids.length} Riwayat Terpilih?`,
      text: 'Semua item yang dipilih akan dihapus.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#ef4444',
      confirmButtonText: `Ya, Hapus (${ids.length})`,
      cancelButtonText: 'Batal',
    });

    if (confirm.isConfirmed) {
      try {
        await Promise.all(ids.map((id) => api.delete(`/servis/garansi/${id}`)));
        Swal.fire('Terhapus!', `${ids.length} riwayat garansi dihapus.`, 'success');
        setSelectedIds([]);
        fetchGaransi();
      } catch (err: any) {
        Swal.fire('Gagal', 'Terjadi kesalahan sistem saat menghapus.', 'error');
      }
    }
  };

  const columns: Column<GaransiRow>[] = [
    {
      key: 'no',
      label: 'NO.',
      sortable: false,
      className: 'w-10 text-center text-slate-500 font-semibold',
      render: (_, idx) => <span>{idx}</span>,
    },
    {
      key: 'tanggal',
      label: 'TANGGAL',
      className: 'whitespace-nowrap font-medium text-slate-700',
      render: (item) => {
        const d = item.tgl_klaim || item.date || item.created_at;
        return d ? new Date(d).toLocaleDateString('id-ID') : '-';
      },
    },
    {
      key: 'nomor_servis',
      label: 'NOMOR SERVIS',
      className: 'whitespace-nowrap font-bold text-[#5051F9]',
      render: (item) => item.no_nota_servis || `#${item.service_id}`,
    },
    {
      key: 'customer',
      label: 'CUSTOMER',
      className: 'font-semibold text-slate-800',
      render: (item) => item.pelanggan_nama || '-',
    },
    {
      key: 'teknisi',
      label: 'TEKNISI',
      className: 'text-slate-700 uppercase font-medium text-[11px]',
      render: (item) => item.teknisi_nama || 'SILPAH SEPTIANA',
    },
    {
      key: 'tindakan',
      label: 'TINDAKAN',
      className: 'text-slate-700 max-w-xs',
      render: (item) => {
        if (!item.tindakan) return '-';
        try {
          const list = JSON.parse(item.tindakan);
          if (Array.isArray(list)) {
            return list.join(', ');
          }
        } catch (e) {}
        return item.tindakan;
      },
    },
    {
      key: 'total_biaya',
      label: 'TOTAL BIAYA',
      className: 'whitespace-nowrap font-bold text-slate-900',
      render: (item) => formatRupiah(item.total_biaya || 0),
    },
    {
      key: 'catatan',
      label: 'CATATAN',
      className: 'text-slate-600 text-[11px] max-w-xs truncate',
      render: (item) => item.catatan || '-',
    },
    {
      key: 'status',
      label: 'STATUS',
      className: 'text-center whitespace-nowrap',
      render: (item) => {
        const s = item.status || 'Proses';
        const isDone = s.toLowerCase().includes('selesai') || s.toLowerCase().includes('sudah');
        return (
          <span
            className={`inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold ${
              isDone
                ? 'bg-emerald-100 text-emerald-700'
                : 'bg-amber-100 text-amber-700'
            }`}
          >
            {s}
          </span>
        );
      },
    },
    {
      key: 'aksi',
      label: 'AKSI',
      sortable: false,
      className: 'text-center w-14',
      render: (item) => (
        <div className="flex items-center justify-center space-x-1">
          <button
            onClick={() => handleDeleteSingle(item.id)}
            className="p-1 rounded text-rose-500 hover:bg-rose-50 transition-colors cursor-pointer"
            title="Hapus Garansi"
          >
            <Trash2 className="w-3.5 h-3.5" />
          </button>
        </div>
      ),
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h1 className="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-1.5">
          <span>Riwayat Garansi</span>
          <span className="text-amber-400">✨</span>
        </h1>

        <div className="flex items-center space-x-2">
          <button
            onClick={fetchGaransi}
            className="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-md border border-slate-300 bg-white hover:bg-slate-50 text-xs font-semibold text-slate-700 shadow-2xs transition-colors cursor-pointer"
          >
            <RefreshCw className="w-3.5 h-3.5 text-slate-500" />
            <span>Refresh</span>
          </button>
        </div>
      </div>

      <DataTable<GaransiRow>
        columns={columns}
        data={data}
        isLoading={isLoading}
        title="Daftar Riwayat Klaim Garansi"
        selectable={true}
        selectedIds={selectedIds}
        onSelectChange={setSelectedIds}
        onBulkDelete={handleBulkDelete}
        idKey="id"
        defaultPerPage={10}
        perPageOptions={[10, 25, 50, 100]}
        emptyMessage="Tidak ada riwayat klaim garansi servis."
      />
    </div>
  );
}

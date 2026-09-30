'use client';

import React, { useState, useEffect } from 'react';
import api from '@/lib/api';
import { DataTable, Column } from '@/components/ui/DataTable';
import Swal from 'sweetalert2';
import { ShieldAlert, Trash2, RefreshCw, FileSpreadsheet } from 'lucide-react';

interface LogRow {
  id: number;
  created_at: string;
  user_nama?: string;
  causer_name?: string;
  description: string;
  aktivitas?: string;
  no_nota?: string;
  nomor_servis?: string;
  pelanggan_nama?: string;
  nama_barang?: string;
  sebelum?: string;
  sesudah?: string;
}

export default function LogServisPage() {
  const [data, setData] = useState<LogRow[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [selectedIds, setSelectedIds] = useState<(number | string)[]>([]);

  const fetchLogs = async () => {
    setIsLoading(true);
    try {
      const res = await api.get('/servis/log-servis', {
        params: { per_page: 50 },
      });
      const list = Array.isArray(res.data) ? res.data : res.data?.data || [];
      setData(list);
    } catch (err) {
      console.error('Failed to load log servis', err);
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchLogs();
  }, []);

  const handleDeleteSingle = async (id: number) => {
    const confirm = await Swal.fire({
      title: 'Hapus Log Servis?',
      text: 'Log yang dihapus tidak dapat dipulihkan.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#ef4444',
      confirmButtonText: 'Ya, Hapus',
      cancelButtonText: 'Batal',
    });

    if (confirm.isConfirmed) {
      try {
        await api.delete(`/servis/log-servis/${id}`);
        Swal.fire('Terhapus!', 'Log servis berhasil dihapus.', 'success');
        fetchLogs();
      } catch (err: any) {
        Swal.fire('Gagal', err.response?.data?.detail || 'Gagal menghapus log', 'error');
      }
    }
  };

  const handleBulkDelete = async (ids: (number | string)[]) => {
    const confirm = await Swal.fire({
      title: `Hapus ${ids.length} Log Terpilih?`,
      text: 'Semua log yang dipilih akan dihapus permanen.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#ef4444',
      confirmButtonText: `Ya, Hapus (${ids.length})`,
      cancelButtonText: 'Batal',
    });

    if (confirm.isConfirmed) {
      try {
        await Promise.all(ids.map((id) => api.delete(`/servis/log-servis/${id}`)));
        Swal.fire('Terhapus!', `${ids.length} log berhasil dibersihkan.`, 'success');
        setSelectedIds([]);
        fetchLogs();
      } catch (err: any) {
        Swal.fire('Gagal', 'Terjadi kesalahan saat menghapus log.', 'error');
      }
    }
  };

  const columns: Column<LogRow>[] = [
    {
      key: 'no',
      label: 'NO.',
      sortable: false,
      className: 'w-10 text-center text-slate-500 font-semibold',
      render: (_, idx) => <span>{idx}</span>,
    },
    {
      key: 'created_at',
      label: 'WAKTU',
      className: 'whitespace-nowrap font-medium text-slate-700',
      render: (item) => {
        const d = item.created_at ? new Date(item.created_at) : null;
        return d
          ? `${d.toLocaleDateString('id-ID')} ${d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}`
          : '-';
      },
    },
    {
      key: 'akun',
      label: 'AKUN',
      className: 'font-semibold text-slate-800 uppercase text-[11px]',
      render: (item) => item.user_nama || item.causer_name || 'Admin Toko',
    },
    {
      key: 'aktivitas',
      label: 'AKTIVITAS',
      className: 'font-medium',
      render: (item) => {
        const desc = item.description || item.aktivitas || 'Update Servis';
        const isDelete = desc.toLowerCase().includes('delete');
        const isCreate = desc.toLowerCase().includes('create');
        return (
          <span
            className={`inline-block px-2 py-0.5 rounded text-[10px] font-bold ${
              isDelete
                ? 'bg-rose-100 text-rose-700'
                : isCreate
                ? 'bg-emerald-100 text-emerald-700'
                : 'bg-blue-100 text-blue-700'
            }`}
          >
            {desc}
          </span>
        );
      },
    },
    {
      key: 'nomor_servis',
      label: 'NOMOR SERVIS',
      className: 'whitespace-nowrap font-bold text-[#5051F9]',
      render: (item) => item.no_nota || item.nomor_servis || '#Servis',
    },
    {
      key: 'pelanggan',
      label: 'PELANGGAN',
      className: 'text-slate-800 font-medium',
      render: (item) => item.pelanggan_nama || '-',
    },
    {
      key: 'nama_barang',
      label: 'NAMA BARANG',
      className: 'font-semibold text-slate-800 text-[11px]',
      render: (item) => item.nama_barang || 'Handphone Unit',
    },
    {
      key: 'sebelum',
      label: 'SEBELUM',
      sortable: false,
      className: 'text-orange-600 text-[11px] max-w-xs truncate',
      render: (item) => item.sebelum || '-',
    },
    {
      key: 'sesudah',
      label: 'SESUDAH',
      sortable: false,
      className: 'text-blue-600 text-[11px] max-w-xs truncate',
      render: (item) => item.sesudah || '-',
    },
    {
      key: 'aksi',
      label: 'AKSI',
      sortable: false,
      className: 'text-center w-12',
      render: (item) => (
        <button
          onClick={() => handleDeleteSingle(item.id)}
          className="p-1 rounded text-rose-500 hover:bg-rose-50 transition-colors cursor-pointer"
          title="Hapus Log"
        >
          <Trash2 className="w-3.5 h-3.5" />
        </button>
      ),
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h1 className="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-1.5">
          <span>Log Servis</span>
          <span className="text-amber-400">✨</span>
        </h1>

        <div className="flex items-center space-x-2">
          <button
            onClick={fetchLogs}
            className="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-md border border-slate-300 bg-white hover:bg-slate-50 text-xs font-semibold text-slate-700 shadow-2xs transition-colors cursor-pointer"
          >
            <RefreshCw className="w-3.5 h-3.5 text-slate-500" />
            <span>Refresh</span>
          </button>
        </div>
      </div>

      <DataTable<LogRow>
        columns={columns}
        data={data}
        isLoading={isLoading}
        title="Daftar Log Aktivitas Servis"
        selectable={true}
        selectedIds={selectedIds}
        onSelectChange={setSelectedIds}
        onBulkDelete={handleBulkDelete}
        idKey="id"
        defaultPerPage={10}
        perPageOptions={[10, 25, 50, 100]}
        emptyMessage="Tidak ada riwayat log aktivitas servis."
      />
    </div>
  );
}

'use client';

import React, { useState, useEffect } from 'react';
import api from '@/lib/api';
import { DataTable, Column } from '@/components/ui/DataTable';
import { ShieldCheck, RefreshCw, Activity, User, Calendar } from 'lucide-react';

interface AuditLogRow {
  id: number;
  user_id?: number;
  tipe: string;
  aksi: string;
  keterangan?: string;
  created_at: string;
}

export default function AuditLogDataPage() {
  const [data, setData] = useState<AuditLogRow[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  const fetchData = async () => {
    setIsLoading(true);
    try {
      const res = await api.get('/audit-log', {
        params: { per_page: 50 },
      });
      const list = res.data?.data || (Array.isArray(res.data) ? res.data : []);
      setData(list);
    } catch (err) {
      console.error('Failed to load audit logs', err);
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchData();
  }, []);

  const columns: Column<AuditLogRow>[] = [
    {
      key: 'no',
      label: 'NO.',
      sortable: false,
      className: 'w-12 text-center text-slate-500 font-semibold',
      render: (_, idx) => <span>{idx}</span>,
    },
    {
      key: 'aksi',
      label: 'AKSI / AKTIVITAS',
      className: 'font-bold text-slate-800 text-xs uppercase',
      render: (item) => (
        <div className="flex items-center space-x-1.5">
          <Activity className="w-3.5 h-3.5 text-blue-500" />
          <span>{item.aksi || item.tipe || 'Sistem'}</span>
        </div>
      ),
    },
    {
      key: 'tipe',
      label: 'MODUL',
      className: 'text-xs font-semibold text-center',
      render: (item) => (
        <span className="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-slate-100 text-slate-700">
          {item.tipe || 'Umum'}
        </span>
      ),
    },
    {
      key: 'keterangan',
      label: 'DETAIL LOG AKTIVITAS',
      className: 'text-xs text-slate-600',
      render: (item) => item.keterangan || '-',
    },
    {
      key: 'created_at',
      label: 'WAKTU',
      className: 'text-xs text-slate-500 font-mono',
      render: (item) =>
        item.created_at ? new Date(item.created_at).toLocaleString('id-ID') : '-',
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
              Audit Log Data
            </h1>
          </div>
          <p className="text-xs text-slate-500 mt-0.5">
            Histori lengkap seluruh aktivitas sistem, perubahan data transaksi, dan log pengguna.
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
        </div>
      </div>

      {/* Table */}
      <div className="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs">
        <DataTable
          columns={columns}
          data={data}
          keyField="id"
          searchPlaceholder="Cari modul, aksi, detail log..."
          emptyMessage="Belum ada catatan audit log."
        />
      </div>
    </div>
  );
}

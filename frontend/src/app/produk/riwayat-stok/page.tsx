'use client';

import React, { useState, useEffect } from 'react';
import Link from 'next/link';
import api from '@/lib/api';
import { DataTable, Column } from '@/components/ui/DataTable';
import { History, ArrowRightLeft, RefreshCw, Box, Calendar } from 'lucide-react';

interface TransferRow {
  id: number;
  no_transfer: string;
  cabang_asal: number;
  cabang_tujuan: number;
  tujuan_nama?: string;
  status: string;
  catatan?: string;
  created_at: string;
  user_nama?: string;
}

export default function RiwayatStokPage() {
  const [data, setData] = useState<TransferRow[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  const fetchData = async () => {
    setIsLoading(true);
    try {
      const res = await api.get('/transfer-stok', {
        params: { per_page: 50 },
      });
      const list = res.data?.data || (Array.isArray(res.data) ? res.data : []);
      setData(list);
    } catch (err) {
      console.error('Failed to load riwayat stok', err);
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchData();
  }, []);

  const columns: Column<TransferRow>[] = [
    {
      key: 'no',
      label: 'NO.',
      sortable: false,
      className: 'w-12 text-center text-slate-500 font-semibold',
      render: (_, idx) => <span>{idx}</span>,
    },
    {
      key: 'no_transfer',
      label: 'NO. MUTASI / TRANSFER',
      className: 'font-mono font-bold text-blue-600 text-xs',
      render: (item) => item.no_transfer || `#TRF-${item.id}`,
    },
    {
      key: 'cabang',
      label: 'TUJUAN CABANG',
      className: 'text-xs font-semibold text-slate-800 uppercase',
      render: (item) => item.tujuan_nama || `Cabang #${item.cabang_tujuan}`,
    },
    {
      key: 'status',
      label: 'STATUS',
      className: 'text-xs font-bold text-center',
      render: (item) => {
        const isApproved = item.status === 'disetujui' || item.status === 'selesai';
        return (
          <span
            className={`inline-block px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase ${
              isApproved
                ? 'bg-emerald-100 text-emerald-700'
                : 'bg-amber-100 text-amber-700'
            }`}
          >
            {item.status || 'Pending'}
          </span>
        );
      },
    },
    {
      key: 'catatan',
      label: 'CATATAN',
      className: 'text-xs text-slate-600',
      render: (item) => item.catatan || '-',
    },
    {
      key: 'created_at',
      label: 'TANGGAL',
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
            <History className="w-5 h-5 text-blue-600" />
            <h1 className="text-lg font-black text-slate-900 tracking-tight">
              Riwayat Mutasi & Transfer Stok
            </h1>
          </div>
          <p className="text-xs text-slate-500 mt-0.5">
            Log perpindahan stok antar cabang dan histori keluar masuk barang.
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
          <Link
            href="/transfer-stok"
            className="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition-all shadow-xs cursor-pointer"
          >
            <ArrowRightLeft className="w-4 h-4" />
            <span>Transfer Stok Baru</span>
          </Link>
        </div>
      </div>

      {/* Table */}
      <div className="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs">
        <DataTable
          columns={columns}
          data={data}
          keyField="id"
          searchPlaceholder="Cari nomor transfer, cabang, catatan..."
          emptyMessage="Belum ada riwayat mutasi stok."
        />
      </div>
    </div>
  );
}

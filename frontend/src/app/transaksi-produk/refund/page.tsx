'use client';

import React, { useState, useEffect } from 'react';
import api from '@/lib/api';
import { DataTable, Column } from '@/components/ui/DataTable';
import Swal from 'sweetalert2';
import { Receipt, Plus, RefreshCw, CheckCircle2, Clock } from 'lucide-react';

interface RefundRow {
  id: number;
  nomor_servis?: string;
  no_invoice?: string;
  nominal: number;
  nominal_servis?: number;
  alasan?: string;
  teknisi_name?: string;
  status: string;
  is_approve?: string;
  created_at: string;
}

export default function RefundProdukPage() {
  const [data, setData] = useState<RefundRow[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  const fetchData = async () => {
    setIsLoading(true);
    try {
      // Try fetching returs or general orders
      const res = await api.get('/produk/retur');
      const returs = res.data?.data || (Array.isArray(res.data) ? res.data : []);
      const mapped: RefundRow[] = returs.map((r: any) => ({
        id: r.id,
        nomor_servis: r.no_retur || `REF-${r.id}`,
        nominal: 0,
        nominal_servis: 0,
        alasan: r.alasan || 'Pengembalian Dana / Retur',
        teknisi_name: r.pelanggan_nama || 'Pelanggan',
        status: r.status || 'menunggu',
        is_approve: r.status === 'disetujui' ? 'Setuju' : 'Belum',
        created_at: r.created_at || new Date().toISOString(),
      }));
      setData(mapped);
    } catch (err) {
      console.error('Failed to load refund', err);
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchData();
  }, []);

  const formatRupiah = (val: number = 0) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
    }).format(val);
  };

  const columns: Column<RefundRow>[] = [
    {
      key: 'no',
      label: 'NO.',
      sortable: false,
      className: 'w-12 text-center text-slate-500 font-semibold',
      render: (_, idx) => <span>{idx}</span>,
    },
    {
      key: 'nomor_servis',
      label: 'NO. INVOICE / REFUND',
      className: 'font-mono font-bold text-blue-600 text-xs',
      render: (item) => item.nomor_servis,
    },
    {
      key: 'teknisi_name',
      label: 'PELANGGAN / TEKNISI',
      className: 'font-semibold text-slate-800 uppercase text-xs',
      render: (item) => item.teknisi_name || '-',
    },
    {
      key: 'alasan',
      label: 'ALASAN PENGEMBALIAN',
      className: 'text-xs text-slate-600',
      render: (item) => item.alasan || '-',
    },
    {
      key: 'status',
      label: 'STATUS APPROVAL',
      className: 'text-xs font-bold text-center',
      render: (item) => {
        const approved = item.is_approve === 'Setuju' || item.status === 'disetujui';
        return (
          <span
            className={`inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase ${
              approved
                ? 'bg-emerald-100 text-emerald-700'
                : 'bg-amber-100 text-amber-700'
            }`}
          >
            {approved ? <CheckCircle2 className="w-3 h-3" /> : <Clock className="w-3 h-3" />}
            <span>{approved ? 'Disetujui' : 'Menunggu'}</span>
          </span>
        );
      },
    },
    {
      key: 'created_at',
      label: 'TANGGAL',
      className: 'text-xs text-slate-500 font-mono',
      render: (item) =>
        item.created_at ? new Date(item.created_at).toLocaleDateString('id-ID') : '-',
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
              Pengembalian Dana & Invoice Refund
            </h1>
          </div>
          <p className="text-xs text-slate-500 mt-0.5">
            Daftar permohonan pengembalian dana transaksi servis dan retur produk.
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
          searchPlaceholder="Cari nomor invoice, refund, pelanggan..."
          emptyMessage="Belum ada data pengembalian dana / invoice refund."
        />
      </div>
    </div>
  );
}

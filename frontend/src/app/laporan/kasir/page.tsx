'use client';

import React, { useState, useEffect } from 'react';
import api from '@/lib/api';
import { DataTable, Column } from '@/components/ui/DataTable';
import { ShoppingCart, Calendar, RefreshCw, UserCheck, DollarSign } from 'lucide-react';

interface KasirRow {
  id: number;
  no_invoice: string;
  pelanggan_nama?: string;
  kasir_nama?: string;
  total: number;
  metode?: string;
  created_at: string;
}

export default function LaporanKasirPage() {
  const [data, setData] = useState<KasirRow[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [bulan, setBulan] = useState(new Date().toISOString().slice(0, 7)); // YYYY-MM

  const fetchData = async () => {
    setIsLoading(true);
    try {
      const res = await api.get('/laporan/penjualan', { params: { bulan } });
      const list = res.data?.data || (Array.isArray(res.data) ? res.data : []);
      setData(list);
    } catch (err) {
      console.error('Failed to load laporan kasir', err);
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchData();
  }, [bulan]);

  const formatRupiah = (val: number = 0) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
    }).format(val);
  };

  const columns: Column<KasirRow>[] = [
    {
      key: 'no',
      label: 'NO.',
      sortable: false,
      className: 'w-12 text-center text-slate-500 font-semibold',
      render: (_, idx) => <span>{idx}</span>,
    },
    {
      key: 'no_invoice',
      label: 'NO. INVOICE',
      className: 'font-mono font-bold text-blue-600 text-xs',
      render: (item) => item.no_invoice || `#INV-${item.id}`,
    },
    {
      key: 'kasir_nama',
      label: 'KASIR (OPERATOR)',
      className: 'font-semibold text-slate-800 uppercase text-xs',
      render: (item) => item.kasir_nama || 'Kasir Toko',
    },
    {
      key: 'pelanggan_nama',
      label: 'PELANGGAN',
      className: 'text-xs text-slate-700 uppercase',
      render: (item) => item.pelanggan_nama || 'Pelanggan Umum',
    },
    {
      key: 'total',
      label: 'TOTAL TRANSAKSI',
      className: 'text-right font-black text-slate-900 text-xs tabular-nums',
      render: (item) => formatRupiah(item.total),
    },
    {
      key: 'created_at',
      label: 'WAKTU TRANSAKSI',
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
            <ShoppingCart className="w-5 h-5 text-blue-600" />
            <h1 className="text-lg font-black text-slate-900 tracking-tight">
              Laporan Kasir & Transaksi POS
            </h1>
          </div>
          <p className="text-xs text-slate-500 mt-0.5">
            Rekapitulasi penjualan produk kasir per periode shift dan bulan berjalan.
          </p>
        </div>

        <div className="flex items-center space-x-2">
          <div className="flex items-center space-x-1.5 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-lg text-xs">
            <Calendar className="w-3.5 h-3.5 text-slate-500" />
            <input
              type="month"
              value={bulan}
              onChange={(e) => setBulan(e.target.value)}
              className="bg-transparent font-semibold text-slate-700 focus:outline-hidden"
            />
          </div>
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
          searchPlaceholder="Cari no. invoice, kasir, pelanggan..."
          emptyMessage="Belum ada transaksi kasir pada periode ini."
        />
      </div>
    </div>
  );
}

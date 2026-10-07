'use client';

import React, { useState, useEffect } from 'react';
import api from '@/lib/api';
import { FileBarChart2, Calendar, RefreshCw, TrendingUp, TrendingDown, DollarSign, Wallet } from 'lucide-react';

export default function LaporanKeuanganPage() {
  const [data, setData] = useState<any>(null);
  const [isLoading, setIsLoading] = useState(true);

  const fetchData = async () => {
    setIsLoading(true);
    try {
      const res = await api.get('/dashboard');
      setData(res.data);
    } catch (err) {
      console.error('Failed to load keuangan', err);
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

  const omzetBulan = data?.keuangan?.omzet_total_bulan || 0;
  const pengeluaranBulan = data?.keuangan?.pengeluaran_bulan || 0;
  const profitBulan = data?.keuangan?.profit_bulan || 0;

  return (
    <div className="space-y-5">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-4 rounded-xl border border-slate-200 shadow-2xs">
        <div>
          <div className="flex items-center space-x-2">
            <FileBarChart2 className="w-5 h-5 text-blue-600" />
            <h1 className="text-lg font-black text-slate-900 tracking-tight">
              Laporan Keuangan & Arus Kas
            </h1>
          </div>
          <p className="text-xs text-slate-500 mt-0.5">
            Ikhtisar performa finansial, omzet gabungan servis & penjualan, serta beban operasional toko.
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

      {/* KPI Cards */}
      <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div className="bg-white p-5 rounded-xl border border-slate-200 shadow-2xs">
          <div className="flex items-center justify-between">
            <span className="text-[11px] font-bold uppercase tracking-wider text-slate-500">
              Total Omzet Kotor (Bulan Ini)
            </span>
            <div className="p-2.5 rounded-lg bg-blue-50 text-blue-600">
              <TrendingUp className="w-5 h-5" />
            </div>
          </div>
          <p className="text-2xl font-black text-slate-900 mt-3 tabular-nums">
            {formatRupiah(omzetBulan)}
          </p>
          <div className="mt-2 text-[11px] text-slate-500 flex justify-between">
            <span>Servis: {formatRupiah(data?.servis?.omzet_bulan_ini)}</span>
            <span>Penjualan: {formatRupiah(data?.penjualan?.omzet_bulan_ini)}</span>
          </div>
        </div>

        <div className="bg-white p-5 rounded-xl border border-slate-200 shadow-2xs">
          <div className="flex items-center justify-between">
            <span className="text-[11px] font-bold uppercase tracking-wider text-slate-500">
              Total Beban Pengeluaran
            </span>
            <div className="p-2.5 rounded-lg bg-rose-50 text-rose-600">
              <TrendingDown className="w-5 h-5" />
            </div>
          </div>
          <p className="text-2xl font-black text-rose-600 mt-3 tabular-nums">
            {formatRupiah(pengeluaranBulan)}
          </p>
          <p className="mt-2 text-[11px] text-slate-500">
            Biaya operasional, utilitas & konsumsi toko
          </p>
        </div>

        <div className="bg-gradient-to-br from-emerald-600 to-teal-700 text-white p-5 rounded-xl shadow-md">
          <div className="flex items-center justify-between">
            <span className="text-[11px] font-bold uppercase tracking-wider text-emerald-100">
              Laba Bersih (Net Profit)
            </span>
            <div className="p-2.5 rounded-lg bg-white/10 text-white">
              <Wallet className="w-5 h-5" />
            </div>
          </div>
          <p className="text-2xl font-black mt-3 tabular-nums">
            {formatRupiah(profitBulan)}
          </p>
          <p className="mt-2 text-[11px] text-emerald-100/80">
            Margin Bersih Operasional Cabang
          </p>
        </div>
      </div>
    </div>
  );
}

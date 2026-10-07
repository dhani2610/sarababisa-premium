'use client';

import React, { useState, useEffect } from 'react';
import api from '@/lib/api';
import { Sparkles, Calendar, RefreshCw, DollarSign, TrendingUp, Users, Wallet } from 'lucide-react';

interface PembagianItem {
  nama: string;
  persentase: number;
  nominal_dapat: number;
}

interface PembagianData {
  bulan: string;
  profit_servis: number;
  profit_penjualan: number;
  total_profit: number;
  pembagian: PembagianItem[];
}

export default function PembagianHasilInvestPage() {
  const [bulan, setBulan] = useState(new Date().toISOString().slice(0, 7)); // YYYY-MM
  const [data, setData] = useState<PembagianData | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  const fetchData = async () => {
    setIsLoading(true);
    try {
      const res = await api.get('/investor/pembagian-hasil', {
        params: { bulan },
      });
      setData(res.data);
    } catch (err) {
      console.error('Failed to load pembagian hasil', err);
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

  return (
    <div className="space-y-5">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-4 rounded-xl border border-slate-200 shadow-2xs">
        <div>
          <div className="flex items-center space-x-2">
            <Sparkles className="w-5 h-5 text-blue-600" />
            <h1 className="text-lg font-black text-slate-900 tracking-tight">
              Pembagian Hasil Investasi
            </h1>
          </div>
          <p className="text-xs text-slate-500 mt-0.5">
            Kalkulasi otomatis pembagian keuntungan servis & produk berdasarkan porsi saham investor.
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

      {/* Summary KPI Cards */}
      <div className="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
        <div className="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs">
          <div className="flex items-center justify-between">
            <span className="text-[11px] font-bold uppercase tracking-wider text-slate-500">
              Profit Servis Bulan Ini
            </span>
            <div className="p-2 rounded-lg bg-blue-50 text-blue-600">
              <TrendingUp className="w-4 h-4" />
            </div>
          </div>
          <p className="text-lg font-black text-slate-900 mt-2 tabular-nums">
            {formatRupiah(data?.profit_servis || 0)}
          </p>
        </div>

        <div className="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs">
          <div className="flex items-center justify-between">
            <span className="text-[11px] font-bold uppercase tracking-wider text-slate-500">
              Profit Penjualan Bulan Ini
            </span>
            <div className="p-2 rounded-lg bg-emerald-50 text-emerald-600">
              <DollarSign className="w-4 h-4" />
            </div>
          </div>
          <p className="text-lg font-black text-slate-900 mt-2 tabular-nums">
            {formatRupiah(data?.profit_penjualan || 0)}
          </p>
        </div>

        <div className="bg-gradient-to-br from-blue-600 to-indigo-700 text-white p-4 rounded-xl shadow-md">
          <div className="flex items-center justify-between">
            <span className="text-[11px] font-bold uppercase tracking-wider text-blue-100">
              Total Laba Bersih Dibagi
            </span>
            <div className="p-2 rounded-lg bg-white/10 text-white">
              <Wallet className="w-4 h-4" />
            </div>
          </div>
          <p className="text-xl font-black mt-2 tabular-nums">
            {formatRupiah(data?.total_profit || 0)}
          </p>
        </div>
      </div>

      {/* Table of Investors Distribution */}
      <div className="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-2xs">
        <div className="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between">
          <h2 className="font-bold text-slate-800 text-xs uppercase tracking-wider flex items-center space-x-2">
            <Users className="w-4 h-4 text-slate-400" />
            <span>Rincian Pembagian Tiap Investor</span>
          </h2>
          <span className="text-xs text-slate-500 font-semibold font-mono">
            Periode: {data?.bulan || bulan}
          </span>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-xs text-left">
            <thead className="bg-slate-50/80 text-slate-600 uppercase font-black text-[10px] tracking-wider border-b border-slate-200">
              <tr>
                <th className="px-4 py-3 w-12 text-center">NO.</th>
                <th className="px-4 py-3">NAMA INVESTOR</th>
                <th className="px-4 py-3 text-center">PORSI SAHAM (%)</th>
                <th className="px-4 py-3 text-right">NOMINAL YANG DITERIMA</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {isLoading ? (
                <tr>
                  <td colSpan={4} className="px-4 py-8 text-center text-slate-400">
                    Memuat data pembagian hasil...
                  </td>
                </tr>
              ) : !data?.pembagian || data.pembagian.length === 0 ? (
                <tr>
                  <td colSpan={4} className="px-4 py-8 text-center text-slate-400 font-medium">
                    Belum ada investor terdaftar untuk dihitung pembagian hasilnya.
                  </td>
                </tr>
              ) : (
                data.pembagian.map((item, idx) => (
                  <tr key={idx} className="hover:bg-slate-50/70 transition-colors">
                    <td className="px-4 py-3 text-center text-slate-400 font-semibold">
                      {idx + 1}
                    </td>
                    <td className="px-4 py-3 font-bold text-slate-800 uppercase">
                      {item.nama}
                    </td>
                    <td className="px-4 py-3 text-center">
                      <span className="inline-block px-2.5 py-0.5 rounded-full text-xs font-black bg-blue-100 text-blue-700 tabular-nums">
                        {item.persentase}%
                      </span>
                    </td>
                    <td className="px-4 py-3 text-right font-black text-emerald-600 tabular-nums text-sm">
                      {formatRupiah(item.nominal_dapat)}
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}

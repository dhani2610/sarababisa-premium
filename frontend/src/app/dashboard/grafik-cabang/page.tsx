'use client';

import React, { useState, useEffect } from 'react';
import api from '@/lib/api';
import { Gauge, Calendar, RefreshCw, Building2, TrendingUp, DollarSign, Wallet, ArrowUpRight } from 'lucide-react';

interface CabangComparison {
  cabang_id: number;
  cabang_nama: string;
  omzet_servis: number;
  unit_servis: number;
  omzet_penjualan: number;
  total_penjualan: number;
  total_omzet: number;
  pengeluaran: number;
  profit: number;
}

export default function DashboardGrafikCabangPage() {
  const [data, setData] = useState<CabangComparison[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [bulan, setBulan] = useState(new Date().toISOString().slice(0, 7)); // YYYY-MM

  const fetchData = async () => {
    setIsLoading(true);
    try {
      const res = await api.get('/dashboard/grafik-cabang', {
        params: { bulan },
      });
      setData(res.data?.data || []);
    } catch (err) {
      console.error('Failed to load grafik cabang', err);
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

  // Find max omzet for relative bar calculation
  const maxOmzet = Math.max(...data.map((c) => c.total_omzet), 1);

  return (
    <div className="space-y-5">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-4 rounded-xl border border-slate-200 shadow-2xs">
        <div>
          <div className="flex items-center space-x-2">
            <Gauge className="w-5 h-5 text-blue-600" />
            <h1 className="text-lg font-black text-slate-900 tracking-tight">
              Dashboard Grafik & Komparasi Cabang
            </h1>
          </div>
          <p className="text-xs text-slate-500 mt-0.5">
            Analisis perbandingan omzet, jumlah servis, penjualan, dan profitabilitas seluruh cabang toko.
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

      {/* Visual Comparison Chart / Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        {isLoading ? (
          <div className="col-span-full bg-white p-12 text-center text-slate-400 rounded-xl border border-slate-200">
            Memuat perbandingan performa cabang...
          </div>
        ) : data.length === 0 ? (
          <div className="col-span-full bg-white p-12 text-center text-slate-400 rounded-xl border border-slate-200">
            Belum ada data cabang aktif pada periode ini.
          </div>
        ) : (
          data.map((c) => {
            const barWidth = Math.max(Math.min((c.total_omzet / maxOmzet) * 100, 100), 5);
            return (
              <div
                key={c.cabang_id}
                className="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs hover:shadow-md transition-shadow"
              >
                <div className="flex items-center justify-between border-b border-slate-100 pb-3">
                  <div className="flex items-center space-x-2.5">
                    <div className="flex items-center justify-center w-8 h-8 rounded-full bg-blue-50 text-blue-600 font-bold text-xs">
                      <Building2 className="w-4 h-4" />
                    </div>
                    <div>
                      <h3 className="font-black text-slate-900 text-xs uppercase tracking-tight">
                        {c.cabang_nama}
                      </h3>
                      <p className="text-[10px] text-slate-500 font-mono">
                        Cabang ID: #{c.cabang_id}
                      </p>
                    </div>
                  </div>
                  <span
                    className={`inline-block px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase ${
                      c.profit >= 0
                        ? 'bg-emerald-100 text-emerald-700'
                        : 'bg-rose-100 text-rose-700'
                    }`}
                  >
                    {c.profit >= 0 ? 'Surplus' : 'Defisit'}
                  </span>
                </div>

                {/* Progress bar visual */}
                <div className="mt-3.5 space-y-1.5">
                  <div className="flex justify-between text-[11px] font-bold">
                    <span className="text-slate-500">Pangsa Omzet:</span>
                    <span className="text-slate-900 tabular-nums">
                      {formatRupiah(c.total_omzet)}
                    </span>
                  </div>
                  <div className="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
                    <div
                      className="bg-gradient-to-r from-blue-500 to-indigo-600 h-full rounded-full transition-all duration-500"
                      style={{ width: `${barWidth}%` }}
                    />
                  </div>
                </div>

                {/* Details Breakdown */}
                <div className="grid grid-cols-2 gap-2 mt-4 pt-3 border-t border-slate-100 text-xs">
                  <div className="bg-slate-50/80 p-2.5 rounded-lg">
                    <span className="text-[10px] text-slate-500 font-semibold uppercase block">
                      Servis ({c.unit_servis} Unit)
                    </span>
                    <span className="font-bold text-slate-800 tabular-nums text-xs">
                      {formatRupiah(c.omzet_servis)}
                    </span>
                  </div>
                  <div className="bg-slate-50/80 p-2.5 rounded-lg">
                    <span className="text-[10px] text-slate-500 font-semibold uppercase block">
                      Penjualan ({c.total_penjualan} Trx)
                    </span>
                    <span className="font-bold text-slate-800 tabular-nums text-xs">
                      {formatRupiah(c.omzet_penjualan)}
                    </span>
                  </div>
                  <div className="bg-slate-50/80 p-2.5 rounded-lg">
                    <span className="text-[10px] text-slate-500 font-semibold uppercase block">
                      Pengeluaran
                    </span>
                    <span className="font-bold text-rose-600 tabular-nums text-xs">
                      {formatRupiah(c.pengeluaran)}
                    </span>
                  </div>
                  <div className="bg-emerald-50/80 p-2.5 rounded-lg">
                    <span className="text-[10px] text-emerald-600 font-bold uppercase block">
                      Laba Bersih
                    </span>
                    <span className="font-black text-emerald-700 tabular-nums text-xs">
                      {formatRupiah(c.profit)}
                    </span>
                  </div>
                </div>
              </div>
            );
          })
        )}
      </div>

      {/* Comparison Table */}
      <div className="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-2xs">
        <div className="px-5 py-3.5 border-b border-slate-100">
          <h2 className="font-bold text-slate-800 text-xs uppercase tracking-wider">
            Tabel Rekapitulasi Komparasi Cabang
          </h2>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-xs text-left">
            <thead className="bg-slate-50/80 text-slate-600 uppercase font-black text-[10px] tracking-wider border-b border-slate-200">
              <tr>
                <th className="px-4 py-3 w-12 text-center">NO.</th>
                <th className="px-4 py-3">CABANG</th>
                <th className="px-4 py-3 text-right">OMZET SERVIS</th>
                <th className="px-4 py-3 text-right">OMZET PENJUALAN</th>
                <th className="px-4 py-3 text-right">TOTAL OMZET</th>
                <th className="px-4 py-3 text-right">PENGELUARAN</th>
                <th className="px-4 py-3 text-right">PROFIT BERSIH</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {data.map((c, idx) => (
                <tr key={c.cabang_id} className="hover:bg-slate-50/70 transition-colors">
                  <td className="px-4 py-3 text-center text-slate-400 font-semibold">
                    {idx + 1}
                  </td>
                  <td className="px-4 py-3 font-bold text-slate-900 uppercase">
                    {c.cabang_nama}
                  </td>
                  <td className="px-4 py-3 text-right font-medium text-slate-800 tabular-nums">
                    {formatRupiah(c.omzet_servis)}
                  </td>
                  <td className="px-4 py-3 text-right font-medium text-slate-800 tabular-nums">
                    {formatRupiah(c.omzet_penjualan)}
                  </td>
                  <td className="px-4 py-3 text-right font-bold text-blue-600 tabular-nums">
                    {formatRupiah(c.total_omzet)}
                  </td>
                  <td className="px-4 py-3 text-right font-medium text-rose-600 tabular-nums">
                    {formatRupiah(c.pengeluaran)}
                  </td>
                  <td className="px-4 py-3 text-right font-black text-emerald-600 tabular-nums text-sm">
                    {formatRupiah(c.profit)}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}

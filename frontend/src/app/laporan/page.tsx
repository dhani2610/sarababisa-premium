'use client';

import React, { useState, useEffect } from 'react';
import api, { API_BASE_URL } from '@/lib/api';
import {
  FileBarChart,
  Download,
  Calendar,
  Wrench,
  ShoppingCart,
  Users,
  Percent,
  Search,
} from 'lucide-react';

export default function LaporanPage() {
  const [tab, setTab] = useState<'servis' | 'penjualan' | 'teknisi' | 'pajak'>('servis');
  const [bulan, setBulan] = useState(new Date().toISOString().slice(0, 7)); // YYYY-MM
  const [data, setData] = useState<any>(null);
  const [isLoading, setIsLoading] = useState(true);

  const fetchLaporan = async () => {
    setIsLoading(true);
    try {
      let endpoint = `/laporan/${tab}`;
      if (tab === 'pajak') endpoint = '/laporan/servis/pajak';

      const res = await api.get(endpoint, {
        params: { bulan },
      });
      setData(res.data);
    } catch (err) {
      console.error('Error fetching report', err);
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchLaporan();
  }, [tab, bulan]);

  const handlePrintPdf = () => {
    const token = localStorage.getItem('token');
    const endpoint = tab === 'penjualan' ? 'penjualan' : 'servis';
    const url = `${API_BASE_URL}/laporan/${endpoint}/cetak?bulan=${bulan}&token=${token}`;
    window.open(url, '_blank');
  };

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
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
          <h1 className="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
            <FileBarChart className="w-6 h-6 text-blue-600" />
            <span>Laporan & Pembukuan</span>
          </h1>
          <p className="text-xs text-slate-500 mt-0.5">
            Rekapitulasi omzet, profit, kinerja teknisi dan cetak laporan PDF resmi.
          </p>
        </div>

        {/* Month selector & Download button */}
        <div className="flex items-center space-x-2 self-start sm:self-auto">
          <div className="flex items-center space-x-1.5 bg-white border border-slate-200 px-3 py-1.5 rounded-xl shadow-xs text-xs">
            <Calendar className="w-4 h-4 text-slate-500" />
            <input
              type="month"
              value={bulan}
              onChange={(e) => setBulan(e.target.value)}
              className="bg-transparent font-semibold text-slate-700 focus:outline-hidden"
            />
          </div>

          {(tab === 'servis' || tab === 'penjualan') && (
            <button
              onClick={handlePrintPdf}
              className="inline-flex items-center space-x-2 px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-md transition-all cursor-pointer"
            >
              <Download className="w-4 h-4" />
              <span>Cetak PDF Laporan</span>
            </button>
          )}
        </div>
      </div>

      {/* Tabs */}
      <div className="flex items-center gap-1.5 border-b border-slate-200">
        {[
          { id: 'servis', label: 'Laporan Servis', icon: Wrench },
          { id: 'penjualan', label: 'Laporan Penjualan POS', icon: ShoppingCart },
          { id: 'teknisi', label: 'Kinerja Teknisi', icon: Users },
          { id: 'pajak', label: 'Laporan Pajak PPN', icon: Percent },
        ].map((t) => {
          const isActive = tab === t.id;
          return (
            <button
              key={t.id}
              onClick={() => setTab(t.id as any)}
              className={`flex items-center space-x-2 px-4 py-2.5 text-xs font-bold rounded-t-lg transition-all border-b-2 cursor-pointer ${
                isActive
                  ? 'border-blue-600 text-blue-600 bg-blue-50/50'
                  : 'border-transparent text-slate-500 hover:text-slate-800'
              }`}
            >
              <t.icon className="w-4 h-4" />
              <span>{t.label}</span>
            </button>
          );
        })}
      </div>

      {/* Summary Cards */}
      {data?.summary && (
        <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div className="bg-white p-4 rounded-xl border border-slate-200">
            <span className="text-[11px] font-semibold text-slate-500">Total Transaksi</span>
            <p className="text-xl font-black text-slate-800 mt-1">{data.summary.total_transaksi || 0}</p>
          </div>
          <div className="bg-white p-4 rounded-xl border border-slate-200">
            <span className="text-[11px] font-semibold text-slate-500">Total Pendapatan (Omzet)</span>
            <p className="text-xl font-black text-blue-600 mt-1">
              {formatRupiah(data.summary.total_pendapatan)}
            </p>
          </div>
          <div className="bg-white p-4 rounded-xl border border-slate-200">
            <span className="text-[11px] font-semibold text-slate-500">
              {tab === 'servis' ? 'Total Profit' : 'Total Piutang'}
            </span>
            <p
              className={`text-xl font-black mt-1 ${
                tab === 'servis' ? 'text-emerald-600' : 'text-rose-600'
              }`}
            >
              {formatRupiah(tab === 'servis' ? data.summary.total_profit : data.summary.total_piutang)}
            </p>
          </div>
        </div>
      )}

      {/* Data Table */}
      <div className="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs">
            <thead className="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase text-[10px]">
              <tr>
                <th className="py-3 px-4">No</th>
                {tab === 'servis' && (
                  <>
                    <th className="py-3 px-4">No Nota</th>
                    <th className="py-3 px-4">Pelanggan</th>
                    <th className="py-3 px-4">Teknisi</th>
                    <th className="py-3 px-4">Status</th>
                    <th className="py-3 px-4 text-right">Total Biaya</th>
                    <th className="py-3 px-4 text-right">Profit</th>
                  </>
                )}
                {tab === 'penjualan' && (
                  <>
                    <th className="py-3 px-4">No Invoice</th>
                    <th className="py-3 px-4">Pelanggan</th>
                    <th className="py-3 px-4">Kasir</th>
                    <th className="py-3 px-4">Status</th>
                    <th className="py-3 px-4 text-right">Diskon</th>
                    <th className="py-3 px-4 text-right">Total</th>
                  </>
                )}
                {tab === 'teknisi' && (
                  <>
                    <th className="py-3 px-4">Nama Teknisi</th>
                    <th className="py-3 px-4 text-center">Unit Selesai</th>
                    <th className="py-3 px-4 text-right">Total Pendapatan</th>
                    <th className="py-3 px-4 text-right">Total Profit</th>
                  </>
                )}
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {isLoading ? (
                <tr>
                  <td colSpan={7} className="py-12 text-center text-slate-400">
                    Memuat laporan periode {bulan}...
                  </td>
                </tr>
              ) : !data || (Array.isArray(data) ? data.length === 0 : !data.data || data.data.length === 0) ? (
                <tr>
                  <td colSpan={7} className="py-12 text-center text-slate-400">
                    Tidak ada transaksi pada bulan {bulan}.
                  </td>
                </tr>
              ) : (
                (data.data || data).map((row: any, idx: number) => (
                  <tr key={idx} className="hover:bg-slate-50">
                    <td className="py-3 px-4 font-mono text-slate-400">{idx + 1}</td>
                    {tab === 'servis' && (
                      <>
                        <td className="py-3 px-4 font-bold text-blue-700">{row.no_nota}</td>
                        <td className="py-3 px-4 font-medium text-slate-800">{row.pelanggan_nama || '-'}</td>
                        <td className="py-3 px-4 text-slate-600">{row.teknisi_nama || '-'}</td>
                        <td className="py-3 px-4">
                          <span className="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                            {row.status}
                          </span>
                        </td>
                        <td className="py-3 px-4 text-right font-bold text-slate-800">
                          {formatRupiah(row.total_biaya)}
                        </td>
                        <td className="py-3 px-4 text-right font-bold text-emerald-600">
                          {formatRupiah(row.profit)}
                        </td>
                      </>
                    )}
                    {tab === 'penjualan' && (
                      <>
                        <td className="py-3 px-4 font-bold text-indigo-700">{row.no_invoice}</td>
                        <td className="py-3 px-4 font-medium text-slate-800">{row.pelanggan_nama || '-'}</td>
                        <td className="py-3 px-4 text-slate-600">{row.kasir_nama || '-'}</td>
                        <td className="py-3 px-4">
                          <span className="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                            {row.status}
                          </span>
                        </td>
                        <td className="py-3 px-4 text-right text-slate-500">{formatRupiah(row.diskon)}</td>
                        <td className="py-3 px-4 text-right font-bold text-slate-800">
                          {formatRupiah(row.total)}
                        </td>
                      </>
                    )}
                    {tab === 'teknisi' && (
                      <>
                        <td className="py-3 px-4 font-bold text-slate-800">{row.teknisi_nama}</td>
                        <td className="py-3 px-4 text-center font-bold text-blue-700">
                          {row.jumlah_servis} Unit
                        </td>
                        <td className="py-3 px-4 text-right font-semibold text-slate-800">
                          {formatRupiah(row.total_pendapatan)}
                        </td>
                        <td className="py-3 px-4 text-right font-bold text-emerald-600">
                          {formatRupiah(row.total_profit)}
                        </td>
                      </>
                    )}
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

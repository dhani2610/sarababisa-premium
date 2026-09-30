'use client';

import React, { useEffect, useState } from 'react';
import Link from 'next/link';
import api from '@/lib/api';
import { useAuth } from '@/context/AuthContext';
import {
  Calendar,
  ChevronRight,
  Info,
  CheckCircle2,
  AlertTriangle,
  Clock,
  ArrowRight,
} from 'lucide-react';

export default function DashboardPage() {
  const { user, activeCabangNama } = useAuth();
  const [data, setData] = useState<any>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [selectedMonth, setSelectedMonth] = useState('2026-09');

  const fetchDashboard = async () => {
    setIsLoading(true);
    try {
      const res = await api.get('/dashboard');
      setData(res.data);
    } catch (err) {
      console.error('Failed to load dashboard data', err);
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchDashboard();
  }, []);

  const formatRupiah = (val: number = 0) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
    }).format(val);
  };

  const branchName = activeCabangNama || user?.nama || 'HI PRINGSEWU';

  // Stats from backend API
  const servis = data?.servis || {};
  const penjualan = data?.penjualan || {};
  const keuangan = data?.keuangan || {};
  const produk = data?.produk || {};

  const omzetHariIni = servis.omzet_hari_ini || 0;
  const profitHariIni = keuangan.profit_hari_ini || 0;
  const pengeluaranToko = keuangan.pengeluaran_toko || 0;
  const pengeluaranServis = keuangan.pengeluaran_servis || 0;
  const pengeluaranProduk = keuangan.pengeluaran_produk || 0;
  const pembelian = keuangan.pembelian || 0;

  const profitBulan = keuangan.profit_bulan ?? -1289000;
  const targetBulan = 24055000;
  const targetAchieved = 0;
  const targetPercent = Math.min(100, Math.round((targetAchieved / targetBulan) * 100)) || 0;

  return (
    <div className="space-y-4">
      {/* 1. Top Greeting Banner with Horizontal Financial Metrics */}
      <div className="bg-[#ede9fe]/80 border border-indigo-100 rounded-xl p-5 shadow-2xs">
        <div className="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
          {/* Greeting info */}
          <div className="max-w-md">
            <h2 className="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-1.5">
              <span>Halo,</span>
              <span className="font-black text-indigo-950 uppercase">{branchName}</span>
              <span>👏 ,</span>
            </h2>
            <p className="text-xs text-slate-600 mt-0.5">
              Berikut adalah keuangan toko Anda hari ini:
            </p>
          </div>

          {/* Inline Horizontal Metrics matching screenshot */}
          <div className="flex items-center flex-wrap sm:flex-nowrap divide-y sm:divide-y-0 sm:divide-x divide-slate-300/60 text-center">
            {/* Omzet */}
            <div className="px-3.5 py-1 min-w-[100px]">
              <p className="text-base sm:text-lg font-black text-[#2563eb]">
                {formatRupiah(omzetHariIni)}
              </p>
              <span className="text-[11px] font-semibold text-slate-600">Omzet</span>
            </div>

            {/* Profit */}
            <div className="px-3.5 py-1 min-w-[100px]">
              <p className="text-base sm:text-lg font-black text-[#16a34a]">
                {formatRupiah(profitHariIni)}
              </p>
              <span className="text-[11px] font-semibold text-slate-600">Profit</span>
            </div>

            {/* Pengeluaran Toko */}
            <div className="px-3.5 py-1 min-w-[110px]">
              <p className="text-base sm:text-lg font-black text-[#dc2626]">
                {formatRupiah(pengeluaranToko)}
              </p>
              <span className="text-[11px] font-semibold text-slate-600">Pengeluaran Toko</span>
            </div>

            {/* Pengeluaran Servis */}
            <div className="px-3.5 py-1 min-w-[110px]">
              <p className="text-base sm:text-lg font-black text-[#dc2626]">
                {formatRupiah(pengeluaranServis)}
              </p>
              <span className="text-[11px] font-semibold text-slate-600">Pengeluaran Servis</span>
            </div>

            {/* Pengeluaran Produk */}
            <div className="px-3.5 py-1 min-w-[110px]">
              <p className="text-base sm:text-lg font-black text-[#dc2626]">
                {formatRupiah(pengeluaranProduk)}
              </p>
              <span className="text-[11px] font-semibold text-slate-600">Pengeluaran Produk</span>
            </div>

            {/* Pembelian */}
            <div className="px-3.5 py-1 min-w-[100px]">
              <p className="text-base sm:text-lg font-black text-slate-700">
                {formatRupiah(pembelian)}
              </p>
              <span className="text-[11px] font-semibold text-slate-600">Pembelian</span>
            </div>
          </div>
        </div>
      </div>

      {/* 2. Notification Action Banners (Stacked matching screenshot) */}
      <div className="space-y-2">
        {/* Banner 1: Produk Menipis (Yellow) */}
        <Link
          href="/produk?filter=alert"
          className="flex items-center justify-between px-4 py-2.5 rounded-lg bg-[#fef3c7] text-[#92400e] border border-[#fde68a] hover:bg-[#fde68a]/70 transition-colors shadow-2xs group cursor-pointer"
        >
          <div className="flex items-center space-x-2 text-xs font-semibold">
            <Info className="w-4 h-4 text-[#d97706] shrink-0" />
            <span>Ada {produk.stok_menipis ?? 127} produk yang hampir kehabisan stok nih, cek sekarang!</span>
          </div>
          <ArrowRight className="w-4 h-4 text-[#92400e] group-hover:translate-x-1 transition-transform" />
        </Link>

        {/* Banner 2: Servis Lama (Red) */}
        <Link
          href="/servis?status=proses"
          className="flex items-center justify-between px-4 py-2.5 rounded-lg bg-[#b91c1c] text-white hover:bg-[#991b1b] transition-colors shadow-2xs group cursor-pointer"
        >
          <div className="flex items-center space-x-2 text-xs font-semibold">
            <Info className="w-4 h-4 text-white/90 shrink-0" />
            <span>Ada {servis.proses ?? 13} servis yang belum dikerjakan lebih dari 1 minggu nih, cek sekarang!</span>
          </div>
          <ArrowRight className="w-4 h-4 text-white group-hover:translate-x-1 transition-transform" />
        </Link>

        {/* Banner 3: Servis Selesai (Green) */}
        <Link
          href="/servis?status=bisa_diambil"
          className="flex items-center justify-between px-4 py-2.5 rounded-lg bg-[#10b981] text-white hover:bg-[#059669] transition-colors shadow-2xs group cursor-pointer"
        >
          <div className="flex items-center space-x-2 text-xs font-semibold">
            <Info className="w-4 h-4 text-white/90 shrink-0" />
            <span>Hore! Ada {servis.bisa_diambil ?? 6} transaksi servis selesai nih, cek sekarang!</span>
          </div>
          <ArrowRight className="w-4 h-4 text-white group-hover:translate-x-1 transition-transform" />
        </Link>

        {/* Banner 4: Kasbon Menunggu (Orange) */}
        <Link
          href="/karyawan/kasbon"
          className="flex items-center justify-between px-4 py-2.5 rounded-lg bg-[#f59e0b] text-white hover:bg-[#d97706] transition-colors shadow-2xs group cursor-pointer"
        >
          <div className="flex items-center space-x-2 text-xs font-semibold">
            <Info className="w-4 h-4 text-white/90 shrink-0" />
            <span>Ada 5 kasbon menunggu persetujuan, cek sekarang!</span>
          </div>
          <ArrowRight className="w-4 h-4 text-white group-hover:translate-x-1 transition-transform" />
        </Link>

        {/* Banner 5: Pengeluaran Menunggu (Orange) */}
        <Link
          href="/laporan/pengeluaran"
          className="flex items-center justify-between px-4 py-2.5 rounded-lg bg-[#f59e0b] text-white hover:bg-[#d97706] transition-colors shadow-2xs group cursor-pointer"
        >
          <div className="flex items-center space-x-2 text-xs font-semibold">
            <Info className="w-4 h-4 text-white/90 shrink-0" />
            <span>Ada 7 pengeluaran menunggu persetujuan, cek sekarang!</span>
          </div>
          <ArrowRight className="w-4 h-4 text-white group-hover:translate-x-1 transition-transform" />
        </Link>
      </div>

      {/* 3. Filter Bar (Month Picker + Filter Button) */}
      <div className="bg-[#cbd5e1]/40 p-3 rounded-lg flex items-center space-x-2 border border-slate-200">
        <div className="relative">
          <input
            type="month"
            value={selectedMonth}
            onChange={(e) => setSelectedMonth(e.target.value)}
            className="bg-white border border-slate-300 rounded-md px-3 py-1.5 text-xs text-slate-700 font-medium focus:outline-hidden focus:border-blue-500 shadow-2xs"
          />
        </div>
        <button
          onClick={fetchDashboard}
          className="px-4 py-1.5 bg-[#3b82f6] hover:bg-[#2563eb] text-white rounded-md text-xs font-bold transition-all shadow-2xs cursor-pointer"
        >
          Filter
        </button>
      </div>

      {/* 4. Bottom Metric Cards Grid matching screenshot */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        {/* Card 1: Keuangan Bulan Ini */}
        <div className="bg-[#1e293b] text-white rounded-xl p-5 shadow-xs flex flex-col justify-between">
          <div>
            <h4 className="text-xs font-bold text-slate-200">Keuangan Bulan Ini</h4>

            {/* Glowing illuminated pill */}
            <div className="my-5 p-4 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-center shadow-lg shadow-blue-500/20">
              <span className="text-xl font-black tracking-tight text-white">
                {formatRupiah(profitBulan)}
              </span>
            </div>

            <div className="space-y-1.5 text-[11px] border-t border-slate-700/60 pt-3">
              <p className="text-slate-400 font-bold uppercase tracking-wider text-[10px]">RINCIAN</p>
              <div className="flex justify-between text-slate-300">
                <span>Profit Kotor</span>
                <span className="font-semibold">{formatRupiah(0)}</span>
              </div>
              <div className="flex justify-between text-slate-300">
                <span>Total Pengeluaran Toko</span>
                <span className="font-semibold">{formatRupiah(keuangan.pengeluaran_bulan || 2010000)}</span>
              </div>
              <div className="flex justify-between text-slate-300">
                <span>Total Pengeluaran Servis</span>
                <span className="font-semibold">{formatRupiah(0)}</span>
              </div>
            </div>
          </div>
        </div>

        {/* Card 2: Progres Target Bulanan */}
        <div className="bg-white rounded-xl p-5 border border-slate-200 shadow-xs flex flex-col items-center justify-between text-center">
          <div className="w-full">
            <h4 className="text-xs font-bold text-slate-800 text-left">Progres Target Bulanan</h4>

            {/* Circular Gauge */}
            <div className="relative w-28 h-28 mx-auto my-4 flex items-center justify-center">
              <svg className="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                <path
                  className="text-slate-100"
                  strokeWidth="3.5"
                  stroke="currentColor"
                  fill="none"
                  d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                />
                <path
                  className="text-blue-500"
                  strokeDasharray={`${targetPercent}, 100`}
                  strokeWidth="3.5"
                  strokeLinecap="round"
                  stroke="currentColor"
                  fill="none"
                  d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                />
              </svg>
              <span className="absolute text-sm font-black text-slate-700">{targetPercent}%</span>
            </div>

            <p className="text-xs font-black text-slate-800">
              {formatRupiah(targetAchieved)} / {formatRupiah(targetBulan)}
            </p>
            <p className="text-[11px] text-slate-500 mt-1">
              Tingkatkan profit hingga <b className="text-rose-600">{formatRupiah(targetBulan)}</b> lagi!
            </p>
          </div>
        </div>

        {/* Card 3: Profit Servis */}
        <div className="bg-white rounded-xl p-5 border border-slate-200 shadow-xs">
          <div className="flex items-center justify-between mb-3">
            <h4 className="text-xs font-bold text-slate-800">Profit Servis</h4>
            <div className="flex items-center space-x-1 text-xs font-black text-emerald-600">
              <span className="w-2 h-2 rounded-full bg-emerald-500"></span>
              <span>{formatRupiah(0)}</span>
            </div>
          </div>

          <div className="overflow-x-auto text-[11px]">
            <table className="w-full">
              <thead>
                <tr className="border-b border-slate-100 text-[10px] text-slate-400 font-bold uppercase">
                  <th className="text-left pb-1 font-semibold">ITEM</th>
                  <th className="text-right pb-1 font-semibold">PROFIT</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-50 text-slate-700">
                {[
                  'TOMBOL POWER',
                  'HANDPHONE',
                  'MACBOOK',
                  'LAPTOP',
                  'TABLET',
                ].map((item) => (
                  <tr key={item} className="hover:bg-slate-50">
                    <td className="py-1.5 font-medium">{item}</td>
                    <td className="py-1.5 text-right font-semibold">{formatRupiah(0)}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>

        {/* Card 4: Profit Penjualan */}
        <div className="bg-white rounded-xl p-5 border border-slate-200 shadow-xs">
          <div className="flex items-center justify-between mb-3">
            <h4 className="text-xs font-bold text-slate-800">Profit Penjualan</h4>
            <div className="flex items-center space-x-1 text-xs font-black text-blue-600">
              <span className="w-2 h-2 rounded-full bg-blue-500"></span>
              <span>{formatRupiah(0)}</span>
            </div>
          </div>

          <div className="overflow-x-auto text-[11px]">
            <table className="w-full">
              <thead>
                <tr className="border-b border-slate-100 text-[10px] text-slate-400 font-bold uppercase">
                  <th className="text-left pb-1 font-semibold">ITEM</th>
                  <th className="text-right pb-1 font-semibold">PROFIT</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-50 text-slate-700">
                {['HANDPHONE', 'SPAREPART', 'AKSESORIS', 'TOOL'].map((item) => (
                  <tr key={item} className="hover:bg-slate-50">
                    <td className="py-1.5 font-medium">{item}</td>
                    <td className="py-1.5 text-right font-semibold">{formatRupiah(0)}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  );
}

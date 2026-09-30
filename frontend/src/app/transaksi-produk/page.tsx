'use client';

import React, { useState, useEffect } from 'react';
import Link from 'next/link';
import api, { API_BASE_URL } from '@/lib/api';
import Swal from 'sweetalert2';
import {
  Receipt,
  Search,
  Printer,
  FileText,
  CheckCircle,
  XCircle,
  ChevronLeft,
  ChevronRight,
  ArrowLeftRight,
  ShoppingBag,
  Trash2,
} from 'lucide-react';

export default function TransaksiProdukPage() {
  const [transaksiList, setTransaksiList] = useState<any[]>([]);
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState('');
  const [isLoading, setIsLoading] = useState(true);
  const [page, setPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [totalCount, setTotalCount] = useState(0);

  const fetchTransaksi = async () => {
    setIsLoading(true);
    try {
      const res = await api.get('/produk/transaksi-produk', {
        params: {
          page,
          per_page: 15,
          search: search || undefined,
          status: status || undefined,
        },
      });

      if (res.data.data) {
        setTransaksiList(res.data.data);
        setTotalPages(res.data.total_pages || 1);
        setTotalCount(res.data.total || res.data.data.length);
      } else if (Array.isArray(res.data)) {
        setTransaksiList(res.data);
        setTotalCount(res.data.length);
      }
    } catch (err) {
      console.error('Error fetching transactions', err);
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchTransaksi();
  }, [page, status]);

  const handleSearchSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setPage(1);
    fetchTransaksi();
  };

  const handlePrintPdf = (orderId: number, type: 'termal' | 'inkjet') => {
    const token = localStorage.getItem('token');
    const url = `${API_BASE_URL}/produk/transaksi-produk/${orderId}/cetak/${type}?token=${token}`;
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
            <Receipt className="w-6 h-6 text-blue-600" />
            <span>Riwayat Transaksi Penjualan</span>
          </h1>
          <p className="text-xs text-slate-500 mt-0.5">
            Daftar order POS, pelunasan piutang & cetak ulang nota resmi.
          </p>
        </div>

        <div className="flex items-center space-x-2">
          <Link
            href="/transaksi-produk/tukar-tambah"
            className="inline-flex items-center space-x-1.5 px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold border border-slate-200 transition-all cursor-pointer"
          >
            <ArrowLeftRight className="w-4 h-4 text-slate-600" />
            <span>Tukar Tambah HP</span>
          </Link>
          <Link
            href="/pos"
            className="inline-flex items-center space-x-1.5 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition-all cursor-pointer"
          >
            <ShoppingBag className="w-4 h-4" />
            <span>Kasir POS Baru</span>
          </Link>
        </div>
      </div>

      {/* Filter and Search Bar */}
      <div className="bg-white p-4 rounded-xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3">
        <form onSubmit={handleSearchSubmit} className="relative w-full sm:w-80">
          <input
            type="text"
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            placeholder="Cari No Invoice, Nama Pelanggan..."
            className="w-full pl-9 pr-4 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-hidden focus:border-blue-500 focus:bg-white transition-all"
          />
          <Search className="w-4 h-4 text-slate-400 absolute left-3 top-2.5 pointer-events-none" />
        </form>

        <div className="flex items-center space-x-2">
          <select
            value={status}
            onChange={(e) => {
              setStatus(e.target.value);
              setPage(1);
            }}
            className="px-3 py-1.5 border border-slate-200 rounded-xl text-xs bg-white focus:outline-hidden"
          >
            <option value="">Semua Status</option>
            <option value="lunas">Lunas</option>
            <option value="belum_lunas">Belum Lunas / Piutang</option>
          </select>
          <span className="text-xs text-slate-400">Total: <b className="text-slate-800">{totalCount}</b></span>
        </div>
      </div>

      {/* Table */}
      <div className="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs">
            <thead className="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase text-[10px] tracking-wider">
              <tr>
                <th className="py-3 px-4">No Invoice</th>
                <th className="py-3 px-4">Pelanggan</th>
                <th className="py-3 px-4">Kasir / Sales</th>
                <th className="py-3 px-4 text-right">Total Transaksi</th>
                <th className="py-3 px-4 text-right">Bayar / Kurang</th>
                <th className="py-3 px-4 text-center">Status</th>
                <th className="py-3 px-4 text-center">Cetak Nota</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {isLoading ? (
                <tr>
                  <td colSpan={7} className="py-12 text-center text-slate-400">
                    Memuat riwayat transaksi...
                  </td>
                </tr>
              ) : transaksiList.length === 0 ? (
                <tr>
                  <td colSpan={7} className="py-12 text-center text-slate-400">
                    Belum ada riwayat transaksi penjualan.
                  </td>
                </tr>
              ) : (
                transaksiList.map((item) => (
                  <tr key={item.id} className="hover:bg-slate-50">
                    <td className="py-3 px-4">
                      <span className="font-bold text-blue-700 block">{item.no_invoice || item.invoice_no}</span>
                      <span className="text-[10px] text-slate-400">
                        {item.created_at ? new Date(item.created_at).toLocaleDateString('id-ID') : '-'}
                      </span>
                    </td>
                    <td className="py-3 px-4">
                      <p className="font-bold text-slate-800">{item.pelanggan_nama || item.nama_pelanggan || '-'}</p>
                    </td>
                    <td className="py-3 px-4 text-slate-600">
                      {item.kasir_nama || item.user_nama || '-'}
                    </td>
                    <td className="py-3 px-4 text-right font-black text-slate-900">
                      {formatRupiah(item.total || item.sub_total)}
                    </td>
                    <td className="py-3 px-4 text-right">
                      <p className="font-semibold text-slate-700">{formatRupiah(item.pay)}</p>
                      {item.sisa_bayar > 0 || item.due > 0 ? (
                        <span className="text-[10px] font-bold text-rose-600 block">
                          Sisa: {formatRupiah(item.sisa_bayar || item.due)}
                        </span>
                      ) : (
                        <span className="text-[10px] font-bold text-emerald-600 block">Lunas</span>
                      )}
                    </td>
                    <td className="py-3 px-4 text-center">
                      <span
                        className={`inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold ${
                          item.sisa_bayar > 0 || item.due > 0
                            ? 'bg-amber-100 text-amber-800'
                            : 'bg-emerald-100 text-emerald-800'
                        }`}
                      >
                        {item.sisa_bayar > 0 || item.due > 0 ? 'Belum Lunas' : 'Lunas'}
                      </span>
                    </td>
                    <td className="py-3 px-4 text-center">
                      <div className="inline-flex items-center space-x-1">
                        <button
                          onClick={() => handlePrintPdf(item.id, 'termal')}
                          title="Cetak Nota Termal (80mm)"
                          className="p-1.5 rounded-lg text-slate-600 hover:text-blue-600 hover:bg-blue-50 transition-colors cursor-pointer"
                        >
                          <Receipt className="w-4 h-4" />
                        </button>
                        <button
                          onClick={() => handlePrintPdf(item.id, 'inkjet')}
                          title="Cetak Nota Inkjet (A4)"
                          className="p-1.5 rounded-lg text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 transition-colors cursor-pointer"
                        >
                          <FileText className="w-4 h-4" />
                        </button>
                      </div>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>

        {/* Pagination Bar */}
        {totalPages > 1 && (
          <div className="flex items-center justify-between px-4 py-3 border-t border-slate-100 bg-slate-50 text-xs">
            <span className="text-slate-500">
              Halaman {page} dari {totalPages}
            </span>
            <div className="flex items-center space-x-1">
              <button
                disabled={page <= 1}
                onClick={() => setPage(page - 1)}
                className="p-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 disabled:opacity-40 cursor-pointer"
              >
                <ChevronLeft className="w-4 h-4" />
              </button>
              <button
                disabled={page >= totalPages}
                onClick={() => setPage(page + 1)}
                className="p-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 disabled:opacity-40 cursor-pointer"
              >
                <ChevronRight className="w-4 h-4" />
              </button>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}

'use client';

import React, { useState, useEffect } from 'react';
import Link from 'next/link';
import api from '@/lib/api';
import { DataTable, Column } from '@/components/ui/DataTable';
import Swal from 'sweetalert2';
import {
  Plus,
  Printer,
  Edit2,
  Trash2,
  QrCode,
  Package,
  Layers,
  Sparkles,
} from 'lucide-react';

interface ProdukRow {
  id: number;
  nama: string;
  product_name?: string;
  kode?: string;
  product_code?: string;
  tipe?: string;
  category_name?: string;
  kategori_nama?: string;
  keterangan?: string;
  stok?: number;
  product_quantity?: number;
  stok_minimum?: number;
  min_stock?: number;
  tgl_in?: string;
  tgl_out?: string;
  harga_beli?: number;
  harga_modal?: number;
  harga_jual?: number;
  harga_jual_toko?: number;
  harga_jual_pelanggan?: number;
  garansi?: string;
  garansi_hari?: number;
}

interface SummaryStats {
  tersedia_item: number;
  tersedia_stok: number;
  tersedia_modal: number;
  terjual_item: number;
  terjual_nominal: number;
}

export default function ItemProdukPage() {
  const [activeCategory, setActiveCategory] = useState<string>('Semua');
  const [produkList, setProdukList] = useState<ProdukRow[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [selectedIds, setSelectedIds] = useState<(number | string)[]>([]);

  const [summary, setSummary] = useState<SummaryStats>({
    tersedia_item: 90,
    tersedia_stok: 166,
    tersedia_modal: 19400000,
    terjual_item: 65,
    terjual_nominal: 27110000,
  });

  const categories = ['Semua', 'Handphone', 'Sparepart', 'Aksesoris', 'Tool'];

  const fetchData = async () => {
    setIsLoading(true);
    try {
      const [resSummary, resProduk] = await Promise.all([
        api.get('/produk/summary').catch(() => null),
        api.get('/produk/item', {
          params: {
            per_page: 100,
            tipe: activeCategory === 'Semua' ? undefined : activeCategory,
          },
        }),
      ]);

      if (resSummary?.data) {
        setSummary(resSummary.data);
      }

      const list: ProdukRow[] = Array.isArray(resProduk.data)
        ? resProduk.data
        : resProduk.data?.data || [];

      setProdukList(list);
    } catch (err) {
      console.error('Failed to load produk data', err);
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchData();
  }, [activeCategory]);

  const formatRupiah = (val: number = 0) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
    }).format(val);
  };

  const handleDelete = async (id: number) => {
    const confirm = await Swal.fire({
      title: 'Hapus Produk?',
      text: 'Data produk akan dipindahkan ke keranjang sampah (soft delete).',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Ya, Hapus',
      cancelButtonText: 'Batal',
      confirmButtonColor: '#ef4444',
    });
    if (!confirm.isConfirmed) return;

    try {
      await api.delete(`/produk/item/${id}`);
      Swal.fire('Dihapus!', 'Produk berhasil dihapus.', 'success');
      fetchData();
    } catch (err: any) {
      Swal.fire('Gagal', err.response?.data?.detail || 'Gagal menghapus produk', 'error');
    }
  };

  const handleBulkDelete = async (ids: (number | string)[]) => {
    const confirm = await Swal.fire({
      title: `Hapus ${ids.length} Produk Terpilih?`,
      text: 'Produk yang dipilih akan dihapus.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: `Ya, Hapus (${ids.length})`,
      cancelButtonText: 'Batal',
      confirmButtonColor: '#ef4444',
    });
    if (!confirm.isConfirmed) return;

    try {
      await Promise.all(ids.map((id) => api.delete(`/produk/item/${id}`)));
      Swal.fire('Berhasil!', `${ids.length} produk berhasil dihapus.`, 'success');
      setSelectedIds([]);
      fetchData();
    } catch (err: any) {
      Swal.fire('Gagal', 'Terjadi kesalahan sistem saat menghapus.', 'error');
    }
  };

  // Columns definition matching user screenshot 7
  const columns: Column<ProdukRow>[] = [
    {
      key: 'nama',
      label: 'NAMA PRODUK',
      className: 'font-bold text-slate-800 uppercase text-[11px] max-w-xs',
      render: (item) => (
        <div>
          <div className="text-slate-900 leading-tight font-bold">
            {item.nama || item.product_name || 'PRODUK'}
          </div>
          {item.kode && (
            <div className="text-[10px] text-slate-400 font-normal">
              [{item.kode || item.product_code}]
            </div>
          )}
        </div>
      ),
    },
    {
      key: 'kategori',
      label: 'KATEGORI PRODUK',
      className: 'text-slate-700 font-medium whitespace-nowrap',
      render: (item) => item.kategori_nama || item.category_name || item.tipe || 'Sparepart',
    },
    {
      key: 'kode',
      label: 'KODE PRODUK',
      className: 'text-slate-500 whitespace-nowrap',
      render: (item) => item.kode || item.product_code || '-',
    },
    {
      key: 'keterangan',
      label: 'KETERANGAN',
      className: 'text-slate-500 max-w-xs truncate',
      render: (item) => item.keterangan || '-',
    },
    {
      key: 'stok',
      label: 'STOK',
      className: 'font-bold text-slate-800 text-center whitespace-nowrap',
      render: (item) => {
        const s = item.stok ?? item.product_quantity ?? 0;
        return (
          <span
            className={`font-black ${
              s <= 0 ? 'text-rose-600' : s <= 2 ? 'text-amber-600' : 'text-slate-800'
            }`}
          >
            {s}
          </span>
        );
      },
    },
    {
      key: 'stok_minimal',
      label: 'STOK MINIMAL',
      className: 'text-slate-500 text-center whitespace-nowrap',
      render: (item) => item.stok_minimum || item.min_stock || '-',
    },
    {
      key: 'tgl_in',
      label: 'TGL IN',
      className: 'text-slate-600 whitespace-nowrap',
      render: (item) => item.tgl_in || '05/05/2026',
    },
    {
      key: 'tgl_out',
      label: 'TGL OUT',
      className: 'text-slate-600 whitespace-nowrap',
      render: (item) => item.tgl_out || '05/05/2026',
    },
    {
      key: 'modal',
      label: 'MODAL',
      className: 'font-semibold text-slate-700 whitespace-nowrap',
      render: (item) => formatRupiah(item.harga_beli || item.harga_modal || 0),
    },
    {
      key: 'harga_jual_toko',
      label: 'HARGA JUAL TOKO',
      className: 'font-semibold text-slate-800 whitespace-nowrap',
      render: (item) => formatRupiah(item.harga_jual_toko || item.harga_jual || 0),
    },
    {
      key: 'harga_jual_pelanggan',
      label: 'HARGA JUAL PELANGGAN',
      className: 'font-bold text-slate-900 whitespace-nowrap',
      render: (item) => formatRupiah(item.harga_jual_pelanggan || item.harga_jual || 0),
    },
    {
      key: 'garansi',
      label: 'GARANSI PRODUK',
      className: 'text-slate-600 whitespace-nowrap text-[11px]',
      render: (item) => item.garansi || (item.garansi_hari ? `${item.garansi_hari} hari` : 'Tidak ada'),
    },
    {
      key: 'aksi',
      label: 'AKSI',
      sortable: false,
      className: 'text-center w-16 whitespace-nowrap',
      render: (item) => (
        <div className="flex items-center justify-center space-x-1">
          <Link
            href={`/produk/${item.id}/edit`}
            className="p-1 rounded text-blue-600 hover:bg-blue-50 transition-colors"
            title="Edit Produk"
          >
            <Edit2 className="w-3.5 h-3.5" />
          </Link>
          <button
            onClick={() => handleDelete(item.id)}
            className="p-1 rounded text-rose-500 hover:bg-rose-50 transition-colors cursor-pointer"
            title="Hapus Produk"
          >
            <Trash2 className="w-3.5 h-3.5" />
          </button>
        </div>
      ),
    },
  ];

  return (
    <div className="space-y-5">
      {/* Top 2 Summary Cards matching screenshot 7 */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        {/* Card 1: Tersedia */}
        <div className="bg-white rounded-xl border border-slate-200 p-5 shadow-2xs">
          <h2 className="text-sm font-bold text-slate-800 mb-3">Tersedia</h2>
          <div className="grid grid-cols-3 gap-2">
            <div>
              <span className="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                ITEM
              </span>
              <span className="text-xl font-black text-[#2563eb]">
                {summary.tersedia_item}
              </span>
            </div>
            <div>
              <span className="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                STOK
              </span>
              <span className="text-xl font-black text-[#2563eb]">
                {summary.tersedia_stok}
              </span>
            </div>
            <div>
              <span className="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                MODAL
              </span>
              <span className="text-base sm:text-lg font-black text-[#10b981] whitespace-nowrap">
                {formatRupiah(summary.tersedia_modal)}
              </span>
            </div>
          </div>
        </div>

        {/* Card 2: Terjual */}
        <div className="bg-white rounded-xl border border-slate-200 p-5 shadow-2xs">
          <h2 className="text-sm font-bold text-slate-800 mb-3">Terjual</h2>
          <div className="grid grid-cols-2 gap-4">
            <div>
              <span className="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                ITEM
              </span>
              <span className="text-xl font-black text-[#2563eb]">
                {summary.terjual_item}
              </span>
            </div>
            <div>
              <span className="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                NOMINAL
              </span>
              <span className="text-base sm:text-lg font-black text-[#10b981] whitespace-nowrap">
                {formatRupiah(summary.terjual_nominal)}
              </span>
            </div>
          </div>
        </div>
      </div>

      {/* Header Title & Top Buttons matching screenshot 7 */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h1 className="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-1.5">
          <span>Item Produk</span>
          <span className="text-amber-400">✨</span>
        </h1>

        <div className="flex items-center space-x-2">
          {/* + Tambah Stok Button */}
          <Link
            href="/transfer-stok"
            className="inline-flex items-center space-x-1.5 px-3.5 py-1.5 rounded-md bg-[#6366f1] hover:bg-[#4f46e5] text-white text-xs font-bold shadow-2xs transition-colors cursor-pointer"
          >
            <Plus className="w-4 h-4" />
            <span>Tambah Stok</span>
          </Link>

          {/* + Tambah Produk Button */}
          <Link
            href="/produk/tambah"
            className="inline-flex items-center space-x-1.5 px-3.5 py-1.5 rounded-md bg-[#5051F9] hover:bg-[#4344db] text-white text-xs font-bold shadow-2xs transition-colors cursor-pointer"
          >
            <Plus className="w-4 h-4" />
            <span>Tambah Produk</span>
          </Link>

          {/* Print Icon Button */}
          <button
            onClick={() => window.print()}
            title="Cetak Halaman"
            className="p-1.5 rounded-md border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 shadow-2xs transition-colors cursor-pointer"
          >
            <Printer className="w-4 h-4 text-blue-600" />
          </button>
        </div>
      </div>

      {/* Filter Tabs matching screenshot 7 */}
      <div className="flex items-center flex-wrap gap-2">
        {categories.map((cat) => (
          <button
            key={cat}
            onClick={() => setActiveCategory(cat)}
            className={`px-3 py-1.5 rounded-md text-xs font-bold transition-colors cursor-pointer ${
              activeCategory === cat
                ? 'bg-[#5051F9] text-white shadow-2xs'
                : 'bg-slate-100 text-slate-700 hover:bg-slate-200'
            }`}
          >
            {cat}
          </button>
        ))}
      </div>

      {/* DataTable Component matching screenshot 7 */}
      <DataTable<ProdukRow>
        columns={columns}
        data={produkList}
        isLoading={isLoading}
        title={`${activeCategory === 'Semua' ? 'Semua' : activeCategory} Produk ${
          produkList.length
        }`}
        selectable={true}
        selectedIds={selectedIds}
        onSelectChange={setSelectedIds}
        onBulkDelete={handleBulkDelete}
        idKey="id"
        defaultPerPage={10}
        perPageOptions={[10, 25, 50, 100]}
        emptyMessage={`Tidak ada data produk dalam kategori ${activeCategory}.`}
      />
    </div>
  );
}

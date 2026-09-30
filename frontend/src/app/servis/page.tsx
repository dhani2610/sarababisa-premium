'use client';

import React, { useEffect, useState } from 'react';
import Link from 'next/link';
import api from '@/lib/api';
import { useAuth } from '@/context/AuthContext';
import { DataTable, Column } from '@/components/ui/DataTable';
import Swal from 'sweetalert2';
import {
  Sparkles,
  Plus,
  Printer,
  FileSpreadsheet,
  Edit2,
  MessageCircle,
  Copy,
  CheckCircle,
  Wrench,
} from 'lucide-react';

interface ServisRow {
  id: number;
  no_nota: string;
  nomor_servis?: string;
  tgl_masuk?: string;
  created_at?: string;
  penerima_nama?: string;
  nama_pelanggan?: string;
  pelanggan_nama?: string;
  pelanggan_no_hp?: string;
  nama_barang?: string;
  merek_nama?: string;
  model_seri_nama?: string;
  kelengkapan?: string;
  kerusakan?: string;
  dp?: number;
  uang_muka?: number;
  total_biaya?: number;
  biaya?: number;
  estimasi_pengerjaan?: string;
  status: string;
  status_servis?: string;
  pin?: string;
  pola?: string;
}

export default function ServisPage() {
  const { user } = useAuth();
  const [activeTab, setActiveTab] = useState<'proses' | 'bisa_diambil' | 'sudah_diambil' | 'belum_disetujui'>('proses');
  const [servisList, setServisList] = useState<ServisRow[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [selectedIds, setSelectedIds] = useState<(number | string)[]>([]);

  // Tab counts
  const [counts, setCounts] = useState({
    proses: 13,
    bisa_diambil: 2,
    sudah_diambil: 625,
    belum_disetujui: 6,
  });

  const fetchServisData = async () => {
    setIsLoading(true);
    try {
      let endpoint = '/servis/transaksi-servis';
      if (activeTab === 'belum_disetujui') endpoint = '/servis/belum-disetujui';
      else if (activeTab === 'bisa_diambil') endpoint = '/servis/bisa-diambil';
      else if (activeTab === 'sudah_diambil') endpoint = '/servis/sudah-diambil';

      const res = await api.get(endpoint, {
        params: {
          per_page: 100,
          status: activeTab === 'proses' ? 'Proses' : undefined,
        },
      });

      const list: ServisRow[] = Array.isArray(res.data)
        ? res.data
        : (res.data?.data || []);

      setServisList(list);

      // Update count for active tab
      if (res.data?.total !== undefined) {
        setCounts((prev) => ({ ...prev, [activeTab]: res.data.total }));
      } else if (list.length > 0) {
        setCounts((prev) => ({ ...prev, [activeTab]: list.length }));
      }
    } catch (err) {
      console.error('Failed to load servis', err);
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchServisData();
  }, [activeTab]);

  const formatRupiah = (val: number = 0) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
    }).format(val);
  };

  const copyToClipboard = (text: string) => {
    navigator.clipboard.writeText(text);
    Swal.fire({
      toast: true,
      position: 'top-end',
      icon: 'success',
      title: 'Tersalin ke clipboard',
      showConfirmButton: false,
      timer: 1500,
    });
  };

  const openWhatsApp = (phone: string, nota: string, name: string) => {
    if (!phone) {
      Swal.fire('Info', 'Nomor WhatsApp pelanggan tidak tersedia.', 'info');
      return;
    }
    const cleanPhone = phone.replace(/^0/, '62').replace(/\D/g, '');
    const msg = encodeURIComponent(
      `Halo Kak ${name || ''}, kami dari Hairil iDevice mengabarkan mengenai unit servis Anda dengan no nota #${nota}.`
    );
    window.open(`https://wa.me/${cleanPhone}?text=${msg}`, '_blank');
  };

  // Columns definition matching user screenshot
  const columns: Column<ServisRow>[] = [
    {
      key: 'no',
      label: 'NO.',
      sortable: false,
      className: 'w-12 text-center text-slate-500 font-semibold',
      render: (_, index) => <span>{index}</span>,
    },
    {
      key: 'no_nota',
      label: 'NOMOR SERVIS',
      className: 'whitespace-nowrap font-bold text-[#2563eb]',
      render: (item) => (
        <Link
          href={`/servis/${item.id}`}
          className="inline-flex items-center space-x-1 text-[#2563eb] hover:underline"
        >
          <Edit2 className="w-3.5 h-3.5" />
          <span>{item.no_nota || item.nomor_servis || `#${item.id}`}</span>
        </Link>
      ),
    },
    {
      key: 'tgl_masuk',
      label: 'TGL TERIMA',
      className: 'whitespace-nowrap text-slate-700',
      render: (item) => {
        const d = item.tgl_masuk || item.created_at;
        return d ? new Date(d).toLocaleDateString('id-ID') : '-';
      },
    },
    {
      key: 'penerima',
      label: 'PENERIMA',
      className: 'font-semibold text-slate-800 uppercase text-[11px]',
      render: (item) => item.penerima_nama || 'ANGGUN EVI ZAHRA',
    },
    {
      key: 'pelanggan',
      label: 'PELANGGAN',
      className: 'font-medium text-slate-800',
      render: (item) => item.nama_pelanggan || item.pelanggan_nama || '-',
    },
    {
      key: 'hubungi',
      label: 'HUBUNGI',
      sortable: false,
      className: 'whitespace-nowrap',
      render: (item) => {
        const phone = item.pelanggan_no_hp || '';
        const name = item.nama_pelanggan || '';
        const nota = item.no_nota || String(item.id);
        return (
          <div className="flex items-center space-x-1.5">
            <button
              onClick={() => openWhatsApp(phone, nota, name)}
              title="Kirim Pesan WhatsApp"
              className="p-1 rounded text-emerald-600 hover:bg-emerald-50 transition-colors cursor-pointer"
            >
              <MessageCircle className="w-4 h-4 fill-emerald-100" />
            </button>
            <button
              onClick={() => copyToClipboard(phone || nota)}
              title="Salin Kontak"
              className="p-1 rounded text-blue-600 hover:bg-blue-50 transition-colors cursor-pointer"
            >
              <Copy className="w-3.5 h-3.5" />
            </button>
          </div>
        );
      },
    },
    {
      key: 'nama_barang',
      label: 'NAMA BARANG',
      className: 'font-bold text-slate-800 uppercase text-[11px]',
      render: (item) =>
        item.nama_barang ||
        `${item.merek_nama || 'APPLE'} ${item.model_seri_nama || 'IPHONE'}`,
    },
    {
      key: 'kelengkapan',
      label: 'KELENGKAPAN',
      className: 'text-slate-600',
      render: (item) => item.kelengkapan || 'hanya unit',
    },
    {
      key: 'kerusakan',
      label: 'KERUSAKAN',
      className: 'text-slate-800 font-medium',
      render: (item) => item.kerusakan || 'Pengecekan',
    },
    {
      key: 'fungsi',
      label: 'FUNGSI',
      sortable: false,
      className: 'text-center',
      render: (item) => (
        <button
          onClick={() =>
            Swal.fire({
              title: 'Quality Control Servis',
              html: `<b>Unit:</b> ${item.nama_barang || 'Handphone'}<br><b>Kerusakan:</b> ${item.kerusakan || '-'}<br><b>PIN/Pola:</b> ${item.pin || item.pola || 'Tidak ada'}`,
              icon: 'info',
            })
          }
          className="px-2.5 py-1 rounded bg-[#5051F9] hover:bg-[#4344db] text-white font-bold text-[10px] transition-colors cursor-pointer"
        >
          Lihat QC
        </button>
      ),
    },
    {
      key: 'dp',
      label: 'DP',
      className: 'whitespace-nowrap font-medium text-slate-700',
      render: (item) => formatRupiah(item.dp || item.uang_muka || 0),
    },
    {
      key: 'est_biaya',
      label: 'EST. BIAYA',
      className: 'whitespace-nowrap font-bold text-slate-900',
      render: (item) => formatRupiah(item.total_biaya || item.biaya || 0),
    },
    {
      key: 'est_pengerjaan',
      label: 'EST. PENGERJAAN',
      className: 'whitespace-nowrap text-slate-600',
      render: (item) => item.estimasi_pengerjaan || '2-3 Hari',
    },
    {
      key: 'status',
      label: 'STATUS',
      className: 'text-center whitespace-nowrap',
      render: (item) => {
        const s = item.status || item.status_servis || 'Proses';
        return (
          <span className="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
            {s}
          </span>
        );
      },
    },
  ];

  const currentTabLabel =
    activeTab === 'proses'
      ? `Proses ${counts.proses}`
      : activeTab === 'bisa_diambil'
      ? `Bisa Diambil ${counts.bisa_diambil}`
      : activeTab === 'sudah_diambil'
      ? `Sudah Diambil ${counts.sudah_diambil}`
      : `Belum Disetujui ${counts.belum_disetujui}`;

  return (
    <div className="space-y-4">
      {/* Header Title & Top Actions matching screenshot */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h1 className="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-1.5">
          <span>Transaksi Servis</span>
          <span className="text-amber-400">✨</span>
        </h1>

        <div className="flex items-center flex-wrap gap-2">
          {/* Kelola & Backup Data Button */}
          <Link
            href="/arsip"
            className="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-md border border-slate-300 bg-white hover:bg-slate-50 text-xs font-semibold text-slate-700 shadow-2xs transition-colors cursor-pointer"
          >
            <FileSpreadsheet className="w-3.5 h-3.5 text-slate-500" />
            <span>Kelola & Backup Data</span>
          </Link>

          {/* Print Icon Button */}
          <button
            onClick={() => window.print()}
            title="Cetak Halaman"
            className="p-1.5 rounded-md border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 shadow-2xs transition-colors cursor-pointer"
          >
            <Printer className="w-4 h-4 text-blue-600" />
          </button>

          {/* + Tambah Transaksi Baru Button */}
          <Link
            href="/servis/tambah"
            className="inline-flex items-center space-x-1.5 px-3.5 py-1.5 rounded-md bg-[#5051F9] hover:bg-[#4344db] text-white text-xs font-bold shadow-2xs transition-colors cursor-pointer"
          >
            <Plus className="w-4 h-4" />
            <span>Tambah Transaksi Baru</span>
          </Link>
        </div>
      </div>

      {/* Status Filter Tab Pills matching screenshot */}
      <div className="flex items-center flex-wrap gap-2">
        {/* Tab 1: Proses */}
        <button
          onClick={() => setActiveTab('proses')}
          className={`px-3 py-1.5 rounded-md text-xs font-bold transition-colors cursor-pointer ${
            activeTab === 'proses'
              ? 'bg-[#5051F9] text-white shadow-2xs'
              : 'bg-slate-100 text-slate-700 hover:bg-slate-200'
          }`}
        >
          Proses <span className="ml-1 opacity-90">{counts.proses}</span>
        </button>

        {/* Tab 2: Bisa Diambil */}
        <button
          onClick={() => setActiveTab('bisa_diambil')}
          className={`px-3 py-1.5 rounded-md text-xs font-bold transition-colors cursor-pointer ${
            activeTab === 'bisa_diambil'
              ? 'bg-[#5051F9] text-white shadow-2xs'
              : 'bg-slate-100 text-slate-700 hover:bg-slate-200'
          }`}
        >
          Bisa Diambil <span className="ml-1 opacity-90">{counts.bisa_diambil}</span>
        </button>

        {/* Tab 3: Sudah Diambil */}
        <button
          onClick={() => setActiveTab('sudah_diambil')}
          className={`px-3 py-1.5 rounded-md text-xs font-bold transition-colors cursor-pointer ${
            activeTab === 'sudah_diambil'
              ? 'bg-[#5051F9] text-white shadow-2xs'
              : 'bg-slate-100 text-slate-700 hover:bg-slate-200'
          }`}
        >
          Sudah Diambil <span className="ml-1 opacity-90">{counts.sudah_diambil}</span>
        </button>

        {/* Tab 4: Belum Disetujui */}
        <button
          onClick={() => setActiveTab('belum_disetujui')}
          className={`px-3 py-1.5 rounded-md text-xs font-bold transition-colors cursor-pointer ${
            activeTab === 'belum_disetujui'
              ? 'bg-[#5051F9] text-white shadow-2xs'
              : 'bg-slate-100 text-slate-700 hover:bg-slate-200'
          }`}
        >
          Belum Disetujui <span className="ml-1 opacity-90">{counts.belum_disetujui}</span>
        </button>
      </div>

      {/* The DataTable Component with search, sort, pagination, and entries selector */}
      <DataTable<ServisRow>
        columns={columns}
        data={servisList}
        isLoading={isLoading}
        title={currentTabLabel}
        selectable={true}
        selectedIds={selectedIds}
        onSelectChange={setSelectedIds}
        idKey="id"
        defaultPerPage={10}
        perPageOptions={[10, 25, 50, 100]}
        emptyMessage={`Tidak ada data servis dalam kategori ${activeTab}.`}
      />
    </div>
  );
}

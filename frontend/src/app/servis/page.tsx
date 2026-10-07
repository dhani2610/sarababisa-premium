'use client';

import React, { useEffect, useState, useCallback } from 'react';
import Link from 'next/link';
import api from '@/lib/api';
import { useAuth } from '@/context/AuthContext';
import { DataTable, Column } from '@/components/ui/DataTable';
import Swal from 'sweetalert2';
import {
  Plus,
  Printer,
  FileSpreadsheet,
  Edit2,
  MessageCircle,
  Copy,
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
  teknisi_nama?: string;
}

type TabKey = 'proses' | 'bisa_diambil' | 'sudah_diambil' | 'belum_disetujui';

const TAB_CONFIG: { key: TabKey; label: string; endpoint: string; statusParam?: string }[] = [
  { key: 'proses',          label: 'Proses',          endpoint: '/servis/transaksi-servis', statusParam: 'proses' },
  { key: 'bisa_diambil',   label: 'Bisa Diambil',    endpoint: '/servis/bisa-diambil' },
  { key: 'sudah_diambil',  label: 'Sudah Diambil',   endpoint: '/servis/sudah-diambil' },
  { key: 'belum_disetujui',label: 'Belum Disetujui', endpoint: '/servis/belum-disetujui' },
];

export default function ServisPage() {
  const { user } = useAuth();
  const [activeTab, setActiveTab] = useState<TabKey>('proses');
  const [servisList, setServisList] = useState<ServisRow[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [selectedIds, setSelectedIds] = useState<(number | string)[]>([]);
  const [search, setSearch] = useState('');
  const [page, setPage] = useState(1);
  const [perPage, setPerPage] = useState(15);
  const [totalPages, setTotalPages] = useState(1);

  // Real tab counts from API — initialized to null (loading state)
  const [counts, setCounts] = useState<Record<TabKey, number | null>>({
    proses: null,
    bisa_diambil: null,
    sudah_diambil: null,
    belum_disetujui: null,
  });

  // ── Fetch all tab counts in a single fast request ─────────
  const fetchAllCounts = useCallback(async () => {
    try {
      const res = await api.get('/servis/summary/counts');
      if (res.data) {
        setCounts({
          proses: res.data.proses ?? 0,
          bisa_diambil: res.data.bisa_diambil ?? 0,
          sudah_diambil: res.data.sudah_diambil ?? 0,
          belum_disetujui: res.data.belum_disetujui ?? 0,
        });
      }
    } catch (err) {
      console.error('Failed to load tab counts', err);
    }
  }, []);

  // ── Fetch active tab data with pagination ──────────────────
  const fetchServisData = useCallback(async () => {
    setIsLoading(true);
    try {
      const tabCfg = TAB_CONFIG.find((t) => t.key === activeTab)!;
      const res = await api.get(tabCfg.endpoint, {
        params: {
          page,
          per_page: perPage,
          ...(tabCfg.statusParam ? { status: tabCfg.statusParam } : {}),
          ...(search ? { search } : {}),
        },
      });

      const d = res.data;
      const list: ServisRow[] = Array.isArray(d) ? d : (d?.data || []);
      setServisList(list);
      setTotalPages(d?.total_pages ?? 1);

      // Update this tab's count from the paginated response
      const total = d?.total ?? (Array.isArray(d) ? d.length : list.length);
      setCounts((prev) => ({ ...prev, [activeTab]: total }));
    } catch (err) {
      console.error('Failed to load servis', err);
    } finally {
      setIsLoading(false);
    }
  }, [activeTab, page, perPage, search]);

  // On mount: fetch all counts
  useEffect(() => {
    fetchAllCounts();
  }, [fetchAllCounts]);

  // On tab / page / perPage change: fetch data
  useEffect(() => {
    fetchServisData();
  }, [fetchServisData]);

  // Reset page to 1 when tab changes
  const handleTabChange = (tab: TabKey) => {
    setActiveTab(tab);
    setPage(1);
    setSearch('');
    setSelectedIds([]);
  };

  const formatRupiah = (val: number = 0) =>
    new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
    }).format(val);

  const copyToClipboard = (text: string) => {
    navigator.clipboard.writeText(text);
    Swal.fire({
      toast: true, position: 'top-end', icon: 'success',
      title: 'Tersalin ke clipboard', showConfirmButton: false, timer: 1500,
    });
  };

  const openWhatsApp = (phone: string, nota: string, name: string) => {
    if (!phone) {
      Swal.fire('Info', 'Nomor WhatsApp pelanggan tidak tersedia.', 'info');
      return;
    }
    const cleanPhone = phone.replace(/^0/, '62').replace(/\D/g, '');
    const msg = encodeURIComponent(
      `Halo Kak ${name || ''}, kami mengabarkan mengenai unit servis Anda dengan no nota #${nota}.`
    );
    window.open(`https://wa.me/${cleanPhone}?text=${msg}`, '_blank');
  };

  const handleSearchSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setPage(1);
    fetchServisData();
  };

  const columns: Column<ServisRow>[] = [
    {
      key: 'no',
      label: 'NO.',
      sortable: false,
      className: 'w-12 text-center text-slate-500 font-semibold',
      render: (_, index) => <span>{(page - 1) * perPage + index}</span>,
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
      render: (item) => item.penerima_nama || user?.nama || '-',
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
        const name = item.nama_pelanggan || item.pelanggan_nama || '';
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
        [item.merek_nama, item.model_seri_nama].filter(Boolean).join(' ') ||
        '-',
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
        const s = item.status || item.status_servis || 'proses';
        const colorMap: Record<string, string> = {
          proses: 'bg-amber-100 text-amber-800',
          bisa_diambil: 'bg-emerald-100 text-emerald-800',
          sudah_diambil: 'bg-slate-100 text-slate-700',
          belum_disetujui: 'bg-rose-100 text-rose-700',
        };
        return (
          <span className={`inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold ${colorMap[s] || 'bg-slate-100 text-slate-700'}`}>
            {s.replace(/_/g, ' ')}
          </span>
        );
      },
    },
  ];

  const activeTabCfg = TAB_CONFIG.find((t) => t.key === activeTab)!;
  const countLabel = counts[activeTab] !== null ? counts[activeTab] : '…';

  return (
    <div className="space-y-4">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h1 className="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-1.5">
          <span>Transaksi Servis</span>
          <span className="text-amber-400">✨</span>
        </h1>

        <div className="flex items-center flex-wrap gap-2">
          <Link
            href="/arsip"
            className="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-md border border-slate-300 bg-white hover:bg-slate-50 text-xs font-semibold text-slate-700 shadow-2xs transition-colors cursor-pointer"
          >
            <FileSpreadsheet className="w-3.5 h-3.5 text-slate-500" />
            <span>Kelola &amp; Backup Data</span>
          </Link>

          <button
            onClick={() => window.print()}
            title="Cetak Halaman"
            className="p-1.5 rounded-md border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 shadow-2xs transition-colors cursor-pointer"
          >
            <Printer className="w-4 h-4 text-blue-600" />
          </button>

          <Link
            href="/servis/tambah"
            className="inline-flex items-center space-x-1.5 px-3.5 py-1.5 rounded-md bg-[#5051F9] hover:bg-[#4344db] text-white text-xs font-bold shadow-2xs transition-colors cursor-pointer"
          >
            <Plus className="w-4 h-4" />
            <span>Tambah Transaksi Baru</span>
          </Link>
        </div>
      </div>

      {/* Status Tab Pills — real counts from API */}
      <div className="flex items-center flex-wrap gap-2">
        {TAB_CONFIG.map((tab) => {
          const cnt = counts[tab.key];
          const isActive = activeTab === tab.key;
          return (
            <button
              key={tab.key}
              onClick={() => handleTabChange(tab.key)}
              className={`px-3 py-1.5 rounded-md text-xs font-bold transition-colors cursor-pointer flex items-center gap-1.5 ${
                isActive
                  ? 'bg-[#5051F9] text-white shadow-2xs'
                  : 'bg-slate-100 text-slate-700 hover:bg-slate-200'
              }`}
            >
              {tab.label}
              <span
                className={`inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full text-[10px] font-black ${
                  isActive
                    ? 'bg-white/20 text-white'
                    : 'bg-white text-slate-600 border border-slate-200'
                }`}
              >
                {cnt !== null ? cnt : '…'}
              </span>
            </button>
          );
        })}
      </div>

      {/* Search Bar */}
      <div className="flex items-center gap-2">
        <form onSubmit={handleSearchSubmit} className="relative w-full sm:w-72">
          <input
            type="text"
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            placeholder="Cari no nota, pelanggan, kerusakan..."
            className="w-full pl-3 pr-4 py-1.5 text-xs bg-white border border-slate-200 rounded-md focus:outline-hidden focus:border-blue-500 transition-all shadow-2xs"
          />
        </form>
        <button
          onClick={handleSearchSubmit}
          className="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-md border border-slate-200 transition-colors cursor-pointer"
        >
          Cari
        </button>
        {search && (
          <button
            onClick={() => { setSearch(''); setPage(1); }}
            className="px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50 rounded-md transition-colors cursor-pointer"
          >
            Reset
          </button>
        )}
      </div>

      {/* DataTable with pagination */}
      <DataTable<ServisRow>
        columns={columns}
        data={servisList}
        isLoading={isLoading}
        title={`${activeTabCfg.label} (${countLabel})`}
        selectable={true}
        selectedIds={selectedIds}
        onSelectChange={setSelectedIds}
        idKey="id"
        defaultPerPage={perPage}
        perPageOptions={[15, 25, 50, 100]}
        emptyMessage={`Tidak ada data servis dalam kategori ${activeTabCfg.label}.`}
      />

      {/* Manual pagination if DataTable doesn't handle it */}
      {totalPages > 1 && (
        <div className="flex items-center justify-between text-xs text-slate-500 bg-white border border-slate-200 rounded-md px-4 py-2.5 shadow-2xs">
          <span>Halaman <b className="text-slate-800">{page}</b> dari <b className="text-slate-800">{totalPages}</b></span>
          <div className="flex items-center gap-1">
            <button
              disabled={page <= 1}
              onClick={() => setPage(page - 1)}
              className="px-2.5 py-1 rounded border border-slate-200 bg-white hover:bg-slate-50 disabled:opacity-40 cursor-pointer"
            >
              ‹ Prev
            </button>
            <button
              disabled={page >= totalPages}
              onClick={() => setPage(page + 1)}
              className="px-2.5 py-1 rounded border border-slate-200 bg-white hover:bg-slate-50 disabled:opacity-40 cursor-pointer"
            >
              Next ›
            </button>
          </div>
        </div>
      )}
    </div>
  );
}

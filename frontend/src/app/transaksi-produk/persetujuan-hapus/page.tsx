'use client';

import React, { useEffect, useState } from 'react';
import api from '@/lib/api';
import { DataTable, Column } from '@/components/ui/DataTable';
import Swal from 'sweetalert2';
import { Trash2, CheckCircle, XCircle, Clock } from 'lucide-react';

interface ApprovalRow {
  id: number;
  tipe: string;
  transaksi_id: number;
  alasan: string;
  diajukan_oleh: string;
  status: string;
  created_at: string;
}

export default function PersetujuanHapusPage() {
  const [data, setData] = useState<ApprovalRow[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  const fetchData = async () => {
    setIsLoading(true);
    try {
      const res = await api.get('/persetujuan-hapus-transaksi');
      const list = Array.isArray(res.data) ? res.data : (res.data?.data || []);
      setData(list);
    } catch (err) {
      console.error('Error fetching persetujuan hapus', err);
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchData();
  }, []);

  const handleApprove = async (id: number) => {
    const confirm = await Swal.fire({
      title: 'Setujui Penghapusan?',
      text: 'Transaksi ini akan dihapus permanen sesuai permohonan staf.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Ya, Setujui',
      confirmButtonColor: '#ef4444',
    });
    if (!confirm.isConfirmed) return;

    try {
      await api.post(`/persetujuan-hapus-transaksi/${id}/approve`);
      Swal.fire('Disetujui!', 'Permintaan hapus disetujui.', 'success');
      fetchData();
    } catch (err: any) {
      Swal.fire('Gagal', err.response?.data?.detail || 'Gagal menyetujui', 'error');
    }
  };

  const handleReject = async (id: number) => {
    try {
      await api.post(`/persetujuan-hapus-transaksi/${id}/reject`);
      Swal.fire('Ditolak', 'Permintaan hapus ditolak.', 'info');
      fetchData();
    } catch (err: any) {
      Swal.fire('Gagal', err.response?.data?.detail || 'Gagal menolak', 'error');
    }
  };

  const columns: Column<ApprovalRow>[] = [
    {
      key: 'no',
      label: 'NO.',
      sortable: false,
      className: 'w-10 text-center text-slate-500 font-semibold',
      render: (_, idx) => <span>{idx}</span>,
    },
    {
      key: 'tipe',
      label: 'TIPE TRANSAKSI',
      className: 'font-bold uppercase text-slate-800 text-[11px]',
      render: (item) => item.tipe || 'Servis / Penjualan',
    },
    {
      key: 'transaksi_id',
      label: 'ID TRANSAKSI',
      className: 'font-mono font-bold text-blue-600',
      render: (item) => `#${item.transaksi_id}`,
    },
    {
      key: 'alasan',
      label: 'ALASAN PENGHAPUSAN',
      className: 'text-slate-700 max-w-sm',
      render: (item) => item.alasan || 'Salah input oleh kasir',
    },
    {
      key: 'diajukan_oleh',
      label: 'DIAJUKAN OLEH',
      className: 'font-medium text-slate-800',
      render: (item) => item.diajukan_oleh || 'Staff Toko',
    },
    {
      key: 'status',
      label: 'STATUS',
      className: 'text-center',
      render: (item) => (
        <span className="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
          {item.status || 'Menunggu Persetujuan'}
        </span>
      ),
    },
    {
      key: 'aksi',
      label: 'AKSI',
      sortable: false,
      className: 'text-center whitespace-nowrap',
      render: (item) => (
        <div className="flex items-center justify-center space-x-2">
          <button
            onClick={() => handleApprove(item.id)}
            className="px-2.5 py-1 rounded bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-xs border border-emerald-200 transition-colors cursor-pointer"
          >
            Setujui
          </button>
          <button
            onClick={() => handleReject(item.id)}
            className="px-2.5 py-1 rounded bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs border border-rose-200 transition-colors cursor-pointer"
          >
            Tolak
          </button>
        </div>
      ),
    },
  ];

  return (
    <div className="space-y-4">
      <div>
        <h1 className="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-1.5">
          <span>Approval Hapus Transaksi</span>
          <span className="text-amber-400">✨</span>
        </h1>
        <p className="text-xs text-slate-500 mt-0.5">
          Daftar pengajuan penghapusan transaksi penjualan atau servis yang memerlukan persetujuan Kepala Toko.
        </p>
      </div>

      <DataTable<ApprovalRow>
        columns={columns}
        data={data}
        isLoading={isLoading}
        title="Antrean Permintaan Hapus"
        countBadge={data.length}
        defaultPerPage={10}
        emptyMessage="Tidak ada permintaan hapus transaksi yang menunggu persetujuan."
      />
    </div>
  );
}

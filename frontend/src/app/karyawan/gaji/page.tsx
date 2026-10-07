'use client';

import React, { useState, useEffect } from 'react';
import api, { API_BASE_URL } from '@/lib/api';
import { DataTable, Column } from '@/components/ui/DataTable';
import Swal from 'sweetalert2';
import { Users, FileDown, RefreshCw, DollarSign, Calendar, UserCheck } from 'lucide-react';

interface KaryawanGajiRow {
  id: number;
  nama: string;
  username: string;
  role: string;
  gaji_pokok?: number;
  shift_nama?: string;
  is_active: boolean;
}

export default function KaryawanGajiPage() {
  const [data, setData] = useState<KaryawanGajiRow[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [bulan, setBulan] = useState(new Date().toISOString().slice(0, 7)); // YYYY-MM

  const fetchData = async () => {
    setIsLoading(true);
    try {
      const res = await api.get('/gaji/karyawan', { params: { per_page: 50 } });
      const list = res.data?.data || (Array.isArray(res.data) ? res.data : []);
      setData(list);
    } catch (err) {
      console.error('Failed to load gaji karyawan', err);
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchData();
  }, []);

  const handleCetakSlip = (id: number) => {
    const token = localStorage.getItem('token');
    const url = `${API_BASE_URL}/gaji/karyawan/${id}/slip?bulan=${bulan}&token=${token}`;
    window.open(url, '_blank');
  };

  const formatRupiah = (val: number = 0) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
    }).format(val);
  };

  const columns: Column<KaryawanGajiRow>[] = [
    {
      key: 'no',
      label: 'NO.',
      sortable: false,
      className: 'w-12 text-center text-slate-500 font-semibold',
      render: (_, idx) => <span>{idx}</span>,
    },
    {
      key: 'nama',
      label: 'NAMA KARYAWAN',
      className: 'font-bold text-slate-800 uppercase text-xs',
      render: (item) => (
        <div>
          <p className="font-bold text-slate-900">{item.nama}</p>
          <p className="text-[10px] text-slate-500 font-mono">@{item.username}</p>
        </div>
      ),
    },
    {
      key: 'role',
      label: 'JABATAN / ROLE',
      className: 'text-xs font-semibold uppercase',
      render: (item) => (
        <span className="inline-block px-2 py-0.5 rounded text-[10px] font-black bg-blue-50 text-blue-700">
          {item.role}
        </span>
      ),
    },
    {
      key: 'shift_nama',
      label: 'SHIFT KERJA',
      className: 'text-xs font-medium text-slate-600',
      render: (item) => item.shift_nama || 'Reguler',
    },
    {
      key: 'gaji_pokok',
      label: 'GAJI POKOK',
      className: 'text-right font-black text-slate-900 text-xs tabular-nums',
      render: (item) => (item.gaji_pokok ? formatRupiah(item.gaji_pokok) : 'Rp 0'),
    },
    {
      key: 'aksi',
      label: 'SLIP GAJI',
      sortable: false,
      className: 'text-center w-36',
      render: (item) => (
        <button
          onClick={() => handleCetakSlip(item.id)}
          className="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-[11px] font-bold shadow-2xs transition-all cursor-pointer"
        >
          <FileDown className="w-3.5 h-3.5 text-blue-400" />
          <span>Cetak Slip</span>
        </button>
      ),
    },
  ];

  return (
    <div className="space-y-4">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-4 rounded-xl border border-slate-200 shadow-2xs">
        <div>
          <div className="flex items-center space-x-2">
            <Users className="w-5 h-5 text-blue-600" />
            <h1 className="text-lg font-black text-slate-900 tracking-tight">
              Data Karyawan & Slip Gaji
            </h1>
          </div>
          <p className="text-xs text-slate-500 mt-0.5">
            Daftar karyawan cabang, gaji pokok, serta unduh slip gaji bulanan resmi.
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

      {/* Table */}
      <div className="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs">
        <DataTable
          columns={columns}
          data={data}
          keyField="id"
          searchPlaceholder="Cari nama karyawan, username, role..."
          emptyMessage="Belum ada data karyawan terdaftar."
        />
      </div>
    </div>
  );
}

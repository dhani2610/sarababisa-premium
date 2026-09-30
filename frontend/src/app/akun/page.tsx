'use client';

import React, { useEffect, useState } from 'react';
import api from '@/lib/api';
import { useAuth } from '@/context/AuthContext';
import { DataTable, Column } from '@/components/ui/DataTable';
import Swal from 'sweetalert2';
import { Plus, Edit2, Trash2, RefreshCw } from 'lucide-react';
import ModalAkun from '@/components/akun/ModalAkun';

interface AkunRow {
  id: number;
  nama: string;
  name?: string;
  email?: string;
  username: string;
  role: string;
  cabang_id: number;
  cabang_nama?: string;
  bagian_teknisi?: string;
  nik?: string;
  alamat?: string;
  no_hp?: string;
  nomor_hp?: string;
  persen_hardware?: number | string;
  persen?: number | string;
  persen_bonus_interface?: number | string;
  investor_produk?: string;
  shift?: string;
  ktp?: string;
  kk?: string;
  ijasah?: string;
  dokumen_lain?: string;
  is_active?: boolean;
}

export default function AkunPage() {
  const { user } = useAuth();
  const [data, setData] = useState<AkunRow[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [selectedIds, setSelectedIds] = useState<(number | string)[]>([]);

  // Modal State
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingItem, setEditingItem] = useState<AkunRow | null>(null);

  const fetchAkun = async () => {
    setIsLoading(true);
    try {
      const res = await api.get('/akun', {
        params: { per_page: 100 },
      });
      const list = Array.isArray(res.data) ? res.data : res.data?.data || [];
      setData(list);
    } catch (err) {
      console.error('Failed to load akun', err);
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchAkun();
  }, []);

  const handleOpenAdd = () => {
    setEditingItem(null);
    setIsModalOpen(true);
  };

  const handleOpenEdit = (item: AkunRow) => {
    setEditingItem(item);
    setIsModalOpen(true);
  };

  const handleDeleteSingle = async (id: number) => {
    const confirm = await Swal.fire({
      title: 'Hapus Akun Karyawan?',
      text: 'Akun ini akan dinonaktifkan dari sistem.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Ya, Hapus',
      cancelButtonText: 'Batal',
      confirmButtonColor: '#ef4444',
    });
    if (!confirm.isConfirmed) return;

    try {
      await api.delete(`/akun/${id}`);
      Swal.fire('Dihapus!', 'Akun berhasil dihapus.', 'success');
      fetchAkun();
    } catch (err: any) {
      Swal.fire('Gagal', err.response?.data?.detail || 'Gagal menghapus akun', 'error');
    }
  };

  const handleBulkDelete = async (ids: (number | string)[]) => {
    const confirm = await Swal.fire({
      title: `Hapus ${ids.length} Akun Terpilih?`,
      text: 'Semua akun yang dipilih akan dihapus.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: `Ya, Hapus (${ids.length})`,
      cancelButtonText: 'Batal',
      confirmButtonColor: '#ef4444',
    });
    if (!confirm.isConfirmed) return;

    try {
      await Promise.all(ids.map((id) => api.delete(`/akun/${id}`)));
      Swal.fire('Berhasil!', `${ids.length} akun berhasil dihapus.`, 'success');
      setSelectedIds([]);
      fetchAkun();
    } catch (err: any) {
      Swal.fire('Gagal', 'Terjadi kesalahan saat menghapus data.', 'error');
    }
  };

  // Columns matching screenshot 9
  const columns: Column<AkunRow>[] = [
    {
      key: 'no',
      label: 'NO.',
      sortable: false,
      className: 'w-10 text-center text-slate-500 font-semibold',
      render: (_, index) => <span>{index}</span>,
    },
    {
      key: 'cabang',
      label: 'CABANG',
      className: 'font-semibold text-slate-800 uppercase text-[11px] whitespace-nowrap',
      render: (item) => item.cabang_nama || 'HI PRINGSEWU',
    },
    {
      key: 'nama',
      label: 'NAMA',
      className: 'font-bold text-slate-900 uppercase text-[11px] whitespace-nowrap',
      render: (item) => item.nama || item.name || item.username,
    },
    {
      key: 'email',
      label: 'EMAIL',
      className: 'text-slate-600 text-[11px] whitespace-nowrap',
      render: (item) => item.email || '-',
    },
    {
      key: 'username',
      label: 'NAMA PENGGUNA',
      className: 'text-slate-700 font-medium text-[11px] whitespace-nowrap',
      render: (item) => item.username,
    },
    {
      key: 'role',
      label: 'ROLE / AKSES',
      className: 'whitespace-nowrap',
      render: (item) => {
        const r = item.role;
        return (
          <span className="inline-block px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-800">
            {r === 'KepalaToko' ? 'Kepala Toko' : r === 'AdminToko' ? 'Admin Toko' : r}
          </span>
        );
      },
    },
    {
      key: 'persen_hardware',
      label: 'PERSEN HARDWARE',
      className: 'text-center font-bold text-slate-800 whitespace-nowrap',
      render: (item) => {
        if (item.role === 'KepalaToko') return <span className="text-slate-400">-</span>;
        const p = item.persen_hardware || item.persen;
        return <span>{p ? `${p}%` : '%'}</span>;
      },
    },
    {
      key: 'persen_interface',
      label: 'PERSEN INTERFACE',
      className: 'text-center font-bold text-slate-800 whitespace-nowrap',
      render: (item) => {
        if (item.role === 'KepalaToko') return <span className="text-slate-400">-</span>;
        const p = item.persen_bonus_interface;
        return <span>{p !== undefined ? `${p}%` : '0%'}</span>;
      },
    },
    {
      key: 'investor_produk',
      label: 'INVESTOR PRODUK',
      className: 'text-center text-slate-500 text-[11px] whitespace-nowrap',
      render: (item) => item.investor_produk || '-',
    },
    {
      key: 'shift',
      label: 'SHIFT',
      className: 'text-slate-700 font-medium text-[11px] whitespace-nowrap',
      render: (item) => item.shift || `SHIFT ${item.nama?.split(' ')?.[0] || 'KARYAWAN'}`,
    },
    {
      key: 'ktp',
      label: 'KTP',
      className: 'text-center text-[11px] whitespace-nowrap',
      render: (item) =>
        item.ktp ? (
          <a href={item.ktp} target="_blank" className="text-blue-600 hover:underline font-bold">
            Lihat
          </a>
        ) : (
          <span className="text-slate-400">-</span>
        ),
    },
    {
      key: 'kk',
      label: 'KK',
      className: 'text-center text-[11px] whitespace-nowrap',
      render: (item) =>
        item.kk ? (
          <a href={item.kk} target="_blank" className="text-blue-600 hover:underline font-bold">
            Lihat
          </a>
        ) : (
          <span className="text-slate-400">-</span>
        ),
    },
    {
      key: 'ijasah',
      label: 'IJASAH',
      className: 'text-center text-[11px] whitespace-nowrap',
      render: (item) =>
        item.ijasah ? (
          <a href={item.ijasah} target="_blank" className="text-blue-600 hover:underline font-bold">
            Lihat
          </a>
        ) : (
          <span className="text-slate-400">-</span>
        ),
    },
    {
      key: 'dokumen_lain',
      label: 'DOK. LAIN',
      className: 'text-center text-[11px] whitespace-nowrap',
      render: (item) =>
        item.dokumen_lain ? (
          <a href={item.dokumen_lain} target="_blank" className="text-blue-600 hover:underline font-bold">
            Lihat
          </a>
        ) : (
          <span className="text-slate-400">-</span>
        ),
    },
    {
      key: 'aksi',
      label: 'AKSI',
      sortable: false,
      className: 'text-center w-16 whitespace-nowrap',
      render: (item) => (
        <div className="flex items-center justify-center space-x-1.5">
          <button
            onClick={() => handleOpenEdit(item)}
            className="p-1 rounded text-blue-600 hover:bg-blue-50 transition-colors cursor-pointer"
            title="Edit Akun"
          >
            <Edit2 className="w-3.5 h-3.5" />
          </button>
          <button
            onClick={() => handleDeleteSingle(item.id)}
            className="p-1 rounded text-rose-500 hover:bg-rose-50 transition-colors cursor-pointer"
            title="Hapus Akun"
          >
            <Trash2 className="w-3.5 h-3.5" />
          </button>
        </div>
      ),
    },
  ];

  return (
    <div className="space-y-4">
      {/* Header and Add Button matching screenshot 9 */}
      <div className="flex items-center justify-between">
        <h1 className="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-1.5">
          <span>Akun</span>
          <span className="text-amber-400">✨</span>
        </h1>

        <div className="flex items-center space-x-2">
          <button
            onClick={fetchAkun}
            className="p-1.5 rounded-md border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 shadow-2xs transition-colors cursor-pointer"
            title="Refresh"
          >
            <RefreshCw className="w-4 h-4 text-slate-500" />
          </button>

          <button
            onClick={handleOpenAdd}
            className="inline-flex items-center space-x-1.5 px-3.5 py-1.5 rounded-lg bg-[#5051F9] hover:bg-[#4344db] text-white text-xs font-bold shadow-2xs transition-colors cursor-pointer"
          >
            <Plus className="w-4 h-4" />
            <span>Tambah Akun</span>
          </button>
        </div>
      </div>

      {/* Info Alert Banner matching screenshot 9 */}
      <div className="p-3.5 rounded-lg bg-[#d1fae5]/70 border border-[#a7f3d0] text-[#065f46] text-xs font-medium">
        Silahkan atur persen pada tiap-tiap akun untuk implementasi pembagian hasil. Persen Kepala Toko tidak perlu diisi.
      </div>

      {/* DataTable Container */}
      <DataTable<AkunRow>
        columns={columns}
        data={data}
        isLoading={isLoading}
        title={`Semua Akun`}
        countBadge={data.length}
        selectable={true}
        selectedIds={selectedIds}
        onSelectChange={setSelectedIds}
        onBulkDelete={handleBulkDelete}
        idKey="id"
        defaultPerPage={10}
        perPageOptions={[10, 25, 50, 100]}
        emptyMessage="Belum ada data akun karyawan."
      />

      {/* Modal Tambah / Edit Akun */}
      <ModalAkun
        isOpen={isModalOpen}
        onClose={() => setIsModalOpen(false)}
        onSuccess={fetchAkun}
        initialData={editingItem}
      />
    </div>
  );
}

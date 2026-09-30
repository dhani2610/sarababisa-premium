'use client';

import React, { useEffect, useState } from 'react';
import Link from 'next/link';
import api from '@/lib/api';
import { useAuth } from '@/context/AuthContext';
import { DataTable, Column } from '@/components/ui/DataTable';
import Swal from 'sweetalert2';
import {
  User,
  Plus,
  MessageCircle,
  Phone,
  Edit2,
  Trash2,
  MapPin,
  Tag,
} from 'lucide-react';

interface PelangganRow {
  id: number;
  nama: string;
  nomor_hp?: string;
  no_hp?: string;
  alamat?: string;
  kategori?: string;
  created_at?: string;
}

export default function PelangganPage() {
  const { user } = useAuth();
  const [data, setData] = useState<PelangganRow[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [selectedIds, setSelectedIds] = useState<(number | string)[]>([]);

  const fetchPelanggan = async () => {
    setIsLoading(true);
    try {
      const res = await api.get('/pelanggan', {
        params: { per_page: 100 },
      });
      const list = Array.isArray(res.data) ? res.data : (res.data?.data || []);
      setData(list);
    } catch (err) {
      console.error('Failed to load pelanggan', err);
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchPelanggan();
  }, []);

  const openWhatsApp = (phone: string, name: string) => {
    if (!phone) {
      Swal.fire('Info', 'Nomor telepon tidak tersedia.', 'info');
      return;
    }
    const cleanPhone = phone.replace(/^0/, '62').replace(/\D/g, '');
    const msg = encodeURIComponent(`Halo Kak ${name || ''}, terima kasih telah mempercayakan perbaikan di Hairil iDevice.`);
    window.open(`https://wa.me/${cleanPhone}?text=${msg}`, '_blank');
  };

  const handleAddPelanggan = async () => {
    const { value: formValues } = await Swal.fire({
      title: 'Tambah Pelanggan Baru',
      html: `
        <div style="display:flex; flex-direction:column; gap:8px; text-align:left; font-size:12px;">
          <label><b>Nama Lengkap:</b></label>
          <input id="swal-nama" class="swal2-input" placeholder="Nama pelanggan">
          <label><b>Nomor WhatsApp / HP:</b></label>
          <input id="swal-hp" class="swal2-input" placeholder="08xxxxxxxxxx">
          <label><b>Alamat:</b></label>
          <textarea id="swal-alamat" class="swal2-textarea" placeholder="Alamat pelanggan"></textarea>
        </div>
      `,
      showCancelButton: true,
      confirmButtonText: 'Simpan Pelanggan',
      confirmButtonColor: '#5051F9',
      preConfirm: () => {
        const nama = (document.getElementById('swal-nama') as HTMLInputElement).value;
        const no_hp = (document.getElementById('swal-hp') as HTMLInputElement).value;
        const alamat = (document.getElementById('swal-alamat') as HTMLTextAreaElement).value;
        if (!nama) {
          Swal.showValidationMessage('Nama pelanggan wajib diisi');
          return false;
        }
        return { nama, no_hp, alamat };
      },
    });

    if (!formValues) return;

    try {
      await api.post('/pelanggan', formValues);
      Swal.fire('Berhasil!', 'Pelanggan baru berhasil ditambahkan.', 'success');
      fetchPelanggan();
    } catch (err: any) {
      Swal.fire('Gagal', err.response?.data?.detail || 'Gagal menyimpan data', 'error');
    }
  };

  const columns: Column<PelangganRow>[] = [
    {
      key: 'no',
      label: 'NO.',
      sortable: false,
      className: 'w-10 text-center text-slate-500 font-semibold',
      render: (_, index) => <span>{index}</span>,
    },
    {
      key: 'nama',
      label: 'NAMA PELANGGAN',
      className: 'font-bold text-slate-900',
      render: (item) => (
        <div className="flex items-center space-x-2">
          <div className="w-7 h-7 rounded-full bg-blue-50 text-blue-700 flex items-center justify-center font-bold text-xs uppercase">
            {item.nama ? item.nama.charAt(0) : 'P'}
          </div>
          <div>
            <p className="font-bold text-slate-900">{item.nama}</p>
            <span className="text-[10px] text-slate-400">ID #{item.id}</span>
          </div>
        </div>
      ),
    },
    {
      key: 'kategori',
      label: 'KATEGORI',
      className: 'whitespace-nowrap',
      render: (item) => (
        <span className="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
          {item.kategori || 'Umum'}
        </span>
      ),
    },
    {
      key: 'no_hp',
      label: 'NOMOR HP / WA',
      className: 'whitespace-nowrap font-mono text-slate-700',
      render: (item) => {
        const phone = item.no_hp || item.nomor_hp || '-';
        return (
          <div className="flex items-center space-x-2">
            <span>{phone}</span>
            {phone !== '-' && (
              <button
                onClick={() => openWhatsApp(phone, item.nama)}
                title="Hubungi WhatsApp"
                className="p-1 rounded text-emerald-600 hover:bg-emerald-50 transition-colors cursor-pointer"
              >
                <MessageCircle className="w-4 h-4 fill-emerald-100" />
              </button>
            )}
          </div>
        );
      },
    },
    {
      key: 'alamat',
      label: 'ALAMAT',
      className: 'text-slate-600 max-w-xs truncate',
      render: (item) => item.alamat || '-',
    },
    {
      key: 'created_at',
      label: 'TERDAFTAR',
      className: 'whitespace-nowrap text-slate-500 text-[11px]',
      render: (item) => (item.created_at ? new Date(item.created_at).toLocaleDateString('id-ID') : '-'),
    },
  ];

  return (
    <div className="space-y-4">
      {/* Title & Actions */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
          <h1 className="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-1.5">
            <span>Data Pelanggan</span>
            <span className="text-amber-400">✨</span>
          </h1>
          <p className="text-xs text-slate-500 mt-0.5">
            Database pelanggan servis & pembeli toko Hairil iDevice Group.
          </p>
        </div>

        <button
          onClick={handleAddPelanggan}
          className="inline-flex items-center space-x-1.5 px-3.5 py-1.5 rounded-lg bg-[#5051F9] hover:bg-[#4344db] text-white text-xs font-bold shadow-2xs transition-colors self-start sm:self-auto cursor-pointer"
        >
          <Plus className="w-4 h-4" />
          <span>Tambah Pelanggan</span>
        </button>
      </div>

      {/* DataTable */}
      <DataTable<PelangganRow>
        columns={columns}
        data={data}
        isLoading={isLoading}
        title="Daftar Pelanggan"
        countBadge={data.length}
        selectable={true}
        selectedIds={selectedIds}
        onSelectChange={setSelectedIds}
        idKey="id"
        defaultPerPage={10}
        perPageOptions={[10, 25, 50, 100]}
        emptyMessage="Belum ada data pelanggan."
      />
    </div>
  );
}

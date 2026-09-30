'use client';

import React, { useState, useEffect } from 'react';
import api from '@/lib/api';
import { DataTable, Column } from '@/components/ui/DataTable';
import Swal from 'sweetalert2';
import { Plus, Edit2, Trash2, RefreshCw, X, Save } from 'lucide-react';

export interface MasterRow {
  id: number;
  nama?: string;
  name?: string;
  merek_nama?: string;
  merek_id?: number;
  alamat?: string;
  nomor_hp?: string;
  [key: string]: any;
}

interface MasterDataViewProps {
  title: string;
  endpoint: string;
  itemLabel: string;
  hasMerekFilter?: boolean;
}

export const MasterDataView: React.FC<MasterDataViewProps> = ({
  title,
  endpoint,
  itemLabel,
  hasMerekFilter = false,
}) => {
  const [data, setData] = useState<MasterRow[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [selectedIds, setSelectedIds] = useState<(number | string)[]>([]);

  // Modal State
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingItem, setEditingItem] = useState<MasterRow | null>(null);
  const [formNama, setFormNama] = useState('');
  const [formMerekId, setFormMerekId] = useState<number | ''>('');
  const [formAlamat, setFormAlamat] = useState('');
  const [formHp, setFormHp] = useState('');
  const [merekOptions, setMerekOptions] = useState<any[]>([]);

  const fetchData = async () => {
    setIsLoading(true);
    try {
      const res = await api.get(endpoint);
      const list = Array.isArray(res.data) ? res.data : res.data?.data || [];
      setData(list);

      if (hasMerekFilter) {
        const resMerek = await api.get('/master/merek');
        setMerekOptions(Array.isArray(resMerek.data) ? resMerek.data : resMerek.data?.data || []);
      }
    } catch (err) {
      console.error(`Failed to load ${title}`, err);
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchData();
  }, [endpoint]);

  const handleOpenAdd = () => {
    setEditingItem(null);
    setFormNama('');
    setFormMerekId('');
    setFormAlamat('');
    setFormHp('');
    setIsModalOpen(true);
  };

  const handleOpenEdit = (item: MasterRow) => {
    setEditingItem(item);
    setFormNama(item.nama || item.name || '');
    setFormMerekId(item.merek_id || '');
    setFormAlamat(item.alamat || '');
    setFormHp(item.nomor_hp || '');
    setIsModalOpen(true);
  };

  const handleSave = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!formNama.trim()) {
      Swal.fire('Validasi', `Nama ${itemLabel} wajib diisi.`, 'warning');
      return;
    }

    try {
      const payload: any = { nama: formNama };
      if (hasMerekFilter && formMerekId) payload.merek_id = formMerekId;
      if (formAlamat) payload.alamat = formAlamat;
      if (formHp) payload.nomor_hp = formHp;

      if (editingItem) {
        await api.put(`${endpoint}/${editingItem.id}`, payload);
        Swal.fire('Berhasil', `${itemLabel} berhasil diperbarui.`, 'success');
      } else {
        await api.post(endpoint, payload);
        Swal.fire('Berhasil', `${itemLabel} berhasil ditambahkan.`, 'success');
      }

      setIsModalOpen(false);
      fetchData();
    } catch (err: any) {
      Swal.fire('Gagal', err.response?.data?.detail || 'Terjadi kesalahan sistem', 'error');
    }
  };

  const handleDelete = async (id: number) => {
    const confirm = await Swal.fire({
      title: `Hapus ${itemLabel}?`,
      text: 'Data yang dihapus tidak dapat dipulihkan.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Ya, Hapus',
      cancelButtonText: 'Batal',
      confirmButtonColor: '#ef4444',
    });
    if (!confirm.isConfirmed) return;

    try {
      await api.delete(`${endpoint}/${id}`);
      Swal.fire('Terhapus', `${itemLabel} berhasil dihapus.`, 'success');
      fetchData();
    } catch (err: any) {
      Swal.fire('Gagal', err.response?.data?.detail || 'Gagal menghapus data', 'error');
    }
  };

  const handleBulkDelete = async (ids: (number | string)[]) => {
    const confirm = await Swal.fire({
      title: `Hapus ${ids.length} ${itemLabel}?`,
      text: 'Semua item yang dipilih akan dihapus.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: `Ya, Hapus (${ids.length})`,
      cancelButtonText: 'Batal',
      confirmButtonColor: '#ef4444',
    });
    if (!confirm.isConfirmed) return;

    try {
      await Promise.all(ids.map((id) => api.delete(`${endpoint}/${id}`)));
      Swal.fire('Terhapus', `${ids.length} data berhasil dihapus.`, 'success');
      setSelectedIds([]);
      fetchData();
    } catch (err: any) {
      Swal.fire('Gagal', 'Terjadi kesalahan sistem saat menghapus data.', 'error');
    }
  };

  const columns: Column<MasterRow>[] = [
    {
      key: 'no',
      label: 'NO.',
      sortable: false,
      className: 'w-12 text-center text-slate-500 font-semibold',
      render: (_, idx) => <span>{idx}</span>,
    },
    {
      key: 'nama',
      label: `NAMA ${itemLabel.toUpperCase()}`,
      className: 'font-bold text-slate-800 uppercase text-xs',
      render: (item) => item.nama || item.name || '-',
    },
    ...(hasMerekFilter
      ? [
          {
            key: 'merek',
            label: 'MEREK',
            className: 'font-semibold text-slate-700 uppercase text-xs',
            render: (item: MasterRow) => item.merek_nama || '-',
          },
        ]
      : []),
    ...(endpoint.includes('cabang')
      ? [
          {
            key: 'alamat',
            label: 'ALAMAT',
            className: 'text-slate-600 text-xs',
            render: (item: MasterRow) => item.alamat || '-',
          },
          {
            key: 'nomor_hp',
            label: 'NO HP',
            className: 'text-slate-600 text-xs',
            render: (item: MasterRow) => item.nomor_hp || '-',
          },
        ]
      : []),
    {
      key: 'aksi',
      label: 'AKSI',
      sortable: false,
      className: 'w-20 text-center',
      render: (item) => (
        <div className="flex items-center justify-center space-x-1.5">
          <button
            onClick={() => handleOpenEdit(item)}
            className="p-1 rounded text-blue-600 hover:bg-blue-50 transition-colors cursor-pointer"
            title="Edit"
          >
            <Edit2 className="w-3.5 h-3.5" />
          </button>
          <button
            onClick={() => handleDelete(item.id)}
            className="p-1 rounded text-rose-500 hover:bg-rose-50 transition-colors cursor-pointer"
            title="Hapus"
          >
            <Trash2 className="w-3.5 h-3.5" />
          </button>
        </div>
      ),
    },
  ];

  return (
    <div className="space-y-4">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h1 className="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-1.5">
          <span>{title}</span>
          <span className="text-amber-400">✨</span>
        </h1>

        <div className="flex items-center space-x-2">
          <button
            onClick={fetchData}
            className="p-1.5 rounded-md border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 shadow-2xs transition-colors cursor-pointer"
            title="Refresh"
          >
            <RefreshCw className="w-4 h-4 text-slate-500" />
          </button>

          <button
            onClick={handleOpenAdd}
            className="inline-flex items-center space-x-1.5 px-3.5 py-1.5 rounded-md bg-[#5051F9] hover:bg-[#4344db] text-white text-xs font-bold shadow-2xs transition-colors cursor-pointer"
          >
            <Plus className="w-4 h-4" />
            <span>Tambah {itemLabel}</span>
          </button>
        </div>
      </div>

      <DataTable<MasterRow>
        columns={columns}
        data={data}
        isLoading={isLoading}
        title={`Semua Data ${title} (${data.length})`}
        selectable={true}
        selectedIds={selectedIds}
        onSelectChange={setSelectedIds}
        onBulkDelete={handleBulkDelete}
        idKey="id"
        defaultPerPage={10}
        perPageOptions={[10, 25, 50, 100]}
        emptyMessage={`Tidak ada data ${itemLabel}.`}
      />

      {/* Add / Edit Modal */}
      {isModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-2xs">
          <div className="bg-white rounded-xl shadow-2xl border border-slate-200 w-full max-w-md overflow-hidden animate-in fade-in zoom-in-95 duration-150">
            <div className="flex items-center justify-between px-5 py-3.5 border-b border-slate-100 bg-slate-50/80">
              <h3 className="font-bold text-slate-800 text-sm">
                {editingItem ? `Edit ${itemLabel}` : `Tambah ${itemLabel}`}
              </h3>
              <button
                onClick={() => setIsModalOpen(false)}
                className="text-slate-400 hover:text-slate-600 cursor-pointer"
              >
                <X className="w-4 h-4" />
              </button>
            </div>

            <form onSubmit={handleSave} className="p-5 space-y-4 text-xs">
              <div>
                <label className="block font-bold text-slate-700 mb-1">
                  Nama {itemLabel} <span className="text-rose-500">*</span>
                </label>
                <input
                  type="text"
                  value={formNama}
                  onChange={(e) => setFormNama(e.target.value)}
                  placeholder={`Masukkan nama ${itemLabel.toLowerCase()}...`}
                  required
                  className="w-full px-3 py-1.5 border border-slate-300 rounded-md focus:border-[#5051F9] text-xs font-semibold text-slate-800"
                />
              </div>

              {hasMerekFilter && (
                <div>
                  <label className="block font-bold text-slate-700 mb-1">Pilih Merek</label>
                  <select
                    value={formMerekId}
                    onChange={(e) => setFormMerekId(Number(e.target.value) || '')}
                    className="w-full px-3 py-1.5 border border-slate-300 rounded-md focus:border-[#5051F9] bg-white text-xs cursor-pointer"
                  >
                    <option value="">Pilih Merek...</option>
                    {merekOptions.map((m) => (
                      <option key={m.id} value={m.id}>
                        {m.nama || m.name}
                      </option>
                    ))}
                  </select>
                </div>
              )}

              {endpoint.includes('cabang') && (
                <>
                  <div>
                    <label className="block font-bold text-slate-700 mb-1">Alamat</label>
                    <input
                      type="text"
                      value={formAlamat}
                      onChange={(e) => setFormAlamat(e.target.value)}
                      placeholder="Alamat cabang..."
                      className="w-full px-3 py-1.5 border border-slate-300 rounded-md focus:border-[#5051F9] text-xs"
                    />
                  </div>
                  <div>
                    <label className="block font-bold text-slate-700 mb-1">Nomor HP</label>
                    <input
                      type="text"
                      value={formHp}
                      onChange={(e) => setFormHp(e.target.value)}
                      placeholder="Nomor telepon cabang..."
                      className="w-full px-3 py-1.5 border border-slate-300 rounded-md focus:border-[#5051F9] text-xs"
                    />
                  </div>
                </>
              )}

              <div className="flex items-center justify-end space-x-2 pt-2 border-t border-slate-100">
                <button
                  type="button"
                  onClick={() => setIsModalOpen(false)}
                  className="px-3 py-1.5 rounded-md border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  className="inline-flex items-center space-x-1.5 px-4 py-1.5 rounded-md bg-[#5051F9] hover:bg-[#4344db] text-white font-bold"
                >
                  <Save className="w-3.5 h-3.5" />
                  <span>Simpan</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
export default MasterDataView;

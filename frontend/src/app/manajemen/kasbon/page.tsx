'use client';

import React, { useState, useEffect } from 'react';
import api from '@/lib/api';
import Swal from 'sweetalert2';
import {
  Wallet,
  Plus,
  CheckCircle,
  XCircle,
  Trash2,
  Calendar,
  User,
} from 'lucide-react';

export default function KasbonPage() {
  const [kasbonList, setKasbonList] = useState<any[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [showModal, setShowModal] = useState(false);

  // Form input
  const [jumlah, setJumlah] = useState<number>(0);
  const [keterangan, setKeterangan] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);

  const fetchKasbon = async () => {
    setIsLoading(true);
    try {
      const res = await api.get('/manajemen/kasbon');
      setKasbonList(res.data.data || res.data || []);
    } catch (err) {
      console.error('Error fetching kasbon', err);
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchKasbon();
  }, []);

  const handleApprove = async (id: number) => {
    try {
      await api.patch(`/manajemen/kasbon/${id}/setujui`);
      Swal.fire('Disetujui', 'Pengajuan kasbon berhasil disetujui.', 'success');
      fetchKasbon();
    } catch (err: any) {
      Swal.fire('Gagal', err.response?.data?.detail || 'Terjadi kesalahan', 'error');
    }
  };

  const handleReject = async (id: number) => {
    try {
      await api.patch(`/manajemen/kasbon/${id}/tolak`);
      Swal.fire('Ditolak', 'Pengajuan kasbon ditolak.', 'info');
      fetchKasbon();
    } catch (err: any) {
      Swal.fire('Gagal', err.response?.data?.detail || 'Terjadi kesalahan', 'error');
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!jumlah || jumlah <= 0) {
      Swal.fire('Validasi', 'Nominal kasbon wajib diisi.', 'warning');
      return;
    }

    setIsSubmitting(true);
    try {
      await api.post('/manajemen/kasbon', {
        jumlah: Number(jumlah),
        keterangan,
      });
      Swal.fire('Diajukan', 'Pengajuan kasbon Anda berhasil dikirim ke atasan.', 'success');
      setShowModal(false);
      setJumlah(0);
      setKeterangan('');
      fetchKasbon();
    } catch (err: any) {
      Swal.fire('Gagal', err.response?.data?.detail || 'Terjadi kesalahan', 'error');
    } finally {
      setIsSubmitting(false);
    }
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
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
          <h1 className="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
            <Wallet className="w-6 h-6 text-blue-600" />
            <span>Kasbon & Pinjaman Karyawan</span>
          </h1>
          <p className="text-xs text-slate-500 mt-0.5">
            Pencatatan kasbon staf, riwayat persetujuan dan status pelunasan payroll.
          </p>
        </div>

        <button
          onClick={() => setShowModal(true)}
          className="inline-flex items-center space-x-1.5 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition-all cursor-pointer self-start sm:self-auto"
        >
          <Plus className="w-4 h-4" />
          <span>Ajukan Kasbon Baru</span>
        </button>
      </div>

      {/* Main Table */}
      <div className="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs">
            <thead className="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase text-[10px] tracking-wider">
              <tr>
                <th className="py-3 px-4">Tgl Pengajuan</th>
                <th className="py-3 px-4">Nama Karyawan</th>
                <th className="py-3 px-4 text-right">Jumlah Nominal</th>
                <th className="py-3 px-4">Keterangan</th>
                <th className="py-3 px-4 text-center">Status</th>
                <th className="py-3 px-4 text-center">Aksi Persetujuan</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {isLoading ? (
                <tr>
                  <td colSpan={6} className="py-12 text-center text-slate-400">
                    Memuat data kasbon...
                  </td>
                </tr>
              ) : kasbonList.length === 0 ? (
                <tr>
                  <td colSpan={6} className="py-12 text-center text-slate-400">
                    Belum ada riwayat pengajuan kasbon.
                  </td>
                </tr>
              ) : (
                kasbonList.map((item) => (
                  <tr key={item.id} className="hover:bg-slate-50">
                    <td className="py-3 px-4 text-slate-600">
                      {item.tgl_kasbon || (item.created_at ? new Date(item.created_at).toLocaleDateString('id-ID') : '-')}
                    </td>
                    <td className="py-3 px-4 font-bold text-slate-800">
                      {item.karyawan_nama || `Staf #${item.user_id}`}
                    </td>
                    <td className="py-3 px-4 text-right font-black text-slate-900">
                      {formatRupiah(item.jumlah)}
                    </td>
                    <td className="py-3 px-4 text-slate-500">{item.keterangan || '-'}</td>
                    <td className="py-3 px-4 text-center">
                      <span
                        className={`inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold ${
                          item.status === 'disetujui'
                            ? 'bg-emerald-100 text-emerald-800'
                            : item.status === 'ditolak'
                            ? 'bg-rose-100 text-rose-800'
                            : 'bg-amber-100 text-amber-800'
                        }`}
                      >
                        {item.status ? item.status.toUpperCase() : 'MENUNGGU'}
                      </span>
                    </td>
                    <td className="py-3 px-4 text-center">
                      {item.status === 'menunggu' && (
                        <div className="inline-flex items-center space-x-1">
                          <button
                            onClick={() => handleApprove(item.id)}
                            title="Setujui"
                            className="p-1 rounded-lg text-emerald-600 hover:bg-emerald-50 cursor-pointer"
                          >
                            <CheckCircle className="w-4 h-4" />
                          </button>
                          <button
                            onClick={() => handleReject(item.id)}
                            title="Tolak"
                            className="p-1 rounded-lg text-rose-600 hover:bg-rose-50 cursor-pointer"
                          >
                            <XCircle className="w-4 h-4" />
                          </button>
                        </div>
                      )}
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* Modal Ajukan Kasbon */}
      {showModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4">
          <div className="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div className="flex items-center justify-between pb-3 border-b border-slate-100">
              <h3 className="text-sm font-bold text-slate-800">Ajukan Kasbon Staf</h3>
              <button onClick={() => setShowModal(false)} className="text-slate-400 hover:text-slate-600">
                &times;
              </button>
            </div>

            <form onSubmit={handleSubmit} className="space-y-3 text-xs">
              <div>
                <label className="font-semibold text-slate-700 block mb-1">Nominal Pinjaman (Rp) *</label>
                <input
                  type="number"
                  required
                  value={jumlah}
                  onChange={(e) => setJumlah(Number(e.target.value))}
                  placeholder="Contoh: 500000"
                  className="w-full px-3 py-2 border border-slate-200 rounded-xl focus:outline-hidden font-bold"
                />
              </div>

              <div>
                <label className="font-semibold text-slate-700 block mb-1">Keperluan / Keterangan</label>
                <textarea
                  rows={2}
                  value={keterangan}
                  onChange={(e) => setKeterangan(e.target.value)}
                  placeholder="Alasan pengajuan kasbon"
                  className="w-full px-3 py-2 border border-slate-200 rounded-xl focus:outline-hidden"
                />
              </div>

              <div className="pt-2 flex justify-end space-x-2">
                <button
                  type="button"
                  onClick={() => setShowModal(false)}
                  className="px-4 py-2 border border-slate-200 rounded-xl hover:bg-slate-50 font-bold"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  disabled={isSubmitting}
                  className="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold disabled:opacity-50"
                >
                  {isSubmitting ? 'Mengirim...' : 'Kirim Pengajuan'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}

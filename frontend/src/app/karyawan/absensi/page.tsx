'use client';

import React, { useState, useEffect } from 'react';
import api from '@/lib/api';
import Swal from 'sweetalert2';
import {
  Users,
  Calendar,
  CheckCircle,
  Clock,
  Plus,
  Search,
  Filter,
} from 'lucide-react';

export default function AbsensiPage() {
  const [absensiList, setAbsensiList] = useState<any[]>([]);
  const [karyawanList, setKaryawanList] = useState<any[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [showModal, setShowModal] = useState(false);

  // Form input
  const [userId, setUserId] = useState<number | ''>('');
  const [status, setStatus] = useState('hadir');
  const [keterangan, setKeterangan] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);

  const fetchAbsensi = async () => {
    setIsLoading(true);
    try {
      const [resAbs, resUsers] = await Promise.all([
        api.get('/master/absensi'),
        api.get('/akun'),
      ]);
      setAbsensiList(resAbs.data.data || resAbs.data || []);
      setKaryawanList(resUsers.data.data || resUsers.data || []);
    } catch (err) {
      console.error('Error fetching absensi', err);
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchAbsensi();
  }, []);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!userId) {
      Swal.fire('Validasi', 'Pilih nama karyawan.', 'warning');
      return;
    }

    setIsSubmitting(true);
    try {
      await api.post('/master/absensi', {
        user_id: Number(userId),
        status,
        keterangan,
      });
      Swal.fire('Berhasil', 'Data absensi berhasil dicatat.', 'success');
      setShowModal(false);
      fetchAbsensi();
    } catch (err: any) {
      Swal.fire('Gagal', err.response?.data?.detail || 'Terjadi kesalahan', 'error');
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <div className="space-y-5">
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
          <h1 className="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
            <Users className="w-6 h-6 text-blue-600" />
            <span>Absensi & Kehadiran Karyawan</span>
          </h1>
          <p className="text-xs text-slate-500 mt-0.5">
            Pencatatan jam kerja, absensi harian, sakit, izin dan alfa.
          </p>
        </div>

        <button
          onClick={() => setShowModal(true)}
          className="inline-flex items-center space-x-1.5 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition-all cursor-pointer self-start sm:self-auto"
        >
          <Plus className="w-4 h-4" />
          <span>Input Absensi Harian</span>
        </button>
      </div>

      {/* Main Table */}
      <div className="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs">
            <thead className="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase text-[10px] tracking-wider">
              <tr>
                <th className="py-3 px-4">Tanggal</th>
                <th className="py-3 px-4">Nama Karyawan</th>
                <th className="py-3 px-4">Jam Masuk / Pulang</th>
                <th className="py-3 px-4 text-center">Status</th>
                <th className="py-3 px-4">Keterangan</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {isLoading ? (
                <tr>
                  <td colSpan={5} className="py-12 text-center text-slate-400">
                    Memuat data absensi...
                  </td>
                </tr>
              ) : absensiList.length === 0 ? (
                <tr>
                  <td colSpan={5} className="py-12 text-center text-slate-400">
                    Belum ada data absensi tercatat.
                  </td>
                </tr>
              ) : (
                absensiList.map((item) => (
                  <tr key={item.id} className="hover:bg-slate-50">
                    <td className="py-3 px-4 font-semibold text-slate-800">
                      {item.tanggal || (item.created_at ? new Date(item.created_at).toLocaleDateString('id-ID') : '-')}
                    </td>
                    <td className="py-3 px-4 font-bold text-slate-800">
                      {item.nama || item.karyawan_nama || `User #${item.user_id}`}
                    </td>
                    <td className="py-3 px-4 text-slate-600">
                      <span className="flex items-center gap-1 font-mono text-[11px]">
                        <Clock className="w-3.5 h-3.5 text-slate-400" />
                        {item.jam_masuk || '08:00'} - {item.jam_pulang || '17:00'}
                      </span>
                    </td>
                    <td className="py-3 px-4 text-center">
                      <span
                        className={`inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold ${
                          item.status === 'hadir'
                            ? 'bg-emerald-100 text-emerald-800'
                            : item.status === 'izin'
                            ? 'bg-amber-100 text-amber-800'
                            : 'bg-rose-100 text-rose-800'
                        }`}
                      >
                        {item.status ? item.status.toUpperCase() : 'HADIR'}
                      </span>
                    </td>
                    <td className="py-3 px-4 text-slate-500">{item.keterangan || '-'}</td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* Modal Input Absensi */}
      {showModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4">
          <div className="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div className="flex items-center justify-between pb-3 border-b border-slate-100">
              <h3 className="text-sm font-bold text-slate-800">Input Absensi Karyawan</h3>
              <button onClick={() => setShowModal(false)} className="text-slate-400 hover:text-slate-600">
                &times;
              </button>
            </div>

            <form onSubmit={handleSubmit} className="space-y-3 text-xs">
              <div>
                <label className="font-semibold text-slate-700 block mb-1">Nama Karyawan *</label>
                <select
                  required
                  value={userId}
                  onChange={(e) => setUserId(e.target.value ? Number(e.target.value) : '')}
                  className="w-full px-3 py-2 border border-slate-200 rounded-xl focus:outline-hidden bg-white"
                >
                  <option value="">-- Pilih Karyawan --</option>
                  {karyawanList.map((k) => (
                    <option key={k.id} value={k.id}>
                      {k.nama || k.name || k.username} ({k.role})
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label className="font-semibold text-slate-700 block mb-1">Status Kehadiran *</label>
                <select
                  value={status}
                  onChange={(e) => setStatus(e.target.value)}
                  className="w-full px-3 py-2 border border-slate-200 rounded-xl focus:outline-hidden bg-white"
                >
                  <option value="hadir">Hadir Tepat Waktu</option>
                  <option value="terlambat">Terlambat</option>
                  <option value="izin">Izin</option>
                  <option value="sakit">Sakit</option>
                  <option value="alfa">Tanpa Keterangan (Alfa)</option>
                </select>
              </div>

              <div>
                <label className="font-semibold text-slate-700 block mb-1">Keterangan / Catatan</label>
                <input
                  type="text"
                  value={keterangan}
                  onChange={(e) => setKeterangan(e.target.value)}
                  placeholder="Opsional"
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
                  {isSubmitting ? 'Menyimpan...' : 'Simpan Absensi'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}

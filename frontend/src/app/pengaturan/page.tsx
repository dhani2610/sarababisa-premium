'use client';

import React, { useState, useEffect } from 'react';
import api from '@/lib/api';
import { useAuth } from '@/context/AuthContext';
import Swal from 'sweetalert2';
import {
  Settings,
  Store,
  Building2,
  FileText,
  Save,
  Clock,
  Percent,
  Check,
} from 'lucide-react';

export default function PengaturanPage() {
  const { user, activeCabangNama } = useAuth();

  const [activeTab, setActiveTab] = useState<'toko' | 'syarat' | 'cabang'>('toko');
  const [storeData, setStoreData] = useState<any>({
    nama_toko: '',
    alamat_toko: '',
    nomor_hp_toko: '',
    bank: '',
    rekening: '',
    pemilik_rekening: '',
    ppn: '11',
  });

  const [terms, setTerms] = useState<any>({
    isi_terima: '',
    isi_pengambilan: '',
    isi_penjualan: '',
  });

  const [cabangs, setCabangs] = useState<any[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [isSaving, setIsSaving] = useState(false);

  useEffect(() => {
    const fetchSettings = async () => {
      setIsLoading(true);
      try {
        const [resStore, resTerms, resCabang] = await Promise.all([
          api.get('/pengaturan/profil'),
          api.get('/pengaturan/syarat-ketentuan'),
          api.get('/cabang'),
        ]);

        if (resStore.data) setStoreData(resStore.data);
        if (resTerms.data) setTerms(resTerms.data);
        setCabangs(Array.isArray(resCabang.data) ? resCabang.data : resCabang.data.data || []);
      } catch (err) {
        console.error('Error fetching settings', err);
      } finally {
        setIsLoading(false);
      }
    };
    fetchSettings();
  }, []);

  const handleSaveStore = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsSaving(true);
    try {
      await api.put('/pengaturan/profil', storeData);
      Swal.fire('Tersimpan', 'Profil toko cabang berhasil diperbarui.', 'success');
    } catch (err: any) {
      Swal.fire('Gagal', err.response?.data?.detail || 'Terjadi kesalahan', 'error');
    } finally {
      setIsSaving(false);
    }
  };

  const handleSaveTerms = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsSaving(true);
    try {
      await api.post('/pengaturan/syarat-ketentuan', terms);
      Swal.fire('Tersimpan', 'Syarat dan ketentuan nota berhasil diperbarui.', 'success');
    } catch (err: any) {
      Swal.fire('Gagal', err.response?.data?.detail || 'Terjadi kesalahan', 'error');
    } finally {
      setIsSaving(false);
    }
  };

  return (
    <div className="space-y-6 max-w-4xl mx-auto">
      {/* Header */}
      <div>
        <h1 className="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
          <Settings className="w-6 h-6 text-blue-600" />
          <span>Pengaturan Sistem & Toko</span>
        </h1>
        <p className="text-xs text-slate-500 mt-0.5">
          Konfigurasi identitas cabang {activeCabangNama}, rekening pembayaran, dan klausul syarat nota resmi.
        </p>
      </div>

      {/* Tabs */}
      <div className="flex items-center gap-1.5 border-b border-slate-200">
        {[
          { id: 'toko', label: 'Profil Toko & Bank', icon: Store },
          { id: 'syarat', label: 'Syarat & Ketentuan Nota', icon: FileText },
          { id: 'cabang', label: 'Daftar Semua Cabang', icon: Building2 },
        ].map((t) => {
          const isActive = activeTab === t.id;
          return (
            <button
              key={t.id}
              onClick={() => setActiveTab(t.id as any)}
              className={`flex items-center space-x-2 px-4 py-2.5 text-xs font-bold rounded-t-lg transition-all border-b-2 cursor-pointer ${
                isActive
                  ? 'border-blue-600 text-blue-600 bg-blue-50/50'
                  : 'border-transparent text-slate-500 hover:text-slate-800'
              }`}
            >
              <t.icon className="w-4 h-4" />
              <span>{t.label}</span>
            </button>
          );
        })}
      </div>

      {/* Tab 1: Profil Toko Form */}
      {activeTab === 'toko' && (
        <form onSubmit={handleSaveStore} className="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <div>
              <label className="block font-semibold text-slate-700 mb-1">Nama Toko / Outlet *</label>
              <input
                type="text"
                required
                value={storeData.nama_toko || ''}
                onChange={(e) => setStoreData({ ...storeData, nama_toko: e.target.value })}
                className="w-full px-3 py-2 border border-slate-200 rounded-xl focus:outline-hidden"
              />
            </div>

            <div>
              <label className="block font-semibold text-slate-700 mb-1">No. WhatsApp / Telepon Toko</label>
              <input
                type="text"
                value={storeData.nomor_hp_toko || ''}
                onChange={(e) => setStoreData({ ...storeData, nomor_hp_toko: e.target.value })}
                className="w-full px-3 py-2 border border-slate-200 rounded-xl focus:outline-hidden"
              />
            </div>

            <div className="sm:col-span-2">
              <label className="block font-semibold text-slate-700 mb-1">Alamat Lengkap Toko</label>
              <textarea
                rows={2}
                value={storeData.alamat_toko || ''}
                onChange={(e) => setStoreData({ ...storeData, alamat_toko: e.target.value })}
                className="w-full px-3 py-2 border border-slate-200 rounded-xl focus:outline-hidden"
              />
            </div>

            <div>
              <label className="block font-semibold text-slate-700 mb-1">Nama Bank Pembayaran</label>
              <input
                type="text"
                value={storeData.bank || ''}
                onChange={(e) => setStoreData({ ...storeData, bank: e.target.value })}
                placeholder="BCA / Mandiri / BRI"
                className="w-full px-3 py-2 border border-slate-200 rounded-xl focus:outline-hidden"
              />
            </div>

            <div>
              <label className="block font-semibold text-slate-700 mb-1">Nomor Rekening</label>
              <input
                type="text"
                value={storeData.rekening || ''}
                onChange={(e) => setStoreData({ ...storeData, rekening: e.target.value })}
                className="w-full px-3 py-2 border border-slate-200 rounded-xl focus:outline-hidden font-mono"
              />
            </div>

            <div>
              <label className="block font-semibold text-slate-700 mb-1">Pemilik Rekening (A.N)</label>
              <input
                type="text"
                value={storeData.pemilik_rekening || ''}
                onChange={(e) => setStoreData({ ...storeData, pemilik_rekening: e.target.value })}
                className="w-full px-3 py-2 border border-slate-200 rounded-xl focus:outline-hidden"
              />
            </div>

            <div>
              <label className="block font-semibold text-slate-700 mb-1">PPN Standar (%)</label>
              <input
                type="number"
                value={storeData.ppn || 11}
                onChange={(e) => setStoreData({ ...storeData, ppn: e.target.value })}
                className="w-full px-3 py-2 border border-slate-200 rounded-xl focus:outline-hidden"
              />
            </div>
          </div>

          <div className="pt-2 flex justify-end">
            <button
              type="submit"
              disabled={isSaving}
              className="inline-flex items-center space-x-2 px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-md cursor-pointer disabled:opacity-50"
            >
              <Save className="w-4 h-4" />
              <span>{isSaving ? 'Menyimpan...' : 'Simpan Profil Toko'}</span>
            </button>
          </div>
        </form>
      )}

      {/* Tab 2: Syarat & Ketentuan Nota */}
      {activeTab === 'syarat' && (
        <form onSubmit={handleSaveTerms} className="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4 text-xs">
          <div>
            <label className="block font-bold text-slate-800 mb-1">
              Klausul Nota Penerimaan Servis (Tanda Terima Masuk)
            </label>
            <p className="text-[11px] text-slate-400 mb-2">
              Klausul yang dicetak di bagian bawah nota masuk servis (PDF Termal & Inkjet).
            </p>
            <textarea
              rows={4}
              value={terms.isi_terima || ''}
              onChange={(e) => setTerms({ ...terms, isi_terima: e.target.value })}
              className="w-full p-3 border border-slate-200 rounded-xl focus:outline-hidden font-mono text-xs"
            />
          </div>

          <div>
            <label className="block font-bold text-slate-800 mb-1">
              Klausul Nota Pengambilan Servis (Garansi & Penyerahan)
            </label>
            <p className="text-[11px] text-slate-400 mb-2">
              Klausul garansi dan syarat segel unit yang dicetak saat unit diambil.
            </p>
            <textarea
              rows={4}
              value={terms.isi_pengambilan || ''}
              onChange={(e) => setTerms({ ...terms, isi_pengambilan: e.target.value })}
              className="w-full p-3 border border-slate-200 rounded-xl focus:outline-hidden font-mono text-xs"
            />
          </div>

          <div className="pt-2 flex justify-end">
            <button
              type="submit"
              disabled={isSaving}
              className="inline-flex items-center space-x-2 px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-md cursor-pointer disabled:opacity-50"
            >
              <Save className="w-4 h-4" />
              <span>{isSaving ? 'Menyimpan...' : 'Simpan Syarat & Ketentuan'}</span>
            </button>
          </div>
        </form>
      )}

      {/* Tab 3: Daftar Semua Cabang */}
      {activeTab === 'cabang' && (
        <div className="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
          <div className="overflow-x-auto">
            <table className="w-full text-left text-xs">
              <thead className="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase text-[10px]">
                <tr>
                  <th className="py-3 px-4">ID Cabang</th>
                  <th className="py-3 px-4">Nama Cabang</th>
                  <th className="py-3 px-4">Status</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100">
                {cabangs.map((c) => (
                  <tr key={c.id} className="hover:bg-slate-50">
                    <td className="py-3 px-4 font-mono font-bold text-blue-700">#{c.id}</td>
                    <td className="py-3 px-4 font-bold text-slate-800">{c.nama}</td>
                    <td className="py-3 px-4">
                      <span className="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                        Aktif
                      </span>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}
    </div>
  );
}

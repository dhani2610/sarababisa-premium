'use client';

import React, { useState, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import api from '@/lib/api';
import Swal from 'sweetalert2';
import {
  Wrench,
  ArrowLeft,
  Save,
  User,
  Smartphone,
  KeyRound,
  DollarSign,
  Info,
} from 'lucide-react';

export default function TambahServisPage() {
  const router = useRouter();

  const [pelangganList, setPelangganList] = useState<any[]>([]);
  const [brands, setBrands] = useState<any[]>([]);
  const [teknisiList, setTeknisiList] = useState<any[]>([]);

  // Form Fields
  const [pelangganId, setPelangganId] = useState<number | ''>('');
  const [namaPelanggan, setNamaPelanggan] = useState('');
  const [noHpPelanggan, setNoHpPelanggan] = useState('');
  const [merekId, setMerekId] = useState<number | ''>('');
  const [namaBarang, setNamaBarang] = useState('');
  const [imei, setImei] = useState('');
  const [warna, setWarna] = useState('');
  const [kerusakan, setKerusakan] = useState('');
  const [kelengkapan, setKelengkapan] = useState('Unit Only');
  const [pin, setPin] = useState('');
  const [pola, setPola] = useState('');
  const [estimasiBiaya, setEstimasiBiaya] = useState<number>(0);
  const [dp, setDp] = useState<number>(0);
  const [teknisiId, setTeknisiId] = useState<number | ''>('');
  const [catatan, setCatatan] = useState('');

  const [isSubmitting, setIsSubmitting] = useState(false);

  useEffect(() => {
    const fetchData = async () => {
      try {
        const [resPelanggan, resBrands, resTeknisi] = await Promise.all([
          api.get('/pelanggan', { params: { per_page: 50 } }),
          api.get('/master/master-merek'),
          api.get('/akun', { params: { role: 'Teknisi' } }),
        ]);
        setPelangganList(resPelanggan.data.data || resPelanggan.data || []);
        setBrands(resBrands.data.data || resBrands.data || []);
        setTeknisiList(resTeknisi.data.data || resTeknisi.data || []);
      } catch (err) {
        console.error('Error loading initial data', err);
      }
    };
    fetchData();
  }, []);

  const handleSelectPelanggan = (pId: string) => {
    if (!pId) {
      setPelangganId('');
      return;
    }
    const selected = pelangganList.find((p) => p.id === Number(pId));
    if (selected) {
      setPelangganId(selected.id);
      setNamaPelanggan(selected.nama);
      setNoHpPelanggan(selected.no_hp || '');
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!namaPelanggan || !namaBarang || !kerusakan) {
      Swal.fire('Validasi', 'Nama pelanggan, nama barang, dan kerusakan wajib diisi.', 'warning');
      return;
    }

    setIsSubmitting(true);
    try {
      const payload = {
        pelanggan_id: pelangganId || null,
        nama_pelanggan: namaPelanggan,
        nomor_hp: noHpPelanggan,
        merek_id: merekId || null,
        nama_barang: namaBarang,
        imei,
        warna,
        kerusakan,
        kelengkapan,
        pin,
        pola,
        estimasi_biaya: Number(estimasiBiaya),
        dp: Number(dp),
        teknisi_id: teknisiId || null,
        catatan,
      };

      const res = await api.post('/servis/transaksi-servis', payload);

      Swal.fire({
        icon: 'success',
        title: 'Servis Diterima!',
        text: `Nomor Nota: ${res.data.no_nota || 'Tersimpan'}. Unit siap dikerjakan.`,
      });

      router.push('/servis');
    } catch (err: any) {
      Swal.fire('Gagal Menyimpan', err.response?.data?.detail || 'Terjadi kesalahan sistem', 'error');
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <div className="space-y-6 max-w-4xl mx-auto">
      {/* Header */}
      <div className="flex items-center justify-between pb-4 border-b border-slate-200">
        <div className="flex items-center space-x-3">
          <button
            onClick={() => router.back()}
            className="p-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer"
          >
            <ArrowLeft className="w-4 h-4 text-slate-600" />
          </button>
          <div>
            <h1 className="text-xl font-black text-slate-900 tracking-tight">Terima Servis Baru</h1>
            <p className="text-xs text-slate-500">
              Input data unit masuk, keluhan kerusakan, estimasi biaya dan penugasan teknisi.
            </p>
          </div>
        </div>
      </div>

      <form onSubmit={handleSubmit} className="space-y-6">
        {/* Section 1: Data Pelanggan */}
        <div className="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-4">
          <h2 className="text-xs font-bold text-blue-700 uppercase tracking-wider flex items-center gap-1.5 pb-2 border-b border-slate-100">
            <User className="w-4 h-4 text-blue-600" />
            <span>1. Data Pemilik / Pelanggan</span>
          </h2>

          <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
            <div>
              <label className="block font-semibold text-slate-700 mb-1">Cari Pelanggan Terdaftar</label>
              <select
                value={pelangganId}
                onChange={(e) => handleSelectPelanggan(e.target.value)}
                className="w-full px-3 py-2 border border-slate-200 rounded-xl focus:border-blue-500 focus:outline-hidden bg-white"
              >
                <option value="">-- Pelanggan Baru / Ketik Manual --</option>
                {pelangganList.map((p) => (
                  <option key={p.id} value={p.id}>
                    {p.nama} ({p.no_hp || '-'})
                  </option>
                ))}
              </select>
            </div>

            <div>
              <label className="block font-semibold text-slate-700 mb-1">Nama Pelanggan *</label>
              <input
                type="text"
                required
                value={namaPelanggan}
                onChange={(e) => setNamaPelanggan(e.target.value)}
                placeholder="Contoh: Budi Santoso"
                className="w-full px-3 py-2 border border-slate-200 rounded-xl focus:border-blue-500 focus:outline-hidden"
              />
            </div>

            <div>
              <label className="block font-semibold text-slate-700 mb-1">No. WhatsApp / HP</label>
              <input
                type="text"
                value={noHpPelanggan}
                onChange={(e) => setNoHpPelanggan(e.target.value)}
                placeholder="08123456789"
                className="w-full px-3 py-2 border border-slate-200 rounded-xl focus:border-blue-500 focus:outline-hidden"
              />
            </div>
          </div>
        </div>

        {/* Section 2: Data Unit & Kerusakan */}
        <div className="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-4">
          <h2 className="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5 pb-2 border-b border-slate-100">
            <Smartphone className="w-4 h-4 text-blue-600" />
            <span>2. Data Perangkat & Keluhan Kerusakan</span>
          </h2>

          <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
            <div>
              <label className="block font-semibold text-slate-700 mb-1">Merek Perangkat</label>
              <select
                value={merekId}
                onChange={(e) => setMerekId(e.target.value ? Number(e.target.value) : '')}
                className="w-full px-3 py-2 border border-slate-200 rounded-xl focus:border-blue-500 focus:outline-hidden bg-white"
              >
                <option value="">-- Pilih Merek --</option>
                {brands.map((b) => (
                  <option key={b.id} value={b.id}>
                    {b.nama || b.name}
                  </option>
                ))}
              </select>
            </div>

            <div>
              <label className="block font-semibold text-slate-700 mb-1">Tipe / Model Barang *</label>
              <input
                type="text"
                required
                value={namaBarang}
                onChange={(e) => setNamaBarang(e.target.value)}
                placeholder="Contoh: iPhone 11 64GB Black"
                className="w-full px-3 py-2 border border-slate-200 rounded-xl focus:border-blue-500 focus:outline-hidden"
              />
            </div>

            <div>
              <label className="block font-semibold text-slate-700 mb-1">Nomor IMEI / Seri</label>
              <input
                type="text"
                value={imei}
                onChange={(e) => setImei(e.target.value)}
                placeholder="15 digit IMEI"
                className="w-full px-3 py-2 border border-slate-200 rounded-xl focus:border-blue-500 focus:outline-hidden"
              />
            </div>

            <div className="sm:col-span-2">
              <label className="block font-semibold text-slate-700 mb-1">Keluhan / Kerusakan *</label>
              <input
                type="text"
                required
                value={kerusakan}
                onChange={(e) => setKerusakan(e.target.value)}
                placeholder="Contoh: LCD pecah garis hijau, baterai drop 75%"
                className="w-full px-3 py-2 border border-slate-200 rounded-xl focus:border-blue-500 focus:outline-hidden"
              />
            </div>

            <div>
              <label className="block font-semibold text-slate-700 mb-1">Kelengkapan Unit</label>
              <input
                type="text"
                value={kelengkapan}
                onChange={(e) => setKelengkapan(e.target.value)}
                placeholder="Unit, Sim Tray, Box, Case"
                className="w-full px-3 py-2 border border-slate-200 rounded-xl focus:border-blue-500 focus:outline-hidden"
              />
            </div>
          </div>
        </div>

        {/* Section 3: Kunci Unit & Biaya */}
        <div className="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-4">
          <h2 className="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5 pb-2 border-b border-slate-100">
            <DollarSign className="w-4 h-4 text-emerald-600" />
            <span>3. Kunci Layar, Biaya & Teknisi Penanggung Jawab</span>
          </h2>

          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
            <div>
              <label className="block font-semibold text-slate-700 mb-1">PIN / Password Layar</label>
              <input
                type="text"
                value={pin}
                onChange={(e) => setPin(e.target.value)}
                placeholder="123456"
                className="w-full px-3 py-2 border border-slate-200 rounded-xl focus:border-blue-500 focus:outline-hidden font-mono"
              />
            </div>

            <div>
              <label className="block font-semibold text-slate-700 mb-1">Pola Kunci (Jika Ada)</label>
              <input
                type="text"
                value={pola}
                onChange={(e) => setPola(e.target.value)}
                placeholder="Contoh: L-Pattern"
                className="w-full px-3 py-2 border border-slate-200 rounded-xl focus:border-blue-500 focus:outline-hidden"
              />
            </div>

            <div>
              <label className="block font-semibold text-slate-700 mb-1">Estimasi Biaya (Rp)</label>
              <input
                type="number"
                value={estimasiBiaya}
                onChange={(e) => setEstimasiBiaya(Number(e.target.value))}
                className="w-full px-3 py-2 border border-slate-200 rounded-xl focus:border-blue-500 focus:outline-hidden font-bold"
              />
            </div>

            <div>
              <label className="block font-semibold text-slate-700 mb-1">Uang Muka / DP (Rp)</label>
              <input
                type="number"
                value={dp}
                onChange={(e) => setDp(Number(e.target.value))}
                className="w-full px-3 py-2 border border-slate-200 rounded-xl focus:border-blue-500 focus:outline-hidden"
              />
            </div>

            <div className="sm:col-span-2">
              <label className="block font-semibold text-slate-700 mb-1">Tugaskan ke Teknisi</label>
              <select
                value={teknisiId}
                onChange={(e) => setTeknisiId(e.target.value ? Number(e.target.value) : '')}
                className="w-full px-3 py-2 border border-slate-200 rounded-xl focus:border-blue-500 focus:outline-hidden bg-white"
              >
                <option value="">-- Pilih Teknisi (Opsional) --</option>
                {teknisiList.map((t) => (
                  <option key={t.id} value={t.id}>
                    {t.nama || t.username}
                  </option>
                ))}
              </select>
            </div>

            <div className="sm:col-span-2">
              <label className="block font-semibold text-slate-700 mb-1">Catatan Tambahan</label>
              <input
                type="text"
                value={catatan}
                onChange={(e) => setCatatan(e.target.value)}
                placeholder="Misal: jangan direset data"
                className="w-full px-3 py-2 border border-slate-200 rounded-xl focus:border-blue-500 focus:outline-hidden"
              />
            </div>
          </div>
        </div>

        {/* Submit Actions */}
        <div className="flex items-center justify-end space-x-3 pt-2">
          <button
            type="button"
            onClick={() => router.back()}
            className="px-5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs cursor-pointer"
          >
            Batal
          </button>
          <button
            type="submit"
            disabled={isSubmitting}
            className="inline-flex items-center space-x-2 px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-500/25 transition-all cursor-pointer disabled:opacity-50"
          >
            <Save className="w-4 h-4" />
            <span>{isSubmitting ? 'Menyimpan...' : 'Terima Unit Servis'}</span>
          </button>
        </div>
      </form>
    </div>
  );
}

'use client';

import React, { useState, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import api, { API_BASE_URL } from '@/lib/api';
import Swal from 'sweetalert2';
import {
  ArrowLeftRight,
  ArrowLeft,
  Plus,
  Trash2,
  Printer,
  Save,
  Smartphone,
  DollarSign,
  User,
  Receipt,
} from 'lucide-react';

export default function TukarTambahPage() {
  const router = useRouter();

  const [tukarTambahList, setTukarTambahList] = useState<any[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [showModalNew, setShowModalNew] = useState(false);

  // Form Fields
  const [pelangganNama, setPelangganNama] = useState('');
  const [pelangganHp, setPelangganHp] = useState('');
  const [produkKeluarNama, setProdukKeluarNama] = useState('');
  const [hargaProdukKeluar, setHargaProdukKeluar] = useState<number>(0);
  const [produkMasukNama, setProdukMasukNama] = useState('');
  const [imeiMasuk, setImeiMasuk] = useState('');
  const [nilaiTukarMasuk, setNilaiTukarMasuk] = useState<number>(0);
  const [uangBayar, setUangBayar] = useState<number>(0);
  const [catatan, setCatatan] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);

  const selisihBayar = Math.max(0, hargaProdukKeluar - nilaiTukarMasuk);

  const fetchTukarTambah = async () => {
    setIsLoading(true);
    try {
      const res = await api.get('/produk/tukar-tambah');
      setTukarTambahList(res.data.data || res.data || []);
    } catch (err) {
      console.error('Error fetching tukar tambah', err);
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchTukarTambah();
  }, []);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!pelangganNama || !produkKeluarNama || !produkMasukNama) {
      Swal.fire('Validasi', 'Nama pelanggan, produk keluar dan produk masuk wajib diisi.', 'warning');
      return;
    }

    setIsSubmitting(true);
    try {
      const payload = {
        pelanggan_nama: pelangganNama,
        pelanggan_hp: pelangganHp,
        produk_keluar_nama: produkKeluarNama,
        harga_produk_keluar: Number(hargaProdukKeluar),
        produk_masuk_nama: produkMasukNama,
        imei_masuk: imeiMasuk,
        nilai_tukar: Number(nilaiTukarMasuk),
        selisih_bayar: selisihBayar,
        pay: Number(uangBayar),
        catatan,
      };

      await api.post('/produk/tukar-tambah', payload);
      Swal.fire('Berhasil!', 'Transaksi tukar tambah berhasil disimpan.', 'success');
      setShowModalNew(false);
      fetchTukarTambah();
    } catch (err: any) {
      Swal.fire('Gagal Menyimpan', err.response?.data?.detail || 'Terjadi kesalahan sistem', 'error');
    } finally {
      setIsSubmitting(false);
    }
  };

  const handlePrintNota = (id: number) => {
    const token = localStorage.getItem('token');
    const url = `${API_BASE_URL}/produk/tukar-tambah/${id}/cetak/inkjet?token=${token}`;
    window.open(url, '_blank');
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
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div className="flex items-center space-x-3">
          <button
            onClick={() => router.back()}
            className="p-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer"
          >
            <ArrowLeft className="w-4 h-4 text-slate-600" />
          </button>
          <div>
            <h1 className="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
              <ArrowLeftRight className="w-6 h-6 text-blue-600" />
              <span>Tukar Tambah Handphone</span>
            </h1>
            <p className="text-xs text-slate-500 mt-0.5">
              Pencatatan unit baru keluar vs penerimaan unit second dari pelanggan.
            </p>
          </div>
        </div>

        <button
          onClick={() => setShowModalNew(true)}
          className="inline-flex items-center space-x-1.5 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition-all cursor-pointer self-start sm:self-auto"
        >
          <Plus className="w-4 h-4" />
          <span>Transaksi Tukar Tambah Baru</span>
        </button>
      </div>

      {/* Main Table */}
      <div className="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs">
            <thead className="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase text-[10px] tracking-wider">
              <tr>
                <th className="py-3 px-4">No Nota / Tgl</th>
                <th className="py-3 px-4">Pelanggan</th>
                <th className="py-3 px-4">Unit Keluar (Baru)</th>
                <th className="py-3 px-4">Unit Diterima (Second)</th>
                <th className="py-3 px-4 text-right">Nilai Tukar</th>
                <th className="py-3 px-4 text-right">Selisih Bayar</th>
                <th className="py-3 px-4 text-center">Cetak Nota</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {isLoading ? (
                <tr>
                  <td colSpan={7} className="py-12 text-center text-slate-400">
                    Memuat data tukar tambah...
                  </td>
                </tr>
              ) : tukarTambahList.length === 0 ? (
                <tr>
                  <td colSpan={7} className="py-12 text-center text-slate-400">
                    Belum ada riwayat transaksi tukar tambah.
                  </td>
                </tr>
              ) : (
                tukarTambahList.map((item) => (
                  <tr key={item.id} className="hover:bg-slate-50">
                    <td className="py-3 px-4 font-bold text-blue-700">
                      {item.no_transaksi || `TT-${item.id}`}
                    </td>
                    <td className="py-3 px-4 font-medium text-slate-800">
                      {item.pelanggan_nama || '-'}
                    </td>
                    <td className="py-3 px-4">
                      <p className="font-bold text-slate-800">{item.produk_keluar_nama || '-'}</p>
                      <span className="text-[10px] text-slate-400">{formatRupiah(item.harga_produk_keluar)}</span>
                    </td>
                    <td className="py-3 px-4">
                      <p className="font-bold text-slate-800">{item.produk_masuk_nama || '-'}</p>
                      {item.imei_masuk && (
                        <span className="text-[10px] text-slate-400 font-mono block">
                          IMEI: {item.imei_masuk}
                        </span>
                      )}
                    </td>
                    <td className="py-3 px-4 text-right font-semibold text-emerald-600">
                      {formatRupiah(item.nilai_tukar)}
                    </td>
                    <td className="py-3 px-4 text-right font-black text-slate-900">
                      {formatRupiah(item.selisih_bayar)}
                    </td>
                    <td className="py-3 px-4 text-center">
                      <button
                        onClick={() => handlePrintNota(item.id)}
                        className="p-1.5 rounded-lg text-slate-600 hover:text-blue-600 hover:bg-blue-50 cursor-pointer"
                        title="Cetak Nota A4"
                      >
                        <Receipt className="w-4 h-4" />
                      </button>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* Modal Transaksi Baru */}
      {showModalNew && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4">
          <div className="bg-white rounded-2xl max-w-xl w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
            <div className="flex items-center justify-between pb-3 border-b border-slate-100">
              <div className="flex items-center space-x-2">
                <ArrowLeftRight className="w-5 h-5 text-blue-600" />
                <h3 className="text-sm font-bold text-slate-800">Formulir Tukar Tambah Baru</h3>
              </div>
              <button
                onClick={() => setShowModalNew(false)}
                className="text-slate-400 hover:text-slate-600 text-lg leading-none cursor-pointer"
              >
                &times;
              </button>
            </div>

            <form onSubmit={handleSubmit} className="space-y-4 text-xs">
              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="font-semibold text-slate-700 block mb-1">Nama Pelanggan *</label>
                  <input
                    type="text"
                    required
                    value={pelangganNama}
                    onChange={(e) => setPelangganNama(e.target.value)}
                    placeholder="Nama pelanggan"
                    className="w-full px-3 py-2 border border-slate-200 rounded-xl focus:outline-hidden"
                  />
                </div>
                <div>
                  <label className="font-semibold text-slate-700 block mb-1">No. WhatsApp</label>
                  <input
                    type="text"
                    value={pelangganHp}
                    onChange={(e) => setPelangganHp(e.target.value)}
                    placeholder="08..."
                    className="w-full px-3 py-2 border border-slate-200 rounded-xl focus:outline-hidden"
                  />
                </div>
              </div>

              {/* Unit Baru Keluar */}
              <div className="p-3.5 bg-blue-50/50 rounded-xl border border-blue-100 space-y-2">
                <span className="text-[10px] font-bold text-blue-700 uppercase tracking-wider block">
                  1. Unit Baru / Yang Diberikan ke Pelanggan
                </span>
                <div className="grid grid-cols-2 gap-3">
                  <div>
                    <label className="text-slate-600 block mb-0.5">Nama Unit Baru *</label>
                    <input
                      type="text"
                      required
                      value={produkKeluarNama}
                      onChange={(e) => setProdukKeluarNama(e.target.value)}
                      placeholder="iPhone 14 128GB"
                      className="w-full px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg focus:outline-hidden"
                    />
                  </div>
                  <div>
                    <label className="text-slate-600 block mb-0.5">Harga Jual Unit (Rp) *</label>
                    <input
                      type="number"
                      required
                      value={hargaProdukKeluar}
                      onChange={(e) => setHargaProdukKeluar(Number(e.target.value))}
                      className="w-full px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg focus:outline-hidden font-bold"
                    />
                  </div>
                </div>
              </div>

              {/* Unit Second Diterima */}
              <div className="p-3.5 bg-emerald-50/50 rounded-xl border border-emerald-100 space-y-2">
                <span className="text-[10px] font-bold text-emerald-700 uppercase tracking-wider block">
                  2. Unit Second Yang Diterima Dari Pelanggan
                </span>
                <div className="grid grid-cols-3 gap-2">
                  <div className="col-span-2">
                    <label className="text-slate-600 block mb-0.5">Nama Unit Second *</label>
                    <input
                      type="text"
                      required
                      value={produkMasukNama}
                      onChange={(e) => setProdukMasukNama(e.target.value)}
                      placeholder="iPhone 11 64GB Black"
                      className="w-full px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg focus:outline-hidden"
                    />
                  </div>
                  <div>
                    <label className="text-slate-600 block mb-0.5">IMEI Unit</label>
                    <input
                      type="text"
                      value={imeiMasuk}
                      onChange={(e) => setImeiMasuk(e.target.value)}
                      placeholder="15 digit"
                      className="w-full px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg focus:outline-hidden font-mono"
                    />
                  </div>
                </div>
                <div>
                  <label className="text-slate-600 block mb-0.5">Nilai Taksiran Tukar (Rp) *</label>
                  <input
                    type="number"
                    required
                    value={nilaiTukarMasuk}
                    onChange={(e) => setNilaiTukarMasuk(Number(e.target.value))}
                    className="w-full px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg focus:outline-hidden font-bold text-emerald-700"
                  />
                </div>
              </div>

              {/* Calculation Summary */}
              <div className="bg-slate-50 p-3 rounded-xl border border-slate-200 space-y-1">
                <div className="flex justify-between font-bold text-slate-800">
                  <span>Selisih Yang Wajib Dibayar Pelanggan:</span>
                  <span className="text-blue-700 text-sm">{formatRupiah(selisihBayar)}</span>
                </div>
                <div className="flex justify-between items-center pt-2">
                  <span className="font-semibold text-slate-600">Uang Diterima (Rp):</span>
                  <input
                    type="number"
                    value={uangBayar}
                    onChange={(e) => setUangBayar(Number(e.target.value))}
                    className="w-40 px-2 py-1 bg-white border border-slate-200 rounded-lg font-bold text-right"
                  />
                </div>
              </div>

              <div className="pt-2 flex justify-end space-x-2">
                <button
                  type="button"
                  onClick={() => setShowModalNew(false)}
                  className="px-4 py-2 border border-slate-200 rounded-xl hover:bg-slate-50 font-bold cursor-pointer"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  disabled={isSubmitting}
                  className="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold cursor-pointer disabled:opacity-50"
                >
                  {isSubmitting ? 'Menyimpan...' : 'Simpan Transaksi'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}

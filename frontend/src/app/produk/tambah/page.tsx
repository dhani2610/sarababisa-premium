'use client';

import React, { useState, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import api from '@/lib/api';
import Swal from 'sweetalert2';
import {
  Smartphone,
  Save,
  ArrowLeft,
  Upload,
  Info,
  Layers,
  Tag,
  DollarSign,
  Package,
} from 'lucide-react';

export default function TambahProdukPage() {
  const router = useRouter();

  const [tipe, setTipe] = useState<'handphone' | 'sparepart' | 'aksesoris' | 'tools'>('handphone');
  const [dynamicFieldsMeta, setDynamicFieldsMeta] = useState<any[]>([]);
  const [categories, setCategories] = useState<any[]>([]);
  const [subCategories, setSubCategories] = useState<any[]>([]);
  const [brands, setBrands] = useState<any[]>([]);

  // Base Form Fields
  const [nama, setNama] = useState('');
  const [kode, setKode] = useState('');
  const [barcode, setBarcode] = useState('');
  const [hargaBeli, setHargaBeli] = useState(0);
  const [hargaJual, setHargaJual] = useState(0);
  const [stok, setStok] = useState(1);
  const [stokMinimum, setStokMinimum] = useState(2);
  const [kategoriId, setKategoriId] = useState<number | ''>('');
  const [subKategoriId, setSubKategoriId] = useState<number | ''>('');
  const [merekId, setMerekId] = useState<number | ''>('');

  // Dynamic values state dictionary
  const [dynamicValues, setDynamicValues] = useState<{ [key: string]: any }>({
    kondisi: 'baru',
    kualitas: 'Original',
    satuan: 'Pcs',
  });

  const [isSubmitting, setIsSubmitting] = useState(false);

  // Fetch Category Fields Metadata whenever tipe changes
  useEffect(() => {
    const fetchFieldsMeta = async () => {
      try {
        const res = await api.get('/produk/kategori-fields', { params: { tipe } });
        setDynamicFieldsMeta(res.data.fields || []);
      } catch (err) {
        console.error('Error fetching dynamic fields meta', err);
      }
    };
    fetchFieldsMeta();
  }, [tipe]);

  // Fetch Master Data (Kategori, Brands)
  useEffect(() => {
    const fetchMaster = async () => {
      try {
        const [resKat, resBrands] = await Promise.all([
          api.get('/master/kategori-produk'),
          api.get('/master/master-merek'),
        ]);
        setCategories(Array.isArray(resKat.data) ? resKat.data : resKat.data.data || []);
        setBrands(Array.isArray(resBrands.data) ? resBrands.data : resBrands.data.data || []);
      } catch (err) {
        console.error('Error loading master data', err);
      }
    };
    fetchMaster();
  }, []);

  const handleDynamicChange = (key: string, value: any) => {
    setDynamicValues((prev) => ({ ...prev, [key]: value }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!nama || !hargaJual) {
      Swal.fire('Validasi', 'Nama produk dan Harga Jual wajib diisi', 'warning');
      return;
    }

    setIsSubmitting(true);
    try {
      const payload = {
        nama,
        kode: kode || `PRD-${Date.now().toString().slice(-6)}`,
        barcode,
        tipe,
        kategori_id: kategoriId || null,
        sub_kategori_id: subKategoriId || null,
        merek_id: merekId || null,
        harga_beli: Number(hargaBeli),
        harga_jual: Number(hargaJual),
        stok: Number(stok),
        stok_minimum: Number(stokMinimum),
        ...dynamicValues,
      };

      await api.post('/produk/item', payload);

      Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: `Produk kategori ${tipe} berhasil disimpan beserta spesifikasinya.`,
      });

      router.push('/produk');
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
            <h1 className="text-xl font-black text-slate-900 tracking-tight">Tambah Produk Baru</h1>
            <p className="text-xs text-slate-500">
              Form input dinamis otomatis menyesuaikan kategori produk yang dipilih.
            </p>
          </div>
        </div>
      </div>

      <form onSubmit={handleSubmit} className="space-y-6">
        {/* Category Type Selector Pill */}
        <div className="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-3">
          <label className="text-xs font-bold text-slate-700 block">1. Pilih Kategori Produk Utama</label>
          <div className="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
            {[
              { id: 'handphone', label: 'Handphone / Unit' },
              { id: 'sparepart', label: 'Sparepart / LCD' },
              { id: 'aksesoris', label: 'Aksesoris' },
              { id: 'tools', label: 'Tools / Peralatan' },
            ].map((k) => (
              <button
                key={k.id}
                type="button"
                onClick={() => setTipe(k.id as any)}
                className={`py-3 px-3 rounded-xl border text-xs font-bold transition-all flex flex-col items-center justify-center space-y-1 cursor-pointer ${
                  tipe === k.id
                    ? 'border-blue-600 bg-blue-50/70 text-blue-700 shadow-xs ring-1 ring-blue-500'
                    : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'
                }`}
              >
                <span>{k.label}</span>
              </button>
            ))}
          </div>
        </div>

        {/* Base Information */}
        <div className="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-4">
          <h2 className="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5 pb-2 border-b border-slate-100">
            <Info className="w-4 h-4 text-blue-600" />
            <span>2. Informasi Dasar Produk</span>
          </h2>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <div>
              <label className="block font-semibold text-slate-700 mb-1">Nama Produk / Barang *</label>
              <input
                type="text"
                required
                value={nama}
                onChange={(e) => setNama(e.target.value)}
                placeholder="Contoh: iPhone 13 Pro 128GB Sierra Blue"
                className="w-full px-3 py-2 border border-slate-200 rounded-xl focus:border-blue-500 focus:outline-hidden"
              />
            </div>

            <div>
              <label className="block font-semibold text-slate-700 mb-1">Merek / Brand</label>
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
              <label className="block font-semibold text-slate-700 mb-1">Kode Produk / SKU</label>
              <input
                type="text"
                value={kode}
                onChange={(e) => setKode(e.target.value)}
                placeholder="Auto jika kosong"
                className="w-full px-3 py-2 border border-slate-200 rounded-xl focus:border-blue-500 focus:outline-hidden"
              />
            </div>

            <div>
              <label className="block font-semibold text-slate-700 mb-1">Barcode / EAN</label>
              <input
                type="text"
                value={barcode}
                onChange={(e) => setBarcode(e.target.value)}
                placeholder="Scan barcode"
                className="w-full px-3 py-2 border border-slate-200 rounded-xl focus:border-blue-500 focus:outline-hidden"
              />
            </div>

            <div>
              <label className="block font-semibold text-slate-700 mb-1">Harga Beli / Modal (Rp)</label>
              <input
                type="number"
                value={hargaBeli}
                onChange={(e) => setHargaBeli(Number(e.target.value))}
                className="w-full px-3 py-2 border border-slate-200 rounded-xl focus:border-blue-500 focus:outline-hidden"
              />
            </div>

            <div>
              <label className="block font-semibold text-slate-700 mb-1">Harga Jual Toko (Rp) *</label>
              <input
                type="number"
                required
                value={hargaJual}
                onChange={(e) => setHargaJual(Number(e.target.value))}
                className="w-full px-3 py-2 border border-slate-200 rounded-xl focus:border-blue-500 focus:outline-hidden font-bold text-slate-900"
              />
            </div>

            <div>
              <label className="block font-semibold text-slate-700 mb-1">Jumlah Stok Awal</label>
              <input
                type="number"
                value={stok}
                onChange={(e) => setStok(Number(e.target.value))}
                className="w-full px-3 py-2 border border-slate-200 rounded-xl focus:border-blue-500 focus:outline-hidden"
              />
            </div>

            <div>
              <label className="block font-semibold text-slate-700 mb-1">Stok Minimum (Alert)</label>
              <input
                type="number"
                value={stokMinimum}
                onChange={(e) => setStokMinimum(Number(e.target.value))}
                className="w-full px-3 py-2 border border-slate-200 rounded-xl focus:border-blue-500 focus:outline-hidden"
              />
            </div>
          </div>
        </div>

        {/* Dynamic Category-Specific Fields */}
        <div className="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-4">
          <div className="flex items-center justify-between pb-2 border-b border-slate-100">
            <h2 className="text-xs font-bold text-blue-700 uppercase tracking-wider flex items-center gap-1.5">
              <Layers className="w-4 h-4 text-blue-600" />
              <span>3. Spesifikasi Khusus Kategori ({tipe.toUpperCase()})</span>
            </h2>
            <span className="text-[10px] text-slate-400 font-semibold bg-slate-100 px-2 py-0.5 rounded-full">
              Field Dinamis
            </span>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            {dynamicFieldsMeta.map((field) => (
              <div key={field.key}>
                <label className="block font-semibold text-slate-700 mb-1">
                  {field.label} {field.required && '*'}
                </label>

                {field.type === 'select' ? (
                  <select
                    value={dynamicValues[field.key] || ''}
                    onChange={(e) => handleDynamicChange(field.key, e.target.value)}
                    className="w-full px-3 py-2 border border-slate-200 rounded-xl focus:border-blue-500 focus:outline-hidden bg-white"
                  >
                    <option value="">-- Pilih {field.label} --</option>
                    {field.options?.map((opt: string) => (
                      <option key={opt} value={opt}>
                        {opt}
                      </option>
                    ))}
                  </select>
                ) : (
                  <input
                    type={field.type}
                    value={dynamicValues[field.key] || ''}
                    onChange={(e) => handleDynamicChange(field.key, e.target.value)}
                    placeholder={field.placeholder || `Masukkan ${field.label}`}
                    className="w-full px-3 py-2 border border-slate-200 rounded-xl focus:border-blue-500 focus:outline-hidden"
                  />
                )}
              </div>
            ))}
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
            <span>{isSubmitting ? 'Menyimpan...' : 'Simpan Produk'}</span>
          </button>
        </div>
      </form>
    </div>
  );
}

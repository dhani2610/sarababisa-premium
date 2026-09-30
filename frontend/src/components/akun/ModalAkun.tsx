'use client';

import React, { useState, useEffect } from 'react';
import api from '@/lib/api';
import Swal from 'sweetalert2';
import { X, Save, Upload, FileText, Check } from 'lucide-react';

interface CabangOption {
  id: number;
  nama: string;
}

interface ModalAkunProps {
  isOpen: boolean;
  onClose: () => void;
  onSuccess: () => void;
  initialData?: any | null;
}

export const ModalAkun: React.FC<ModalAkunProps> = ({
  isOpen,
  onClose,
  onSuccess,
  initialData = null,
}) => {
  const [cabangs, setCabangs] = useState<CabangOption[]>([]);
  const [isSubmitting, setIsSubmitting] = useState(false);

  // Form Fields matching Screenshot 9
  const [nama, setNama] = useState('');
  const [email, setEmail] = useState('');
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [nik, setNik] = useState('');
  const [hp, setHp] = useState('');
  const [alamat, setAlamat] = useState('');
  const [selectedCabangs, setSelectedCabangs] = useState<number[]>([]);
  const [role, setRole] = useState('Kepala Toko');

  // Specific to Teknisi
  const [bagianTeknisi, setBagianTeknisi] = useState('Teknisi Hardware');
  const [persenHardware, setPersenHardware] = useState<number | string>(20);
  const [persenInterface, setPersenInterface] = useState<number | string>(0);

  // File uploads
  const [ktpFile, setKtpFile] = useState<File | null>(null);
  const [kkFile, setKkFile] = useState<File | null>(null);
  const [ijasahFile, setIjasahFile] = useState<File | null>(null);
  const [dokumenLainFile, setDokumenLainFile] = useState<File | null>(null);

  // Load Branches
  useEffect(() => {
    if (!isOpen) return;

    const loadCabangs = async () => {
      try {
        const res = await api.get('/master/cabang');
        const list = Array.isArray(res.data) ? res.data : res.data?.data || [];
        setCabangs(list);
      } catch (err) {
        // Fallback default branches from screenshot
        setCabangs([
          { id: 1, nama: 'HI BDL PUSAT' },
          { id: 2, nama: 'HI PRINGSEWU' },
          { id: 3, nama: 'HI AMT' },
          { id: 4, nama: 'HI BJM' },
        ]);
      }
    };

    loadCabangs();

    if (initialData) {
      setNama(initialData.nama || initialData.name || '');
      setEmail(initialData.email || '');
      setUsername(initialData.username || '');
      setPassword('');
      setNik(initialData.nik || '');
      setHp(initialData.no_hp || initialData.nomor_hp || '');
      setAlamat(initialData.alamat || '');
      setRole(
        initialData.role === 'KepalaToko'
          ? 'Kepala Toko'
          : initialData.role === 'AdminToko'
          ? 'Admin Toko'
          : initialData.role || 'Kepala Toko'
      );
      setBagianTeknisi(initialData.bagian_teknisi || 'Teknisi Hardware');
      setPersenHardware(initialData.persen_hardware || initialData.persen || 20);
      setPersenInterface(initialData.persen_bonus_interface || 0);

      const cIds = initialData.assigned_cabangs || [initialData.cabang_id || 2];
      setSelectedCabangs(cIds);
    } else {
      setNama('');
      setEmail('');
      setUsername('');
      setPassword('');
      setNik('');
      setHp('');
      setAlamat('');
      setSelectedCabangs([2]); // HI PRINGSEWU default checked
      setRole('Kepala Toko');
      setBagianTeknisi('Teknisi Hardware');
      setPersenHardware(20);
      setPersenInterface(0);
    }
  }, [isOpen, initialData]);

  if (!isOpen) return null;

  const toggleCabang = (id: number) => {
    setSelectedCabangs((prev) =>
      prev.includes(id) ? prev.filter((c) => c !== id) : [...prev, id]
    );
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!nama.trim() || !username.trim() || !email.trim()) {
      Swal.fire('Validasi', 'Nama, Email, dan Username wajib diisi.', 'warning');
      return;
    }
    if (!initialData && !password.trim()) {
      Swal.fire('Validasi', 'Kata sandi wajib diisi untuk akun baru.', 'warning');
      return;
    }
    if (selectedCabangs.length === 0) {
      Swal.fire('Validasi', 'Pilih minimal 1 penugasan cabang.', 'warning');
      return;
    }

    setIsSubmitting(true);
    try {
      const payload: any = {
        nama,
        email,
        username,
        nik,
        nomor_hp: hp,
        alamat,
        role: role.replace(/\s+/g, ''),
        cabang_id: selectedCabangs[0],
        assigned_cabangs: selectedCabangs,
        bagian_teknisi: role === 'Teknisi' ? bagianTeknisi : undefined,
        persen: role === 'Teknisi' ? Number(persenHardware) : undefined,
        persen_bonus_interface: role === 'Teknisi' ? Number(persenInterface) : undefined,
      };

      if (password.trim()) {
        payload.password = password;
      }

      if (initialData) {
        await api.put(`/akun/${initialData.id}`, payload);
        Swal.fire('Berhasil!', 'Data akun berhasil diperbarui.', 'success');
      } else {
        await api.post('/akun', payload);
        Swal.fire('Berhasil!', 'Akun baru berhasil ditambahkan.', 'success');
      }

      onSuccess();
      onClose();
    } catch (err: any) {
      Swal.fire('Gagal', err.response?.data?.detail || 'Terjadi kesalahan sistem', 'error');
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5 bg-slate-900/60 backdrop-blur-2xs overflow-y-auto">
      <div className="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden my-6">
        {/* Header matching screenshot 9 */}
        <div className="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/60">
          <h2 className="text-base font-bold text-slate-900">
            {initialData ? 'Edit Akun' : 'Tambah Akun'}
          </h2>
          <button
            onClick={onClose}
            className="p-1 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-200/50 cursor-pointer"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        {/* Form Body matching screenshot 9 */}
        <form
          onSubmit={handleSubmit}
          className="p-6 space-y-4 max-h-[82vh] overflow-y-auto text-xs text-slate-700"
        >
          {/* Nama Lengkap */}
          <div>
            <label className="block font-bold text-slate-800 mb-1">
              Nama Lengkap <span className="text-rose-500">*</span>
            </label>
            <input
              type="text"
              value={nama}
              onChange={(e) => setNama(e.target.value)}
              required
              className="w-full px-3 py-1.5 border border-slate-300 rounded-md focus:border-[#5051F9] text-xs font-medium"
            />
          </div>

          {/* Email */}
          <div>
            <label className="block font-bold text-slate-800 mb-1">
              Email <span className="text-rose-500">*</span>
            </label>
            <input
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required
              className="w-full px-3 py-1.5 border border-slate-300 rounded-md focus:border-[#5051F9] text-xs font-medium"
            />
          </div>

          {/* Nama Pengguna */}
          <div>
            <label className="block font-bold text-slate-800 mb-1">
              Nama Pengguna <span className="text-rose-500">*</span>
            </label>
            <input
              type="text"
              value={username}
              onChange={(e) => setUsername(e.target.value)}
              required
              className="w-full px-3 py-1.5 border border-slate-300 rounded-md focus:border-[#5051F9] text-xs font-medium"
            />
          </div>

          {/* Kata Sandi */}
          <div>
            <label className="block font-bold text-slate-800 mb-1">
              Kata Sandi {!initialData && <span className="text-rose-500">*</span>}
            </label>
            <input
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              placeholder={initialData ? 'Kosongkan jika tidak diubah' : 'Kata sandi akun'}
              required={!initialData}
              className="w-full px-3 py-1.5 border border-slate-300 rounded-md focus:border-[#5051F9] text-xs font-medium"
            />
          </div>

          {/* NIK */}
          <div>
            <label className="block font-bold text-slate-800 mb-1">
              NIK <span className="text-rose-500">*</span>
            </label>
            <input
              type="text"
              value={nik}
              onChange={(e) => setNik(e.target.value)}
              required
              className="w-full px-3 py-1.5 border border-slate-300 rounded-md focus:border-[#5051F9] text-xs font-medium"
            />
          </div>

          {/* HP */}
          <div>
            <label className="block font-bold text-slate-800 mb-1">
              HP <span className="text-rose-500">*</span>
            </label>
            <input
              type="text"
              value={hp}
              onChange={(e) => setHp(e.target.value)}
              required
              className="w-full px-3 py-1.5 border border-slate-300 rounded-md focus:border-[#5051F9] text-xs font-medium"
            />
          </div>

          {/* Alamat */}
          <div>
            <label className="block font-bold text-slate-800 mb-1">
              Alamat <span className="text-rose-500">*</span>
            </label>
            <textarea
              rows={2}
              value={alamat}
              onChange={(e) => setAlamat(e.target.value)}
              required
              className="w-full px-3 py-1.5 border border-slate-300 rounded-md focus:border-[#5051F9] text-xs font-medium"
            />
          </div>

          {/* Penugasan Cabang Checkboxes matching screenshot 9 */}
          <div>
            <label className="block font-bold text-slate-800 mb-1.5">
              Penugasan Cabang <span className="text-rose-500">*</span>
            </label>
            <div className="border border-slate-200 rounded-lg p-3 space-y-2 bg-slate-50/50">
              {cabangs.map((c) => (
                <label
                  key={c.id}
                  className="flex items-center space-x-2.5 cursor-pointer text-slate-700"
                >
                  <input
                    type="checkbox"
                    checked={selectedCabangs.includes(c.id)}
                    onChange={() => toggleCabang(c.id)}
                    className="rounded text-[#5051F9] focus:ring-0 cursor-pointer"
                  />
                  <span className="font-semibold text-xs">{c.nama}</span>
                </label>
              ))}
            </div>
            <p className="text-[11px] text-slate-400 mt-1">
              Pilih cabang mana saja yang dapat diakses oleh user ini (bisa lebih dari 1 cabang).
            </p>
          </div>

          {/* Role */}
          <div>
            <label className="block font-bold text-slate-800 mb-1">
              Role <span className="text-rose-500">*</span>
            </label>
            <select
              value={role}
              onChange={(e) => setRole(e.target.value)}
              className="w-full px-3 py-1.5 border border-slate-300 rounded-md focus:border-[#5051F9] bg-white text-xs font-semibold cursor-pointer"
            >
              <option value="Kepala Toko">Kepala Toko</option>
              <option value="Admin Toko">Admin Toko</option>
              <option value="Teknisi">Teknisi</option>
              <option value="Sales">Sales</option>
            </select>
          </div>

          {/* Extra options if Role is Teknisi */}
          {role === 'Teknisi' && (
            <div className="p-3 bg-indigo-50/60 rounded-xl border border-indigo-100 space-y-3">
              <div>
                <label className="block font-bold text-indigo-950 mb-1">Bagian Teknisi</label>
                <select
                  value={bagianTeknisi}
                  onChange={(e) => setBagianTeknisi(e.target.value)}
                  className="w-full px-3 py-1.5 border border-slate-300 rounded-md bg-white text-xs font-medium cursor-pointer"
                >
                  <option value="Teknisi Hardware">Teknisi Hardware</option>
                  <option value="Teknisi Interface">Teknisi Interface</option>
                  <option value="Teknisi Persentase Interface">Teknisi Persentase Interface</option>
                </select>
              </div>

              <div className="grid grid-cols-2 gap-2">
                <div>
                  <label className="block font-bold text-indigo-950 mb-1">Persen Hardware (%)</label>
                  <input
                    type="number"
                    value={persenHardware}
                    onChange={(e) => setPersenHardware(e.target.value)}
                    className="w-full px-3 py-1.5 border border-slate-300 rounded-md bg-white text-xs font-bold"
                  />
                </div>
                <div>
                  <label className="block font-bold text-indigo-950 mb-1">Persen Interface (%)</label>
                  <input
                    type="number"
                    value={persenInterface}
                    onChange={(e) => setPersenInterface(e.target.value)}
                    className="w-full px-3 py-1.5 border border-slate-300 rounded-md bg-white text-xs font-bold"
                  />
                </div>
              </div>
            </div>
          )}

          {/* Detail Akun (Dokumen) matching screenshot 9 */}
          <div className="space-y-3 pt-2 border-t border-slate-100">
            <div>
              <h4 className="font-bold text-slate-800 text-xs">Detail Akun (Dokumen)</h4>
              <p className="text-[10px] text-rose-500 font-semibold">
                Format: JPG, PNG, atau PDF
              </p>
            </div>

            <div>
              <label className="block font-semibold text-slate-700 mb-1">KTP</label>
              <input
                type="file"
                accept=".jpg,.jpeg,.png,.pdf"
                onChange={(e) => setKtpFile(e.target.files?.[0] || null)}
                className="w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer"
              />
            </div>

            <div>
              <label className="block font-semibold text-slate-700 mb-1">KK</label>
              <input
                type="file"
                accept=".jpg,.jpeg,.png,.pdf"
                onChange={(e) => setKkFile(e.target.files?.[0] || null)}
                className="w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer"
              />
            </div>

            <div>
              <label className="block font-semibold text-slate-700 mb-1">Ijasah</label>
              <input
                type="file"
                accept=".jpg,.jpeg,.png,.pdf"
                onChange={(e) => setIjasahFile(e.target.files?.[0] || null)}
                className="w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer"
              />
            </div>

            <div>
              <label className="block font-semibold text-slate-700 mb-1">Dokumen Lain</label>
              <input
                type="file"
                accept=".jpg,.jpeg,.png,.pdf"
                onChange={(e) => setDokumenLainFile(e.target.files?.[0] || null)}
                className="w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer"
              />
            </div>
          </div>

          {/* Footer Save Button matching screenshot 9 */}
          <div className="flex items-center justify-end pt-3 border-t border-slate-100">
            <button
              type="submit"
              disabled={isSubmitting}
              className="inline-flex items-center space-x-1.5 px-6 py-2 rounded-lg bg-[#5051F9] hover:bg-[#4344db] text-white font-bold text-xs shadow-xs transition-colors cursor-pointer disabled:opacity-50"
            >
              <span>{isSubmitting ? 'Menyimpan...' : 'Simpan'}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};
export default ModalAkun;

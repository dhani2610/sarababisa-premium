'use client';

import React, { useState, useEffect } from 'react';
import api from '@/lib/api';
import { useAuth } from '@/context/AuthContext';
import Swal from 'sweetalert2';
import {
  X,
  Plus,
  Trash2,
  CheckCircle,
  PlusCircle,
  Sparkles,
} from 'lucide-react';

export const DEFAULT_QC_ITEMS = [
  'CHECK FACE ID/FINGER',
  'CHECK FRONT CAM 1/2',
  'CHECK BACK CAM 1/2/3',
  'CHECK CAM 30FPS,60FPS',
  'TOP SPEAKER',
  'BOTTOM SPEAKER',
  'BODY HOUSING',
  'LCD (Truetone,TS)',
  'NETWORK',
  'CALLING PHONE',
  'BATTERY',
  'BACK MIC',
  'BOTTOM MIC',
  'FRONT MIC',
  'TOP AUDIO',
  'BOTTOM AUDIO',
  'WIFI/BLUETOOTH',
  'FLASH LED',
  'ALL BUTTON',
  'COMPAS',
  'VIBRANT/SILENT',
  'CHARGING',
  'PANIC FULL',
  'OTHER',
];

interface QCItem {
  no: number;
  item: string;
  remark_in: string;
  remark_out?: string;
}

interface TindakanItem {
  id?: number;
  tindakan_id?: number | string;
  tindakan_nama: string;
  is_manual?: boolean;
  garansi: string;
  pakai_sparepart_toko: boolean;
  modal_part: number;
  biaya_servis: number;
}

interface TeknisiCard {
  id: string;
  user_id: number | '';
  tipe_bagi_hasil: string;
  tindakan_list: TindakanItem[];
}

interface ModalTambahServisProps {
  isOpen: boolean;
  onClose: () => void;
  onSuccess: () => void;
}

export const ModalTambahServis: React.FC<ModalTambahServisProps> = ({
  isOpen,
  onClose,
  onSuccess,
}) => {
  const { user } = useAuth();

  // Mode: Ditinggal or Langsung
  const [mode, setMode] = useState<'ditinggal' | 'langsung'>('ditinggal');

  // Master Data State
  const [pelangganList, setPelangganList] = useState<any[]>([]);
  const [jenisBarangList, setJenisBarangList] = useState<any[]>([]);
  const [merekList, setMerekList] = useState<any[]>([]);
  const [modelSeriList, setModelSeriList] = useState<any[]>([]);
  const [kapasitasList, setKapasitasList] = useState<any[]>([]);
  const [teknisiList, setTeknisiList] = useState<any[]>([]);
  const [tindakanMasterList, setTindakanMasterList] = useState<any[]>([]);

  // Manual Checkboxes
  const [manualPelanggan, setManualPelanggan] = useState(false);
  const [manualJenisBarang, setManualJenisBarang] = useState(false);
  const [manualMerek, setManualMerek] = useState(false);
  const [manualModelSeri, setManualModelSeri] = useState(false);

  // Common Form Fields
  const [selectedPelangganId, setSelectedPelangganId] = useState<number | ''>('');
  const [manualPelangganNama, setManualPelangganNama] = useState('');
  const [manualPelangganHp, setManualPelangganHp] = useState('');

  const [selectedJenisBarangId, setSelectedJenisBarangId] = useState<number | ''>('');
  const [manualJenisBarangNama, setManualJenisBarangNama] = useState('');

  const [selectedMerekId, setSelectedMerekId] = useState<number | ''>('');
  const [manualMerekNama, setManualMerekNama] = useState('');

  const [selectedModelSeriId, setSelectedModelSeriId] = useState<number | ''>('');
  const [manualModelSeriNama, setManualModelSeriNama] = useState('');

  const [imei, setImei] = useState('');
  const [warna, setWarna] = useState('');
  const [kapasitas, setKapasitas] = useState('8 GB');
  const [kelengkapan, setKelengkapan] = useState('');
  const [kerusakan, setKerusakan] = useState('');

  // QC Table (24 items default)
  const [qcList, setQcList] = useState<QCItem[]>(
    DEFAULT_QC_ITEMS.map((item, idx) => ({
      no: idx + 1,
      item,
      remark_in: '-',
      remark_out: '-',
    }))
  );

  // Ditinggal specific fields
  const [estimasiPengerjaan, setEstimasiPengerjaan] = useState('1 Hari');
  const [estimasiBiaya, setEstimasiBiaya] = useState<number | ''>('');
  const [uangMuka, setUangMuka] = useState<number | ''>('');
  const [penerima, setPenerima] = useState(user?.nama || 'SILPAH SEPTIANA ADM');

  // Langsung specific fields
  const [kondisiServis, setKondisiServis] = useState('Sudah jadi');
  const [diskon, setDiskon] = useState<number | ''>('');
  const [caraPembayaran, setCaraPembayaran] = useState('Tunai');
  const [statusPembayaran, setStatusPembayaran] = useState('Lunas');
  const [catatan, setCatatan] = useState('');

  // Multi-Teknisi State (The first one is the main PIC!)
  const [teknisiCards, setTeknisiCards] = useState<TeknisiCard[]>([
    {
      id: 'tek-1',
      user_id: '',
      tipe_bagi_hasil: 'Persentase',
      tindakan_list: [
        {
          tindakan_nama: '',
          garansi: 'Tidak Ada',
          pakai_sparepart_toko: false,
          modal_part: 0,
          biaya_servis: 0,
        },
      ],
    },
  ]);

  const [isSubmitting, setIsSubmitting] = useState(false);

  // Fetch Master Data on Mount
  useEffect(() => {
    if (!isOpen) return;

    const loadMaster = async () => {
      try {
        const [resPel, resJb, resMerek, resKap, resTek, resTindakan] = await Promise.all([
          api.get('/pelanggan', { params: { per_page: 100 } }),
          api.get('/master/jenis-barang'),
          api.get('/master/master-merek'),
          api.get('/master/master-kapasitas'),
          api.get('/akun', { params: { role: 'Teknisi' } }),
          api.get('/servis/tindakan'),
        ]);

        setPelangganList(Array.isArray(resPel.data) ? resPel.data : resPel.data.data || []);
        setJenisBarangList(Array.isArray(resJb.data) ? resJb.data : resJb.data.data || []);
        setMerekList(Array.isArray(resMerek.data) ? resMerek.data : resMerek.data.data || []);
        setKapasitasList(Array.isArray(resKap.data) ? resKap.data : resKap.data.data || []);
        setTeknisiList(Array.isArray(resTek.data) ? resTek.data : resTek.data.data || []);
        setTindakanMasterList(Array.isArray(resTindakan.data) ? resTindakan.data : resTindakan.data.data || []);
      } catch (err) {
        console.error('Error loading master data', err);
      }
    };

    loadMaster();
  }, [isOpen]);

  // Load Model Seri when Merek changes
  useEffect(() => {
    if (!selectedMerekId) {
      setModelSeriList([]);
      return;
    }
    const loadModel = async () => {
      try {
        const res = await api.get('/master/master-model-seri', {
          params: { merek_id: selectedMerekId },
        });
        setModelSeriList(Array.isArray(res.data) ? res.data : res.data.data || []);
      } catch (err) {
        console.error('Error loading model seri', err);
      }
    };
    loadModel();
  }, [selectedMerekId]);

  if (!isOpen) return null;

  // QC Helpers
  const handleQcChange = (index: number, field: 'remark_in' | 'remark_out', val: string) => {
    setQcList((prev) => {
      const updated = [...prev];
      updated[index] = { ...updated[index], [field]: val };
      return updated;
    });
  };

  const handleAddCustomQc = () => {
    setQcList((prev) => [
      ...prev,
      {
        no: prev.length + 1,
        item: `CUSTOM CHECK #${prev.length + 1}`,
        remark_in: '-',
        remark_out: '-',
      },
    ]);
  };

  const handleRemoveQc = (index: number) => {
    setQcList((prev) => prev.filter((_, idx) => idx !== index));
  };

  // Multi-Teknisi Helpers
  const handleAddTeknisiCard = () => {
    setTeknisiCards((prev) => [
      ...prev,
      {
        id: `tek-${Date.now()}`,
        user_id: '',
        tipe_bagi_hasil: 'Persentase',
        tindakan_list: [
          {
            tindakan_nama: '',
            garansi: 'Tidak Ada',
            pakai_sparepart_toko: false,
            modal_part: 0,
            biaya_servis: 0,
          },
        ],
      },
    ]);
  };

  const handleRemoveTeknisiCard = (cardId: string) => {
    if (teknisiCards.length <= 1) return;
    setTeknisiCards((prev) => prev.filter((c) => c.id !== cardId));
  };

  const handleAddTindakanToTeknisi = (cardIndex: number) => {
    setTeknisiCards((prev) => {
      const updated = [...prev];
      updated[cardIndex].tindakan_list.push({
        tindakan_nama: '',
        garansi: 'Tidak Ada',
        pakai_sparepart_toko: false,
        modal_part: 0,
        biaya_servis: 0,
      });
      return updated;
    });
  };

  const handleRemoveTindakan = (cardIndex: number, actionIndex: number) => {
    setTeknisiCards((prev) => {
      const updated = [...prev];
      updated[cardIndex].tindakan_list = updated[cardIndex].tindakan_list.filter((_, idx) => idx !== actionIndex);
      return updated;
    });
  };

  const handleTindakanChange = (
    cardIndex: number,
    actionIndex: number,
    field: keyof TindakanItem,
    val: any
  ) => {
    setTeknisiCards((prev) => {
      const updated = [...prev];
      const targetAction = { ...updated[cardIndex].tindakan_list[actionIndex], [field]: val };
      updated[cardIndex].tindakan_list[actionIndex] = targetAction;
      return updated;
    });
  };

  // Auto-calculated totals in Mode Langsung
  const totalModalPart = teknisiCards.reduce(
    (acc, card) =>
      acc + card.tindakan_list.reduce((sum, act) => sum + Number(act.modal_part || 0), 0),
    0
  );

  const totalBiayaServis = teknisiCards.reduce(
    (acc, card) =>
      acc + card.tindakan_list.reduce((sum, act) => sum + Number(act.biaya_servis || 0), 0),
    0
  );

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsSubmitting(true);

    try {
      // 1. Resolve Pelanggan
      let finalPelangganId = selectedPelangganId;
      let finalPelangganNama = '';
      if (manualPelanggan) {
        if (!manualPelangganNama.trim()) {
          Swal.fire('Validasi', 'Nama pelanggan manual wajib diisi.', 'warning');
          setIsSubmitting(false);
          return;
        }
        const resPel = await api.post('/pelanggan', {
          nama: manualPelangganNama,
          no_hp: manualPelangganHp,
        });
        finalPelangganId = resPel.data.id;
        finalPelangganNama = manualPelangganNama;
      } else {
        const found = pelangganList.find((p) => p.id === selectedPelangganId);
        finalPelangganNama = found ? found.nama : '';
      }

      // 2. Resolve Device Name
      const jbName = manualJenisBarang
        ? manualJenisBarangNama
        : jenisBarangList.find((j) => j.id === selectedJenisBarangId)?.nama || '';
      const merekName = manualMerek
        ? manualMerekNama
        : merekList.find((m) => m.id === selectedMerekId)?.nama || '';
      const modelName = manualModelSeri
        ? manualModelSeriNama
        : modelSeriList.find((m) => m.id === selectedModelSeriId)?.nama || '';
      const namaBarang = `${jbName} ${merekName} ${modelName}`.trim() || 'Unit Handphone';

      // 3. QC Payload
      const qcMasukDict = qcList.reduce((acc: any, cur) => {
        acc[cur.item] = cur.remark_in;
        return acc;
      }, {});

      const qcKeluarDict = qcList.reduce((acc: any, cur) => {
        acc[cur.item] = cur.remark_out || '-';
        return acc;
      }, {});

      if (mode === 'ditinggal') {
        const payload = {
          pelanggan_id: finalPelangganId || undefined,
          nama_pelanggan: finalPelangganNama,
          jenis_barang_id: selectedJenisBarangId || undefined,
          merek_id: selectedMerekId || undefined,
          model_seri_id: selectedModelSeriId || undefined,
          nama_barang: namaBarang,
          imei,
          warna,
          kapasitas,
          kelengkapan: kelengkapan || 'hanya unit',
          kerusakan,
          estimasi_pengerjaan: estimasiPengerjaan,
          estimasi_biaya: Number(estimasiBiaya) || 0,
          dp: Number(uangMuka) || 0,
          penerima,
          qc_masuk: qcMasukDict,
          status: 'Proses',
          tipe: 'Ditinggal',
        };

        await api.post('/servis', payload);
        Swal.fire('Berhasil!', 'Transaksi servis ditinggal berhasil dicatat.', 'success');
      } else {
        // Mode Langsung
        // The first teknisi is the primary PIC!
        const primaryTeknisiId = teknisiCards[0]?.user_id || undefined;

        const payload = {
          pelanggan_id: finalPelangganId || undefined,
          nama_pelanggan: finalPelangganNama,
          jenis_barang_id: selectedJenisBarangId || undefined,
          merek_id: selectedMerekId || undefined,
          model_seri_id: selectedModelSeriId || undefined,
          nama_barang: namaBarang,
          imei,
          warna,
          kapasitas,
          kelengkapan: kelengkapan || 'hanya unit',
          kerusakan,
          kondisi_servis: kondisiServis,
          penerima,
          teknisi_id: primaryTeknisiId,
          multi_teknisi: teknisiCards,
          total_modal_sparepart: totalModalPart,
          total_biaya: totalBiayaServis - Number(diskon || 0),
          diskon: Number(diskon || 0),
          cara_pembayaran: caraPembayaran,
          status_pembayaran: statusPembayaran,
          catatan,
          qc_masuk: qcMasukDict,
          qc_keluar: qcKeluarDict,
          status: 'Sudah Diambil',
          tipe: 'Langsung',
        };

        await api.post('/servis/langsung', payload);
        Swal.fire('Berhasil!', 'Transaksi servis langsung berhasil disimpan.', 'success');
      }

      onSuccess();
      onClose();
    } catch (err: any) {
      Swal.fire('Gagal Menyimpan', err.response?.data?.detail || 'Terjadi kesalahan sistem', 'error');
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5 bg-slate-900/60 backdrop-blur-2xs overflow-y-auto">
      <div className="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden my-6">
        {/* Header */}
        <div className="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/60">
          <h2 className="text-base font-bold text-slate-900">Tambah Transaksi Baru</h2>
          <button
            onClick={onClose}
            className="p-1 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-200/50 cursor-pointer"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        <form onSubmit={handleSubmit} className="p-6 space-y-4 max-h-[82vh] overflow-y-auto text-xs text-slate-700">
          {/* Mode Selector Radio (Ditinggal / Langsung) matching screenshot */}
          <div className="flex items-center space-x-6 pb-2 border-b border-slate-100">
            <label className="flex items-center space-x-2 cursor-pointer font-bold text-slate-800">
              <input
                type="radio"
                name="transaksi_mode"
                checked={mode === 'ditinggal'}
                onChange={() => setMode('ditinggal')}
                className="text-[#5051F9] focus:ring-0"
              />
              <span>Ditinggal</span>
            </label>

            <label className="flex items-center space-x-2 cursor-pointer font-bold text-slate-800">
              <input
                type="radio"
                name="transaksi_mode"
                checked={mode === 'langsung'}
                onChange={() => setMode('langsung')}
                className="text-[#5051F9] focus:ring-0"
              />
              <span>Langsung</span>
            </label>
          </div>

          {/* 1. Nama Pelanggan */}
          <div className="space-y-1">
            <label className="font-bold text-slate-800">
              Nama Pelanggan <span className="text-rose-500">*</span>
            </label>
            {!manualPelanggan ? (
              <select
                value={selectedPelangganId}
                onChange={(e) => setSelectedPelangganId(Number(e.target.value) || '')}
                className="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs focus:outline-hidden focus:border-[#5051F9]"
              >
                <option value="">Pilih Pelanggan</option>
                {pelangganList.map((p) => (
                  <option key={p.id} value={p.id}>
                    {p.nama} {p.no_hp ? `(${p.no_hp})` : ''}
                  </option>
                ))}
              </select>
            ) : (
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <input
                  type="text"
                  placeholder="Nama Pelanggan Baru"
                  value={manualPelangganNama}
                  onChange={(e) => setManualPelangganNama(e.target.value)}
                  className="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs focus:outline-hidden focus:border-[#5051F9]"
                />
                <input
                  type="text"
                  placeholder="No. WhatsApp / HP"
                  value={manualPelangganHp}
                  onChange={(e) => setManualPelangganHp(e.target.value)}
                  className="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs focus:outline-hidden focus:border-[#5051F9]"
                />
              </div>
            )}
            <label className="flex items-center space-x-1.5 pt-1 text-[11px] text-slate-500 cursor-pointer">
              <input
                type="checkbox"
                checked={manualPelanggan}
                onChange={(e) => setManualPelanggan(e.target.checked)}
                className="rounded text-[#5051F9] focus:ring-0"
              />
              <span>Isi manual <span className="text-rose-500">*data akan auto masuk ke master data</span></span>
            </label>
          </div>

          {/* 2. Jenis Barang */}
          <div className="space-y-1">
            <label className="font-bold text-slate-800">
              Jenis Barang <span className="text-rose-500">*</span>
            </label>
            {!manualJenisBarang ? (
              <select
                value={selectedJenisBarangId}
                onChange={(e) => setSelectedJenisBarangId(Number(e.target.value) || '')}
                className="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs focus:outline-hidden focus:border-[#5051F9]"
              >
                <option value="">Pilih Jenis Barang</option>
                {jenisBarangList.map((j) => (
                  <option key={j.id} value={j.id}>
                    {j.nama}
                  </option>
                ))}
              </select>
            ) : (
              <input
                type="text"
                placeholder="Jenis Barang Manual (e.g. HANDPHONE)"
                value={manualJenisBarangNama}
                onChange={(e) => setManualJenisBarangNama(e.target.value)}
                className="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs focus:outline-hidden focus:border-[#5051F9]"
              />
            )}
            <label className="flex items-center space-x-1.5 pt-1 text-[11px] text-slate-500 cursor-pointer">
              <input
                type="checkbox"
                checked={manualJenisBarang}
                onChange={(e) => setManualJenisBarang(e.target.checked)}
                className="rounded text-[#5051F9] focus:ring-0"
              />
              <span>Isi manual <span className="text-rose-500">*data akan auto masuk ke master data</span></span>
            </label>
          </div>

          {/* 3. Merek */}
          <div className="space-y-1">
            <label className="font-bold text-slate-800">
              Merek <span className="text-rose-500">*</span>
            </label>
            {!manualMerek ? (
              <select
                value={selectedMerekId}
                onChange={(e) => setSelectedMerekId(Number(e.target.value) || '')}
                className="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs focus:outline-hidden focus:border-[#5051F9]"
              >
                <option value="">Pilih Merek</option>
                {merekList.map((m) => (
                  <option key={m.id} value={m.id}>
                    {m.nama}
                  </option>
                ))}
              </select>
            ) : (
              <input
                type="text"
                placeholder="Merek Manual (e.g. APPLE)"
                value={manualMerekNama}
                onChange={(e) => setManualMerekNama(e.target.value)}
                className="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs focus:outline-hidden focus:border-[#5051F9]"
              />
            )}
            <label className="flex items-center space-x-1.5 pt-1 text-[11px] text-slate-500 cursor-pointer">
              <input
                type="checkbox"
                checked={manualMerek}
                onChange={(e) => setManualMerek(e.target.checked)}
                className="rounded text-[#5051F9] focus:ring-0"
              />
              <span>Isi manual <span className="text-rose-500">*data akan auto masuk ke master data</span></span>
            </label>
          </div>

          {/* 4. Model Seri */}
          <div className="space-y-1">
            <label className="font-bold text-slate-800">
              Model Seri <span className="text-rose-500">*</span>
            </label>
            {!manualModelSeri ? (
              <select
                value={selectedModelSeriId}
                onChange={(e) => setSelectedModelSeriId(Number(e.target.value) || '')}
                className="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs focus:outline-hidden focus:border-[#5051F9]"
              >
                <option value="">Pilih Model Seri</option>
                {modelSeriList.map((m) => (
                  <option key={m.id} value={m.id}>
                    {m.nama}
                  </option>
                ))}
              </select>
            ) : (
              <input
                type="text"
                placeholder="Model Seri Manual (e.g. IPHONE 13 PRO MAX)"
                value={manualModelSeriNama}
                onChange={(e) => setManualModelSeriNama(e.target.value)}
                className="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs focus:outline-hidden focus:border-[#5051F9]"
              />
            )}
            <label className="flex items-center space-x-1.5 pt-1 text-[11px] text-slate-500 cursor-pointer">
              <input
                type="checkbox"
                checked={manualModelSeri}
                onChange={(e) => setManualModelSeri(e.target.checked)}
                className="rounded text-[#5051F9] focus:ring-0"
              />
              <span>Isi manual <span className="text-rose-500">*data akan auto masuk ke master data</span></span>
            </label>
          </div>

          {/* 5. Nomor IMEI */}
          <div className="space-y-1">
            <label className="font-bold text-slate-800">
              Nomor Imei <span className="text-rose-500">*</span>
            </label>
            <input
              type="text"
              placeholder="Nomor IMEI / Serial Number"
              value={imei}
              onChange={(e) => setImei(e.target.value)}
              className="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs focus:outline-hidden focus:border-[#5051F9]"
            />
          </div>

          {/* 6. Warna */}
          <div className="space-y-1">
            <label className="font-bold text-slate-800">
              Warna <span className="text-rose-500">*</span>
            </label>
            <input
              type="text"
              placeholder="Warna perangkat"
              value={warna}
              onChange={(e) => setWarna(e.target.value)}
              className="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs focus:outline-hidden focus:border-[#5051F9]"
            />
          </div>

          {/* 7. Kapasitas */}
          <div className="space-y-1">
            <label className="font-bold text-slate-800">
              Kapasitas <span className="text-rose-500">*</span>
            </label>
            <select
              value={kapasitas}
              onChange={(e) => setKapasitas(e.target.value)}
              className="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs focus:outline-hidden focus:border-[#5051F9]"
            >
              {kapasitasList.length > 0 ? (
                kapasitasList.map((k) => (
                  <option key={k.id} value={k.nama}>
                    {k.nama}
                  </option>
                ))
              ) : (
                <>
                  <option value="8 GB">8 GB</option>
                  <option value="16 GB">16 GB</option>
                  <option value="32 GB">32 GB</option>
                  <option value="64 GB">64 GB</option>
                  <option value="128 GB">128 GB</option>
                  <option value="256 GB">256 GB</option>
                  <option value="512 GB">512 GB</option>
                  <option value="1 TB">1 TB</option>
                </>
              )}
            </select>
          </div>

          {/* 8. Kelengkapan */}
          <div className="space-y-1">
            <label className="font-bold text-slate-800">Kelengkapan</label>
            <input
              type="text"
              placeholder="Kosongkan jika kelengkapannya hanya unit"
              value={kelengkapan}
              onChange={(e) => setKelengkapan(e.target.value)}
              className="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs focus:outline-hidden focus:border-[#5051F9]"
            />
          </div>

          {/* 9. Kerusakan */}
          <div className="space-y-1">
            <label className="font-bold text-slate-800">
              Kerusakan <span className="text-rose-500">*</span>
            </label>
            <input
              type="text"
              placeholder="Deskripsi kerusakan perangkat"
              value={kerusakan}
              onChange={(e) => setKerusakan(e.target.value)}
              className="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs focus:outline-hidden focus:border-[#5051F9]"
            />
          </div>

          {/* 10. QC Table matching screenshot */}
          <div className="space-y-2 pt-2 border-t border-slate-100">
            <div className="flex items-center justify-between">
              <label className="font-bold text-slate-900">
                {mode === 'ditinggal'
                  ? 'List Pengecekan Fungsi (Masuk) *'
                  : 'List Pengecekan Fungsi (Masuk & Keluar) *'}
              </label>
              <button
                type="button"
                onClick={handleAddCustomQc}
                className="text-[11px] font-bold text-blue-600 hover:underline cursor-pointer"
              >
                + Tambah Baris Custom
              </button>
            </div>
            <p className="text-[11px] text-rose-500 italic">
              *Jika ingin cepat silahkan isi kolom other.
            </p>

            <div className="border border-slate-200 rounded-lg overflow-hidden max-h-60 overflow-y-auto">
              <table className="w-full text-left text-[11px]">
                <thead className="bg-slate-50 text-slate-600 font-bold uppercase border-b border-slate-200">
                  <tr>
                    <th className="py-2 px-3 w-8 text-center">NO</th>
                    <th className="py-2 px-3">ITEM</th>
                    <th className="py-2 px-3 text-center">REMARK IN</th>
                    {mode === 'langsung' && <th className="py-2 px-3 text-center">REMARK OUT</th>}
                    <th className="py-2 px-3 w-8 text-center"></th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                  {qcList.map((item, idx) => (
                    <tr key={idx} className="hover:bg-slate-50">
                      <td className="py-1 px-3 text-center font-bold text-slate-500">{idx + 1}</td>
                      <td className="py-1 px-3 font-semibold text-slate-800">{item.item}</td>
                      <td className="py-1 px-3 text-center">
                        <input
                          type="text"
                          value={item.remark_in}
                          onChange={(e) => handleQcChange(idx, 'remark_in', e.target.value)}
                          className="w-20 text-center border border-slate-200 rounded px-1 py-0.5 text-xs bg-slate-50/50 focus:bg-white"
                        />
                      </td>
                      {mode === 'langsung' && (
                        <td className="py-1 px-3 text-center">
                          <input
                            type="text"
                            value={item.remark_out}
                            onChange={(e) => handleQcChange(idx, 'remark_out', e.target.value)}
                            className="w-20 text-center border border-slate-200 rounded px-1 py-0.5 text-xs bg-slate-50/50 focus:bg-white"
                          />
                        </td>
                      )}
                      <td className="py-1 px-3 text-center">
                        <button
                          type="button"
                          onClick={() => handleRemoveQc(idx)}
                          className="text-rose-400 hover:text-rose-600 cursor-pointer"
                        >
                          <Trash2 className="w-3.5 h-3.5" />
                        </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>

          {/* MODE DITINGGAL SPECIFIC FIELDS */}
          {mode === 'ditinggal' && (
            <div className="space-y-4 pt-2 border-t border-slate-100">
              <div className="space-y-1">
                <label className="font-bold text-slate-800">Estimasi Pengerjaan</label>
                <select
                  value={estimasiPengerjaan}
                  onChange={(e) => setEstimasiPengerjaan(e.target.value)}
                  className="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs focus:outline-hidden focus:border-[#5051F9]"
                >
                  <option value="1 Hari">1 Hari</option>
                  <option value="2 Hari">2 Hari</option>
                  <option value="3 Hari">3 Hari</option>
                  <option value="4 Hari">4 Hari</option>
                  <option value="1 Minggu">1 Minggu</option>
                  <option value="2 Minggu">2 Minggu</option>
                </select>
              </div>

              <div className="space-y-1">
                <label className="font-bold text-slate-800">Estimasi Biaya Servis</label>
                <input
                  type="number"
                  placeholder="Rp. Kosongkan jika tidak ada"
                  value={estimasiBiaya}
                  onChange={(e) => setEstimasiBiaya(Number(e.target.value) || '')}
                  className="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs focus:outline-hidden focus:border-[#5051F9]"
                />
              </div>

              <div className="space-y-1">
                <label className="font-bold text-slate-800">DP/Uang Muka</label>
                <input
                  type="number"
                  placeholder="Rp. Kosongkan jika tidak ada"
                  value={uangMuka}
                  onChange={(e) => setUangMuka(Number(e.target.value) || '')}
                  className="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs focus:outline-hidden focus:border-[#5051F9]"
                />
              </div>

              <div className="space-y-1">
                <label className="font-bold text-slate-800">Penerima</label>
                <input
                  type="text"
                  value={penerima}
                  onChange={(e) => setPenerima(e.target.value)}
                  className="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs focus:outline-hidden focus:border-[#5051F9]"
                />
              </div>
            </div>
          )}

          {/* MODE LANGSUNG SPECIFIC FIELDS (MULTI-TEKNISI!) */}
          {mode === 'langsung' && (
            <div className="space-y-4 pt-2 border-t border-slate-100">
              <div className="space-y-1">
                <label className="font-bold text-slate-800">Penerima</label>
                <input
                  type="text"
                  value={penerima}
                  onChange={(e) => setPenerima(e.target.value)}
                  className="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs focus:outline-hidden focus:border-[#5051F9]"
                />
              </div>

              {/* Kondisi Servis */}
              <div className="space-y-1">
                <label className="font-bold text-slate-800">
                  Kondisi Servis <span className="text-rose-500">*</span>
                </label>
                <div className="flex items-center space-x-6 pt-1">
                  {['Sudah jadi', 'Menunggu konfirmasi', 'Dibatalkan'].map((kond) => (
                    <label key={kond} className="flex items-center space-x-1.5 cursor-pointer font-medium text-slate-700">
                      <input
                        type="radio"
                        name="kondisi_servis"
                        checked={kondisiServis === kond}
                        onChange={() => setKondisiServis(kond)}
                        className="text-[#5051F9] focus:ring-0"
                      />
                      <span>{kond}</span>
                    </label>
                  ))}
                </div>
              </div>

              {/* MULTI-TEKNISI CARDS */}
              <div className="space-y-3 pt-2">
                <div className="flex items-center justify-between">
                  <h4 className="font-black text-slate-900 uppercase tracking-tight text-xs flex items-center gap-1.5">
                    <Sparkles className="w-4 h-4 text-amber-500" />
                    <span>Data Teknisi & Bagi Hasil (Multi-Teknisi)</span>
                  </h4>
                  <span className="text-[10px] text-blue-600 font-semibold bg-blue-50 px-2 py-0.5 rounded">
                    Teknisi #1 adalah PIC Utama
                  </span>
                </div>

                {teknisiCards.map((card, cardIdx) => (
                  <div key={card.id} className="p-4 rounded-xl border border-indigo-200 bg-indigo-50/20 space-y-3">
                    <div className="flex items-center justify-between">
                      <span className="font-bold text-indigo-900 text-xs">
                        Teknisi {cardIdx + 1} {cardIdx === 0 ? '(PIC Utama)' : ''}
                      </span>
                      {teknisiCards.length > 1 && (
                        <button
                          type="button"
                          onClick={() => handleRemoveTeknisiCard(card.id)}
                          className="px-2.5 py-0.5 rounded bg-rose-500 hover:bg-rose-600 text-white font-bold text-[10px] cursor-pointer"
                        >
                          Hapus Teknisi
                        </button>
                      )}
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                      <div>
                        <label className="font-bold text-slate-700">
                          Nama Teknisi <span className="text-rose-500">*</span>
                        </label>
                        <select
                          value={card.user_id}
                          onChange={(e) => {
                            const val = Number(e.target.value) || '';
                            setTeknisiCards((prev) => {
                              const updated = [...prev];
                              updated[cardIdx].user_id = val;
                              return updated;
                            });
                          }}
                          className="w-full bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 text-xs focus:outline-hidden focus:border-[#5051F9]"
                        >
                          <option value="">Pilih Teknisi</option>
                          {teknisiList.map((t) => (
                            <option key={t.id} value={t.id}>
                              {t.nama || t.username}
                            </option>
                          ))}
                        </select>
                      </div>

                      <div>
                        <label className="font-bold text-slate-700">
                          Tipe Bagi Hasil <span className="text-rose-500">*</span>
                        </label>
                        <select
                          value={card.tipe_bagi_hasil}
                          onChange={(e) => {
                            const val = e.target.value;
                            setTeknisiCards((prev) => {
                              const updated = [...prev];
                              updated[cardIdx].tipe_bagi_hasil = val;
                              return updated;
                            });
                          }}
                          className="w-full bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 text-xs focus:outline-hidden focus:border-[#5051F9]"
                        >
                          <option value="Persentase">Persentase</option>
                          <option value="Nominal">Nominal</option>
                        </select>
                      </div>
                    </div>

                    {/* Tindakan items for this technician */}
                    <div className="space-y-3 pt-2">
                      {card.tindakan_list.map((act, actIdx) => (
                        <div key={actIdx} className="p-3 bg-white rounded-lg border border-slate-200 space-y-2 relative">
                          {card.tindakan_list.length > 1 && (
                            <button
                              type="button"
                              onClick={() => handleRemoveTindakan(cardIdx, actIdx)}
                              className="absolute top-2 right-2 w-5 h-5 rounded-full bg-rose-500 text-white flex items-center justify-center font-bold text-xs cursor-pointer hover:bg-rose-600"
                            >
                              ✕
                            </button>
                          )}

                          <div className="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <div>
                              <label className="font-bold text-slate-700">
                                Tindakan Servis <span className="text-rose-500">*</span>
                              </label>
                              <input
                                type="text"
                                placeholder="Pilih / Ketik Tindakan"
                                value={act.tindakan_nama}
                                onChange={(e) =>
                                  handleTindakanChange(cardIdx, actIdx, 'tindakan_nama', e.target.value)
                                }
                                className="w-full bg-white border border-slate-300 rounded px-2.5 py-1 text-xs focus:outline-hidden focus:border-[#5051F9]"
                              />
                            </div>

                            <div>
                              <label className="font-bold text-slate-700">Garansi</label>
                              <select
                                value={act.garansi}
                                onChange={(e) =>
                                  handleTindakanChange(cardIdx, actIdx, 'garansi', e.target.value)
                                }
                                className="w-full bg-white border border-slate-300 rounded px-2.5 py-1 text-xs focus:outline-hidden focus:border-[#5051F9]"
                              >
                                <option value="Tidak Ada">Tidak Ada</option>
                                <option value="3 Hari">3 Hari</option>
                                <option value="1 Minggu">1 Minggu</option>
                                <option value="1 Bulan">1 Bulan</option>
                                <option value="3 Bulan">3 Bulan</option>
                              </select>
                            </div>
                          </div>

                          <div className="grid grid-cols-1 sm:grid-cols-3 gap-2 items-center">
                            <div>
                              <label className="font-bold text-slate-700 block text-[10px]">
                                Pakai Sparepart Toko?
                              </label>
                              <div className="flex items-center space-x-3 pt-1">
                                <label className="flex items-center space-x-1 cursor-pointer">
                                  <input
                                    type="radio"
                                    checked={!act.pakai_sparepart_toko}
                                    onChange={() =>
                                      handleTindakanChange(cardIdx, actIdx, 'pakai_sparepart_toko', false)
                                    }
                                    className="text-[#5051F9]"
                                  />
                                  <span>Tidak</span>
                                </label>
                                <label className="flex items-center space-x-1 cursor-pointer">
                                  <input
                                    type="radio"
                                    checked={act.pakai_sparepart_toko}
                                    onChange={() =>
                                      handleTindakanChange(cardIdx, actIdx, 'pakai_sparepart_toko', true)
                                    }
                                    className="text-[#5051F9]"
                                  />
                                  <span>Ya</span>
                                </label>
                              </div>
                            </div>

                            <div>
                              <label className="font-bold text-slate-700">
                                Modal Part <span className="text-rose-500">*</span>
                              </label>
                              <input
                                type="number"
                                value={act.modal_part}
                                onChange={(e) =>
                                  handleTindakanChange(cardIdx, actIdx, 'modal_part', Number(e.target.value) || 0)
                                }
                                className="w-full bg-white border border-slate-300 rounded px-2.5 py-1 text-xs focus:outline-hidden focus:border-[#5051F9]"
                              />
                            </div>

                            <div>
                              <label className="font-bold text-slate-700">
                                Biaya Servis <span className="text-rose-500">*</span>
                              </label>
                              <input
                                type="number"
                                value={act.biaya_servis}
                                onChange={(e) =>
                                  handleTindakanChange(cardIdx, actIdx, 'biaya_servis', Number(e.target.value) || 0)
                                }
                                className="w-full bg-white border border-slate-300 rounded px-2.5 py-1 text-xs focus:outline-hidden focus:border-[#5051F9]"
                              />
                            </div>
                          </div>
                        </div>
                      ))}

                      <button
                        type="button"
                        onClick={() => handleAddTindakanToTeknisi(cardIdx)}
                        className="w-full py-1.5 rounded-lg bg-[#10b981] hover:bg-[#059669] text-white font-bold text-xs transition-colors cursor-pointer"
                      >
                        + Tambah Tindakan Lain Untuk Teknisi Ini
                      </button>
                    </div>
                  </div>
                ))}

                {/* Add Another Teknisi Button matching screenshot */}
                <button
                  type="button"
                  onClick={handleAddTeknisiCard}
                  className="w-full py-2 rounded-xl bg-[#5051F9] hover:bg-[#4344db] text-white font-bold text-xs shadow-2xs transition-colors cursor-pointer"
                >
                  + Tambah Teknisi Baru
                </button>
              </div>

              {/* Totals & Payment Fields matching screenshot */}
              <div className="space-y-3 pt-3 border-t border-slate-100">
                <div className="space-y-1">
                  <label className="font-bold text-slate-800">
                    Total Modal / Sparepart <span className="text-rose-500">*</span>
                  </label>
                  <input
                    type="text"
                    disabled
                    value={`Rp. ${totalModalPart.toLocaleString('id-ID')}`}
                    className="w-full bg-slate-100 border border-slate-300 rounded-lg px-3 py-2 text-xs font-bold text-slate-800"
                  />
                </div>

                <div className="space-y-1">
                  <label className="font-bold text-slate-800">
                    Total Biaya Servis (Ke Pelanggan) <span className="text-rose-500">*</span>
                  </label>
                  <input
                    type="text"
                    disabled
                    value={`Rp. ${totalBiayaServis.toLocaleString('id-ID')}`}
                    className="w-full bg-slate-100 border border-slate-300 rounded-lg px-3 py-2 text-xs font-bold text-slate-800"
                  />
                </div>

                <div className="space-y-1">
                  <label className="font-bold text-slate-800">Diskon</label>
                  <input
                    type="number"
                    placeholder="Kosongkan jika tidak ada diskon"
                    value={diskon}
                    onChange={(e) => setDiskon(Number(e.target.value) || '')}
                    className="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs focus:outline-hidden focus:border-[#5051F9]"
                  />
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <div>
                    <label className="font-bold text-slate-800">Cara Pembayaran</label>
                    <select
                      value={caraPembayaran}
                      onChange={(e) => setCaraPembayaran(e.target.value)}
                      className="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs focus:outline-hidden focus:border-[#5051F9]"
                    >
                      <option value="Tunai">Tunai</option>
                      <option value="Transfer">Transfer</option>
                      <option value="QRIS">QRIS</option>
                      <option value="Debit">Debit</option>
                    </select>
                  </div>

                  <div>
                    <label className="font-bold text-slate-800">Status Pembayaran</label>
                    <select
                      value={statusPembayaran}
                      onChange={(e) => setStatusPembayaran(e.target.value)}
                      className="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs focus:outline-hidden focus:border-[#5051F9]"
                    >
                      <option value="Lunas">Lunas</option>
                      <option value="Belum Lunas">Belum Lunas</option>
                    </select>
                  </div>
                </div>

                <div className="space-y-1">
                  <label className="font-bold text-slate-800">Catatan (disimpan jika ada pesan)</label>
                  <textarea
                    rows={2}
                    value={catatan}
                    onChange={(e) => setCatatan(e.target.value)}
                    placeholder="Tulis catatan yang diperlukan untuk pelanggan, catatan akan muncul pada nota pengambilan."
                    className="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs focus:outline-hidden focus:border-[#5051F9]"
                  ></textarea>
                </div>
              </div>
            </div>
          )}

          {/* Modal Footer Actions matching screenshot */}
          <div className="flex items-center justify-between pt-4 border-t border-slate-100">
            <button
              type="button"
              onClick={() => {
                setManualPelanggan(true);
              }}
              className="px-3.5 py-2 rounded-lg bg-[#10b981] hover:bg-[#059669] text-white text-xs font-bold transition-colors cursor-pointer"
            >
              + Tambah Pelanggan Baru
            </button>

            <div className="flex items-center space-x-2">
              <button
                type="button"
                onClick={onClose}
                className="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 text-xs font-semibold transition-colors cursor-pointer"
              >
                Batal
              </button>
              <button
                type="submit"
                disabled={isSubmitting}
                className="px-5 py-2 rounded-lg bg-[#5051F9] hover:bg-[#4344db] text-white text-xs font-bold shadow-2xs transition-colors cursor-pointer disabled:opacity-50"
              >
                {isSubmitting ? 'Menyimpan...' : 'Simpan'}
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  );
};

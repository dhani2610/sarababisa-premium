'use client';

import React, { useState, useEffect } from 'react';
import { useParams, useRouter } from 'next/navigation';
import Link from 'next/link';
import api from '@/lib/api';
import { useAuth } from '@/context/AuthContext';
import Swal from 'sweetalert2';
import SearchableSelect from '@/components/ui/SearchableSelect';
import {
  ArrowLeft,
  Save,
  Printer,
  Plus,
  Trash2,
  CheckCircle,
  Wrench,
  FileText,
  User,
  Smartphone,
  CreditCard,
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
  tindakan_id?: number | string;
  tindakan_nama: string;
  is_manual?: boolean;
  garansi: string;
  pakai_sparepart_toko: boolean;
  produk_id?: number | string;
  modal_part: number;
  biaya_servis: number;
}

interface TeknisiCard {
  id: string;
  user_id: number | '';
  tipe_bagi_hasil: string;
  tindakan_list: TindakanItem[];
}

export default function EditServisPage() {
  const params = useParams();
  const router = useRouter();
  const { user } = useAuth();
  const servisId = params.id as string;

  const [isLoading, setIsLoading] = useState(true);
  const [isSubmitting, setIsSubmitting] = useState(false);

  // Mode: Ditinggal or Langsung
  const [mode, setMode] = useState<'ditinggal' | 'langsung'>('ditinggal');

  // Master Data
  const [pelangganList, setPelangganList] = useState<any[]>([]);
  const [jenisBarangList, setJenisBarangList] = useState<any[]>([]);
  const [merekList, setMerekList] = useState<any[]>([]);
  const [modelSeriList, setModelSeriList] = useState<any[]>([]);
  const [kapasitasList, setKapasitasList] = useState<any[]>([]);
  const [teknisiList, setTeknisiList] = useState<any[]>([]);
  const [tindakanMasterList, setTindakanMasterList] = useState<any[]>([]);
  const [produkList, setProdukList] = useState<any[]>([]);

  // Manual Checkboxes
  const [manualPelanggan, setManualPelanggan] = useState(false);
  const [manualJenisBarang, setManualJenisBarang] = useState(false);
  const [manualMerek, setManualMerek] = useState(false);
  const [manualModelSeri, setManualModelSeri] = useState(false);

  // Form Fields
  const [noNota, setNoNota] = useState('');
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
  const [kelengkapan, setKelengkapan] = useState('hanya unit');
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

  // Load Master Data & Existing Servis Data
  useEffect(() => {
    const fetchData = async () => {
      setIsLoading(true);
      try {
        const [resPel, resJb, resMerek, resKap, resTek, resTindakan, resProduk, resServis] =
          await Promise.all([
            api.get('/pelanggan', { params: { per_page: 100 } }),
            api.get('/master/jenis-barang'),
            api.get('/master/master-merek'),
            api.get('/master/master-kapasitas'),
            api.get('/akun', { params: { role: 'Teknisi' } }),
            api.get('/servis/tindakan-servis'),
            api.get('/produk/daftar-produk', { params: { per_page: 200 } }),
            api.get(`/servis/${servisId}`),
          ]);

        setPelangganList(Array.isArray(resPel.data) ? resPel.data : resPel.data.data || []);
        setJenisBarangList(Array.isArray(resJb.data) ? resJb.data : resJb.data.data || []);
        setMerekList(Array.isArray(resMerek.data) ? resMerek.data : resMerek.data.data || []);
        setKapasitasList(Array.isArray(resKap.data) ? resKap.data : resKap.data.data || []);
        setTeknisiList(Array.isArray(resTek.data) ? resTek.data : resTek.data.data || []);
        setTindakanMasterList(Array.isArray(resTindakan.data) ? resTindakan.data : resTindakan.data.data || []);
        setProdukList(Array.isArray(resProduk.data) ? resProduk.data : resProduk.data.data || []);

        // Populate Existing Data
        const s = resServis.data;
        if (s) {
          setNoNota(s.no_nota || `#${s.id}`);
          setSelectedPelangganId(s.pelanggan?.id || s.pelanggan_id || '');
          setSelectedJenisBarangId(s.jenis_barang_id || '');
          setSelectedMerekId(s.merek_id || '');
          setSelectedModelSeriId(s.model_seri_id || '');
          setImei(s.imei || '');
          setWarna(s.warna || '');
          setKapasitas(s.kapasitas || '8 GB');
          setKelengkapan(s.kelengkapan || 'hanya unit');
          setKerusakan(s.kerusakan || '');
          setCatatan(s.catatan || s.catatan_teknisi || '');
          setUangMuka(s.dp || '');
          setEstimasiBiaya(s.total_biaya || s.biaya || '');
          setDiskon(s.diskon || '');
          setCaraPembayaran(s.cara_pembayaran || 'Tunai');
          setStatusPembayaran(s.status_pembayaran || (s.sisa_bayar <= 0 ? 'Lunas' : 'Belum Lunas'));
          setKondisiServis(s.kondisi_servis || 'Sudah jadi');
          if (s.tipe === 'Langsung' || s.kondisi_servis === 'Sudah jadi' || s.multi_teknisi?.length > 0) {
            setMode('langsung');
          }

          // Populate QC List if available
          if (s.qc_masuk || s.qc_keluar) {
            const qcIn = typeof s.qc_masuk === 'object' ? s.qc_masuk : {};
            const qcOut = typeof s.qc_keluar === 'object' ? s.qc_keluar : {};
            const keys = Array.from(new Set([...DEFAULT_QC_ITEMS, ...Object.keys(qcIn), ...Object.keys(qcOut)]));
            setQcList(
              keys.map((item, idx) => ({
                no: idx + 1,
                item,
                remark_in: qcIn[item] || '-',
                remark_out: qcOut[item] || '-',
              }))
            );
          }

          // Populate Multi-Teknisi if available
          if (s.multi_teknisi && s.multi_teknisi.length > 0) {
            setTeknisiCards(
              s.multi_teknisi.map((tk: any, idx: number) => ({
                id: `tek-${idx + 1}`,
                user_id: tk.teknisi_id || tk.user_id || '',
                tipe_bagi_hasil: tk.tipe || 'Persentase',
                tindakan_list: [
                  {
                    tindakan_nama: tk.tindakan_nama || tk.catatan || '',
                    garansi: 'Tidak Ada',
                    pakai_sparepart_toko: false,
                    modal_part: 0,
                    biaya_servis: Number(tk.biaya || 0),
                  },
                ],
              }))
            );
          } else if (s.teknisi_id || s.teknisi?.id) {
            setTeknisiCards([
              {
                id: 'tek-1',
                user_id: s.teknisi_id || s.teknisi?.id || '',
                tipe_bagi_hasil: 'Persentase',
                tindakan_list: [
                  {
                    tindakan_nama: s.tindakan || '',
                    garansi: 'Tidak Ada',
                    pakai_sparepart_toko: false,
                    modal_part: Number(s.biaya_sparepart || 0),
                    biaya_servis: Number(s.total_biaya || 0),
                  },
                ],
              },
            ]);
          }
        }
      } catch (err) {
        console.error('Failed to load servis detail', err);
        Swal.fire('Error', 'Gagal memuat detail servis', 'error');
      } finally {
        setIsLoading(false);
      }
    };

    fetchData();
  }, [servisId]);

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
    if (teknisiCards.length <= 1) {
      Swal.fire('Info', 'Minimal harus ada 1 Teknisi sebagai PIC utama.', 'info');
      return;
    }
    setTeknisiCards((prev) => prev.filter((c) => c.id !== cardId));
  };

  const handleAddTindakan = (cardIndex: number) => {
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
      if (updated[cardIndex].tindakan_list.length <= 1) {
        Swal.fire('Info', 'Minimal 1 tindakan per teknisi.', 'info');
        return prev;
      }
      updated[cardIndex].tindakan_list = updated[cardIndex].tindakan_list.filter(
        (_, idx) => idx !== actionIndex
      );
      return updated;
    });
  };

  const handleTindakanChange = (
    cardIndex: number,
    actionIndex: number,
    field: keyof TindakanItem,
    value: any
  ) => {
    setTeknisiCards((prev) => {
      const updated = [...prev];
      const target = updated[cardIndex].tindakan_list[actionIndex];
      (target as any)[field] = value;
      return updated;
    });
  };

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

  // Form Submit (Update Servis)
  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsSubmitting(true);

    try {
      let finalPelangganId = selectedPelangganId;
      if (manualPelanggan && manualPelangganNama.trim()) {
        const resPel = await api.post('/pelanggan', {
          nama: manualPelangganNama,
          no_hp: manualPelangganHp,
        });
        finalPelangganId = resPel.data.id;
      }

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

      const qcMasukDict = qcList.reduce((acc: any, cur) => {
        acc[cur.item] = cur.remark_in;
        return acc;
      }, {});

      const qcKeluarDict = qcList.reduce((acc: any, cur) => {
        acc[cur.item] = cur.remark_out || '-';
        return acc;
      }, {});

      const primaryTeknisiId = teknisiCards[0]?.user_id || undefined;

      const payload = {
        pelanggan_id: finalPelangganId || undefined,
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
        teknisi_id: primaryTeknisiId,
        multi_teknisi: teknisiCards,
        total_modal_sparepart: totalModalPart,
        total_biaya: totalBiayaServis > 0 ? totalBiayaServis - Number(diskon || 0) : Number(estimasiBiaya || 0),
        diskon: Number(diskon || 0),
        dp: Number(uangMuka || 0),
        cara_pembayaran: caraPembayaran,
        status_pembayaran: statusPembayaran,
        catatan,
        qc_masuk: qcMasukDict,
        qc_keluar: qcKeluarDict,
        tipe: mode === 'langsung' ? 'Langsung' : 'Ditinggal',
      };

      await api.put(`/servis/${servisId}`, payload);
      Swal.fire({
        icon: 'success',
        title: 'Berhasil Disimpan!',
        text: 'Data transaksi servis berhasil diperbarui.',
        timer: 2000,
        showConfirmButton: false,
      });

      router.push('/servis');
    } catch (err: any) {
      Swal.fire('Gagal Menyimpan', err.response?.data?.detail || 'Terjadi kesalahan sistem', 'error');
    } finally {
      setIsSubmitting(false);
    }
  };

  const handlePrintQC = () => {
    window.open(`/api/v1/servis/${servisId}/cetak/qc`, '_blank');
  };

  if (isLoading) {
    return (
      <div className="flex flex-col items-center justify-center min-h-[60vh] space-y-3">
        <div className="w-10 h-10 border-4 border-[#5051F9] border-t-transparent rounded-full animate-spin"></div>
        <p className="text-sm font-semibold text-slate-600">Memuat data servis #{servisId}...</p>
      </div>
    );
  }

  return (
    <div className="space-y-5 pb-12 max-w-5xl mx-auto">
      {/* Top Header Navigation */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-4 rounded-xl border border-slate-200 shadow-2xs">
        <div className="flex items-center space-x-3">
          <Link
            href="/servis"
            className="p-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors"
          >
            <ArrowLeft className="w-4 h-4" />
          </Link>
          <div>
            <h1 className="text-lg font-bold text-slate-900 flex items-center gap-2">
              <span>Edit Transaksi Servis</span>
              <span className="text-[#5051F9]">{noNota}</span>
            </h1>
            <p className="text-xs text-slate-500">
              Ubah data rincian unit, master kategori, tim teknisi, dan QC check.
            </p>
          </div>
        </div>

        <div className="flex items-center space-x-2">
          <button
            type="button"
            onClick={handlePrintQC}
            className="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-xs font-semibold text-slate-700 shadow-2xs transition-colors cursor-pointer"
          >
            <Printer className="w-4 h-4 text-blue-600" />
            <span>Cetak PDF QC</span>
          </button>
        </div>
      </div>

      <form onSubmit={handleSubmit} className="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-6 text-xs text-slate-700">
        {/* Mode Selector Radio (Ditinggal / Langsung) matching screenshot */}
        <div className="flex items-center space-x-6 pb-4 border-b border-slate-100">
          <label className="flex items-center space-x-2 cursor-pointer font-bold text-slate-800">
            <input
              type="radio"
              name="transaksi_mode"
              value="ditinggal"
              checked={mode === 'ditinggal'}
              onChange={() => setMode('ditinggal')}
              className="w-4 h-4 text-[#5051F9] focus:ring-0 cursor-pointer"
            />
            <span>Ditinggal</span>
          </label>

          <label className="flex items-center space-x-2 cursor-pointer font-bold text-slate-800">
            <input
              type="radio"
              name="transaksi_mode"
              value="langsung"
              checked={mode === 'langsung'}
              onChange={() => setMode('langsung')}
              className="w-4 h-4 text-[#5051F9] focus:ring-0 cursor-pointer"
            />
            <span>Langsung</span>
          </label>
        </div>

        {/* 1. DATA PELANGGAN */}
        <div className="space-y-2">
          <label className="block font-bold text-slate-800">
            Nama Pelanggan <span className="text-rose-500">*</span>
          </label>
          {!manualPelanggan ? (
            <SearchableSelect
              options={pelangganList.map((p) => ({
                value: p.id,
                label: p.nama,
                subLabel: p.no_hp || p.nomor_hp || '',
              }))}
              value={selectedPelangganId}
              onChange={(val) => setSelectedPelangganId(val)}
              placeholder="Pilih Pelanggan..."
            />
          ) : (
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-2">
              <input
                type="text"
                value={manualPelangganNama}
                onChange={(e) => setManualPelangganNama(e.target.value)}
                placeholder="Nama Pelanggan Baru..."
                className="w-full px-2.5 py-1.5 border border-slate-300 rounded-md focus:border-[#5051F9] text-xs"
              />
              <input
                type="text"
                value={manualPelangganHp}
                onChange={(e) => setManualPelangganHp(e.target.value)}
                placeholder="Nomor HP / WhatsApp..."
                className="w-full px-2.5 py-1.5 border border-slate-300 rounded-md focus:border-[#5051F9] text-xs"
              />
            </div>
          )}
          <label className="inline-flex items-center space-x-1.5 cursor-pointer mt-1">
            <input
              type="checkbox"
              checked={manualPelanggan}
              onChange={(e) => setManualPelanggan(e.target.checked)}
              className="rounded text-[#5051F9] focus:ring-0 cursor-pointer"
            />
            <span className="text-[11px] text-slate-500">
              Isi manual <span className="text-rose-500">*data akan auto masuk ke master data</span>
            </span>
          </label>
        </div>

        {/* 2. CASCADING MASTER DATA: JENIS BARANG, MEREK, MODEL SERI */}
        <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
          {/* Jenis Barang */}
          <div className="space-y-1.5">
            <label className="block font-bold text-slate-800">
              Jenis Barang <span className="text-rose-500">*</span>
            </label>
            {!manualJenisBarang ? (
              <SearchableSelect
                options={jenisBarangList.map((j) => ({
                  value: j.id,
                  label: j.name || j.nama,
                }))}
                value={selectedJenisBarangId}
                onChange={(val) => setSelectedJenisBarangId(val)}
                placeholder="Pilih Jenis Barang..."
              />
            ) : (
              <input
                type="text"
                value={manualJenisBarangNama}
                onChange={(e) => setManualJenisBarangNama(e.target.value)}
                placeholder="Ketik Jenis Barang..."
                className="w-full px-2.5 py-1.5 border border-slate-300 rounded-md focus:border-[#5051F9] text-xs"
              />
            )}
            <label className="inline-flex items-center space-x-1.5 cursor-pointer">
              <input
                type="checkbox"
                checked={manualJenisBarang}
                onChange={(e) => setManualJenisBarang(e.target.checked)}
                className="rounded text-[#5051F9] focus:ring-0 cursor-pointer"
              />
              <span className="text-[10px] text-slate-500">Isi manual</span>
            </label>
          </div>

          {/* Merek */}
          <div className="space-y-1.5">
            <label className="block font-bold text-slate-800">
              Merek <span className="text-rose-500">*</span>
            </label>
            {!manualMerek ? (
              <SearchableSelect
                options={merekList.map((m) => ({
                  value: m.id,
                  label: m.name || m.nama,
                }))}
                value={selectedMerekId}
                onChange={(val) => setSelectedMerekId(val)}
                placeholder="Pilih Merek..."
              />
            ) : (
              <input
                type="text"
                value={manualMerekNama}
                onChange={(e) => setManualMerekNama(e.target.value)}
                placeholder="Ketik Merek..."
                className="w-full px-2.5 py-1.5 border border-slate-300 rounded-md focus:border-[#5051F9] text-xs"
              />
            )}
            <label className="inline-flex items-center space-x-1.5 cursor-pointer">
              <input
                type="checkbox"
                checked={manualMerek}
                onChange={(e) => setManualMerek(e.target.checked)}
                className="rounded text-[#5051F9] focus:ring-0 cursor-pointer"
              />
              <span className="text-[10px] text-slate-500">Isi manual</span>
            </label>
          </div>

          {/* Model Seri */}
          <div className="space-y-1.5">
            <label className="block font-bold text-slate-800">
              Model Seri <span className="text-rose-500">*</span>
            </label>
            {!manualModelSeri ? (
              <SearchableSelect
                options={modelSeriList.map((s) => ({
                  value: s.id,
                  label: s.name || s.nama,
                }))}
                value={selectedModelSeriId}
                onChange={(val) => setSelectedModelSeriId(val)}
                placeholder="Pilih Model Seri..."
              />
            ) : (
              <input
                type="text"
                value={manualModelSeriNama}
                onChange={(e) => setManualModelSeriNama(e.target.value)}
                placeholder="Ketik Model Seri..."
                className="w-full px-2.5 py-1.5 border border-slate-300 rounded-md focus:border-[#5051F9] text-xs"
              />
            )}
            <label className="inline-flex items-center space-x-1.5 cursor-pointer">
              <input
                type="checkbox"
                checked={manualModelSeri}
                onChange={(e) => setManualModelSeri(e.target.checked)}
                className="rounded text-[#5051F9] focus:ring-0 cursor-pointer"
              />
              <span className="text-[10px] text-slate-500">Isi manual</span>
            </label>
          </div>
        </div>

        {/* 3. DEVICE SPECS: IMEI, WARNA, KAPASITAS, KELENGKAPAN */}
        <div className="grid grid-cols-1 sm:grid-cols-4 gap-4">
          <div>
            <label className="block font-bold text-slate-800 mb-1">Nomor Imei *</label>
            <input
              type="text"
              value={imei}
              onChange={(e) => setImei(e.target.value)}
              placeholder="Contoh: 356789123456789"
              className="w-full px-2.5 py-1.5 border border-slate-300 rounded-md focus:border-[#5051F9] text-xs"
            />
          </div>

          <div>
            <label className="block font-bold text-slate-800 mb-1">Warna *</label>
            <input
              type="text"
              value={warna}
              onChange={(e) => setWarna(e.target.value)}
              placeholder="Hitam, Putih, Gold..."
              className="w-full px-2.5 py-1.5 border border-slate-300 rounded-md focus:border-[#5051F9] text-xs"
            />
          </div>

          <div>
            <label className="block font-bold text-slate-800 mb-1">Kapasitas *</label>
            <SearchableSelect
              options={
                kapasitasList.length > 0
                  ? kapasitasList.map((k) => ({
                      value: k.name || k.nama,
                      label: k.name || k.nama,
                    }))
                  : [
                      { value: '64 GB', label: '64 GB' },
                      { value: '128 GB', label: '128 GB' },
                      { value: '256 GB', label: '256 GB' },
                      { value: '512 GB', label: '512 GB' },
                    ]
              }
              value={kapasitas}
              onChange={(val) => setKapasitas(val)}
              placeholder="Pilih Kapasitas..."
            />
          </div>

          <div>
            <label className="block font-bold text-slate-800 mb-1">Kelengkapan</label>
            <input
              type="text"
              value={kelengkapan}
              onChange={(e) => setKelengkapan(e.target.value)}
              placeholder="hanya unit"
              className="w-full px-2.5 py-1.5 border border-slate-300 rounded-md focus:border-[#5051F9] text-xs"
            />
          </div>
        </div>

        {/* 4. KERUSAKAN */}
        <div>
          <label className="block font-bold text-slate-800 mb-1">
            Kerusakan <span className="text-rose-500">*</span>
          </label>
          <input
            type="text"
            value={kerusakan}
            onChange={(e) => setKerusakan(e.target.value)}
            placeholder="Deskripsi kerusakan perangkat..."
            required
            className="w-full px-2.5 py-1.5 border border-slate-300 rounded-md focus:border-[#5051F9] text-xs"
          />
        </div>

        {/* 5. QUALITY CONTROL CHECKLIST TABLE */}
        <div className="border border-slate-200 rounded-xl overflow-hidden">
          <div className="bg-slate-50 px-4 py-2.5 border-b border-slate-200 flex items-center justify-between">
            <div>
              <span className="font-bold text-slate-800">
                List Pengecekan Fungsi ({mode === 'ditinggal' ? 'Masuk' : 'Masuk & Keluar'}) *
              </span>
              <span className="ml-2 text-[10px] text-rose-500 font-medium">
                *Jika ingin cepat silahkan isi kolom other.
              </span>
            </div>
            <button
              type="button"
              onClick={handleAddCustomQc}
              className="inline-flex items-center space-x-1 text-xs font-bold text-[#5051F9] hover:underline cursor-pointer"
            >
              <Plus className="w-3.5 h-3.5" />
              <span>Tambah Baris Custom</span>
            </button>
          </div>

          <div className="max-h-64 overflow-y-auto">
            <table className="w-full text-left text-xs">
              <thead className="bg-slate-100 text-slate-600 font-bold uppercase text-[10px] sticky top-0 z-10 border-b border-slate-200">
                <tr>
                  <th className="w-8 px-2 py-1.5 text-center">NO</th>
                  <th className="px-3 py-1.5">ITEM</th>
                  <th className="w-40 px-2 py-1.5 text-center">REMARK IN</th>
                  {mode === 'langsung' && (
                    <th className="w-40 px-2 py-1.5 text-center">REMARK OUT</th>
                  )}
                  <th className="w-8 px-2 py-1.5 text-center"></th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100">
                {qcList.map((qc, index) => (
                  <tr key={qc.no} className="hover:bg-slate-50/60">
                    <td className="px-2 py-1 text-center font-bold text-slate-500">
                      {index + 1}
                    </td>
                    <td className="px-3 py-1 font-semibold text-slate-700 uppercase text-[11px]">
                      {qc.item}
                    </td>
                    <td className="px-2 py-1 text-center">
                      <input
                        type="text"
                        value={qc.remark_in}
                        onChange={(e) => handleQcChange(index, 'remark_in', e.target.value)}
                        className="w-full text-center px-1.5 py-0.5 border border-slate-200 rounded text-xs focus:border-[#5051F9]"
                      />
                    </td>
                    {mode === 'langsung' && (
                      <td className="px-2 py-1 text-center">
                        <input
                          type="text"
                          value={qc.remark_out || '-'}
                          onChange={(e) => handleQcChange(index, 'remark_out', e.target.value)}
                          className="w-full text-center px-1.5 py-0.5 border border-slate-200 rounded text-xs focus:border-[#5051F9]"
                        />
                      </td>
                    )}
                    <td className="px-2 py-1 text-center">
                      <button
                        type="button"
                        onClick={() => handleRemoveQc(index)}
                        className="text-slate-400 hover:text-rose-500 cursor-pointer"
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

        {/* 6. MODE SPESIFIK: DITINGGAL */}
        {mode === 'ditinggal' && (
          <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
            <div>
              <label className="block font-bold text-slate-800 mb-1">Estimasi Pengerjaan</label>
              <select
                value={estimasiPengerjaan}
                onChange={(e) => setEstimasiPengerjaan(e.target.value)}
                className="w-full px-2.5 py-1.5 border border-slate-300 rounded-md focus:border-[#5051F9] bg-white text-xs cursor-pointer"
              >
                <option value="1 Hari">1 Hari</option>
                <option value="2 Hari">2 Hari</option>
                <option value="3 Hari">3 Hari</option>
                <option value="1 Minggu">1 Minggu</option>
                <option value="2 Minggu">2 Minggu</option>
              </select>
            </div>

            <div>
              <label className="block font-bold text-slate-800 mb-1">Estimasi Biaya Servis</label>
              <input
                type="number"
                value={estimasiBiaya}
                onChange={(e) => setEstimasiBiaya(Number(e.target.value))}
                placeholder="Rp. Kosongkan jika tidak ada"
                className="w-full px-2.5 py-1.5 border border-slate-300 rounded-md focus:border-[#5051F9] text-xs"
              />
            </div>

            <div>
              <label className="block font-bold text-slate-800 mb-1">DP / Uang Muka</label>
              <input
                type="number"
                value={uangMuka}
                onChange={(e) => setUangMuka(Number(e.target.value))}
                placeholder="Rp. Kosongkan jika tidak ada"
                className="w-full px-2.5 py-1.5 border border-slate-300 rounded-md focus:border-[#5051F9] text-xs"
              />
            </div>
          </div>
        )}

        {/* 7. MODE SPESIFIK: LANGSUNG (MULTI TEKNISI & ACTIONS) */}
        {mode === 'langsung' && (
          <div className="space-y-4 pt-2 border-t border-slate-100">
            {/* Kondisi Servis */}
            <div>
              <label className="block font-bold text-slate-800 mb-1.5">
                Kondisi Servis <span className="text-rose-500">*</span>
              </label>
              <div className="flex items-center space-x-6">
                {['Sudah jadi', 'Menunggu konfirmasi', 'Dibatalkan'].map((val) => (
                  <label key={val} className="flex items-center space-x-2 cursor-pointer font-medium">
                    <input
                      type="radio"
                      name="kondisi_servis_edit"
                      value={val}
                      checked={kondisiServis === val}
                      onChange={(e) => setKondisiServis(e.target.value)}
                      className="w-4 h-4 text-[#5051F9] focus:ring-0 cursor-pointer"
                    />
                    <span>{val}</span>
                  </label>
                ))}
              </div>
            </div>

            {/* Teknisi Cards Multi-Teknisi */}
            {teknisiCards.map((card, cardIdx) => (
              <div
                key={card.id}
                className="border-2 border-slate-200 rounded-xl p-4 bg-slate-50/50 space-y-4 relative"
              >
                <div className="flex items-center justify-between pb-2 border-b border-slate-200">
                  <div className="flex items-center space-x-2">
                    <span className="font-bold text-slate-800 text-xs">
                      Data Teknisi #{cardIdx + 1}
                    </span>
                    {cardIdx === 0 && (
                      <span className="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[10px]">
                        PIC UTAMA
                      </span>
                    )}
                  </div>

                  {teknisiCards.length > 1 && (
                    <button
                      type="button"
                      onClick={() => handleRemoveTeknisiCard(card.id)}
                      className="px-2 py-1 rounded bg-rose-500 hover:bg-rose-600 text-white font-bold text-[10px] transition-colors cursor-pointer"
                    >
                      Hapus Teknisi
                    </button>
                  )}
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  {/* Select Teknisi */}
                  <div>
                    <label className="block font-bold text-slate-700 mb-1">
                      Nama Teknisi <span className="text-rose-500">*</span>
                    </label>
                    <SearchableSelect
                      options={teknisiList.map((t) => ({
                        value: t.id,
                        label: t.nama || t.name,
                        subLabel: t.bagian_teknisi || 'Teknisi',
                      }))}
                      value={card.user_id}
                      onChange={(val) => {
                        setTeknisiCards((prev) => {
                          const updated = [...prev];
                          updated[cardIdx].user_id = val;
                          return updated;
                        });
                      }}
                      placeholder="Pilih Teknisi..."
                    />
                  </div>

                  {/* Select Tipe Bagi Hasil */}
                  <div>
                    <label className="block font-bold text-slate-700 mb-1">
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
                      className="w-full px-2.5 py-1.5 border border-slate-300 rounded-md focus:border-[#5051F9] bg-white text-xs cursor-pointer"
                    >
                      <option value="Interface">Interface (bonus pertipe tetap)</option>
                      <option value="Interface Leveling">Interface (bonus pertipe leveling)</option>
                      <option value="Interface Persentase">Interface Persentase</option>
                      <option value="Hardware">Hardware & interface (bonus persen)</option>
                    </select>
                  </div>
                </div>

                {/* Tindakan List */}
                <div className="space-y-3">
                  {card.tindakan_list.map((act, actIdx) => (
                    <div
                      key={actIdx}
                      className="p-3 bg-white border border-slate-200 rounded-lg space-y-3 relative"
                    >
                      <div className="flex items-center justify-between">
                        <span className="font-bold text-slate-800 text-[11px]">
                          Tindakan #{actIdx + 1}
                        </span>
                        {card.tindakan_list.length > 1 && (
                          <button
                            type="button"
                            onClick={() => handleRemoveTindakan(cardIdx, actIdx)}
                            className="p-1 rounded text-rose-500 hover:bg-rose-50 transition-colors cursor-pointer"
                            title="Hapus Tindakan"
                          >
                            <Trash2 className="w-3.5 h-3.5" />
                          </button>
                        )}
                      </div>

                      <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        {/* Tindakan Nama */}
                        <div>
                          <div className="flex items-center justify-between mb-1">
                            <label className="font-semibold text-slate-700">Tindakan Servis *</label>
                            <label className="flex items-center space-x-1 cursor-pointer">
                              <input
                                type="checkbox"
                                checked={act.is_manual}
                                onChange={(e) =>
                                  handleTindakanChange(cardIdx, actIdx, 'is_manual', e.target.checked)
                                }
                                className="rounded text-[#5051F9] focus:ring-0 cursor-pointer"
                              />
                              <span className="text-[10px] text-slate-500">Isi Manual</span>
                            </label>
                          </div>

                          {!act.is_manual ? (
                            <SearchableSelect
                              options={tindakanMasterList.map((t) => ({
                                value: t.id,
                                label: t.nama || t.nama_tindakan,
                                subLabel: `Rp ${Number(t.harga || 0).toLocaleString('id-ID')}`,
                              }))}
                              value={act.tindakan_id}
                              onChange={(val) => {
                                const found = tindakanMasterList.find((t) => t.id === val);
                                handleTindakanChange(cardIdx, actIdx, 'tindakan_id', val);
                                handleTindakanChange(cardIdx, actIdx, 'tindakan_nama', found?.nama || '');
                                if (found?.harga) {
                                  handleTindakanChange(cardIdx, actIdx, 'biaya_servis', Number(found.harga));
                                }
                              }}
                              placeholder="Pilih Tindakan..."
                            />
                          ) : (
                            <input
                              type="text"
                              value={act.tindakan_nama}
                              onChange={(e) =>
                                handleTindakanChange(cardIdx, actIdx, 'tindakan_nama', e.target.value)
                              }
                              placeholder="Ketik tindakan..."
                              className="w-full px-2.5 py-1.5 border border-slate-300 rounded-md focus:border-[#5051F9] text-xs"
                            />
                          )}
                        </div>

                        {/* Garansi */}
                        <div>
                          <label className="block font-semibold text-slate-700 mb-1">Garansi</label>
                          <select
                            value={act.garansi}
                            onChange={(e) =>
                              handleTindakanChange(cardIdx, actIdx, 'garansi', e.target.value)
                            }
                            className="w-full px-2.5 py-1.5 border border-slate-300 rounded-md focus:border-[#5051F9] bg-white text-xs cursor-pointer"
                          >
                            <option value="Tidak Ada">Tidak Ada</option>
                            <option value="7 Hari">7 Hari</option>
                            <option value="14 Hari">14 Hari</option>
                            <option value="30 Hari">30 Hari</option>
                            <option value="90 Hari">90 Hari</option>
                          </select>
                        </div>
                      </div>

                      {/* Sparepart Toko Toggle */}
                      <div>
                        <label className="block font-semibold text-slate-700 mb-1">
                          Pakai Sparepart Toko?
                        </label>
                        <div className="flex items-center space-x-4 mb-2">
                          <label className="flex items-center space-x-1.5 cursor-pointer">
                            <input
                              type="radio"
                              name={`sparepart_opt_${cardIdx}_${actIdx}`}
                              checked={!act.pakai_sparepart_toko}
                              onChange={() =>
                                handleTindakanChange(cardIdx, actIdx, 'pakai_sparepart_toko', false)
                              }
                              className="w-3.5 h-3.5 text-[#5051F9] focus:ring-0 cursor-pointer"
                            />
                            <span>Tidak</span>
                          </label>
                          <label className="flex items-center space-x-1.5 cursor-pointer">
                            <input
                              type="radio"
                              name={`sparepart_opt_${cardIdx}_${actIdx}`}
                              checked={act.pakai_sparepart_toko}
                              onChange={() =>
                                handleTindakanChange(cardIdx, actIdx, 'pakai_sparepart_toko', true)
                              }
                              className="w-3.5 h-3.5 text-[#5051F9] focus:ring-0 cursor-pointer"
                            />
                            <span>Ya</span>
                          </label>
                        </div>

                        {act.pakai_sparepart_toko && (
                          <div className="mb-2">
                            <SearchableSelect
                              options={produkList.map((p) => ({
                                value: p.id,
                                label: p.nama || p.product_name,
                                subLabel: `Modal: Rp ${Number(p.harga_modal || 0).toLocaleString('id-ID')} | Stok: ${p.stok || 0}`,
                              }))}
                              value={act.produk_id}
                              onChange={(val) => {
                                const found = produkList.find((p) => p.id === val);
                                handleTindakanChange(cardIdx, actIdx, 'produk_id', val);
                                if (found?.harga_modal) {
                                  handleTindakanChange(cardIdx, actIdx, 'modal_part', Number(found.harga_modal));
                                }
                              }}
                              placeholder="Pilih Sparepart dari Inventaris..."
                            />
                          </div>
                        )}

                        <div className="grid grid-cols-2 gap-3">
                          <div>
                            <label className="block font-semibold text-slate-700 mb-1">Modal Part *</label>
                            <input
                              type="number"
                              value={act.modal_part}
                              onChange={(e) =>
                                handleTindakanChange(cardIdx, actIdx, 'modal_part', Number(e.target.value))
                              }
                              className="w-full px-2.5 py-1.5 border border-slate-300 rounded-md focus:border-[#5051F9] text-xs font-semibold"
                            />
                          </div>
                          <div>
                            <label className="block font-semibold text-slate-700 mb-1">Biaya Servis *</label>
                            <input
                              type="number"
                              value={act.biaya_servis}
                              onChange={(e) =>
                                handleTindakanChange(cardIdx, actIdx, 'biaya_servis', Number(e.target.value))
                              }
                              className="w-full px-2.5 py-1.5 border border-slate-300 rounded-md focus:border-[#5051F9] text-xs font-bold text-slate-900"
                            />
                          </div>
                        </div>
                      </div>
                    </div>
                  ))}

                  <button
                    type="button"
                    onClick={() => handleAddTindakan(cardIdx)}
                    className="w-full py-1.5 rounded-lg border border-dashed border-[#5051F9] text-[#5051F9] hover:bg-[#5051F9]/5 font-bold text-xs transition-colors cursor-pointer"
                  >
                    + Tambah Tindakan Lain (Untuk Teknisi Ini)
                  </button>
                </div>
              </div>
            ))}

            {/* Button Tambah Teknisi Baru */}
            <button
              type="button"
              onClick={handleAddTeknisiCard}
              className="w-full py-2 rounded-xl bg-[#5051F9] hover:bg-[#4344db] text-white font-bold text-xs shadow-xs transition-colors cursor-pointer flex items-center justify-center space-x-1.5"
            >
              <Plus className="w-4 h-4" />
              <span>+ Tambah Teknisi Baru</span>
            </button>

            {/* Financial Summary & Payment Details */}
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
              <div className="bg-slate-100 p-3 rounded-xl space-y-2">
                <div>
                  <span className="block text-slate-500 font-semibold text-[11px]">Total Modal / Sparepart</span>
                  <span className="text-base font-bold text-slate-800">
                    Rp {totalModalPart.toLocaleString('id-ID')}
                  </span>
                </div>
                <div>
                  <span className="block text-slate-500 font-semibold text-[11px]">Total Biaya Servis (Ke Pelanggan)</span>
                  <span className="text-lg font-black text-[#5051F9]">
                    Rp {totalBiayaServis.toLocaleString('id-ID')}
                  </span>
                </div>
              </div>

              <div className="space-y-3">
                <div>
                  <label className="block font-bold text-slate-800 mb-1">Diskon</label>
                  <input
                    type="number"
                    value={diskon}
                    onChange={(e) => setDiskon(Number(e.target.value))}
                    placeholder="Kosongkan jika tidak ada diskon"
                    className="w-full px-2.5 py-1.5 border border-slate-300 rounded-md focus:border-[#5051F9] text-xs"
                  />
                </div>

                <div className="grid grid-cols-2 gap-2">
                  <div>
                    <label className="block font-bold text-slate-800 mb-1">Cara Pembayaran</label>
                    <select
                      value={caraPembayaran}
                      onChange={(e) => setCaraPembayaran(e.target.value)}
                      className="w-full px-2.5 py-1.5 border border-slate-300 rounded-md focus:border-[#5051F9] bg-white text-xs cursor-pointer"
                    >
                      <option value="Tunai">Tunai</option>
                      <option value="Transfer">Transfer</option>
                      <option value="QRIS">QRIS</option>
                      <option value="Debit">Debit</option>
                      <option value="Split">Split (Tunai & Transfer)</option>
                    </select>
                  </div>

                  <div>
                    <label className="block font-bold text-slate-800 mb-1">Status Pembayaran</label>
                    <select
                      value={statusPembayaran}
                      onChange={(e) => setStatusPembayaran(e.target.value)}
                      className="w-full px-2.5 py-1.5 border border-slate-300 rounded-md focus:border-[#5051F9] bg-white text-xs cursor-pointer"
                    >
                      <option value="Lunas">Lunas</option>
                      <option value="Belum Lunas">Belum Lunas</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <div>
              <label className="block font-bold text-slate-800 mb-1">Catatan</label>
              <textarea
                value={catatan}
                onChange={(e) => setCatatan(e.target.value)}
                rows={2}
                placeholder="Tulis catatan yang diperlukan untuk pelanggan..."
                className="w-full px-2.5 py-1.5 border border-slate-300 rounded-md focus:border-[#5051F9] text-xs"
              />
            </div>
          </div>
        )}

        {/* Footer Action Buttons */}
        <div className="flex items-center justify-end space-x-3 pt-4 border-t border-slate-200">
          <Link
            href="/servis"
            className="px-4 py-2 rounded-lg border border-slate-300 hover:bg-slate-100 text-slate-700 font-semibold text-xs transition-colors"
          >
            Batal
          </Link>
          <button
            type="submit"
            disabled={isSubmitting}
            className="inline-flex items-center space-x-1.5 px-5 py-2 rounded-lg bg-[#5051F9] hover:bg-[#4344db] text-white font-bold text-xs shadow-xs transition-colors cursor-pointer disabled:opacity-50"
          >
            <Save className="w-4 h-4" />
            <span>{isSubmitting ? 'Menyimpan...' : 'Simpan Perubahan'}</span>
          </button>
        </div>
      </form>
    </div>
  );
}

// API Types definition for SarabaBisa Premium

export interface User {
  id: number;
  nama: string;
  username: string;
  email?: string;
  role: 'KepalaToko' | 'AdminToko' | 'Teknisi' | 'Sales';
  cabang_id: number;
  foto?: string;
  no_hp?: string;
  is_active: boolean;
  assigned_cabang_ids?: number[];
  assigned_cabangs?: { id: number; nama: string }[];
}

export interface Cabang {
  id: number;
  nama: string;
  alamat?: string;
  no_telp?: string;
  logo?: string;
  status?: number;
}

export interface AuthState {
  user: User | null;
  token: string | null;
  activeCabangId: number | null;
  activeCabangNama: string | null;
  isAuthenticated: boolean;
  isLoading: boolean;
}

export interface ServisItem {
  id: number;
  no_nota: string;
  status: string;
  nama_pelanggan: string;
  pelanggan_id?: number;
  teknisi_nama?: string;
  teknisi_id?: number;
  merek_nama?: string;
  model_seri_nama?: string;
  biaya_tindakan: number;
  biaya_sparepart: number;
  total_biaya: number;
  dp: number;
  sisa_bayar: number;
  profit?: number;
  is_approved?: boolean;
  tgl_masuk?: string;
  tgl_selesai?: string;
  created_at?: string;
  imei?: string;
  kerusakan?: string;
  kelengkapan?: string;
  pin?: string;
  pola?: string;
}

export interface ProdukItem {
  id: number;
  nama: string;
  kode: string;
  barcode?: string;
  tipe: 'handphone' | 'sparepart' | 'aksesoris' | 'tools';
  kategori_nama?: string;
  sub_kategori_nama?: string;
  merek_nama?: string;
  harga_beli: number;
  harga_jual: number;
  stok: number;
  stok_minimum?: number;
  satuan?: string;
  foto?: string;
  is_portal?: boolean;
  imei?: string;
  imei2?: string;
  kapasitas?: string;
  warna?: string;
  tipe_os?: string;
  kondisi?: string;
  kompatibel_merek?: string;
  kompatibel_model?: string;
  kualitas?: string;
}

export interface CategoryFieldMeta {
  key: string;
  label: string;
  type: 'text' | 'select' | 'number';
  required: boolean;
  options?: string[];
  placeholder?: string;
}

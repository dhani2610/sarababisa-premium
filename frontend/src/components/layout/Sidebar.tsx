'use client';

import React, { useState } from 'react';
import Link from 'next/link';
import { usePathname } from 'next/navigation';
import { useAuth } from '@/context/AuthContext';
import {
  Gauge,
  CircleArrowRight,
  User,
  Wrench,
  Disc,
  LayoutGrid,
  Box,
  Trash2,
  Sliders,
  Archive,
  ShieldCheck,
  Sparkles,
  CreditCard,
  Receipt,
  Building2,
  Wallet,
  ChevronDown,
  ChevronRight,
  LogOut,
  X,
  Clock,
} from 'lucide-react';

interface SubItem {
  title: string;
  href: string;
}

interface MenuItem {
  title: string;
  href?: string;
  icon: any;
  subItems?: SubItem[];
  badge?: string;
}

interface MenuSection {
  header: string;
  items: MenuItem[];
}

export const Sidebar: React.FC<{ isOpen: boolean; onClose: () => void }> = ({ isOpen, onClose }) => {
  const pathname = usePathname();
  const { logout } = useAuth();

  const [openMenus, setOpenMenus] = useState<{ [key: string]: boolean }>({
    Servis: true,
    Produk: false,
    'Master Data': false,
    Laporan: false,
    'Manajemen Toko': false,
    Gaji: false,
  });

  const toggleSubmenu = (key: string) => {
    setOpenMenus((prev) => ({ ...prev, [key]: !prev[key] }));
  };

  const sections: MenuSection[] = [
    {
      header: 'MENU',
      items: [
        {
          title: 'Dashboard',
          href: '/dashboard',
          icon: Gauge,
        },
        {
          title: 'Dashboard Grafik Cabang',
          href: '/dashboard/grafik-cabang',
          icon: Gauge,
        },
        {
          title: 'Akun',
          href: '/karyawan/akun',
          icon: CircleArrowRight,
        },
        {
          title: 'Pelanggan',
          href: '/pelanggan',
          icon: User,
        },
        {
          title: 'Servis',
          icon: Wrench,
          subItems: [
            { title: 'Tindakan', href: '/servis/tindakan' },
            { title: 'Transaksi', href: '/servis' },
            { title: 'Log Servis', href: '/servis/log' },
            { title: 'Riwayat Garansi', href: '/servis/garansi' },
          ],
        },
        {
          title: 'Produk',
          icon: Disc,
          subItems: [
            { title: 'Daftar Produk', href: '/produk' },
            { title: 'Tambah Produk', href: '/produk/tambah' },
            { title: 'Riwayat Stok', href: '/produk/riwayat-stok' },
            { title: 'Transfer Stok', href: '/transfer-stok' },
            { title: 'QC Produk', href: '/produk/qc' },
            { title: 'Tukar Tambah', href: '/transaksi-produk/tukar-tambah' },
            { title: 'Retur Produk', href: '/transaksi-produk/retur' },
            { title: 'Refund Produk', href: '/transaksi-produk/refund' },
          ],
        },
        {
          title: 'Master Data',
          icon: LayoutGrid,
          subItems: [
            { title: 'Jenis Barang', href: '/master/jenis-barang' },
            { title: 'Merek', href: '/master/merek' },
            { title: 'Model Seri', href: '/master/model-seri' },
            { title: 'Kapasitas', href: '/master/kapasitas' },
            { title: 'Warna', href: '/master/warna' },
            { title: 'Tipe OS', href: '/master/tipe-os' },
            { title: 'Cabang', href: '/master/cabang' },
            { title: 'Metode Pembayaran', href: '/master/metode-pembayaran' },
          ],
        },
        {
          title: 'Laporan',
          icon: Box,
          subItems: [
            { title: 'Laporan Servis', href: '/laporan/servis' },
            { title: 'Laporan Penjualan', href: '/laporan/penjualan' },
            { title: 'Laporan Pengeluaran', href: '/laporan/pengeluaran' },
            { title: 'Laporan Keuangan', href: '/laporan/keuangan' },
            { title: 'Laporan Kasir', href: '/laporan/kasir' },
          ],
        },
        {
          title: 'Approval Hapus',
          href: '/transaksi-produk/persetujuan-hapus',
          icon: Trash2,
        },
        {
          title: 'Pengaturan Toko',
          href: '/pengaturan',
          icon: Sliders,
        },
        {
          title: 'Arsip Data',
          href: '/arsip',
          icon: Archive,
        },
        {
          title: 'Audit Log Data',
          href: '/arsip/audit-log',
          icon: ShieldCheck,
        },
      ],
    },
    {
      header: 'RINCIAN INVEST',
      items: [
        {
          title: 'Rincian Invest',
          href: '/manajemen/rincian-invest',
          icon: Sparkles,
        },
        {
          title: 'Pembagian Hasil Invest',
          href: '/manajemen/pembagian-hasil-invest',
          icon: Sparkles,
        },
      ],
    },
    {
      header: 'PEMBAYARAN KREDIT',
      items: [
        {
          title: 'Servis Belum Lunas',
          href: '/servis?status=belum_lunas',
          icon: Sparkles,
        },
        {
          title: 'Produk Belum Lunas',
          href: '/transaksi-produk?status=belum_lunas',
          icon: Clock,
        },
      ],
    },
    {
      header: 'PENGEMBALIAN DANA',
      items: [
        {
          title: 'Invoice',
          href: '/transaksi-produk/invoice',
          icon: Sparkles,
        },
      ],
    },
    {
      header: 'LAINNYA',
      items: [
        {
          title: 'Manajemen Toko',
          icon: Sparkles,
          subItems: [
            { title: 'Anggaran', href: '/manajemen/anggaran' },
            { title: 'Inventaris', href: '/manajemen/inventaris' },
            { title: 'Insiden', href: '/manajemen/insiden' },
            { title: 'Kasbon', href: '/karyawan/kasbon' },
            { title: 'Pengeluaran', href: '/manajemen/pengeluaran' },
          ],
        },
        {
          title: 'Gaji',
          icon: Sparkles,
          subItems: [
            { title: 'Data Karyawan', href: '/karyawan/gaji' },
          ],
        },
        {
          title: 'Keranjang Sampah',
          href: '/manajemen/keranjang-sampah',
          icon: Sparkles,
        },
      ],
    },
  ];

  const isItemActive = (item: MenuItem) => {
    if (item.href && pathname === item.href) return true;
    if (item.subItems?.some((sub) => pathname === sub.href)) return true;
    return false;
  };

  return (
    <>
      {/* Mobile Backdrop */}
      {isOpen && (
        <div
          onClick={onClose}
          className="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-2xs lg:hidden"
        />
      )}

      {/* Sidebar Container matching screenshot */}
      <aside
        className={`fixed top-0 bottom-0 left-0 z-40 w-64 bg-[#141b2d] text-slate-300 flex flex-col transition-transform duration-200 ease-in-out lg:translate-x-0 ${
          isOpen ? 'translate-x-0' : '-translate-x-full'
        }`}
      >
        {/* Brand Header with Stylized SB Gradient Logo matching screenshot */}
        <div className="flex items-center justify-between h-16 px-6 border-b border-slate-800/80 bg-[#0f172a]">
          <Link href="/dashboard" className="flex items-center space-x-2.5">
            {/* SB Stylized Gradient Mark */}
            <div className="relative flex items-center justify-center">
              <span className="text-2xl font-black italic tracking-tighter bg-gradient-to-r from-[#00d2ff] via-[#8b5cf6] to-[#ec4899] bg-clip-text text-transparent drop-shadow-md">
                SB
              </span>
            </div>
            <div className="flex flex-col">
              <span className="text-xs font-black tracking-wide text-white uppercase">
                SARABABISA
              </span>
              <span className="text-[9px] font-semibold text-slate-400 tracking-wider">
                PREMIUM ERP
              </span>
            </div>
          </Link>

          <button
            onClick={onClose}
            className="p-1 rounded-lg text-slate-400 hover:text-white lg:hidden cursor-pointer"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        {/* Scrollable Navigation Menu */}
        <div className="flex-1 overflow-y-auto px-3 py-4 space-y-5 scrollbar-thin scrollbar-thumb-slate-700">
          {sections.map((section) => (
            <div key={section.header} className="space-y-1">
              <p className="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 select-none">
                {section.header}
              </p>

              {section.items.map((item) => {
                const active = isItemActive(item);
                const hasSub = !!item.subItems && item.subItems.length > 0;
                const isExpanded = openMenus[item.title] ?? false;
                const Icon = item.icon;

                if (hasSub) {
                  return (
                    <div key={item.title} className="space-y-0.5">
                      <button
                        type="button"
                        onClick={() => toggleSubmenu(item.title)}
                        className={`flex items-center justify-between w-full px-3 py-2 rounded-lg text-xs font-medium transition-colors cursor-pointer ${
                          active
                            ? 'bg-[#1e293b] text-white font-semibold'
                            : 'text-slate-300 hover:bg-slate-800/70 hover:text-white'
                        }`}
                      >
                        <div className="flex items-center space-x-3">
                          <Icon className={`w-4 h-4 shrink-0 ${active ? 'text-blue-400' : 'text-slate-400'}`} />
                          <span className="truncate">{item.title}</span>
                        </div>
                        {isExpanded ? (
                          <ChevronDown className="w-3.5 h-3.5 text-slate-400" />
                        ) : (
                          <ChevronRight className="w-3.5 h-3.5 text-slate-400" />
                        )}
                      </button>

                      {isExpanded && (
                        <div className="pl-9 pr-2 py-1 space-y-1 bg-black/10 rounded-lg">
                          {item.subItems!.map((sub) => {
                            const isSubActive = pathname === sub.href;
                            return (
                              <Link
                                key={sub.href}
                                href={sub.href}
                                onClick={() => onClose()}
                                className={`block px-2.5 py-1.5 rounded-md text-[11px] transition-colors ${
                                  isSubActive
                                    ? 'bg-[#2563eb] text-white font-bold'
                                    : 'text-slate-400 hover:text-slate-100 hover:bg-slate-800/50'
                                }`}
                              >
                                {sub.title}
                              </Link>
                            );
                          })}
                        </div>
                      )}
                    </div>
                  );
                }

                return (
                  <Link
                    key={item.title}
                    href={item.href || '#'}
                    onClick={() => onClose()}
                    className={`flex items-center justify-between px-3 py-2 rounded-lg text-xs font-medium transition-colors ${
                      active
                        ? 'bg-[#1e293b] text-white font-bold border-l-2 border-blue-500'
                        : 'text-slate-300 hover:bg-slate-800/70 hover:text-white'
                    }`}
                  >
                    <div className="flex items-center space-x-3">
                      <Icon className={`w-4 h-4 shrink-0 ${active ? 'text-blue-400' : 'text-slate-400'}`} />
                      <span className="truncate">{item.title}</span>
                    </div>
                  </Link>
                );
              })}
            </div>
          ))}
        </div>

        {/* Footer info & Logout matching screenshot */}
        <div className="p-3 border-t border-slate-800/80 bg-[#0f172a]/80 space-y-2">
          <button
            onClick={logout}
            className="flex items-center justify-center space-x-2 w-full px-3 py-1.5 rounded-lg text-xs font-semibold text-rose-300 hover:text-rose-100 hover:bg-rose-950/40 border border-rose-900/30 transition-all cursor-pointer"
          >
            <LogOut className="w-3.5 h-3.5" />
            <span>Keluar Sistem</span>
          </button>
          <p className="text-[10px] text-center text-slate-500 font-mono select-none">
            © 2023-2026 Saraba Bisa
          </p>
        </div>
      </aside>
    </>
  );
};

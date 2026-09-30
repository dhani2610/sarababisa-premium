'use client';

import React, { useState, useEffect } from 'react';
import Link from 'next/link';
import api from '@/lib/api';
import { PortalNavbar } from '@/components/portal/PortalNavbar';
import { ArrowRight, MapPin } from 'lucide-react';

interface BranchItem {
  id: number;
  nama: string;
  logo?: string;
  alamat?: string;
}

export default function HomePage() {
  const [branches, setBranches] = useState<BranchItem[]>([
    { id: 1, nama: 'HAIRIL IDEVICE BDL', logo: '/images/logo-toko.png' },
    { id: 2, nama: 'HF IDEVICE PRINGSEWU', logo: '/images/logo-toko.png' },
    { id: 3, nama: 'HAIRIL IDEVICE AMT', logo: '/images/logo-toko.png' },
    { id: 4, nama: 'HAIRIL IDEVICE BJM', logo: '/images/logo-toko.png' },
  ]);

  useEffect(() => {
    const fetchBranches = async () => {
      try {
        const res = await api.get('/cabang');
        const list = Array.isArray(res.data) ? res.data : res.data.data;
        if (list && list.length > 0) {
          setBranches(
            list.map((c: any) => ({
              id: c.id,
              nama: c.nama || c.nama_cabang,
              logo: c.logo || '/images/logo-toko.png',
              alamat: c.alamat,
            }))
          );
        }
      } catch (err) {
        console.error('Failed to load branches from API, using default list', err);
      }
    };
    fetchBranches();
  }, []);

  return (
    <div className="min-h-screen bg-[#F8FAFC] flex flex-col font-sans">
      <PortalNavbar />

      <main className="flex-1 flex flex-col items-center justify-center py-16 px-4 sm:px-6 lg:px-8">
        {/* Section Header */}
        <div className="text-center max-w-2xl mx-auto mb-12">
          <h2 className="text-3xl sm:text-4xl font-extrabold text-[#2C3E50] tracking-tight">
            Pilih Lokasi Cabang
          </h2>
          <p className="mt-2.5 text-base sm:text-lg text-slate-500 font-medium">
            Silakan pilih cabang terdekat untuk layanan terbaik kami
          </p>
        </div>

        {/* Branch Cards Grid */}
        <div className="w-full max-w-5xl mx-auto grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8 justify-items-center">
          {branches.map((branch) => (
            <Link
              key={branch.id}
              href={`/login?cabang_id=${branch.id}`}
              className="group w-full max-w-sm bg-white rounded-2xl p-8 text-center transition-all duration-300 ease-out border border-slate-100 shadow-[0_10px_30px_rgba(0,0,0,0.05)] hover:shadow-[0_20px_40px_rgba(0,102,255,0.12)] hover:-translate-y-2 hover:border-blue-500 flex flex-col items-center justify-between"
            >
              {/* Circular Logo Container */}
              <div className="w-24 h-24 rounded-full bg-blue-50/80 p-2 flex items-center justify-center mb-5 group-hover:scale-105 transition-transform duration-300">
                <img
                  src={branch.logo || '/images/logo-toko.png'}
                  alt={branch.nama}
                  className="w-full h-full object-contain"
                  onError={(e: any) => {
                    e.currentTarget.src = '/images/logo-toko.png';
                  }}
                />
              </div>

              {/* Branch Title */}
              <h3 className="text-lg sm:text-xl font-bold text-[#2C3E50] group-hover:text-blue-600 transition-colors uppercase tracking-wide">
                {branch.nama}
              </h3>

              {branch.alamat && (
                <p className="text-xs text-slate-400 mt-1 line-clamp-2">{branch.alamat}</p>
              )}

              {/* Hover Button */}
              <div className="mt-6 pt-2">
                <span className="inline-flex items-center space-x-1.5 px-6 py-2 rounded-full text-xs font-bold text-white bg-blue-600 group-hover:bg-blue-700 shadow-md shadow-blue-500/20 transition-all opacity-90 group-hover:opacity-100">
                  <span>Kunjungi Toko</span>
                  <ArrowRight className="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform" />
                </span>
              </div>
            </Link>
          ))}
        </div>
      </main>

      {/* Footer */}
      <footer className="py-6 border-t border-slate-200/80 bg-white text-center text-xs text-slate-400">
        <p>&copy; {new Date().getFullYear()} HAIRIL IDEVICE GROUP. Powered by SarabaBisa Enterprise.</p>
      </footer>
    </div>
  );
}

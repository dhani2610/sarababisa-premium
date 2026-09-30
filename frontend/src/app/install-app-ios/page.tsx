'use client';

import React from 'react';
import { PortalNavbar } from '@/components/portal/PortalNavbar';
import { ArrowUp } from 'lucide-react';

export default function InstallAppIosPage() {
  const steps = [
    {
      step: '1. Buka Safari',
      desc: 'Masuk ke halaman login melalui browser Safari di iPhone.',
      img: '/step/1.jpeg',
    },
    {
      step: '2. Tekan Icon Panah',
      desc: (
        <>
          Pilih tombol <strong>Share</strong> (ikon panah ke atas) di bagian bawah Safari.
        </>
      ),
      img: '/step/2.jpeg',
    },
    {
      step: '3. Tambahkan ke Layar Utama',
      desc: (
        <>
          Pilih <strong>Tambah ke Layar Utama</strong> agar aplikasi muncul seperti aplikasi biasa.
        </>
      ),
      img: '/step/3.jpeg',
    },
  ];

  const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  return (
    <div className="min-h-screen bg-[#F8FAFC] flex flex-col font-sans">
      <PortalNavbar />

      <main className="flex-1 max-w-7xl w-full mx-auto py-12 px-4 sm:px-6 lg:px-8">
        {/* Title Header */}
        <div className="text-center max-w-2xl mx-auto mb-10">
          <span className="text-xs uppercase font-bold tracking-widest text-slate-400 block mb-1">
            Tutorial
          </span>
          <h1 className="text-3xl sm:text-4xl font-extrabold text-[#2C3E50] tracking-tight">
            Cara Install
          </h1>
          <p className="mt-2 text-sm sm:text-base text-slate-500 font-medium">
            Ikuti langkah mudah berikut untuk menambahkan PWA ke layar utama
          </p>
        </div>

        {/* Steps Grid Cards */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6 sm:gap-8 justify-items-center">
          {steps.map((item, idx) => (
            <div
              key={idx}
              className="w-full max-w-sm bg-white rounded-2xl p-5 border border-slate-100 shadow-[0_10px_30px_rgba(0,0,0,0.05)] text-center flex flex-col items-center justify-between"
            >
              <div className="mb-4">
                <h3 className="text-base sm:text-lg font-bold text-slate-800">{item.step}</h3>
                <p className="text-xs text-slate-500 mt-1 max-w-xs">{item.desc}</p>
              </div>

              {/* iPhone Step Mockup Screenshot */}
              <div className="w-full rounded-xl overflow-hidden border border-slate-200 shadow-inner bg-slate-50">
                <img
                  src={item.img}
                  alt={item.step}
                  className="w-full h-auto object-contain max-h-[480px] mx-auto"
                />
              </div>
            </div>
          ))}
        </div>
      </main>

      {/* Floating Scroll-to-Top Button */}
      <button
        onClick={scrollToTop}
        className="fixed bottom-6 right-6 w-11 h-11 rounded-full bg-[#0066FF] hover:bg-blue-700 text-white flex items-center justify-center shadow-lg shadow-blue-500/30 transition-all cursor-pointer z-40"
        title="Kembali ke atas"
      >
        <ArrowUp className="w-5 h-5" />
      </button>

      {/* Footer */}
      <footer className="py-6 border-t border-slate-200/80 bg-white text-center text-xs text-slate-400">
        <p>&copy; {new Date().getFullYear()} HAIRIL IDEVICE GROUP. All rights reserved.</p>
      </footer>
    </div>
  );
}

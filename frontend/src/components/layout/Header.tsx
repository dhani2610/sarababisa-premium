'use client';

import React, { useState, useEffect } from 'react';
import { useAuth } from '@/context/AuthContext';
import { Menu, ChevronDown, Check, Clock } from 'lucide-react';

export const Header: React.FC<{ onToggleSidebar: () => void }> = ({ onToggleSidebar }) => {
  const { user, activeCabangId, activeCabangNama, cabangList, switchCabang } = useAuth();
  const [showBranchModal, setShowBranchModal] = useState(false);
  const [isSwitching, setIsSwitching] = useState(false);
  const [currentTime, setCurrentTime] = useState<string>('');

  // Live real-time clock in Indonesian format matching screenshots: "Senin, 28 September 2026 16.37.59"
  useEffect(() => {
    const updateTime = () => {
      const now = new Date();
      const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
      const months = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
      ];

      const dayName = days[now.getDay()];
      const day = now.getDate();
      const month = months[now.getMonth()];
      const year = now.getFullYear();
      const hours = String(now.getHours()).padStart(2, '0');
      const minutes = String(now.getMinutes()).padStart(2, '0');
      const seconds = String(now.getSeconds()).padStart(2, '0');

      setCurrentTime(`${dayName}, ${day} ${month} ${year} ${hours}.${minutes}.${seconds}`);
    };

    updateTime();
    const interval = setInterval(updateTime, 1000);
    return () => clearInterval(interval);
  }, []);

  const handleSelectBranch = async (cabangId: number) => {
    if (cabangId === activeCabangId) {
      setShowBranchModal(false);
      return;
    }
    setIsSwitching(true);
    await switchCabang(cabangId);
    setIsSwitching(false);
    setShowBranchModal(false);
  };

  return (
    <header className="sticky top-0 z-30 flex items-center justify-between h-16 px-4 sm:px-6 bg-[#f8fafc]/90 backdrop-blur-xs border-b border-slate-200">
      {/* Left: Mobile hamburger & live date/time pill badge */}
      <div className="flex items-center space-x-3">
        <button
          onClick={onToggleSidebar}
          className="p-2 rounded-lg text-slate-600 hover:bg-slate-200/60 lg:hidden focus:outline-hidden cursor-pointer"
          aria-label="Toggle menu"
        >
          <Menu className="w-5 h-5" />
        </button>

        {/* Date/Time badge matching screenshot */}
        <div className="flex items-center space-x-2 px-3.5 py-1.5 rounded-full bg-white border border-slate-200 text-xs font-medium text-slate-700 shadow-2xs">
          <Clock className="w-3.5 h-3.5 text-blue-500" />
          <span className="tabular-nums font-medium text-[11px] sm:text-xs">
            {currentTime || 'Memuat waktu...'}
          </span>
        </div>
      </div>

      {/* Right: Subscription expiry & Branch Dropdown selector */}
      <div className="flex items-center space-x-4">
        {/* Active subscription notice */}
        <span className="hidden md:inline-block text-xs font-semibold text-[#2563eb]">
          Aktif hingga 19/12/2030
        </span>

        {/* Branch Selector Dropdown matching screenshot */}
        <div className="relative">
          <button
            type="button"
            onClick={() => setShowBranchModal(!showBranchModal)}
            className="flex items-center space-x-2 text-xs font-bold text-slate-800 hover:text-slate-950 transition-colors cursor-pointer select-none"
          >
            {/* Store Avatar Logo */}
            <div className="flex items-center justify-center w-7 h-7 rounded-full bg-emerald-50 border border-emerald-300 text-emerald-700 font-black text-[10px] shadow-2xs">
              HI
            </div>

            <span className="uppercase tracking-tight text-[11px] sm:text-xs font-black">
              {activeCabangNama || 'HI PRINGSEWU'}
            </span>
            <ChevronDown className="w-3.5 h-3.5 text-slate-400" />
          </button>

          {/* Branch Switcher Modal Dropdown */}
          {showBranchModal && (
            <div className="absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-xl border border-slate-200 py-2 z-50">
              <div className="px-3.5 py-1.5 border-b border-slate-100">
                <p className="text-[10px] font-black uppercase tracking-wider text-slate-400">
                  Pilih Cabang Aktif
                </p>
                <p className="text-xs text-slate-500 font-medium">Beralih lokasi operasional</p>
              </div>

              <div className="max-h-60 overflow-y-auto py-1">
                {cabangList.length > 0 ? (
                  cabangList.map((c) => {
                    const isSelected = c.id === activeCabangId;
                    return (
                      <button
                        key={c.id}
                        disabled={isSwitching}
                        onClick={() => handleSelectBranch(c.id)}
                        className={`flex items-center justify-between w-full px-3.5 py-2 text-xs text-left transition-colors cursor-pointer ${
                          isSelected
                            ? 'bg-blue-50 text-blue-700 font-bold'
                            : 'text-slate-700 hover:bg-slate-50'
                        }`}
                      >
                        <span className="truncate">{c.nama}</span>
                        {isSelected && <Check className="w-4 h-4 text-blue-600 shrink-0 ml-2" />}
                      </button>
                    );
                  })
                ) : (
                  <p className="px-3.5 py-2 text-xs text-slate-400">Memuat cabang...</p>
                )}
              </div>
            </div>
          )}
        </div>
      </div>
    </header>
  );
};

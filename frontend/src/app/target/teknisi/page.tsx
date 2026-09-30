'use client';

import React, { useState, useEffect } from 'react';
import api from '@/lib/api';
import Swal from 'sweetalert2';
import {
  Target,
  Plus,
  Copy,
  TrendingUp,
  Award,
  Calendar,
  CheckCircle2,
} from 'lucide-react';

export default function TargetTeknisiPage() {
  const [targetList, setTargetList] = useState<any[]>([]);
  const [progressList, setProgressList] = useState<any[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [isCopying, setIsCopying] = useState(false);

  const fetchTargets = async () => {
    setIsLoading(true);
    try {
      const [resTarget, resProgress] = await Promise.all([
        api.get('/target'),
        api.get('/target-teknisi'),
      ]);
      setTargetList(resTarget.data.data || resTarget.data || []);
      setProgressList(Array.isArray(resProgress.data) ? resProgress.data : []);
    } catch (err) {
      console.error('Error fetching targets', err);
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchTargets();
  }, []);

  const handleCopyLastMonth = async () => {
    const confirm = await Swal.fire({
      title: 'Salin Target Bulan Lalu?',
      text: 'Semua target teknisi dari bulan sebelumnya akan disalin ke bulan ini.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Ya, Salin',
      cancelButtonText: 'Batal',
    });
    if (!confirm.isConfirmed) return;

    setIsCopying(true);
    try {
      const res = await api.post('/target/bulan-sebelumnya');
      Swal.fire('Berhasil Disalin!', res.data.message || 'Target berhasil disalin.', 'success');
      fetchTargets();
    } catch (err: any) {
      Swal.fire('Gagal', err.response?.data?.detail || 'Gagal menyalin target', 'error');
    } finally {
      setIsCopying(false);
    }
  };

  const formatRupiah = (val: number = 0) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
    }).format(val);
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
          <h1 className="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
            <Target className="w-6 h-6 text-blue-600" />
            <span>Target & Kinerja Teknisi</span>
          </h1>
          <p className="text-xs text-slate-500 mt-0.5">
            Pencapaian target pengerjaan servis unit, omzet profit dan bonus reward bulanan.
          </p>
        </div>

        <button
          onClick={handleCopyLastMonth}
          disabled={isCopying}
          className="inline-flex items-center space-x-1.5 px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-md transition-all cursor-pointer self-start sm:self-auto disabled:opacity-50"
        >
          <Copy className="w-4 h-4" />
          <span>{isCopying ? 'Menyalin...' : 'Salin Target Bulan Lalu'}</span>
        </button>
      </div>

      {/* Progress Cards Grid */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        {progressList.map((tp, idx) => (
          <div key={idx} className="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-3">
            <div className="flex items-center justify-between">
              <div className="flex items-center space-x-2">
                <div className="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs">
                  {tp.nama ? tp.nama.charAt(0) : 'T'}
                </div>
                <div>
                  <h4 className="font-bold text-slate-800 text-xs">{tp.nama}</h4>
                  <span className="text-[10px] text-slate-400">Teknisi Servis</span>
                </div>
              </div>
              {tp.progres >= 100 && (
                <span className="inline-flex items-center space-x-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                  <Award className="w-3 h-3" />
                  <span>Tercapai!</span>
                </span>
              )}
            </div>

            <div className="space-y-1 text-xs">
              <div className="flex justify-between text-slate-600 text-[11px]">
                <span>Pencapaian:</span>
                <span className="font-bold text-slate-900">{tp.achieved_text || '-'}</span>
              </div>
              <div className="flex justify-between text-slate-600 text-[11px]">
                <span>Target:</span>
                <span className="font-semibold text-slate-700">{tp.target_text || '-'}</span>
              </div>

              {/* Progress bar */}
              <div className="w-full bg-slate-100 rounded-full h-2 mt-2 overflow-hidden">
                <div
                  className={`h-full rounded-full transition-all duration-500 ${
                    tp.progres >= 100 ? 'bg-emerald-500' : 'bg-blue-600'
                  }`}
                  style={{ width: `${Math.min(100, tp.progres || 0)}%` }}
                />
              </div>

              <div className="flex justify-between items-center pt-2">
                <span className="text-[11px] font-bold text-blue-700">{tp.progres || 0}% Selesai</span>
                {tp.reward > 0 && (
                  <span className="text-[11px] font-bold text-emerald-600">
                    Bonus: {formatRupiah(tp.reward)}
                  </span>
                )}
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}

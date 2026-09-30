'use client';

import React, { useState, Suspense } from 'react';

import { useRouter, useSearchParams } from 'next/navigation';
import Link from 'next/link';
import { useAuth } from '@/context/AuthContext';
import Swal from 'sweetalert2';

function LoginForm() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const cabangIdParam = searchParams.get('cabang_id');

  const { login, switchCabang } = useAuth();
  const [username, setUsername] = useState('HI_GROUP');
  const [password, setPassword] = useState('2008');
  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!username || !password) return;

    setIsSubmitting(true);
    const success = await login(username, password);

    if (success) {
      if (cabangIdParam) {
        try {
          await switchCabang(Number(cabangIdParam));
        } catch (err) {
          console.error('Failed auto-switch branch on login', err);
        }
      }
      router.push('/dashboard');
    }
    setIsSubmitting(false);
  };


  return (
    <div className="min-h-screen bg-white flex">
      {/* Left Column: Form Login */}
      <div className="w-full md:w-1/2 flex flex-col justify-between p-6 sm:p-12 lg:p-16 min-h-screen">
        {/* Top Header Logo */}
        <div>
          <Link href="/" className="inline-block">
            <img
              src="/images/logo-toko.png"
              alt="HAIRIL IDEVICE"
              className="h-16 sm:h-20 object-contain"
              onError={(e: any) => {
                e.currentTarget.src = '/images/logo-saraba-bisa.png';
              }}
            />
          </Link>
        </div>

        {/* Center Content Form */}
        <div className="w-full max-w-sm mx-auto my-auto py-8">
          <h1 className="text-2xl sm:text-3xl font-bold text-slate-800 mb-6 tracking-tight">
            Selamat datang kembali! ✨
          </h1>

          <form onSubmit={handleSubmit} className="space-y-4">
            <div>
              <label className="block text-sm font-semibold text-slate-700 mb-1.5">
                Nama Pengguna
              </label>
              <input
                type="text"
                required
                autoFocus
                value={username}
                onChange={(e) => setUsername(e.target.value)}
                placeholder="Username akun"
                className="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-lg text-slate-800 text-sm focus:outline-hidden focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition-all shadow-2xs"
              />
            </div>

            <div>
              <label className="block text-sm font-semibold text-slate-700 mb-1.5">
                Kata Sandi
              </label>
              <input
                type="password"
                required
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                placeholder="Kata sandi akun"
                className="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-lg text-slate-800 text-sm focus:outline-hidden focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition-all shadow-2xs"
              />
            </div>

            <div className="flex items-center justify-between pt-2">
              <a
                href="#"
                onClick={(e) => {
                  e.preventDefault();
                  Swal.fire(
                    'Lupa Kata Sandi?',
                    'Silakan hubungi Kepala Toko atau Administrator untuk mereset kata sandi akun Anda.',
                    'info'
                  );
                }}
                className="text-sm text-indigo-600 hover:text-indigo-700 underline cursor-pointer"
              >
                Lupa kata sandi?
              </a>

              <button
                type="submit"
                disabled={isSubmitting}
                className="px-6 py-2.5 bg-[#4F46E5] hover:bg-[#4338CA] text-white rounded-lg text-sm font-semibold tracking-wide transition-all shadow-sm cursor-pointer disabled:opacity-50"
              >
                {isSubmitting ? 'Memproses...' : 'Masuk'}
              </button>
            </div>
          </form>
        </div>

        {/* Bottom Copyright */}
        <div className="text-xs text-slate-400">
          <p>&copy; {new Date().getFullYear()} HAIRIL IDEVICE. All rights reserved.</p>
        </div>
      </div>

      {/* Right Column: Hero Image (bg-auth.jpg) */}
      <div className="hidden md:block w-1/2 relative bg-slate-100 overflow-hidden">
        <img
          src="/images/bg-auth.jpg"
          alt="Authentication Background"
          className="w-full h-full object-cover object-center"
        />
        <div className="absolute inset-0 bg-black/10 pointer-events-none" />
      </div>
    </div>
  );
}

export default function LoginPage() {
  return (
    <Suspense
      fallback={
        <div className="min-h-screen bg-white flex items-center justify-center">
          <div className="w-8 h-8 border-3 border-indigo-600 border-t-transparent rounded-full animate-spin" />
        </div>
      }
    >
      <LoginForm />
    </Suspense>
  );
}


'use client';

import React, { useState, useEffect } from 'react';
import { usePathname, useRouter } from 'next/navigation';
import { useAuth } from '@/context/AuthContext';
import { Sidebar } from './Sidebar';
import { Header } from './Header';

export const AppLayout: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const pathname = usePathname();
  const router = useRouter();
  const { user, token, isLoading } = useAuth();
  const [sidebarOpen, setSidebarOpen] = useState(false);

  const isPublicPage =
    !pathname ||
    pathname === '/' ||
    pathname.startsWith('/login') ||
    pathname.startsWith('/public') ||
    pathname.startsWith('/install-app-ios');

  useEffect(() => {
    if (!isLoading && !token && pathname && !isPublicPage) {
      router.push('/login');
    }
  }, [isLoading, token, isPublicPage, pathname, router]);

  // If on landing page, login, or public tutorial, render without dashboard shell
  if (isPublicPage) {
    return <>{children}</>;
  }

  // Show clean loading state while verifying JWT token
  if (isLoading || !token) {
    return (
      <div className="flex items-center justify-center min-h-screen bg-slate-900 text-white">
        <div className="flex flex-col items-center space-y-4">
          <div className="w-10 h-10 border-4 border-blue-500 border-t-transparent rounded-full animate-spin" />
          <p className="text-xs tracking-wider text-slate-400">MEMUAT SARABABISA PREMIUM...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-slate-50 flex">
      {/* Sidebar Navigation */}
      <Sidebar isOpen={sidebarOpen} onClose={() => setSidebarOpen(false)} />

      {/* Main Content Area */}
      <div className="flex-1 flex flex-col min-w-0 lg:pl-64">
        <Header onToggleSidebar={() => setSidebarOpen(!sidebarOpen)} />
        <main className="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto animate-in fade-in duration-200">
          {children}
        </main>
      </div>
    </div>
  );
};

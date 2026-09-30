'use client';

import React, { useState, useEffect } from 'react';
import Link from 'next/link';
import { usePathname } from 'next/navigation';
import { Menu, X } from 'lucide-react';

export const PortalNavbar: React.FC = () => {
  const pathname = usePathname();
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const [deferredPrompt, setDeferredPrompt] = useState<any>(null);

  useEffect(() => {
    const handleBeforeInstall = (e: any) => {
      e.preventDefault();
      setDeferredPrompt(e);
    };
    window.addEventListener('beforeinstallprompt', handleBeforeInstall);
    return () => window.removeEventListener('beforeinstallprompt', handleBeforeInstall);
  }, []);

  const handleInstallApp = async () => {
    if (deferredPrompt) {
      deferredPrompt.prompt();
      const { outcome } = await deferredPrompt.userChoice;
      if (outcome === 'accepted') {
        setDeferredPrompt(null);
      }
    } else {
      window.location.href = '/install-app-ios';
    }
  };

  const navLinks = [
    { label: 'Beranda', href: '/' },
    { label: 'Login', href: '/login' },
    { label: 'Install App', onClick: handleInstallApp },
    { label: 'Install App IOS', href: '/install-app-ios' },
  ];

  return (
    <header className="sticky top-0 z-50 bg-[#0066FF] text-white shadow-md">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        {/* Brand Title */}
        <Link href="/" className="font-extrabold text-lg sm:text-xl tracking-wider uppercase">
          HAIRIL IDEVICE GROUP
        </Link>

        {/* Desktop Nav */}
        <nav className="hidden md:flex items-center space-x-6 text-sm font-medium">
          {navLinks.map((link, idx) => {
            if (link.onClick) {
              return (
                <button
                  key={idx}
                  onClick={link.onClick}
                  className="hover:text-blue-200 transition-colors cursor-pointer"
                >
                  {link.label}
                </button>
              );
            }
            const isActive = pathname === link.href;
            return (
              <Link
                key={idx}
                href={link.href || '#'}
                className={`transition-colors ${
                  isActive ? 'text-white font-bold border-b-2 border-white pb-0.5' : 'hover:text-blue-200'
                }`}
              >
                {link.label}
              </Link>
            );
          })}
        </nav>

        {/* Mobile menu toggle button */}
        <button
          onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
          className="p-2 rounded-lg text-white hover:bg-blue-700 md:hidden cursor-pointer"
        >
          {mobileMenuOpen ? <X className="w-6 h-6" /> : <Menu className="w-6 h-6" />}
        </button>
      </div>

      {/* Mobile Nav Drawer */}
      {mobileMenuOpen && (
        <div className="md:hidden bg-blue-700 border-t border-blue-600 px-4 py-3 space-y-2 text-sm font-medium">
          {navLinks.map((link, idx) => {
            if (link.onClick) {
              return (
                <button
                  key={idx}
                  onClick={() => {
                    link.onClick();
                    setMobileMenuOpen(false);
                  }}
                  className="block w-full text-left py-2 hover:text-blue-200"
                >
                  {link.label}
                </button>
              );
            }
            return (
              <Link
                key={idx}
                href={link.href || '#'}
                onClick={() => setMobileMenuOpen(false)}
                className="block py-2 hover:text-blue-200"
              >
                {link.label}
              </Link>
            );
          })}
        </div>
      )}
    </header>
  );
};

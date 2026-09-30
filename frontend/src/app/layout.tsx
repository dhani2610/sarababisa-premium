import type { Metadata } from 'next';
import './globals.css';
import { AuthProvider } from '@/context/AuthContext';
import { AppLayout } from '@/components/layout/AppLayout';

export const metadata: Metadata = {
  title: 'SarabaBisa Premium — Manajemen Servis & POS Multi Cabang',
  description: 'Aplikasi Enterprise POS & Transaksi Servis Multi-Teknisi dan Multi-Cabang',
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="id">
      <body className="antialiased">
        <AuthProvider>
          <AppLayout>{children}</AppLayout>
        </AuthProvider>
      </body>
    </html>
  );
}

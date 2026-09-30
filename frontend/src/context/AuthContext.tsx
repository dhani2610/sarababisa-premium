'use client';

import React, { createContext, useContext, useState, useEffect } from 'react';
import api from '@/lib/api';
import { User, Cabang } from '@/types';
import Swal from 'sweetalert2';

interface AuthContextType {
  user: User | null;
  token: string | null;
  activeCabangId: number | null;
  activeCabangNama: string | null;
  cabangList: Cabang[];
  isLoading: boolean;
  login: (username: string, password: string) => Promise<boolean>;
  logout: () => void;
  switchCabang: (cabangId: number) => Promise<boolean>;
  refreshUserData: () => Promise<void>;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const AuthProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [user, setUser] = useState<User | null>(null);
  const [token, setToken] = useState<string | null>(null);
  const [activeCabangId, setActiveCabangId] = useState<number | null>(null);
  const [activeCabangNama, setActiveCabangNama] = useState<string | null>(null);
  const [cabangList, setCabangList] = useState<Cabang[]>([]);
  const [isLoading, setIsLoading] = useState<boolean>(true);

  // Load saved state on mount
  useEffect(() => {
    const savedToken = localStorage.getItem('token');
    const savedUser = localStorage.getItem('user');

    if (savedToken && savedUser) {
      try {
        const parsedUser: User = JSON.parse(savedUser);
        setToken(savedToken);
        setUser(parsedUser);
        setActiveCabangId(parsedUser.cabang_id);
      } catch (err) {
        console.error('Failed to parse cached user', err);
      }
    }
    setIsLoading(false);
  }, []);

  // Fetch full user and cabang info once token is set
  useEffect(() => {
    if (token) {
      refreshUserData();
      fetchCabangList();
    }
  }, [token]);

  const refreshUserData = async () => {
    try {
      const res = await api.get('/auth/me');
      const userData: User = res.data;
      setUser(userData);
      setActiveCabangId(userData.cabang_id);
      localStorage.setItem('user', JSON.stringify(userData));

      if (userData.assigned_cabangs && userData.assigned_cabangs.length > 0) {
        const current = userData.assigned_cabangs.find((c) => c.id === userData.cabang_id);
        setActiveCabangNama(current ? current.nama : `Cabang #${userData.cabang_id}`);
      } else if (userData.cabang_id) {
        const found = cabangList.find((c) => c.id === userData.cabang_id);
        if (found) {
          setActiveCabangNama(found.nama);
        } else {
          setActiveCabangNama(`Cabang #${userData.cabang_id}`);
        }
      }
    } catch (err) {
      console.error('Error fetching current user profile', err);
    }
  };

  const fetchCabangList = async () => {
    try {
      const res = await api.get('/cabang');
      const list = Array.isArray(res.data) ? res.data : (res.data?.data || []);
      setCabangList(list);

      // If activeCabangNama is not set or looks like a placeholder, resolve it from the list
      if (user?.cabang_id) {
        const current = list.find((c: any) => c.id === user.cabang_id);
        if (current) {
          setActiveCabangNama(current.nama);
        }
      }
    } catch (err) {
      console.error('Error loading cabangs', err);
    }
  };

  const login = async (username: string, password: string): Promise<boolean> => {
    try {
      const res = await api.post('/auth/login', {
        username,
        password,
      });

      const { access_token, user: userData } = res.data;
      localStorage.setItem('token', access_token);
      localStorage.setItem('user', JSON.stringify(userData));

      setToken(access_token);
      setUser(userData);
      setActiveCabangId(userData.cabang_id);

      Swal.fire({
        icon: 'success',
        title: 'Berhasil Masuk',
        text: `Selamat datang kembali, ${userData.nama || userData.username}!`,
        timer: 1500,
        showConfirmButton: false,
      });

      return true;
    } catch (err: any) {
      let message = 'Username atau password tidak sesuai';
      if (err.response?.data?.detail) {
        if (typeof err.response.data.detail === 'string') {
          message = err.response.data.detail;
        } else if (Array.isArray(err.response.data.detail)) {
          message = err.response.data.detail.map((d: any) => d.msg || JSON.stringify(d)).join(', ');
        }
      }
      Swal.fire({
        icon: 'error',
        title: 'Gagal Masuk',
        text: message,
      });
      return false;
    }
  };

  const logout = () => {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    setToken(null);
    setUser(null);
    setActiveCabangId(null);
    setActiveCabangNama(null);
    window.location.href = '/login';
  };

  const switchCabang = async (targetCabangId: number): Promise<boolean> => {
    try {
      const res = await api.post('/auth/switch-cabang', { cabang_id: targetCabangId });
      const { access_token, active_cabang_nama } = res.data;
      const updatedUser = res.data.user || (user ? { ...user, cabang_id: targetCabangId } : null);

      localStorage.setItem('token', access_token);
      if (updatedUser) {
        localStorage.setItem('user', JSON.stringify(updatedUser));
        setUser(updatedUser);
      }

      setToken(access_token);
      setActiveCabangId(targetCabangId);

      const current = cabangList.find((c) => c.id === targetCabangId);
      const branchName = active_cabang_nama || (current ? current.nama : `Cabang #${targetCabangId}`);
      setActiveCabangNama(branchName);

      Swal.fire({
        icon: 'success',
        title: 'Cabang Berhasil Diganti',
        text: `Sekarang aktif di: ${branchName}`,
        timer: 1500,
        showConfirmButton: false,
      });

      // Reload page to refresh all active queries
      setTimeout(() => {
        window.location.reload();
      }, 500);

      return true;
    } catch (err: any) {
      const message = err.response?.data?.detail || 'Gagal berpindah cabang';
      Swal.fire({
        icon: 'error',
        title: 'Akses Ditolak',
        text: message,
      });
      return false;
    }
  };

  return (
    <AuthContext.Provider
      value={{
        user,
        token,
        activeCabangId,
        activeCabangNama,
        cabangList,
        isLoading,
        login,
        logout,
        switchCabang,
        refreshUserData,
      }}
    >
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};

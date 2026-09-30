'use client';

import React, { useState, useEffect } from 'react';

interface CurrencyInputProps {
  value: number | string | undefined | null;
  onChange: (numericVal: number) => void;
  placeholder?: string;
  className?: string;
  prefix?: string;
  disabled?: boolean;
  required?: boolean;
  id?: string;
}

export const formatRupiahDisplay = (val: number | string | undefined | null): string => {
  if (val === undefined || val === null || val === '') return '';
  const num = typeof val === 'number' ? val : parseInt(String(val).replace(/\D/g, ''), 10);
  if (isNaN(num)) return '';
  return new Intl.NumberFormat('id-ID').format(num);
};

export const CurrencyInput: React.FC<CurrencyInputProps> = ({
  value,
  onChange,
  placeholder = '0',
  className = '',
  prefix = 'Rp. ',
  disabled = false,
  required = false,
  id,
}) => {
  const [displayVal, setDisplayVal] = useState<string>('');
  const [isFocused, setIsFocused] = useState(false);

  useEffect(() => {
    if (!isFocused) {
      if (value === 0 || value === '0') {
        setDisplayVal('0');
      } else if (value) {
        setDisplayVal(formatRupiahDisplay(value));
      } else {
        setDisplayVal('');
      }
    }
  }, [value, isFocused]);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const raw = e.target.value.replace(/\D/g, '');
    if (!raw) {
      setDisplayVal('');
      onChange(0);
      return;
    }
    const num = parseInt(raw, 10);
    setDisplayVal(new Intl.NumberFormat('id-ID').format(num));
    onChange(num);
  };

  const handleFocus = () => {
    setIsFocused(true);
    if (value === 0 || value === '0') {
      setDisplayVal(''); // Auto-clear zero on focus so user can type freely without stuck 0!
    }
  };

  const handleBlur = () => {
    setIsFocused(false);
    if (!displayVal || displayVal === '') {
      setDisplayVal('0');
      onChange(0);
    }
  };

  return (
    <div className={`relative flex items-center ${disabled ? 'opacity-60 pointer-events-none' : ''}`}>
      {prefix && (
        <span className="absolute left-2.5 text-slate-400 font-semibold text-xs pointer-events-none select-none">
          {prefix}
        </span>
      )}
      <input
        id={id}
        type="text"
        inputMode="numeric"
        value={displayVal}
        onChange={handleChange}
        onFocus={handleFocus}
        onBlur={handleBlur}
        placeholder={placeholder}
        disabled={disabled}
        required={required}
        className={`w-full py-1.5 border border-slate-300 rounded-md focus:border-[#5051F9] focus:ring-1 focus:ring-[#5051F9]/30 text-xs font-semibold text-slate-800 bg-white transition-all ${
          prefix ? 'pl-8 pr-2.5' : 'px-2.5'
        } ${className}`}
      />
    </div>
  );
};
export default CurrencyInput;

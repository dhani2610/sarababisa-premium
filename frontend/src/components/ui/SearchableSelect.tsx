'use client';

import React, { useState, useRef, useEffect, useMemo } from 'react';
import { Search, ChevronDown, X, Check } from 'lucide-react';

export interface SelectOption {
  value: string | number;
  label: string;
  subLabel?: string;
}

interface SearchableSelectProps {
  options: SelectOption[];
  value?: string | number | null;
  onChange: (val: any) => void;
  placeholder?: string;
  disabled?: boolean;
  className?: string;
  isClearable?: boolean;
  required?: boolean;
  id?: string;
}

export const SearchableSelect: React.FC<SearchableSelectProps> = ({
  options = [],
  value,
  onChange,
  placeholder = 'Pilih Opsi...',
  disabled = false,
  className = '',
  isClearable = true,
  required = false,
  id,
}) => {
  const [isOpen, setIsOpen] = useState(false);
  const [searchTerm, setSearchTerm] = useState('');
  const [highlightedIndex, setHighlightedIndex] = useState<number>(-1);
  const containerRef = useRef<HTMLDivElement>(null);
  const searchInputRef = useRef<HTMLInputElement>(null);
  const listRef = useRef<HTMLDivElement>(null);

  // Selected Option Object
  const selectedOption = useMemo(() => {
    return options.find((opt) => String(opt.value) === String(value));
  }, [options, value]);

  // Filtered Options based on search
  const filteredOptions = useMemo(() => {
    if (!searchTerm.trim()) return options;
    const term = searchTerm.toLowerCase();
    return options.filter(
      (opt) =>
        opt.label.toLowerCase().includes(term) ||
        (opt.subLabel && opt.subLabel.toLowerCase().includes(term))
    );
  }, [options, searchTerm]);

  // Click Outside to Close
  useEffect(() => {
    const handleClickOutside = (e: MouseEvent) => {
      if (containerRef.current && !containerRef.current.contains(e.target as Node)) {
        setIsOpen(false);
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  // Focus Search Input when opened
  useEffect(() => {
    if (isOpen) {
      setSearchTerm('');
      setHighlightedIndex(0);
      setTimeout(() => {
        searchInputRef.current?.focus();
      }, 50);
    }
  }, [isOpen]);

  // Scroll highlighted item into view
  useEffect(() => {
    if (isOpen && highlightedIndex >= 0 && listRef.current) {
      const el = listRef.current.children[highlightedIndex] as HTMLElement;
      if (el) {
        el.scrollIntoView({ block: 'nearest' });
      }
    }
  }, [highlightedIndex, isOpen]);

  const handleSelect = (val: string | number) => {
    onChange(val);
    setIsOpen(false);
  };

  const handleClear = (e: React.MouseEvent) => {
    e.stopPropagation();
    onChange('');
    setIsOpen(false);
  };

  const handleKeyDown = (e: React.KeyboardEvent) => {
    if (disabled) return;

    if (!isOpen) {
      if (e.key === 'Enter' || e.key === 'ArrowDown' || e.key === ' ') {
        e.preventDefault();
        setIsOpen(true);
      }
      return;
    }

    if (e.key === 'ArrowDown') {
      e.preventDefault();
      setHighlightedIndex((prev) =>
        prev < filteredOptions.length - 1 ? prev + 1 : 0
      );
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      setHighlightedIndex((prev) =>
        prev > 0 ? prev - 1 : filteredOptions.length - 1
      );
    } else if (e.key === 'Enter') {
      e.preventDefault();
      if (filteredOptions[highlightedIndex]) {
        handleSelect(filteredOptions[highlightedIndex].value);
      }
    } else if (e.key === 'Escape' || e.key === 'Tab') {
      setIsOpen(false);
    }
  };

  return (
    <div
      ref={containerRef}
      className={`relative w-full text-xs font-sans ${disabled ? 'opacity-60 pointer-events-none' : ''} ${className}`}
      onKeyDown={handleKeyDown}
    >
      {/* Hidden input for HTML form validation if required */}
      {required && (
        <input
          type="text"
          value={value !== undefined && value !== null ? String(value) : ''}
          required={required}
          onChange={() => {}}
          className="sr-only"
          tabIndex={-1}
        />
      )}

      {/* Main Select Button Box */}
      <div
        id={id}
        tabIndex={disabled ? -1 : 0}
        onClick={() => !disabled && setIsOpen(!isOpen)}
        className={`w-full min-h-[34px] px-2.5 py-1.5 bg-white border rounded-md flex items-center justify-between gap-1.5 transition-all cursor-pointer ${
          isOpen
            ? 'border-[#5051F9] ring-2 ring-[#5051F9]/20 shadow-xs'
            : 'border-slate-300 hover:border-slate-400'
        }`}
      >
        <div className="flex-1 truncate">
          {selectedOption ? (
            <span className="font-semibold text-slate-800">
              {selectedOption.label}
              {selectedOption.subLabel && (
                <span className="ml-1.5 font-normal text-slate-400">
                  ({selectedOption.subLabel})
                </span>
              )}
            </span>
          ) : (
            <span className="text-slate-400">{placeholder}</span>
          )}
        </div>

        <div className="flex items-center gap-1 shrink-0 text-slate-400">
          {isClearable && selectedOption && !disabled && (
            <button
              type="button"
              onClick={handleClear}
              className="p-0.5 rounded-full hover:bg-slate-100 hover:text-slate-600 transition-colors"
              title="Bersihkan"
            >
              <X className="w-3.5 h-3.5" />
            </button>
          )}
          <ChevronDown
            className={`w-3.5 h-3.5 transition-transform duration-200 ${
              isOpen ? 'transform rotate-180 text-[#5051F9]' : ''
            }`}
          />
        </div>
      </div>

      {/* Dropdown Menu (Select2 Style) */}
      {isOpen && (
        <div className="absolute z-50 left-0 right-0 mt-1 bg-white border border-slate-200 rounded-md shadow-xl overflow-hidden animate-in fade-in slide-in-from-top-1 duration-150">
          {/* Search Box Header */}
          <div className="p-1.5 border-b border-slate-100 bg-slate-50/80 sticky top-0 z-10">
            <div className="relative flex items-center">
              <Search className="w-3.5 h-3.5 absolute left-2 text-slate-400" />
              <input
                ref={searchInputRef}
                type="text"
                value={searchTerm}
                onChange={(e) => {
                  setSearchTerm(e.target.value);
                  setHighlightedIndex(0);
                }}
                placeholder="Cari disini..."
                className="w-full pl-7 pr-2 py-1 text-xs bg-white border border-slate-200 rounded focus:outline-hidden focus:border-[#5051F9] text-slate-800 placeholder-slate-400"
              />
            </div>
          </div>

          {/* Options List */}
          <div
            ref={listRef}
            className="max-h-56 overflow-y-auto divide-y divide-slate-50 scrollbar-thin scrollbar-thumb-slate-200"
          >
            {filteredOptions.length > 0 ? (
              filteredOptions.map((opt, idx) => {
                const isSelected = String(opt.value) === String(value);
                const isHighlighted = idx === highlightedIndex;

                return (
                  <div
                    key={`${opt.value}-${idx}`}
                    onClick={() => handleSelect(opt.value)}
                    onMouseEnter={() => setHighlightedIndex(idx)}
                    className={`px-3 py-2 flex items-center justify-between cursor-pointer transition-colors ${
                      isSelected
                        ? 'bg-[#5051F9]/10 text-[#5051F9] font-bold'
                        : isHighlighted
                        ? 'bg-slate-100 text-slate-900 font-medium'
                        : 'text-slate-700 hover:bg-slate-50'
                    }`}
                  >
                    <div className="truncate">
                      <span>{opt.label}</span>
                      {opt.subLabel && (
                        <span className="ml-1 text-[11px] text-slate-400 font-normal">
                          - {opt.subLabel}
                        </span>
                      )}
                    </div>
                    {isSelected && <Check className="w-3.5 h-3.5 text-[#5051F9] shrink-0 ml-1.5" />}
                  </div>
                );
              })
            ) : (
              <div className="px-3 py-4 text-center text-xs text-slate-400">
                Tidak ada data ditemukan
              </div>
            )}
          </div>
        </div>
      )}
    </div>
  );
};
export default SearchableSelect;

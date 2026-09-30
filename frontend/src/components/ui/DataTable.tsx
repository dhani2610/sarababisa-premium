'use client';

import React, { useState, useMemo } from 'react';
import { ChevronLeft, ChevronRight, ChevronsUpDown, ArrowUp, ArrowDown } from 'lucide-react';

export interface Column<T> {
  key: string;
  label: string;
  sortable?: boolean;
  className?: string;
  headerClassName?: string;
  render?: (item: T, index: number) => React.ReactNode;
}

export interface DataTableProps<T> {
  columns: Column<T>[];
  data: T[];
  isLoading?: boolean;
  title?: string;
  countBadge?: number | string;
  selectable?: boolean;
  selectedIds?: (number | string)[];
  onSelectChange?: (selectedIds: (number | string)[]) => void;
  idKey?: keyof T;
  actionsRight?: React.ReactNode;
  filterComponent?: React.ReactNode;
  bulkActions?: (selectedIds: (number | string)[]) => React.ReactNode;
  onBulkDelete?: (selectedIds: (number | string)[]) => void;
  emptyMessage?: string;
  defaultPerPage?: number;
  perPageOptions?: number[];
  serverSide?: boolean;
  totalCount?: number;
  page?: number;
  onPageChange?: (page: number) => void;
  onSearchChange?: (query: string) => void;
  onPerPageChange?: (perPage: number) => void;
  searchPlaceholder?: string;
  showSearch?: boolean;
  showPerPage?: boolean;
}

export function DataTable<T extends Record<string, any>>({
  columns,
  data,
  isLoading = false,
  title,
  countBadge,
  selectable = false,
  selectedIds = [],
  onSelectChange,
  idKey = 'id' as keyof T,
  actionsRight,
  filterComponent,
  bulkActions,
  onBulkDelete,
  emptyMessage = 'Tidak ada data ditemukan.',
  defaultPerPage = 10,
  perPageOptions = [10, 25, 50, 100],
  serverSide = false,
  totalCount,
  page: controlledPage,
  onPageChange,
  onSearchChange,
  onPerPageChange,
  searchPlaceholder = '',
  showSearch = true,
  showPerPage = true,
}: DataTableProps<T>) {
  const [internalSearch, setInternalSearch] = useState('');
  const [internalPage, setInternalPage] = useState(1);
  const [internalPerPage, setInternalPerPage] = useState(defaultPerPage);
  const [sortKey, setSortKey] = useState<string | null>(null);
  const [sortOrder, setSortOrder] = useState<'asc' | 'desc'>('asc');

  const currentPage = serverSide ? (controlledPage ?? 1) : internalPage;
  const currentPerPage = internalPerPage;

  const handleSearch = (e: React.ChangeEvent<HTMLInputElement>) => {
    const val = e.target.value;
    setInternalSearch(val);
    if (serverSide) {
      onSearchChange?.(val);
    } else {
      setInternalPage(1);
    }
  };

  const handlePerPageChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
    const val = Number(e.target.value);
    setInternalPerPage(val);
    if (serverSide) {
      onPerPageChange?.(val);
    } else {
      setInternalPage(1);
    }
  };

  const handleSort = (key: string) => {
    if (sortKey === key) {
      setSortOrder(sortOrder === 'asc' ? 'desc' : 'asc');
    } else {
      setSortKey(key);
      setSortOrder('asc');
    }
  };

  // Client-side filtering and sorting
  const processedData = useMemo(() => {
    if (serverSide) return data;

    let res = [...data];

    // Filter
    if (internalSearch.trim()) {
      const q = internalSearch.toLowerCase();
      res = res.filter((item) =>
        Object.values(item).some((val) =>
          val !== null && val !== undefined && String(val).toLowerCase().includes(q)
        )
      );
    }

    // Sort
    if (sortKey) {
      res.sort((a, b) => {
        const valA = a[sortKey];
        const valB = b[sortKey];
        if (valA === valB) return 0;
        if (valA === null || valA === undefined) return 1;
        if (valB === null || valB === undefined) return -1;
        if (typeof valA === 'number' && typeof valB === 'number') {
          return sortOrder === 'asc' ? valA - valB : valB - valA;
        }
        return sortOrder === 'asc'
          ? String(valA).localeCompare(String(valB))
          : String(valB).localeCompare(String(valA));
      });
    }

    return res;
  }, [data, internalSearch, sortKey, sortOrder, serverSide]);

  // Client-side Pagination
  const totalItems = serverSide ? (totalCount ?? data.length) : processedData.length;
  const totalPages = Math.ceil(totalItems / currentPerPage) || 1;

  const paginatedData = useMemo(() => {
    if (serverSide) return data;
    const start = (currentPage - 1) * currentPerPage;
    return processedData.slice(start, start + currentPerPage);
  }, [processedData, currentPage, currentPerPage, serverSide, data]);

  const handlePageClick = (p: number) => {
    if (p < 1 || p > totalPages) return;
    if (serverSide) {
      onPageChange?.(p);
    } else {
      setInternalPage(p);
    }
  };

  // Checkbox helpers
  const allCurrentIds = useMemo(() => paginatedData.map((d) => d[idKey]), [paginatedData, idKey]);
  const isAllSelected = allCurrentIds.length > 0 && allCurrentIds.every((id) => selectedIds.includes(id));

  const handleToggleAll = () => {
    if (!onSelectChange) return;
    if (isAllSelected) {
      onSelectChange(selectedIds.filter((id) => !allCurrentIds.includes(id)));
    } else {
      const newSelected = Array.from(new Set([...selectedIds, ...allCurrentIds]));
      onSelectChange(newSelected);
    }
  };

  const handleToggleRow = (id: any) => {
    if (!onSelectChange) return;
    if (selectedIds.includes(id)) {
      onSelectChange(selectedIds.filter((i) => i !== id));
    } else {
      onSelectChange([...selectedIds, id]);
    }
  };

  const startEntry = totalItems === 0 ? 0 : (currentPage - 1) * currentPerPage + 1;
  const endEntry = Math.min(currentPage * currentPerPage, totalItems);

  return (
    <div className="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
      {/* Optional Card Title & Extra Actions */}
      {(title || countBadge !== undefined || actionsRight) && (
        <div className="px-5 py-4 border-b border-slate-100 flex items-center justify-between flex-wrap gap-2">
          {title && (
            <div className="flex items-center space-x-2">
              <h3 className="text-base font-bold text-slate-800">{title}</h3>
              {countBadge !== undefined && (
                <span className="text-xs font-semibold text-slate-400">{countBadge}</span>
              )}
            </div>
          )}
          {actionsRight && <div className="flex items-center gap-2">{actionsRight}</div>}
        </div>
      )}

      {/* Filter Component if any */}
      {filterComponent && <div className="p-4 border-b border-slate-100 bg-slate-50/50">{filterComponent}</div>}

      {/* Bulk Selection Bar matching Laravel theme */}
      {selectable && selectedIds.length > 0 && (
        <div className="px-5 py-2.5 bg-indigo-50/90 border-b border-indigo-100 flex items-center justify-between animate-in fade-in duration-150">
          <div className="flex items-center text-xs font-semibold text-indigo-900">
            <span className="inline-flex items-center justify-center w-5 h-5 rounded-full bg-indigo-600 text-white font-bold text-[10px] mr-2">
              {selectedIds.length}
            </span>
            <span>item dipilih</span>
          </div>

          <div className="flex items-center gap-2">
            {bulkActions ? (
              bulkActions(selectedIds)
            ) : onBulkDelete ? (
              <button
                type="button"
                onClick={() => onBulkDelete(selectedIds)}
                className="inline-flex items-center px-2.5 py-1 rounded bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-2xs transition-colors cursor-pointer"
              >
                Hapus ({selectedIds.length})
              </button>
            ) : null}
          </div>
        </div>
      )}

      {/* DataTable Control Header: Show Entries & Search */}
      {(showPerPage || showSearch) && (
        <div className="px-5 py-3.5 flex flex-col sm:flex-row items-center justify-between gap-3 bg-white">
          {showPerPage && (
            <div className="flex items-center space-x-2 text-xs text-slate-600">
              <span>Show</span>
              <select
                value={currentPerPage}
                onChange={handlePerPageChange}
                className="bg-white border border-slate-200 rounded-md px-2 py-1 text-xs text-slate-700 focus:outline-hidden focus:border-indigo-500 font-medium cursor-pointer"
              >
                {perPageOptions.map((opt) => (
                  <option key={opt} value={opt}>
                    {opt}
                  </option>
                ))}
              </select>
              <span>entries</span>
            </div>
          )}

          {showSearch && (
            <div className="flex items-center space-x-2 text-xs text-slate-600 w-full sm:w-auto justify-end">
              <span className="font-medium text-slate-500">Search:</span>
              <input
                type="text"
                value={internalSearch}
                onChange={handleSearch}
                placeholder={searchPlaceholder}
                className="border border-slate-200 rounded-md px-2.5 py-1 text-xs text-slate-800 w-44 sm:w-56 focus:outline-hidden focus:border-indigo-500 bg-slate-50/50 focus:bg-white transition-colors"
              />
            </div>
          )}
        </div>
      )}

      {/* Table Element */}
      <div className="overflow-x-auto">
        <table className="w-full text-left text-xs border-collapse">
          <thead className="bg-[#f8fafc] text-slate-500 text-[11px] font-bold uppercase tracking-wider border-y border-slate-200">
            <tr>
              {selectable && (
                <th className="w-10 px-4 py-3 text-center">
                  <input
                    type="checkbox"
                    checked={isAllSelected}
                    onChange={handleToggleAll}
                    className="rounded text-indigo-600 focus:ring-0 cursor-pointer"
                  />
                </th>
              )}
              {columns.map((col) => (
                <th
                  key={col.key}
                  onClick={() => col.sortable !== false && handleSort(col.key)}
                  className={`px-4 py-3 select-none ${col.sortable !== false ? 'cursor-pointer hover:bg-slate-100/70 transition-colors' : ''} ${col.headerClassName || ''}`}
                >
                  <div className="flex items-center space-x-1">
                    <span>{col.label}</span>
                    {col.sortable !== false && (
                      <span className="text-slate-400">
                        {sortKey === col.key ? (
                          sortOrder === 'asc' ? (
                            <ArrowUp className="w-3.5 h-3.5 text-indigo-600" />
                          ) : (
                            <ArrowDown className="w-3.5 h-3.5 text-indigo-600" />
                          )
                        ) : (
                          <ChevronsUpDown className="w-3 h-3 text-slate-300" />
                        )}
                      </span>
                    )}
                  </div>
                </th>
              ))}
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-100">
            {isLoading ? (
              <tr>
                <td colSpan={columns.length + (selectable ? 1 : 0)} className="py-12 text-center text-slate-400">
                  <div className="flex flex-col items-center justify-center space-y-2">
                    <div className="w-6 h-6 border-2 border-indigo-600 border-t-transparent rounded-full animate-spin" />
                    <span>Memuat data...</span>
                  </div>
                </td>
              </tr>
            ) : paginatedData.length === 0 ? (
              <tr>
                <td colSpan={columns.length + (selectable ? 1 : 0)} className="py-10 text-center text-slate-400 text-xs">
                  {emptyMessage}
                </td>
              </tr>
            ) : (
              paginatedData.map((item, idx) => {
                const rowId = item[idKey];
                const isSelected = selectedIds.includes(rowId);
                const actualIndex = startEntry + idx;

                return (
                  <tr
                    key={rowId ?? idx}
                    className={`hover:bg-slate-50/80 transition-colors ${isSelected ? 'bg-indigo-50/30' : ''}`}
                  >
                    {selectable && (
                      <td className="px-4 py-3 text-center">
                        <input
                          type="checkbox"
                          checked={isSelected}
                          onChange={() => handleToggleRow(rowId)}
                          className="rounded text-indigo-600 focus:ring-0 cursor-pointer"
                        />
                      </td>
                    )}
                    {columns.map((col) => (
                      <td key={col.key} className={`px-4 py-3 text-slate-700 ${col.className || ''}`}>
                        {col.render ? col.render(item, actualIndex) : item[col.key] ?? '-'}
                      </td>
                    ))}
                  </tr>
                );
              })
            )}
          </tbody>
        </table>
      </div>

      {/* DataTable Control Footer: Showing Info & Pagination Buttons */}
      <div className="px-5 py-3.5 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500 bg-white">
        <div>
          Showing {startEntry} to {endEntry} of {totalItems} entries
        </div>

        {totalPages > 1 && (
          <div className="flex items-center space-x-1 select-none">
            <button
              disabled={currentPage <= 1}
              onClick={() => handlePageClick(currentPage - 1)}
              className={`px-3 py-1 rounded text-xs transition-colors ${
                currentPage <= 1
                  ? 'text-slate-300 cursor-not-allowed'
                  : 'text-slate-600 hover:bg-slate-100 cursor-pointer'
              }`}
            >
              Previous
            </button>

            {Array.from({ length: totalPages }, (_, i) => i + 1)
              .filter(
                (p) =>
                  p === 1 ||
                  p === totalPages ||
                  (p >= currentPage - 2 && p <= currentPage + 2)
              )
              .map((p, idx, arr) => {
                const prev = arr[idx - 1];
                const showEllipsis = prev && p - prev > 1;

                return (
                  <React.Fragment key={p}>
                    {showEllipsis && <span className="px-1 text-slate-400">...</span>}
                    <button
                      onClick={() => handlePageClick(p)}
                      className={`min-w-7 h-7 px-2 rounded flex items-center justify-center font-bold text-xs transition-colors cursor-pointer ${
                        currentPage === p
                          ? 'bg-[#5051F9] text-white shadow-xs'
                          : 'text-slate-600 hover:bg-slate-100'
                      }`}
                    >
                      {p}
                    </button>
                  </React.Fragment>
                );
              })}

            <button
              disabled={currentPage >= totalPages}
              onClick={() => handlePageClick(currentPage + 1)}
              className={`px-3 py-1 rounded text-xs transition-colors ${
                currentPage >= totalPages
                  ? 'text-slate-300 cursor-not-allowed'
                  : 'text-slate-600 hover:bg-slate-100 cursor-pointer'
              }`}
            >
              Next
            </button>
          </div>
        )}
      </div>
    </div>
  );
}

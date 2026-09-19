<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;

class DataBackupService
{
    /**
     * Map of supported modules with their configuration
     */
    public static function modules(): array
    {
        return [
            'transaksi-servis' => [
                'name' => 'Transaksi Servis',
                'main_table' => 'service_transactions',
                'date_col' => 'created_at',
                'cabang_col' => 'cabang_id',
                'child_tables' => [
                    'teknisi_servis' => 'service_transactions_id',
                ],
                'preview_cols' => ['id', 'nomor_servis', 'nama_pelanggan', 'nama_barang', 'kerusakan', 'biaya', 'status_servis', 'created_at'],
            ],
            'transaksi-servis-bisa-diambil' => [
                'name' => 'Servis Bisa Diambil',
                'main_table' => 'service_transactions',
                'date_col' => 'created_at',
                'cabang_col' => 'cabang_id',
                'condition' => function ($query) {
                    $query->where('status_servis', 'Bisa Diambil');
                },
                'child_tables' => [
                    'teknisi_servis' => 'service_transactions_id',
                ],
                'preview_cols' => ['id', 'nomor_servis', 'nama_pelanggan', 'nama_barang', 'status_servis', 'created_at'],
            ],
            'transaksi-servis-sudah-diambil' => [
                'name' => 'Servis Sudah Diambil',
                'main_table' => 'service_transactions',
                'date_col' => 'created_at',
                'cabang_col' => 'cabang_id',
                'condition' => function ($query) {
                    $query->where('status_servis', 'Sudah Diambil');
                },
                'child_tables' => [
                    'teknisi_servis' => 'service_transactions_id',
                ],
                'preview_cols' => ['id', 'nomor_servis', 'nama_pelanggan', 'nama_barang', 'tgl_ambil', 'status_servis', 'created_at'],
            ],
            'transaksi-produk' => [
                'name' => 'Transaksi Penjualan Produk',
                'main_table' => 'orders',
                'date_col' => 'created_at',
                'cabang_col' => 'cabang_id',
                'child_tables' => [
                    'order_details' => 'orders_id',
                ],
                'preview_cols' => ['id', 'invoice_no', 'order_date', 'nama_pelanggan', 'total_products', 'sub_total', 'due', 'created_at'],
            ],
            'history-garansi' => [
                'name' => 'Riwayat Garansi',
                'main_table' => 'history_garansis',
                'date_col' => 'date',
                'cabang_col' => 'cabang_id',
                'preview_cols' => ['id', 'date', 'service_id', 'sparepart', 'total_biaya', 'status', 'created_at'],
            ],
            'pembelian-produk' => [
                'name' => 'Pembelian Produk',
                'main_table' => 'purchases',
                'date_col' => 'date',
                'cabang_col' => 'cabang_id',
                'preview_cols' => ['id', 'reference_number', 'suppliers_name', 'product_name', 'quantity', 'total_price', 'date'],
            ],
            'transfer-stok' => [
                'name' => 'Transfer Stok',
                'main_table' => 'transfer_stoks',
                'date_col' => 'tanggal',
                'custom_cabang' => function ($query, $cabangId) {
                    $query->where(function ($q) use ($cabangId) {
                        $q->where('dari_cabang_id', $cabangId)
                          ->orWhere('ke_cabang_id', $cabangId);
                    });
                },
                'preview_cols' => ['id', 'dari_cabang_id', 'ke_cabang_id', 'stok', 'status', 'tanggal'],
            ],
            'absensi' => [
                'name' => 'Absensi Karyawan',
                'main_table' => 'attendances',
                'date_col' => 'tanggal',
                'cabang_col' => 'cabang_id',
                'preview_cols' => ['id', 'user_id', 'type', 'tanggal', 'waktu', 'nominal_potongan', 'telat'],
            ],
            'izin' => [
                'name' => 'Izin Karyawan',
                'main_table' => 'izins',
                'date_col' => 'tanggal',
                'cabang_col' => 'cabang_id',
                'preview_cols' => ['id', 'user_id', 'tipe', 'tanggal_mulai', 'tanggal_selesai', 'nominal_potongan', 'status'],
            ],
            'lembur' => [
                'name' => 'Lembur Karyawan',
                'main_table' => 'overtimes',
                'date_col' => 'tanggal',
                'cabang_col' => 'cabang_id',
                'preview_cols' => ['id', 'id_user', 'nominal_overtime', 'tanggal', 'waktu_start', 'waktu_end', 'status'],
            ],
            'anggaran' => [
                'name' => 'Anggaran Toko',
                'main_table' => 'budgets',
                'date_col' => 'created_at',
                'cabang_col' => 'cabang_id',
                'preview_cols' => ['id', 'name', 'quantity', 'price', 'total', 'created_at'],
            ],
            'inventaris' => [
                'name' => 'Inventaris Toko',
                'main_table' => 'inventories',
                'date_col' => 'created_at',
                'cabang_col' => 'cabang_id',
                'preview_cols' => ['id', 'name', 'code', 'price', 'supplier', 'created_at'],
            ],
            'insiden' => [
                'name' => 'Insiden / Kerusakan',
                'main_table' => 'incidents',
                'date_col' => 'created_at',
                'cabang_col' => 'cabang_id',
                'preview_cols' => ['id', 'name', 'price', 'workers_id', 'biaya_teknisi', 'biaya_toko', 'created_at'],
            ],
            'kasbon' => [
                'name' => 'Kasbon Karyawan',
                'main_table' => 'debts',
                'date_col' => 'created_at',
                'cabang_col' => 'cabang_id',
                'preview_cols' => ['id', 'workers_id', 'item', 'total', 'is_approve', 'created_at'],
            ],
            'pengeluaran' => [
                'name' => 'Pengeluaran Toko',
                'main_table' => 'expenses',
                'date_col' => 'created_at',
                'cabang_col' => 'cabang_id',
                'preview_cols' => ['id', 'name', 'tipe', 'price', 'is_approve', 'created_at'],
            ],
            'gaji' => [
                'name' => 'Gaji Karyawan',
                'main_table' => 'salaries',
                'date_col' => 'created_at',
                'cabang_col' => 'cabang_id',
                'preview_cols' => ['id', 'name', 'bonus', 'workers_id', 'created_at'],
            ],
            'target-teknisi' => [
                'name' => 'Target Teknisi',
                'main_table' => 'teknisi_targets',
                'date_col' => 'created_at',
                'cabang_col' => 'cabang_id',
                'preview_cols' => ['id', 'item', 'teknisi_name', 'nominal', 'bonus_nominal', 'tipe', 'created_at'],
            ],
            'target-sales' => [
                'name' => 'Target Sales',
                'main_table' => 'sales_targets',
                'date_col' => 'created_at',
                'cabang_col' => 'cabang_id',
                'preview_cols' => ['id', 'item', 'sales_name', 'created_at'],
            ],
            'rincian-invest' => [
                'name' => 'Rincian Investasi',
                'main_table' => 'rincian_invests',
                'date_col' => 'tanggal',
                'cabang_col' => 'cabang_id',
                'preview_cols' => ['id', 'tipe', 'id_investor', 'tanggal', 'nominal', 'created_at'],
            ],
            'refund' => [
                'name' => 'Refund Servis',
                'main_table' => 'refunds',
                'date_col' => 'created_at',
                'cabang_col' => 'cabang_id',
                'preview_cols' => ['id', 'servis_transaction_id', 'nominal', 'teknisi_id', 'is_approve', 'created_at'],
            ],
            'log-servis' => [
                'name' => 'Log Aktivitas Servis',
                'main_table' => 'activity_log',
                'date_col' => 'created_at',
                'cabang_col' => 'cabang_id',
                'condition' => function ($query) {
                    $query->where('log_name', 'like', '%Servis%');
                },
                'preview_cols' => ['id', 'log_name', 'description', 'event', 'created_at'],
            ],
            'log-penjualan' => [
                'name' => 'Log Aktivitas Penjualan',
                'main_table' => 'activity_log',
                'date_col' => 'created_at',
                'cabang_col' => 'cabang_id',
                'condition' => function ($query) {
                    $query->where('log_name', 'like', '%Penjualan%');
                },
                'preview_cols' => ['id', 'log_name', 'description', 'event', 'created_at'],
            ],
        ];
    }

    /**
     * Get single module configuration
     */
    public static function getModuleConfig($moduleKey)
    {
        $modules = self::modules();
        return $modules[$moduleKey] ?? null;
    }

    /**
     * Generate SQL Backup for a given module, branch, and date range
     */
    public function exportSql($moduleKey, $startDate, $endDate, $cabangId): array
    {
        $config = self::getModuleConfig($moduleKey);
        if (!$config) {
            throw new \Exception("Modul '{$moduleKey}' tidak didukung untuk backup.");
        }

        $mainTable = $config['main_table'];
        $dateCol = $config['date_col'];

        $query = DB::table($mainTable);

        // Branch condition
        if (isset($config['custom_cabang'])) {
            $config['custom_cabang']($query, $cabangId);
        } elseif (!empty($config['cabang_col']) && Schema::hasColumn($mainTable, $config['cabang_col'])) {
            $query->where($config['cabang_col'], $cabangId);
        }

        // Date range condition
        $query->whereBetween(DB::raw("DATE({$dateCol})"), [$startDate, $endDate]);

        // Custom condition if any (e.g. status_servis)
        if (isset($config['condition']) && is_callable($config['condition'])) {
            $config['condition']($query);
        }

        $mainRows = $query->get();
        $totalCount = $mainRows->count();
        $mainIds = $mainRows->pluck('id')->toArray();

        // Build SQL
        $sql = "-- SarabaBisa Data Backup\n";
        $sql .= "-- Module: {$config['name']} ({$moduleKey})\n";
        $sql .= "-- Cabang ID: {$cabangId}\n";
        $sql .= "-- Periode: {$startDate} s/d {$endDate}\n";
        $sql .= "-- Total Records: {$totalCount}\n";
        $sql .= "-- Generated At: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- --------------------------------------------------------\n\n";

        if ($totalCount > 0) {
            $sql .= $this->buildInsertStatements($mainTable, $mainRows);
        }

        // Handle Child tables if configured
        if (!empty($config['child_tables']) && !empty($mainIds)) {
            foreach ($config['child_tables'] as $childTable => $fkCol) {
                if (Schema::hasTable($childTable)) {
                    $childRows = DB::table($childTable)->whereIn($fkCol, $mainIds)->get();
                    if ($childRows->count() > 0) {
                        $sql .= "\n-- Child Table: {$childTable} (" . $childRows->count() . " records)\n";
                        $sql .= $this->buildInsertStatements($childTable, $childRows);
                    }
                }
            }
        }

        // Ensure storage directory exists
        $dir = storage_path('app/backups');
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $timestamp = date('Ymd_His');
        $safeMod = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $moduleKey);
        $fileName = "backup_{$safeMod}_{$startDate}_{$endDate}_{$timestamp}.sql";
        $filePath = "{$dir}/{$fileName}";

        File::put($filePath, $sql);

        return [
            'file_name' => $fileName,
            'file_path' => $filePath,
            'record_count' => $totalCount,
            'module_name' => $config['name'],
            'sql_content' => $sql,
        ];
    }

    /**
     * Build INSERT statements for rows
     */
    protected function buildInsertStatements(string $table, $rows): string
    {
        if ($rows->isEmpty()) {
            return "";
        }

        $firstRow = (array) $rows->first();
        $columns = array_keys($firstRow);
        $escapedCols = array_map(function ($c) {
            return "`" . str_replace("`", "``", $c) . "`";
        }, $columns);
        $colList = implode(', ', $escapedCols);

        $sql = "";
        $batches = $rows->chunk(100);

        foreach ($batches as $batch) {
            $valueLines = [];
            foreach ($batch as $row) {
                $rowArray = (array) $row;
                $vals = [];
                foreach ($columns as $col) {
                    $val = $rowArray[$col] ?? null;
                    if (is_null($val)) {
                        $vals[] = "NULL";
                    } elseif (is_numeric($val) && !is_string($val)) {
                        $vals[] = $val;
                    } else {
                        $escaped = str_replace(
                            ["\\", "\0", "\n", "\r", "'", '"', "\x1a"],
                            ["\\\\", "\\0", "\\n", "\\r", "\\'", '\\"', "\\Z"],
                            (string)$val
                        );
                        $vals[] = "'{$escaped}'";
                    }
                }
                $valueLines[] = "(" . implode(', ', $vals) . ")";
            }
            $sql .= "INSERT INTO `{$table}` ({$colList}) VALUES\n  " . implode(",\n  ", $valueLines) . ";\n\n";
        }

        return $sql;
    }

    /**
     * Delete data within branch and date range
     */
    public function deleteData($moduleKey, $startDate, $endDate, $cabangId): int
    {
        $config = self::getModuleConfig($moduleKey);
        if (!$config) {
            throw new \Exception("Modul '{$moduleKey}' tidak didukung untuk penghapusan.");
        }

        $mainTable = $config['main_table'];
        $dateCol = $config['date_col'];

        $query = DB::table($mainTable);

        if (isset($config['custom_cabang'])) {
            $config['custom_cabang']($query, $cabangId);
        } elseif (!empty($config['cabang_col']) && Schema::hasColumn($mainTable, $config['cabang_col'])) {
            $query->where($config['cabang_col'], $cabangId);
        }

        $query->whereBetween(DB::raw("DATE({$dateCol})"), [$startDate, $endDate]);

        if (isset($config['condition']) && is_callable($config['condition'])) {
            $config['condition']($query);
        }

        $mainIds = $query->pluck('id')->toArray();
        if (empty($mainIds)) {
            return 0;
        }

        // 1. Delete child table records first
        if (!empty($config['child_tables'])) {
            foreach ($config['child_tables'] as $childTable => $fkCol) {
                if (Schema::hasTable($childTable)) {
                    DB::table($childTable)->whereIn($fkCol, $mainIds)->delete();
                }
            }
        }

        // 2. Delete main records
        $deletedCount = DB::table($mainTable)->whereIn('id', $mainIds)->delete();

        return $deletedCount;
    }

    /**
     * Parse SQL file content to extract rows and columns for preview & chunked restore
     */
    /**
     * Parse SQL file content to extract rows and columns for preview & chunked restore
     */
    public function parseSqlForPreview(string $sqlContent): array
    {
        $tables = [];
        $len = strlen($sqlContent);
        $i = 0;

        while ($i < $len) {
            // Skip comments and whitespace
            if ($sqlContent[$i] === '-' && isset($sqlContent[$i + 1]) && $sqlContent[$i + 1] === '-') {
                while ($i < $len && $sqlContent[$i] !== "\n") $i++;
                continue;
            }
            if ($sqlContent[$i] === '/' && isset($sqlContent[$i + 1]) && $sqlContent[$i + 1] === '*') {
                $i += 2;
                while ($i < $len && !($sqlContent[$i - 1] === '*' && $sqlContent[$i] === '/')) $i++;
                $i++;
                continue;
            }

            // Check for INSERT INTO
            if (strncasecmp(substr($sqlContent, $i, 11), 'INSERT INTO', 11) === 0) {
                $i += 11;

                // Extract table name
                while ($i < $len && ctype_space($sqlContent[$i])) $i++;
                $tableName = '';
                $quote = null;
                if ($i < $len && ($sqlContent[$i] === '`' || $sqlContent[$i] === '"' || $sqlContent[$i] === "'")) {
                    $quote = $sqlContent[$i++];
                    while ($i < $len && $sqlContent[$i] !== $quote) {
                        $tableName .= $sqlContent[$i++];
                    }
                    $i++;
                } else {
                    while ($i < $len && !ctype_space($sqlContent[$i]) && $sqlContent[$i] !== '(') {
                        $tableName .= $sqlContent[$i++];
                    }
                }

                // Extract columns (...)
                while ($i < $len && $sqlContent[$i] !== '(') $i++;
                $i++; // skip '('
                $colStr = '';
                while ($i < $len && $sqlContent[$i] !== ')') {
                    $colStr .= $sqlContent[$i++];
                }
                $i++; // skip ')'
                $columns = array_map(function ($c) {
                    return trim($c, " `\"\t\n\r");
                }, explode(',', $colStr));

                // Move to VALUES
                while ($i < $len && strncasecmp(substr($sqlContent, $i, 6), 'VALUES', 6) !== 0) $i++;
                $i += 6; // skip 'VALUES'

                if (!isset($tables[$tableName])) {
                    $tables[$tableName] = [
                        'columns' => $columns,
                        'rows' => [],
                    ];
                }

                // Parse tuples until ';'
                while ($i < $len && $sqlContent[$i] !== ';') {
                    // Find next '('
                    while ($i < $len && $sqlContent[$i] !== '(' && $sqlContent[$i] !== ';') $i++;
                    if ($i >= $len || $sqlContent[$i] === ';') break;
                    $i++; // skip '('

                    // Parse tuple values
                    $tuple = [];
                    $curVal = '';
                    $inString = false;
                    $strQuote = '';
                    $escaped = false;

                    while ($i < $len) {
                        $char = $sqlContent[$i];

                        if ($escaped) {
                            if ($char === 'n') $curVal .= "\n";
                            elseif ($char === 'r') $curVal .= "\r";
                            elseif ($char === 't') $curVal .= "\t";
                            else $curVal .= $char;
                            $escaped = false;
                            $i++;
                            continue;
                        }

                        if ($char === '\\') {
                            $escaped = true;
                            $i++;
                            continue;
                        }

                        if ($inString) {
                            if ($char === $strQuote) {
                                $inString = false;
                            } else {
                                $curVal .= $char;
                            }
                        } else {
                            if ($char === "'" || $char === '"') {
                                $inString = true;
                                $strQuote = $char;
                            } elseif ($char === ',') {
                                $tuple[] = $this->cleanParsedVal($curVal);
                                $curVal = '';
                            } elseif ($char === ')') {
                                $tuple[] = $this->cleanParsedVal($curVal);
                                $curVal = '';
                                $i++;
                                break;
                            } else {
                                $curVal .= $char;
                            }
                        }
                        $i++;
                    }

                    if (!empty($tuple)) {
                        $row = [];
                        foreach ($columns as $idx => $cName) {
                            $row[$cName] = $tuple[$idx] ?? null;
                        }
                        $tables[$tableName]['rows'][] = $row;
                    }
                }
            }
            $i++;
        }

        if (empty($tables)) {
            throw new \Exception("File SQL tidak mengandung data INSERT yang valid atau kosong.");
        }

        // Primary table is the first one found
        $primaryTable = array_key_first($tables);
        $primaryData = $tables[$primaryTable];

        return [
            'primary_table' => $primaryTable,
            'all_tables' => $tables,
            'columns' => $primaryData['columns'],
            'total_records' => count($primaryData['rows']),
            'preview_rows' => array_slice($primaryData['rows'], 0, 50),
        ];
    }

    /**
     * Restore a chunk of rows into a table, skipping existing IDs
     */
    public function restoreChunk(string $tableName, array $rows, $cabangId): array
    {
        if (!Schema::hasTable($tableName)) {
            throw new \Exception("Tabel '{$tableName}' tidak ditemukan di database.");
        }

        $validColumns = array_flip(Schema::getColumnListing($tableName));
        $restored = 0;
        $skipped = 0;

        // Temporarily disable foreign key checks for safety
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        try {
            foreach ($rows as $row) {
                if (!is_array($row) && !is_object($row)) {
                    continue;
                }
                $rowArray = (array) $row;

                // Filter to only columns that actually exist in the table schema
                $filteredData = array_intersect_key($rowArray, $validColumns);

                if (empty($filteredData)) {
                    continue;
                }

                // If table has cabang_id and user is in a specific branch, enforce cabang_id match
                if ($cabangId && isset($validColumns['cabang_id'])) {
                    if (isset($filteredData['cabang_id']) && (int)$filteredData['cabang_id'] !== (int)$cabangId) {
                        $skipped++;
                        continue;
                    }
                }

                // Check duplicate by 'id' if present
                if (isset($filteredData['id'])) {
                    $exists = DB::table($tableName)->where('id', $filteredData['id'])->exists();
                    if ($exists) {
                        $skipped++;
                        continue;
                    }
                }

                // Insert record
                DB::table($tableName)->insert($filteredData);
                $restored++;
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        return [
            'restored' => $restored,
            'skipped' => $skipped,
        ];
    }

    protected function cleanParsedVal($val)
    {
        $v = trim($val);
        if (strtoupper($v) === 'NULL' || $v === '') {
            return null;
        }
        if ((str_starts_with($v, "'") && str_ends_with($v, "'")) || (str_starts_with($v, '"') && str_ends_with($v, '"'))) {
            $v = substr($v, 1, -1);
        }
        return stripslashes($v);
    }

    /**
     * Verify uploaded SQL file matches selected module and period before deletion
     */
    public function verifyFileMatch(string $sqlContent, string $moduleKey, string $startDate, string $endDate, ?int $cabangId = null): array
    {
        $config = self::getModuleConfig($moduleKey);
        if (!$config) {
            return [
                'matched' => false,
                'message' => 'Modul data tidak dikenali sistem.',
            ];
        }

        $expectedTable = $config['main_table'];

        // 1. Check Header Comments if generated by system
        if (preg_match('/-- Module:.*?\(([a-zA-Z0-9_\-]+)\)/', $sqlContent, $modMatches)) {
            if ($modMatches[1] !== $moduleKey) {
                return [
                    'matched' => false,
                    'message' => "Modul file backup tidak sesuai! File ini untuk modul '{$modMatches[1]}', sedangkan Anda sedang membuka modul '{$moduleKey}'.",
                ];
            }
        }

        if (preg_match('/-- Periode:\s*([0-9]{4}-[0-9]{2}-[0-9]{2})\s*s\/d\s*([0-9]{4}-[0-9]{2}-[0-9]{2})/', $sqlContent, $periodMatches)) {
            $fileStart = $periodMatches[1];
            $fileEnd = $periodMatches[2];
            if ($fileStart !== $startDate || $fileEnd !== $endDate) {
                return [
                    'matched' => false,
                    'message' => "Periode tanggal file backup ({$fileStart} s/d {$fileEnd}) tidak cocok dengan periode yang Anda pilih ({$startDate} s/d {$endDate})!",
                ];
            }
        }

        if ($cabangId && preg_match('/-- Cabang ID:\s*([0-9]+)/', $sqlContent, $cabangMatches)) {
            if ((int)$cabangMatches[1] !== (int)$cabangId) {
                return [
                    'matched' => false,
                    'message' => "File backup ini berasal dari cabang lain (ID {$cabangMatches[1]}), tidak sesuai dengan cabang aktif Anda (ID {$cabangId})!",
                ];
            }
        }

        // 2. Parse SQL content
        try {
            $parsed = $this->parseSqlForPreview($sqlContent);
        } catch (\Throwable $e) {
            return [
                'matched' => false,
                'message' => 'Gagal membaca format file SQL: ' . $e->getMessage(),
            ];
        }

        if (empty($parsed['all_tables'])) {
            return [
                'matched' => false,
                'message' => 'File SQL tidak memuat perintah INSERT INTO atau data kosong.',
            ];
        }

        if (!isset($parsed['all_tables'][$expectedTable])) {
            $foundTables = implode(', ', array_keys($parsed['all_tables']));
            return [
                'matched' => false,
                'message' => "Tabel di dalam file SQL ({$foundTables}) tidak cocok dengan tabel modul {$config['name']} ({$expectedTable}).",
            ];
        }

        $totalRecords = $parsed['total_records'];
        if ($totalRecords <= 0) {
            return [
                'matched' => false,
                'message' => 'File backup tidak memiliki baris data (0 data). Pastikan file berisi data backup yang valid.',
            ];
        }

        return [
            'matched' => true,
            'module_name' => $config['name'],
            'table_name' => $expectedTable,
            'total_records' => $totalRecords,
            'file_period' => ($periodMatches[1] ?? $startDate) . ' s/d ' . ($periodMatches[2] ?? $endDate),
            'columns' => $parsed['columns'],
            'preview_rows' => $parsed['preview_rows'],
            'message' => "File backup terverifikasi valid & sesuai! Memuat {$totalRecords} data modul {$config['name']}.",
        ];
    }
}


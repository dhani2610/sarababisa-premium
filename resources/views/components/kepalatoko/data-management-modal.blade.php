@props([
    'module',
    'title' => null,
    'buttonText' => 'Kelola & Backup Data',
    'class' => '',
])

@php
    $user = Auth::user();
    $canManage = $user && in_array($user->role, ['Kepala Toko', 'Super Admin']);
    $modalId = 'modal-dm-' . Str::slug($module);
    $moduleConfig = \App\Services\DataBackupService::getModuleConfig($module);
    $moduleName = $title ?? ($moduleConfig['name'] ?? ucwords(str_replace('-', ' ', $module)));
@endphp

@if($canManage)
    <!-- Button Trigger -->
    <button type="button"
            onclick="openDataManagementModal('{{ $modalId }}')"
            class="btn bg-white border-slate-300 hover:border-slate-400 text-slate-700 shadow-sm text-xs font-semibold py-1.5 px-2.5 rounded-lg inline-flex items-center gap-1.5 transition {{ $class }}">
        <svg style="width: 14px; height: 14px; min-width: 14px;" class="text-indigo-600 fill-current shrink-0" viewBox="0 0 16 16">
            <path d="M2 1a1 1 0 00-1 1v12a1 1 0 001 1h12a1 1 0 001-1V2a1 1 0 00-1-1H2zm1 2h10v2H3V3zm0 4h10v2H3V7zm0 4h10v2H3v-2z"/>
        </svg>
        <span>{{ $buttonText }}</span>
    </button>

    <!-- Modal Universal Data Management -->
    <div id="{{ $modalId }}" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-60 transition-opacity" onclick="closeDataManagementModal('{{ $modalId }}')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200">
                <!-- Modal Header -->
                <div class="bg-slate-900 px-5 py-3.5 flex justify-between items-center text-white">
                    <div class="flex items-center gap-2">
                        <svg style="width: 16px; height: 16px; min-width: 16px;" class="text-indigo-400 fill-current shrink-0" viewBox="0 0 16 16">
                            <path d="M2 1a1 1 0 00-1 1v12a1 1 0 001 1h12a1 1 0 001-1V2a1 1 0 00-1-1H2zm1 2h10v2H3V3zm0 4h10v2H3V7zm0 4h10v2H3v-2z"/>
                        </svg>
                        <div>
                            <h3 class="font-bold text-sm sm:text-base text-white">Manajemen & Arsip Data: {{ $moduleName }}</h3>
                            <p class="text-[11px] text-slate-400">Khusus Kepala Toko - Backup, Pengurangan Beban Server, dan Restore</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeDataManagementModal('{{ $modalId }}')" class="text-slate-400 hover:text-white text-xl font-bold p-1">&times;</button>
                </div>

                <!-- Tabs Header -->
                <div class="border-b border-slate-200 bg-slate-50 px-5 pt-2 flex space-x-3">
                    <button type="button" onclick="switchDmTab('{{ $modalId }}', 'backup')" id="{{ $modalId }}-tab-btn-backup"
                            class="pb-2 px-2 text-xs font-semibold border-b-2 border-indigo-600 text-indigo-600 flex items-center gap-1 transition">
                        <svg style="width: 13px; height: 13px; min-width: 13px;" class="fill-current shrink-0" viewBox="0 0 16 16">
                            <path d="M8 12l-4-4h2.5V2h3v6H12L8 12zM2 14v-2h12v2H2z"/>
                        </svg>
                        Backup Data
                    </button>
                    <button type="button" onclick="switchDmTab('{{ $modalId }}', 'delete')" id="{{ $modalId }}-tab-btn-delete"
                            class="pb-2 px-2 text-xs font-semibold border-b-2 border-transparent text-slate-500 hover:text-rose-600 flex items-center gap-1 transition">
                        <svg style="width: 13px; height: 13px; min-width: 13px;" class="fill-current shrink-0" viewBox="0 0 16 16">
                            <path d="M5 2V1h6v1h4v2H1V2h4zm1 3h2v8H6V5zm4 0h2v8h-2V5z"/>
                        </svg>
                        Hapus Data
                    </button>
                    <button type="button" onclick="switchDmTab('{{ $modalId }}', 'restore')" id="{{ $modalId }}-tab-btn-restore"
                            class="pb-2 px-2 text-xs font-semibold border-b-2 border-transparent text-slate-500 hover:text-indigo-600 flex items-center gap-1 transition">
                        <svg style="width: 13px; height: 13px; min-width: 13px;" class="fill-current shrink-0" viewBox="0 0 16 16">
                            <path d="M8 3a5 5 0 104.546 2.914.75.75 0 011.36-.632A6.5 6.5 0 118 1.5v-1l3 2-3 2V3z"/>
                        </svg>
                        Restore Data
                    </button>
                </div>

                <!-- Tab Contents -->
                <div class="p-5">
                    <!-- ================= TAB 1: BACKUP ================= -->
                    <div id="{{ $modalId }}-tab-backup" class="space-y-3.5">
                        <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-3 text-xs text-indigo-900 flex items-start gap-2.5">
                            <svg style="width: 15px; height: 15px; min-width: 15px;" class="text-indigo-600 fill-current shrink-0 mt-0.5" viewBox="0 0 16 16">
                                <path d="M8 0a8 8 0 100 16A8 8 0 008 0zm.75 12h-1.5V7h1.5v5zm0-6.5h-1.5V4h1.5v1.5z"/>
                            </svg>
                            <div class="leading-relaxed">
                                <strong>Alur Backup Data:</strong>
                                Sistem akan memfilter data berdasarkan rentang tanggal dan cabang Anda, lalu membuat file <code>.sql</code> berisi baris <code>INSERT INTO</code> yang siap disimpan kembali kapan saja. File akan <strong>otomatis terunduh di browser</strong>, salinan disimpan di server VPS (menu Arsip Data), dan dikirimkan ke email penerima backup.
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Dari Tanggal</label>
                                <input type="date" id="{{ $modalId }}-backup-start" value="{{ date('Y-m-01') }}" class="form-input w-full text-xs py-1.5">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Sampai Tanggal</label>
                                <input type="date" id="{{ $modalId }}-backup-end" value="{{ date('Y-m-d') }}" class="form-input w-full text-xs py-1.5">
                            </div>
                        </div>

                        <div class="flex justify-end pt-2 border-t border-slate-100">
                            <button type="button" id="{{ $modalId }}-btn-exec-backup" onclick="execDmBackup('{{ $modalId }}', '{{ $module }}')" style="background-color: #4f46e5 !important; color: #ffffff !important; padding: 8px 18px !important; border-radius: 8px !important; font-weight: 700 !important; font-size: 12px !important; display: inline-flex !important; align-items: center !important; gap: 6px !important; border: 1px solid #4338ca !important; cursor: pointer !important; box-shadow: 0 1px 3px rgba(0,0,0,0.12) !important;">
                                <svg style="width: 13px; height: 13px; min-width: 13px;" class="fill-current shrink-0" viewBox="0 0 16 16">
                                    <path d="M8 12l-4-4h2.5V2h3v6H12L8 12zM2 14v-2h12v2H2z"/>
                                </svg>
                                <span>Mulai Backup & Unduh</span>
                            </button>
                        </div>
                    </div>

                    <!-- ================= TAB 2: HAPUS DATA ================= -->
                    <div id="{{ $modalId }}-tab-delete" class="hidden space-y-3.5">
                        <div style="background: #fff1f2; border: 1px solid #fecdd3; border-radius: 8px; padding: 10px 12px; font-size: 11.5px; color: #881337; display: flex; align-items: flex-start; gap: 8px;">
                            <svg style="width: 15px; height: 15px; min-width: 15px; color: #e11d48;" class="fill-current shrink-0 mt-0.5" viewBox="0 0 16 16">
                                <path d="M8.982 1.566a1.13 1.13 0 00-1.964 0L.165 13.233c-.457.778.091 1.767.982 1.767h13.706c.89 0 1.438-.99.982-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 01-1.1 0L7.1 5.995A.905.905 0 018 5zm.002 6a1 1 0 110 2 1 1 0 010-2z"/>
                            </svg>
                            <div style="line-height: 1.5;">
                                <strong style="font-weight: 700; color: #be123c;">Peringatan Keamanan Database:</strong>
                                Menghapus data akan membersihkan database pada periode tersebut untuk meringankan beban server dan CPU.
                                <br>
                                <strong>Ketentuan Wajib:</strong> Anda <u>wajib mengunggah file backup (.sql)</u> yang sesuai dengan periode tersebut. Sistem akan memverifikasi kesesuaian modul, rentang tanggal, dan isi file sebelum mengizinkan penghapusan.
                            </div>
                        </div>

                        <!-- Periode Tanggal -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label style="font-size: 10.5px !important; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 3px; display: block; letter-spacing: 0.025em;">Dari Tanggal</label>
                                <input type="date" id="{{ $modalId }}-delete-start" value="{{ date('Y-m-01') }}" class="form-input w-full text-xs py-1.5">
                            </div>
                            <div>
                                <label style="font-size: 10.5px !important; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 3px; display: block; letter-spacing: 0.025em;">Sampai Tanggal</label>
                                <input type="date" id="{{ $modalId }}-delete-end" value="{{ date('Y-m-d') }}" class="form-input w-full text-xs py-1.5">
                            </div>
                        </div>

                        <!-- Upload File Backup Manual untuk Verifikasi -->
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px;" class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label style="font-size: 10.5px !important; font-weight: 700; text-transform: uppercase; color: #334155; display: block; letter-spacing: 0.025em;">
                                    Upload File Backup (.sql) Untuk Verifikasi
                                </label>
                                <span style="font-size: 10px; color: #0284c7; font-weight: 600;">Wajib Upload File</span>
                            </div>
                            <input type="file" id="{{ $modalId }}-file-delete-verify" accept=".sql" class="form-input w-full text-xs py-1.5 file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100">
                            <p style="font-size: 11px; color: #64748b; line-height: 1.4;">
                                Pilih file backup <code>.sql</code> yang telah Anda unduh. Sistem akan memeriksa apakah isi file memuat data modul dan periode tanggal yang dipilih.
                            </p>

                            <div class="pt-1 flex items-center gap-2">
                                <button type="button" id="{{ $modalId }}-btn-verify-file" onclick="verifyDeleteFile('{{ $modalId }}', '{{ $module }}')" style="background-color: #0284c7 !important; color: #ffffff !important; font-size: 12px !important; font-weight: 600; padding: 6px 14px !important; border-radius: 6px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 1px 2px rgba(0,0,0,0.08);">
                                    <svg style="width: 13px; height: 13px; min-width: 13px;" class="fill-current" viewBox="0 0 16 16">
                                        <path d="M1 2a1 1 0 011-1h12a1 1 0 011 1v2H1V2zm0 4h14v3H1V6zm0 5h14v3a1 1 0 01-1 1H2a1 1 0 01-1-1v-3z"/>
                                    </svg>
                                    <span>Cek & Preview File Backup</span>
                                </button>
                            </div>
                        </div>

                        <!-- Preview Area for Deletion Verification -->
                        <div id="{{ $modalId }}-delete-preview-area" class="hidden space-y-2.5 pt-2 border-t border-slate-200">
                            <div id="{{ $modalId }}-verify-status-box" style="padding: 10px 12px; border-radius: 8px; font-size: 11.5px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46;">
                                <div class="font-bold flex items-center gap-1.5 text-emerald-800">
                                    <svg style="width: 14px; height: 14px; min-width: 14px;" class="fill-current text-emerald-600" viewBox="0 0 16 16">
                                        <path d="M8 0a8 8 0 100 16A8 8 0 008 0zm3.5 6L7 10.5 4.5 8l1-1L7 8.5l3.5-3.5 1 1z"/>
                                    </svg>
                                    <span>File Backup Terverifikasi & Sesuai!</span>
                                </div>
                                <div style="margin-top: 6px; display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 8px; font-size: 11px;">
                                    <div>File: <strong id="{{ $modalId }}-del-prev-filename" class="font-mono text-slate-800 break-all"></strong></div>
                                    <div>Modul: <strong id="{{ $modalId }}-del-prev-module" class="text-slate-800"></strong></div>
                                    <div>Periode File: <strong id="{{ $modalId }}-del-prev-period" class="font-mono text-slate-800"></strong></div>
                                    <div>Jumlah Data: <strong id="{{ $modalId }}-del-prev-total" class="font-mono text-emerald-700 font-bold"></strong></div>
                                </div>
                            </div>

                            <div class="border border-slate-200 rounded-lg overflow-x-auto max-h-36">
                                <table class="table-auto w-full text-xs text-left">
                                    <thead class="bg-slate-100 text-slate-700 sticky top-0" id="{{ $modalId }}-del-prev-thead"></thead>
                                    <tbody class="divide-y divide-slate-100 font-mono text-[11px]" id="{{ $modalId }}-del-prev-tbody"></tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Action Button: Cek Arsip & Hapus Data -->
                        <div class="flex justify-end pt-3 border-t border-slate-100">
                            <button type="button" id="{{ $modalId }}-btn-exec-delete" onclick="execDmDelete('{{ $modalId }}', '{{ $module }}')" style="background-color: #e11d48 !important; color: #ffffff !important; padding: 8px 18px !important; border-radius: 8px !important; font-weight: 700 !important; font-size: 12px !important; display: inline-flex !important; align-items: center !important; gap: 6px !important; border: 1px solid #be123c !important; cursor: pointer !important; box-shadow: 0 1px 3px rgba(0,0,0,0.15) !important;">
                                <svg style="width: 13px; height: 13px; min-width: 13px;" class="fill-current shrink-0" viewBox="0 0 16 16">
                                    <path d="M5 2V1h6v1h4v2H1V2h4zm1 3h2v8H6V5zm4 0h2v8h-2V5z"/>
                                </svg>
                                <span>Cek Arsip & Hapus Data</span>
                            </button>
                        </div>
                    </div>

                    <!-- ================= TAB 3: RESTORE DATA ================= -->
                    <div id="{{ $modalId }}-tab-restore" class="hidden space-y-3.5">
                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-xs text-amber-900 flex items-start gap-2.5">
                            <svg style="width: 15px; height: 15px; min-width: 15px;" class="text-amber-600 fill-current shrink-0 mt-0.5" viewBox="0 0 16 16">
                                <path d="M8 0a8 8 0 100 16A8 8 0 008 0zm.75 12h-1.5V7h1.5v5zm0-6.5h-1.5V4h1.5v1.5z"/>
                            </svg>
                            <div class="leading-relaxed">
                                <strong>Alur Restore Aman & Ringan:</strong>
                                Unggah file <code>.sql</code> backup Anda, lalu klik tombol <strong>Preview Data</strong> untuk melihat pratinjau tabel. Proses restore dilakukan bertahap (chunk) dan data yang sudah ada di database akan otomatis <strong>dilewati (skip)</strong>.
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-xs font-semibold text-slate-700 uppercase">Pilih File SQL Backup</label>
                            <input type="file" id="{{ $modalId }}-file-restore" accept=".sql" class="form-input w-full text-xs py-1.5 file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        </div>

                        <div class="flex items-center pt-1">
                            <button type="button" id="{{ $modalId }}-btn-preview" onclick="previewDmSql('{{ $modalId }}')" style="background-color: #1e293b !important; color: #ffffff !important; padding: 6px 14px !important; height: 32px !important; border-radius: 6px !important; font-weight: 600 !important; font-size: 12px !important; display: inline-flex !important; align-items: center !important; gap: 6px !important; border: none !important; cursor: pointer !important;">
                                <svg style="width: 13px; height: 13px; min-width: 13px;" class="fill-current shrink-0" viewBox="0 0 16 16">
                                    <path d="M1 2a1 1 0 011-1h12a1 1 0 011 1v2H1V2zm0 4h14v3H1V6zm0 5h14v3a1 1 0 01-1 1H2a1 1 0 01-1-1v-3z"/>
                                </svg>
                                <span>Preview Data</span>
                            </button>
                        </div>

                        <!-- Preview Area -->
                        <div id="{{ $modalId }}-preview-area" class="hidden space-y-3 pt-3 border-t border-slate-200">
                            <div class="flex flex-wrap items-center justify-between bg-slate-50 p-2.5 rounded-lg border border-slate-200 text-xs">
                                <div><span class="text-slate-500">Tabel:</span> <strong id="{{ $modalId }}-prev-table" class="text-indigo-600 font-mono"></strong></div>
                                <div><span class="text-slate-500">Total Baris:</span> <strong id="{{ $modalId }}-prev-total" class="text-emerald-600 font-mono"></strong></div>
                            </div>

                            <div class="border border-slate-200 rounded-lg overflow-x-auto max-h-48">
                                <table class="table-auto w-full text-xs text-left">
                                    <thead class="bg-slate-100 text-slate-700 sticky top-0" id="{{ $modalId }}-prev-thead"></thead>
                                    <tbody class="divide-y divide-slate-100 font-mono text-[11px]" id="{{ $modalId }}-prev-tbody"></tbody>
                                </table>
                            </div>

                            <!-- Restore Progress Box -->
                            <div id="{{ $modalId }}-progress-box" class="hidden bg-slate-50 p-3 rounded-lg border border-slate-200">
                                <div class="flex justify-between text-xs font-medium text-slate-700 mb-1">
                                    <span id="{{ $modalId }}-progress-text">Memulihkan data bertahap...</span>
                                    <span id="{{ $modalId }}-progress-pct" class="font-mono">0%</span>
                                </div>
                                <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                                    <div id="{{ $modalId }}-progress-bar" class="bg-indigo-600 h-1.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                                </div>
                                <div class="flex justify-between text-[11px] text-slate-500 mt-1.5">
                                    <span>Berhasil: <strong id="{{ $modalId }}-stat-ok" class="text-emerald-600 font-mono">0</strong></span>
                                    <span>Dilewati (Duplikat): <strong id="{{ $modalId }}-stat-skip" class="text-amber-600 font-mono">0</strong></span>
                                </div>
                            </div>

                            <div class="flex justify-end pt-2">
                                <button type="button" id="{{ $modalId }}-btn-exec-restore" onclick="startChunkedRestore('{{ $modalId }}')" style="background-color: #4f46e5 !important; color: #ffffff !important; padding: 8px 18px !important; border-radius: 8px !important; font-weight: 700 !important; font-size: 12px !important; display: inline-flex !important; align-items: center !important; gap: 6px !important; border: 1px solid #4338ca !important; cursor: pointer !important; box-shadow: 0 1px 3px rgba(0,0,0,0.12) !important;">
                                    <svg style="width: 13px; height: 13px; min-width: 13px;" class="fill-current shrink-0" viewBox="0 0 16 16">
                                        <path d="M8 3a5 5 0 104.546 2.914.75.75 0 011.36-.632A6.5 6.5 0 118 1.5v-1l3 2-3 2V3z"/>
                                    </svg>
                                    <span>Mulai Restore ke Database</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

@once
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<link rel="stylesheet" href="{{ asset('css/sweetalert2-custom.css') }}?v={{ time() }}">
@endpush
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    window.dmParsedData = window.dmParsedData || {};

    function openDataManagementModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
    }

    function closeDataManagementModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }

    function switchDmTab(modalId, tabName) {
        ['backup', 'delete', 'restore'].forEach(t => {
            const tabEl = document.getElementById(`${modalId}-tab-${t}`);
            const btnEl = document.getElementById(`${modalId}-tab-btn-${t}`);
            if (t === tabName) {
                tabEl.classList.remove('hidden');
                btnEl.classList.add('border-indigo-600', 'text-indigo-600');
                btnEl.classList.remove('border-transparent', 'text-slate-500');
            } else {
                tabEl.classList.add('hidden');
                btnEl.classList.remove('border-indigo-600', 'text-indigo-600');
                btnEl.classList.add('border-transparent', 'text-slate-500');
            }
        });
    }

    async function execDmBackup(modalId, moduleKey) {
        const startDate = document.getElementById(`${modalId}-backup-start`).value;
        const endDate = document.getElementById(`${modalId}-backup-end`).value;

        if (!startDate || !endDate) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Silakan pilih rentang tanggal awal dan akhir.',
                confirmButtonColor: '#4f46e5'
            });
            return;
        }

        const btn = document.getElementById(`${modalId}-btn-exec-backup`);
        btn.disabled = true;
        btn.innerHTML = 'Memproses Backup...';

        try {
            const res = await fetch('{{ route("data-management.backup") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    module: moduleKey,
                    start_date: startDate,
                    end_date: endDate
                })
            });

            const data = await res.json();
            if (data.status === 'success') {
                // Auto download file
                const downloadLink = document.createElement('a');
                downloadLink.href = data.download_url;
                downloadLink.download = data.file_name;
                document.body.appendChild(downloadLink);
                downloadLink.click();
                document.body.removeChild(downloadLink);

                let emailHtml = '';
                if (data.email_configured) {
                    if (data.email_sent) {
                        emailHtml = `<div class="p-2.5 bg-emerald-50 border border-emerald-200 rounded text-emerald-800 text-xs mt-2">Salinan file backup juga telah dikirim ke email: <strong>${data.email_recipient}</strong></div>`;
                    } else {
                        emailHtml = `<div class="p-2.5 bg-amber-50 border border-amber-200 rounded text-amber-800 text-xs mt-2">Pengiriman email ke ${data.email_recipient} gagal, namun file backup berhasil diunduh & tersimpan di VPS.</div>`;
                    }
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Backup Berhasil!',
                    html: `
                        <div class="text-left text-xs space-y-1.5 text-slate-700">
                            <p><strong>Modul:</strong> ${data.module_name}</p>
                            <p><strong>Jumlah Data:</strong> <span class="font-mono text-emerald-600 font-bold">${data.record_count}</span> baris</p>
                            <p><strong>File:</strong> <span class="font-mono text-[11px] text-slate-600 break-all">${data.file_name}</span></p>
                            <div class="text-slate-500 text-[11px] pt-1 border-t border-slate-100">
                                File backup otomatis diunduh ke komputer Anda dan salinan tersimpan aman di server VPS (Menu Arsip Data).
                            </div>
                            ${emailHtml}
                        </div>
                    `,
                    confirmButtonText: 'OK, Mengerti',
                    confirmButtonColor: '#4f46e5'
                });
                closeDataManagementModal(modalId);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Backup',
                    text: data.message || 'Terjadi kesalahan sistem.',
                    confirmButtonColor: '#e11d48'
                });
            }
        } catch (e) {
            Swal.fire({
                icon: 'error',
                title: 'Kesalahan Jaringan',
                text: e.message,
                confirmButtonColor: '#e11d48'
            });
        } finally {
            btn.disabled = false;
            btn.innerHTML = `
                <svg style="width: 13px; height: 13px; min-width: 13px;" class="fill-current shrink-0 mr-1" viewBox="0 0 16 16">
                    <path d="M8 12l-4-4h2.5V2h3v6H12L8 12zM2 14v-2h12v2H2z"/>
                </svg>
                <span>Mulai Backup & Unduh</span>
            `;
        }
    }

    window.dmVerifiedFiles = window.dmVerifiedFiles || {};

    async function verifyDeleteFile(modalId, moduleKey) {
        const fileInput = document.getElementById(`${modalId}-file-delete-verify`);
        const startDate = document.getElementById(`${modalId}-delete-start`).value;
        const endDate = document.getElementById(`${modalId}-delete-end`).value;

        if (!startDate || !endDate) {
            Swal.fire({
                icon: 'warning',
                title: 'Tentukan Periode',
                text: 'Silakan pilih rentang tanggal awal dan akhir terlebih dahulu.',
                confirmButtonColor: '#0284c7'
            });
            return;
        }

        if (!fileInput.files.length) {
            Swal.fire({
                icon: 'warning',
                title: 'Pilih File Backup',
                text: 'Silakan pilih file backup .sql terlebih dahulu untuk diverifikasi.',
                confirmButtonColor: '#0284c7'
            });
            return;
        }

        const btn = document.getElementById(`${modalId}-btn-verify-file`);
        const originalBtnHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = 'Memverifikasi File...';

        const fd = new FormData();
        fd.append('file', fileInput.files[0]);
        fd.append('module', moduleKey);
        fd.append('start_date', startDate);
        fd.append('end_date', endDate);

        try {
            const res = await fetch('{{ route("data-management.verify-file-delete") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: fd
            });

            const data = await res.json();

            if (data.status === 'success' && data.matched) {
                // Save verification in window state
                window.dmVerifiedFiles[modalId] = {
                    file: fileInput.files[0],
                    data: data,
                    startDate: startDate,
                    endDate: endDate
                };

                // Fill preview elements
                document.getElementById(`${modalId}-del-prev-filename`).innerText = data.file_name;
                document.getElementById(`${modalId}-del-prev-module`).innerText = data.table_name;
                document.getElementById(`${modalId}-del-prev-period`).innerText = data.file_period;
                document.getElementById(`${modalId}-del-prev-total`).innerText = `${data.total_records.toLocaleString()} baris`;

                // Render table preview
                const thead = document.getElementById(`${modalId}-del-prev-thead`);
                const tbody = document.getElementById(`${modalId}-del-prev-tbody`);
                thead.innerHTML = '';
                tbody.innerHTML = '';

                let cols = (data.columns || []).slice(0, 7);
                let headerRow = '<tr>';
                cols.forEach(c => {
                    headerRow += `<th class="px-3 py-1.5">${c}</th>`;
                });
                headerRow += '</tr>';
                thead.innerHTML = headerRow;

                (data.preview_rows || []).slice(0, 5).forEach(row => {
                    let tr = '<tr class="hover:bg-slate-50">';
                    cols.forEach(c => {
                        let val = row[c] !== null && row[c] !== undefined ? row[c] : '<span class="text-slate-300">NULL</span>';
                        tr += `<td class="px-3 py-1 truncate max-w-xs">${val}</td>`;
                    });
                    tr += '</tr>';
                    tbody.innerHTML += tr;
                });

                document.getElementById(`${modalId}-delete-preview-area`).classList.remove('hidden');

                Swal.fire({
                    icon: 'success',
                    title: 'File Backup Terverifikasi & Sesuai!',
                    html: `
                        <div class="text-xs text-left space-y-1.5 text-slate-700">
                            <p>${data.message}</p>
                            <div class="p-2 bg-emerald-50 rounded border border-emerald-200 text-emerald-800">
                                File backup dinyatakan <strong>VALID & SESUAI</strong>. Anda sekarang diperbolehkan melakukan penghapusan data dengan menekan tombol <strong>Cek Arsip & Hapus Data</strong>.
                            </div>
                        </div>
                    `,
                    confirmButtonColor: '#059669'
                });
            } else {
                window.dmVerifiedFiles[modalId] = null;
                document.getElementById(`${modalId}-delete-preview-area`).classList.add('hidden');
                Swal.fire({
                    icon: 'error',
                    title: 'File Tidak Sesuai!',
                    html: `<div class="text-xs text-left text-slate-700 leading-relaxed">${data.message || 'File tidak cocok.'}</div>`,
                    confirmButtonColor: '#e11d48'
                });
            }
        } catch (e) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal Memverifikasi File',
                text: e.message,
                confirmButtonColor: '#e11d48'
            });
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalBtnHtml;
        }
    }

    async function execDmDelete(modalId, moduleKey) {
        const startDate = document.getElementById(`${modalId}-delete-start`).value;
        const endDate = document.getElementById(`${modalId}-delete-end`).value;

        if (!startDate || !endDate) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Silakan tentukan rentang tanggal data yang ingin dihapus.',
                confirmButtonColor: '#4f46e5'
            });
            return;
        }

        const verified = window.dmVerifiedFiles[modalId];
        if (!verified || verified.startDate !== startDate || verified.endDate !== endDate) {
            Swal.fire({
                icon: 'warning',
                title: 'Verifikasi File Backup Diperlukan!',
                html: `
                    <div class="text-left text-xs space-y-2 text-slate-700 leading-relaxed">
                        <p>Sebelum menghapus data dari database, Anda <strong>WAJIB mengunggah file backup (.sql)</strong> yang valid untuk periode ini.</p>
                        <p class="text-sky-700 font-semibold">Silakan pilih file backup Anda lalu klik tombol "Cek & Preview File Backup" terlebih dahulu.</p>
                    </div>
                `,
                confirmButtonText: 'OK, Saya Mengerti',
                confirmButtonColor: '#0284c7'
            });
            return;
        }

        const btn = document.getElementById(`${modalId}-btn-exec-delete`);

        // Konfirmasi Hapus Data via SweetAlert
        const { value: userInput } = await Swal.fire({
            icon: 'warning',
            title: 'Konfirmasi Hapus Data',
            html: `
                <div class="text-left text-xs space-y-2 text-slate-700">
                    <p>File Verifikasi: <strong class="font-mono text-slate-800">${verified.data.file_name}</strong></p>
                    <p>Periode Data: <strong>${startDate} s/d ${endDate}</strong></p>
                    <p>Jumlah Data di File: <strong class="text-emerald-700 font-mono">${verified.data.total_records}</strong> baris.</p>
                    <div class="p-2.5 bg-rose-50 border border-rose-200 rounded text-rose-800 text-[11px] leading-relaxed">
                        <strong>PERINGATAN KERAS:</strong> Data pada periode ini akan dibersihkan permanen dari database.<br>
                        Data hanya bisa dikembalikan melalui menu <strong>Restore Data</strong> menggunakan file backup Anda.
                    </div>
                    <p class="text-slate-600 pt-1">Ketik kata <strong>HAPUS</strong> dengan huruf kapital untuk melanjutkan:</p>
                </div>
            `,
            input: 'text',
            inputPlaceholder: 'Ketik HAPUS di sini...',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus Data Sekarang',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#64748b',
            inputValidator: (val) => {
                if (val !== 'HAPUS') {
                    return 'Ketik kata HAPUS dengan huruf kapital persis!';
                }
            }
        });

        if (userInput !== 'HAPUS') {
            return;
        }

        btn.disabled = true;
        btn.innerHTML = 'Menghapus Data...';

        try {
            const fd = new FormData();
            fd.append('file', verified.file);
            fd.append('module', moduleKey);
            fd.append('start_date', startDate);
            fd.append('end_date', endDate);

            const delRes = await fetch('{{ route("data-management.delete") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: fd
            });

            const delData = await delRes.json();
            if (delData.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Data Berhasil Dihapus!',
                    text: delData.message,
                    confirmButtonColor: '#4f46e5'
                }).then(() => {
                    closeDataManagementModal(modalId);
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Menghapus',
                    text: delData.message || 'Terjadi kesalahan sistem.',
                    confirmButtonColor: '#e11d48'
                });
            }
        } catch (e) {
            Swal.fire({
                icon: 'error',
                title: 'Kesalahan Sistem',
                text: e.message,
                confirmButtonColor: '#e11d48'
            });
        } finally {
            btn.disabled = false;
            btn.innerHTML = `
                <svg style="width: 13px; height: 13px; min-width: 13px;" class="fill-current shrink-0 mr-1" viewBox="0 0 16 16">
                    <path d="M5 2V1h6v1h4v2H1V2h4zm1 3h2v8H6V5zm4 0h2v8h-2V5z"/>
                </svg>
                <span>Cek Arsip & Hapus Data</span>
            `;
        }
    }

    async function previewDmSql(modalId) {
        const fileInput = document.getElementById(`${modalId}-file-restore`);
        if (!fileInput.files.length) {
            Swal.fire({
                icon: 'warning',
                title: 'File Belum Dipilih',
                text: 'Silakan pilih file .sql terlebih dahulu.',
                confirmButtonColor: '#4f46e5'
            });
            return;
        }

        const btn = document.getElementById(`${modalId}-btn-preview`);
        btn.disabled = true;
        btn.innerText = 'Membaca File...';

        const formData = new FormData();
        formData.append('file', fileInput.files[0]);
        formData.append('_token', '{{ csrf_token() }}');

        try {
            const res = await fetch('{{ route("data-management.preview") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await res.json();
            if (data.status === 'success') {
                window.dmParsedData[modalId] = data;

                document.getElementById(`${modalId}-prev-table`).innerText = data.primary_table;
                document.getElementById(`${modalId}-prev-total`).innerText = (data.total_records || 0) + ' baris';

                const thead = document.getElementById(`${modalId}-prev-thead`);
                const tbody = document.getElementById(`${modalId}-prev-tbody`);
                thead.innerHTML = '';
                tbody.innerHTML = '';

                const cols = (data.columns || []).slice(0, 8);
                let trHead = '<tr>';
                cols.forEach(c => trHead += `<th class="px-3 py-1.5 text-left text-[11px]">${c}</th>`);
                trHead += '</tr>';
                thead.innerHTML = trHead;

                (data.preview_rows || []).forEach(row => {
                    let tr = '<tr class="hover:bg-slate-50">';
                    cols.forEach(c => {
                        let val = row[c] !== null && row[c] !== undefined ? row[c] : '<span class="text-slate-300">NULL</span>';
                        tr += `<td class="px-3 py-1 truncate max-w-xs">${val}</td>`;
                    });
                    tr += '</tr>';
                    tbody.innerHTML += tr;
                });

                document.getElementById(`${modalId}-preview-area`).classList.remove('hidden');
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Membaca File',
                    text: data.message || 'Format file SQL tidak valid.',
                    confirmButtonColor: '#e11d48'
                });
            }
        } catch (e) {
            Swal.fire({
                icon: 'error',
                title: 'Kesalahan Sistem',
                text: e.message,
                confirmButtonColor: '#e11d48'
            });
        } finally {
            btn.disabled = false;
            btn.innerHTML = `
                <svg style="width: 13px; height: 13px; min-width: 13px;" class="fill-current shrink-0 mr-1.5" viewBox="0 0 16 16">
                    <path d="M1 2a1 1 0 011-1h12a1 1 0 011 1v2H1V2zm0 4h14v3H1V6zm0 5h14v3a1 1 0 01-1 1H2a1 1 0 01-1-1v-3z"/>
                </svg>
                <span>Preview Data</span>
            `;
        }
    }

    async function startChunkedRestore(modalId) {
        const parsed = window.dmParsedData[modalId];
        if (!parsed || !parsed.all_tables) {
            Swal.fire({
                icon: 'warning',
                title: 'Preview Belum Ada',
                text: 'Silakan pilih dan preview file .sql terlebih dahulu.',
                confirmButtonColor: '#4f46e5'
            });
            return;
        }

        const confirmRes = await Swal.fire({
            icon: 'question',
            title: 'Konfirmasi Restore Data',
            text: 'Apakah Anda yakin ingin memulihkan data ini ke database? Record yang sudah ada di database akan dilewati secara otomatis.',
            showCancelButton: true,
            confirmButtonText: 'Ya, Mulai Restore',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#4f46e5',
            cancelButtonColor: '#64748b'
        });

        if (!confirmRes.isConfirmed) {
            return;
        }

        const btn = document.getElementById(`${modalId}-btn-exec-restore`);
        const progressBox = document.getElementById(`${modalId}-progress-box`);
        btn.disabled = true;
        progressBox.classList.remove('hidden');

        let totalRestored = 0;
        let totalSkipped = 0;

        const tables = parsed.all_tables;
        let totalAllRows = 0;
        for (const t in tables) {
            totalAllRows += tables[t].rows.length;
        }

        let processedRows = 0;
        const CHUNK_SIZE = 50;

        for (const tableName in tables) {
            const rows = tables[tableName].rows;
            for (let i = 0; i < rows.length; i += CHUNK_SIZE) {
                const chunk = rows.slice(i, i + CHUNK_SIZE);

                try {
                    const res = await fetch('{{ route("data-management.restore-chunk") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            table_name: tableName,
                            rows: chunk
                        })
                    });

                    const json = await res.json();
                    if (json.status === 'success') {
                        totalRestored += json.restored || 0;
                        totalSkipped += json.skipped || 0;
                    }
                } catch (e) {
                    console.error('Restore chunk failed: ', e);
                }

                processedRows += chunk.length;
                const percent = totalAllRows > 0 ? Math.round((processedRows / totalAllRows) * 100) : 100;
                document.getElementById(`${modalId}-progress-bar`).style.width = percent + '%';
                document.getElementById(`${modalId}-progress-pct`).innerText = percent + '%';
                document.getElementById(`${modalId}-progress-text`).innerText = `Memproses tabel ${tableName} (${processedRows}/${totalAllRows})...`;
                document.getElementById(`${modalId}-stat-ok`).innerText = totalRestored;
                document.getElementById(`${modalId}-stat-skip`).innerText = totalSkipped;
            }
        }

        // Log restore completion to audit log
        try {
            const fileInput = document.getElementById(`${modalId}-file-restore`);
            const fileName = fileInput && fileInput.files.length ? fileInput.files[0].name : '';
            await fetch('{{ route("data-management.log-restore") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    module_key: '{{ $module }}',
                    file_name: fileName,
                    total_restored: totalRestored,
                    total_skipped: totalSkipped,
                    total_rows: totalAllRows
                })
            });
        } catch (e) {
            console.error('Audit log restore error:', e);
        }

        document.getElementById(`${modalId}-progress-text`).innerText = 'Selesai!';

        Swal.fire({
            icon: 'success',
            title: 'Restore Selesai!',
            html: `
                <div class="text-left text-xs space-y-2 text-slate-700">
                    <p>Data Berhasil Disimpan: <strong class="text-emerald-600 font-mono">${totalRestored}</strong></p>
                    <p>Data Dilewati (Sudah ada di DB): <strong class="text-amber-600 font-mono">${totalSkipped}</strong></p>
                </div>
            `,
            confirmButtonColor: '#4f46e5'
        }).then(() => {
            closeDataManagementModal(modalId);
            window.location.reload();
        });
    }
</script>
@endpush
@endonce

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Member Card Manager</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --bg-color: #f3f4f6;
            --card-bg: #ffffff;
            --text-main: #1f2937;
            --text-muted: #6b7280;
        }

        body {
            background-color: var(--bg-color);
            font-family: 'Inter', sans-serif;
            color: var(--text-main);
        }

        .section-header {
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 1.5rem;
        }

        .card-custom {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            background-color: var(--card-bg);
            transition: all 0.3s ease;
        }

        .card-custom:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -2px rgba(0, 0, 0, 0.04);
            transform: translateY(-2px);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .branch-list-container {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #f3f4f6;
            border-radius: 12px;
            padding: 15px;
            background: #f9fafb;
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .text-xs { font-size: 0.75rem; }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom py-3 mb-4 sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="#">
                <i class="bi bi-person-vcard-fill fs-4"></i> Member Manager
            </a>
            <form action="{{ route('logout') }}" method="POST" class="d-flex">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm d-flex align-items-center gap-2">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </button>
            </form>
        </div>
    </nav>

    <div class="container pb-5">
        <div class="text-center mb-5">
            <h1 class="section-header">Dashboard Pengelolaan Member</h1>
            <p class="text-muted">Kelola data member, cabang, dan masa aktif dengan mudah.</p>
        </div>

        <div class="card card-custom mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-2 mb-4">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle text-primary">
                        <i class="bi bi-person-plus-fill fs-5"></i>
                    </div>
                    <h5 class="card-title mb-0 fw-bold">Tambah Member Baru</h5>
                </div>
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-5">
                        <label class="form-label text-muted small fw-semibold">NAMA MEMBER</label>
                        <input type="text" id="title" class="form-control" placeholder="Masukkan nama member...">
                    </div>
                    <div class="col-12 col-md-5">
                        <label class="form-label text-muted small fw-semibold">URL API MEMBER</label>
                        <input type="text" id="link" class="form-control" placeholder="https://example.com">
                    </div>
                    <div class="col-12 col-md-2">
                        <button onclick="addMember()" class="btn btn-primary w-100 h-100 d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-plus-lg"></i> Tambah
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="searchInput" class="form-control border-start-0 ps-0" placeholder="Cari nama member..." onkeyup="filterMembers()">
                </div>
            </div>
        </div>

        <div class="row g-4" id="memberList">
            @foreach ($members as $member)
                <div class="col-12 col-lg-6 member-card" id="card-{{ $member->id }}" data-name="{{ strtolower($member->title) }}">
                    <div class="card card-custom h-100">
                        <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-start">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-light p-3 rounded-circle border">
                                    <i class="bi bi-building fs-4 text-secondary"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-1 member-title">{{ $member->title }}</h5>
                                    <span class="badge bg-light text-secondary border fw-normal">ID: #{{ $member->id }}</span>
                                </div>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-light btn-sm rounded-circle" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                    <li><button class="dropdown-item text-danger" onclick="deleteMember({{ $member->id }})"><i class="bi bi-trash me-2"></i>Hapus Member</button></li>
                                </ul>
                            </div>
                        </div>

                        <div class="card-body px-4">
                            <div class="bg-light p-3 rounded-3 mb-4 border">
                                <div class="mb-3">
                                    <label class="form-label text-muted small fw-bold">NAMA MEMBER</label>
                                    <input type="text" id="title-{{ $member->id }}" class="form-control form-control-sm bg-white" value="{{ $member->title }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-muted small fw-bold">URL LINK</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-link-45deg"></i></span>
                                        <input type="text" id="link-{{ $member->id }}" class="form-control bg-white border-start-0" value="{{ $member->link }}">
                                    </div>
                                </div>
                                <div class="text-end">
                                    <button onclick="updateMember({{ $member->id }}, this)" class="btn btn-sm btn-dark px-3">
                                        <i class="bi bi-save me-1"></i> Simpan Perubahan
                                    </button>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="fw-bold m-0 text-primary"><i class="bi bi-shop me-2"></i>Manajemen Cabang</h6>
                                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="loadBranches({{ $member->id }}, '{{ $member->link }}')">
                                        <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                                    </button>
                                </div>

                                <div class="bg-primary bg-opacity-10 p-3 rounded-3 mb-3 border border-primary border-opacity-10">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-primary fw-semibold small">MAX CABANG</span>
                                        <span id="cab-now-{{ $member->id }}" class="badge bg-primary rounded-pill">Loading...</span>
                                    </div>
                                    <div class="input-group input-group-sm">
                                        <input type="number" id="cab-input-{{ $member->id }}" class="form-control border-primary border-opacity-25" placeholder="Set limit...">
                                        <button onclick="updateCabang({{ $member->id }}, '{{ $member->link }}')" class="btn btn-primary">Update</button>
                                    </div>
                                </div>

                                <div id="branch-container-{{ $member->id }}" class="branch-list-container">
                                    <div class="text-center text-muted py-4">
                                        <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                                        <div class="small">Menghubungkan ke server member...</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <script>
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;

        // --- FUNGSI SEARCH ---
        function filterMembers() {
            // Ambil value input dan ubah ke lowercase
            let input = document.getElementById('searchInput').value.toLowerCase();
            // Ambil semua elemen card member
            let cards = document.getElementsByClassName('member-card');

            // Loop semua card
            for (let i = 0; i < cards.length; i++) {
                // Ambil nama member dari attribute data-name yang sudah kita set di HTML
                let memberName = cards[i].getAttribute('data-name');

                // Cek apakah input ada di dalam nama member
                if (memberName.includes(input)) {
                    cards[i].style.display = ""; // Tampilkan (reset display)
                    cards[i].classList.remove('d-none'); // Pastikan class d-none hilang (bootstrap)
                } else {
                    cards[i].style.display = "none"; // Sembunyikan
                }
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            @foreach ($members as $member)
                loadBranches({{ $member->id }}, '{{ $member->link }}');

                // Load Total Cabang
                axios.get("{{ $member->link }}/api/get-total-cabang")
                    .then(res => {
                        document.getElementById("cab-now-{{ $member->id }}").textContent = res.data.data;
                        document.getElementById("cab-input-{{ $member->id }}").value = res.data.data;
                    })
                    .catch(err => {
                        document.getElementById("cab-now-{{ $member->id }}").textContent = "Err";
                        document.getElementById("cab-now-{{ $member->id }}").classList.replace('bg-primary', 'bg-danger');
                    });
            @endforeach
        });

        // --- Functions CRUD Member ---
        function addMember() {
            const title = document.getElementById('title').value.trim();
            const link = document.getElementById('link').value.trim();
            if (!title || !link) return Swal.fire('Ops!', 'Nama dan Link wajib diisi ya.', 'warning');
            axios.post('/members', { title, link })
                .then(() => { Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Member baru ditambahkan.', showConfirmButton: false, timer: 1500 }).then(() => location.reload()); })
                .catch(() => Swal.fire('Error', 'Gagal menambah member.', 'error'));
        }

        function updateMember(id, btnElement) {
            const title = document.getElementById(`title-${id}`).value;
            const link = document.getElementById(`link-${id}`).value;
            const oriHtml = btnElement.innerHTML;
            btnElement.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menyimpan...';
            btnElement.disabled = true;
            axios.put(`/members/${id}`, { title, link })
                .then(() => {
                    btnElement.innerHTML = '<i class="bi bi-check2"></i> Tersimpan';
                    btnElement.classList.replace('btn-dark', 'btn-success');

                    // Update juga nama di header card dan attribute data-name agar pencarian real-time
                    const cardElement = document.getElementById(`card-${id}`);
                    const titleElement = cardElement.querySelector('.member-title');

                    if(titleElement) titleElement.textContent = title;
                    if(cardElement) cardElement.setAttribute('data-name', title.toLowerCase());

                    setTimeout(() => { btnElement.innerHTML = oriHtml; btnElement.classList.replace('btn-success', 'btn-dark'); btnElement.disabled = false; }, 2000);
                })
                .catch(() => { Swal.fire('Error', 'Gagal update data member.', 'error'); btnElement.innerHTML = oriHtml; btnElement.disabled = false; });
        }

        function deleteMember(id) {
            Swal.fire({ title: 'Hapus Member?', text: "Data ini tidak bisa dikembalikan.", icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444', cancelButtonColor: '#6b7280', confirmButtonText: 'Ya, Hapus!', cancelButtonText: 'Batal' })
            .then((result) => {
                if (result.isConfirmed) {
                    axios.delete(`/members/${id}`).then(() => { document.getElementById(`card-${id}`).remove(); Swal.fire('Terhapus!', 'Member berhasil dihapus.', 'success'); }).catch(() => Swal.fire('Error', 'Gagal menghapus.', 'error'));
                }
            });
        }

        // --- Functions Remote API ---

        function updateCabang(memberId, link) {
            const val = document.getElementById("cab-input-" + memberId).value;
            if (!val) return Swal.fire("Ops!", "Total cabang harus diisi angka.", "warning");
            const btn = event.target;
            const oriText = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            btn.disabled = true;
            axios.post(link + "/api/update-total-cabang", { total_cabang: val })
                .then(res => { document.getElementById("cab-now-" + memberId).textContent = val; Swal.fire({ icon: 'success', title: 'Updated', text: 'Limit cabang berhasil diperbarui.', toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 }); })
                .catch(() => Swal.fire("Gagal", "Tidak dapat menghubungi server member.", "error"))
                .finally(() => { btn.innerHTML = oriText; btn.disabled = false; });
        }

        function loadBranches(memberId, baseUrl) {
            const container = document.getElementById(`branch-container-${memberId}`);
            container.innerHTML = `<div class="text-center text-muted py-4"><div class="spinner-border spinner-border-sm text-primary mb-2"></div><div class="small">Sedang mengambil data...</div></div>`;
            const cleanUrl = baseUrl.replace(/\/$/, "");

            axios.get(`${cleanUrl}/api/get-setting-cabang`)
                .then(res => {
                    const branches = res.data;
                    let html = '';
                    if(branches.length === 0) {
                        html = `<div class="text-center text-muted py-3 small"><i class="bi bi-inbox fs-4 d-block mb-1"></i>Belum ada data cabang</div>`;
                    } else {
                        branches.forEach(branch => {
                            let defaultDate = branch.expired_date ? branch.expired_date.split('T')[0] : '';
                            let isAllowed = parseInt(branch.allow_transaksi) === 1;

                            let statusBadge = '';
                            if(defaultDate) {
                                const today = new Date().toISOString().split('T')[0];
                                statusBadge = defaultDate < today
                                    ? '<span class="badge bg-danger rounded-pill ms-2" style="font-size: 0.65rem">Expired</span>'
                                    : '<span class="badge bg-success rounded-pill ms-2" style="font-size: 0.65rem">Active</span>';
                            }

                            html += `
                                <div class="branch-item">
                                    <div class="card p-4 mb-2" >
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-shop text-primary me-2"></i>
                                                <strong style="font-size: 0.9rem;">${branch.nama_cabang} ${branch.id == 1 ? '(Pusat)' : '(Cabang)'}</strong>
                                                ${statusBadge}
                                            </div>
                                            <span class="badge-soft small">ID: ${branch.id}</span>
                                        </div>

                                        <div class="input-group input-group-sm mb-2">
                                            <span class="input-group-text bg-white text-muted">Expired:</span>
                                            <input type="date" class="form-control"
                                                id="date-cabang-${memberId}-${branch.id}"
                                                value="${defaultDate}">
                                            <button class="btn btn-outline-primary"
                                                    onclick="saveBranchExpired('${cleanUrl}', ${branch.id}, ${memberId})">
                                                <i class="bi bi-send"></i>
                                            </button>
                                        </div>

                                        <div class="d-flex align-items-center justify-content-between pt-1 ps-1">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" role="switch"
                                                    id="allow-trx-${memberId}-${branch.id}"
                                                    ${isAllowed ? 'checked' : ''}
                                                    onchange="toggleTransaksi('${cleanUrl}', ${branch.id}, ${memberId}, this)">
                                                <label class="form-check-label small text-muted" for="allow-trx-${memberId}-${branch.id}">
                                                    Allow Transaksi
                                                </label>
                                            </div>
                                            <span class="text-xs text-muted" id="status-trx-${memberId}-${branch.id}">
                                                ${isAllowed ? '<span class="text-success">ON</span>' : '<span class="text-danger">OFF</span>'}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                    }
                    container.innerHTML = html;
                })
                .catch(err => {
                    container.innerHTML = `<div class="alert alert-danger border-0 d-flex align-items-center gap-2 p-2 m-0 small"><i class="bi bi-exclamation-triangle-fill"></i><div>Gagal koneksi ke server member.</div></div>`;
                });
        }

        function saveBranchExpired(baseUrl, branchId, memberId) {
            const dateInput = document.getElementById(`date-cabang-${memberId}-${branchId}`).value;
            if (!dateInput) return Swal.fire('Hey!', 'Pilih tanggal dulu ya.', 'info');

            const btn = event.currentTarget;
            const oriHtml = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            btn.disabled = true;

            axios.post(`${baseUrl}/api/update-expired-date-cabang`, {
                id: branchId,
                expired_date: dateInput
            })
            .then(res => {
                if(res.data.status === 'success') {
                    loadBranches(memberId, baseUrl);
                    Swal.fire({ icon: 'success', title: 'Tersimpan!', text: 'Masa aktif cabang diperbarui.', toast: true, position: 'bottom-end', showConfirmButton: false, timer: 3000 });
                } else { throw new Error(res.data.msg); }
            })
            .catch(err => { Swal.fire('Gagal', 'Terjadi kesalahan.', 'error'); })
            .finally(() => { btn.innerHTML = oriHtml; btn.disabled = false; });
        }

        function toggleTransaksi(baseUrl, branchId, memberId, checkboxEl) {
            const isChecked = checkboxEl.checked;
            const statusTextEl = document.getElementById(`status-trx-${memberId}-${branchId}`);
            statusTextEl.innerHTML = '<span class="spinner-border spinner-border-sm text-muted"></span>';
            const val = isChecked ? 1 : 0;

            axios.post(`${baseUrl}/api/update-expired-date-cabang`, {
                id: branchId,
                allow_transaksi: val
            })
            .then(res => {
                if(res.data.status === 'success') {
                    statusTextEl.innerHTML = isChecked ? '<span class="text-success">ON</span>' : '<span class="text-danger">OFF</span>';
                    Swal.fire({ icon: 'success', title: isChecked ? 'Transaksi Aktif' : 'Transaksi Nonaktif', toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
                } else {
                    checkboxEl.checked = !isChecked;
                    statusTextEl.innerHTML = !isChecked ? '<span class="text-success">ON</span>' : '<span class="text-danger">OFF</span>';
                    Swal.fire('Gagal', res.data.msg || 'Terjadi kesalahan', 'error');
                }
            })
            .catch(err => {
                checkboxEl.checked = !isChecked;
                statusTextEl.innerHTML = !isChecked ? '<span class="text-success">ON</span>' : '<span class="text-danger">OFF</span>';
                Swal.fire('Gagal', 'Koneksi bermasalah.', 'error');
            });
        }
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Member Card Manager</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            background-color: #f8f9fa;
        }

        .card:hover {
            transform: translateY(-3px);
            transition: 0.2s;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }

        .section-header {
            font-weight: 700;
            font-size: 1.8rem;
            color: #343a40;
        }

        .expired-box {
            background-color: #e9f5ff;
            border: 1px solid #b6e0ff;
            border-radius: 8px;
            padding: 10px 15px;
        }

        /* Responsive tweaks */
        @media (max-width: 768px) {
            .btn-action {
                width: 100%;
                margin-top: 6px;
            }

            .expired-box .input-group {
                flex-direction: column;
            }

            .expired-box .input-group input,
            .expired-box .input-group button {
                width: 100%;
                margin-top: 5px;
            }
        }
    </style>
</head>

<body>
    <div class="container py-4">
        <div class="d-flex justify-content-end mb-3">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    Logout
                </button>
            </form>
        </div>

        <h1 class="section-header mb-4 text-center">Member Card Manager</h1>

        <!-- Form Tambah Member -->
        <div class="card shadow-sm mb-4 border-0">
            <div class="card-body">
                <h5 class="card-title mb-3">Tambah Member Baru</h5>
                <div class="row g-3">
                    <div class="col-12 col-md-5">
                        <input type="text" id="title" class="form-control" placeholder="Nama Member">
                    </div>
                    <div class="col-12 col-md-5">
                        <input type="text" id="link" class="form-control"
                            placeholder="Link Member (contoh: https://example.com)">
                    </div>
                    <div class="col-12 col-md-2 d-grid">
                        <button onclick="addMember()" class="btn btn-primary w-100">Tambah</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daftar Member -->
        <div class="row" id="memberList">
            @foreach ($members as $member)
                <div class="col-12 col-md-6 mb-4" id="card-{{ $member->id }}">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex flex-column justify-content-between">

                            <!-- Info Member -->
                            <div>
                                <h5 class="card-title text-primary fw-bold mb-3">{{ $member->title }}</h5>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Nama Member</label>
                                    <input type="text" id="title-{{ $member->id }}" class="form-control"
                                        value="{{ $member->title }}">
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Link Member</label>
                                    <input type="text" id="link-{{ $member->id }}" class="form-control"
                                        value="{{ $member->link }}">
                                </div>

                                <!-- Update Expired -->
                                <div class="expired-box mb-3">
                                    <h6 class="text-primary fw-bold mb-2">Update Expired Date</h6>
                                    <div class="input-group">
                                        <input type="date" id="exp_date-{{ $member->id }}" class="form-control">
                                        <button onclick="updateExpired({{ $member->id }})"
                                            class="btn btn-success btn-action">Update</button>
                                    </div>
                                </div>

                                <div class="card shadow-sm mb-4 border-0">
                                    <div class="card-body">

                                        <h6 class="text-primary fw-bold mb-2">Paket Cabang Member</h6>

                                        <div class="mb-2">
                                            <small class="text-muted">Total Max Cabang Saat Ini:</small><br>
                                            <span id="cab-now-{{ $member->id }}"
                                                class="fw-bold text-dark">Loading...</span>
                                        </div>

                                        <div class="input-group mt-2">
                                            <input type="number" id="cab-input-{{ $member->id }}"
                                                class="form-control" placeholder="Masukan jumlah max cabang">
                                            <button onclick="updateCabang({{ $member->id }}, '{{ $member->link }}')"
                                                class="btn btn-primary">Update</button>
                                        </div>

                                    </div>
                                </div>


                            </div>

                            <!-- Action Buttons -->
                            <div class="mt-3 d-flex flex-wrap justify-content-end gap-2">
                                <button onclick="updateMember({{ $member->id }})"
                                    class="btn btn-warning text-white btn-action">Edit</button>
                                <button onclick="deleteMember({{ $member->id }})"
                                    class="btn btn-danger btn-action">Hapus</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <script>
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;

        function addMember() {
            const title = document.getElementById('title').value.trim();
            const link = document.getElementById('link').value.trim();

            if (!title || !link) {
                return Swal.fire('Peringatan', 'Nama dan Link wajib diisi!', 'warning');
            }

            axios.post('/members', {
                    title,
                    link
                })
                .then(() => Swal.fire('Sukses', 'Member berhasil ditambahkan', 'success'))
                .then(() => location.reload())
                .catch(() => Swal.fire('Error', 'Gagal menambah member', 'error'));
        }

        function updateMember(id) {
            const title = document.getElementById(`title-${id}`).value;
            const link = document.getElementById(`link-${id}`).value;

            axios.put(`/members/${id}`, {
                    title,
                    link
                })
                .then(() => Swal.fire('Sukses', 'Data member diperbarui', 'success'))
                .then(() => location.reload())
                .catch(() => Swal.fire('Error', 'Gagal update member', 'error'));
        }

        function deleteMember(id) {
            Swal.fire({
                title: 'Yakin hapus?',
                text: 'Data member ini akan dihapus permanen.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#dc3545',
            }).then(result => {
                if (result.isConfirmed) {
                    axios.delete(`/members/${id}`)
                        .then(() => {
                            Swal.fire('Dihapus!', 'Member berhasil dihapus', 'success');
                            document.getElementById(`card-${id}`).remove();
                        })
                        .catch(() => Swal.fire('Error', 'Gagal menghapus member', 'error'));
                }
            });
        }

        function updateExpired(id) {
            const exp_date = document.getElementById(`exp_date-${id}`).value;
            if (!exp_date) return Swal.fire('Peringatan', 'Tanggal expired belum diisi', 'warning');

            axios.post(`/members/${id}/update-expired`, {
                    exp_date
                })
                .then(res => Swal.fire('Sukses', res.data.msg, 'success'))
                .catch(() => Swal.fire('Error', 'Gagal update expired', 'error'));
        }


        // LOAD TOTAL CABANG untuk tiap member dari API remote
        @foreach ($members as $member)
            axios.get("{{ $member->link }}/api/get-total-cabang")
                .then(res => {
                    document.getElementById("cab-now-{{ $member->id }}").textContent = res.data.data;
                    document.getElementById("cab-input-{{ $member->id }}").value = res.data.data;
                })
                .catch(err => {
                    document.getElementById("cab-now-{{ $member->id }}").textContent = "Gagal Ambil Data";
                });
        @endforeach


        // UPDATE CABANG
        function updateCabang(memberId, link) {
            const val = document.getElementById("cab-input-" + memberId).value;

            if (!val) {
                return Swal.fire("Peringatan", "Total cabang wajib diisi!", "warning");
            }

            axios.post(link + "/api/update-total-cabang", {
                total_cabang: val
            }).then(res => {
                Swal.fire("Sukses", res.data.msg, "success");
                document.getElementById("cab-now-" + memberId).textContent = val;
            }).catch(() => {
                Swal.fire("Error", "Gagal update cabang!", "error");
            });
        }
    </script>
</body>

</html>

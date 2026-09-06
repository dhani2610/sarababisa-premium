<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if ($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let errorHtml = `
                <div style="text-align: left; font-size: 14px; max-height: 250px; overflow-y: auto; padding: 5px 10px;">
                    <ul style="list-style-type: disc; margin-left: 20px; line-height: 1.6;">
                        @foreach ($errors->all() as $error)
                            <li class="text-rose-600 font-medium">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            `;

            Swal.fire({
                icon: 'error',
                title: 'Peringatan: Data Tidak Sesuai!',
                html: errorHtml,
                confirmButtonText: 'Periksa Kembali',
                confirmButtonColor: '#4f46e5',
                showConfirmButton: true,
                allowOutsideClick: true
            });
        });
    </script>
@endif

@if (Session::has('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ Session::get('success') }}',
                confirmButtonColor: '#4f46e5',
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true
            });
        });
    </script>
@endif

@if (Session::has('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan',
                text: '{{ Session::get('error') }}',
                confirmButtonText: 'Tutup',
                confirmButtonColor: '#4f46e5'
            });
        });
    </script>
@endif

@if (Session::has('failed'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: '{{ Session::get('failed') }}',
                confirmButtonText: 'Tutup',
                confirmButtonColor: '#4f46e5'
            });
        });
    </script>
@endif

@if (Session::has('warning'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan!',
                text: '{{ Session::get('warning') }}',
                confirmButtonText: 'Mengerti',
                confirmButtonColor: '#f59e0b'
            });
        });
    </script>
@endif

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Interceptor jQuery AJAX untuk validasi 422
        if (window.jQuery) {
            $(document).ajaxError(function (event, jqXHR, ajaxSettings, thrownError) {
                if (jqXHR.status === 422 && jqXHR.responseJSON && jqXHR.responseJSON.errors) {
                    let items = [];
                    for (let field in jqXHR.responseJSON.errors) {
                        items.push(...jqXHR.responseJSON.errors[field]);
                    }
                    let listHtml = items.map(m => `<li class="text-rose-600 font-medium">${m}</li>`).join('');
                    Swal.fire({
                        icon: 'error',
                        title: 'Peringatan: Data Tidak Valid!',
                        html: `<div style="text-align: left; font-size: 14px; max-height: 250px; overflow-y: auto; padding: 5px 10px;"><ul style="list-style-type: disc; margin-left: 20px; line-height: 1.6;">${listHtml}</ul></div>`,
                        confirmButtonText: 'Periksa Kembali',
                        confirmButtonColor: '#4f46e5'
                    });
                }
            });
        }

        // Interceptor event Livewire
        if (window.Livewire) {
            window.Livewire.on('alert', param => {
                Swal.fire({
                    icon: param.type || 'info',
                    title: param.title || '',
                    text: param.message || '',
                    confirmButtonColor: '#4f46e5'
                });
            });
        }
    });
</script>

@section('title')
    Daftar Pelanggan
@endsection

<x-toko-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        @if (session()->has('message'))
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ session('message') }}
            </div>
        @endif

        <livewire:customer-data></livewire:customer-data>

    </div>
</x-toko-layout>

@section('title')
    History Garansi
@endsection

<x-toko-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        <!-- Table -->
        {{-- <livewire:edit-history-garansi></livewire:edit-history-garansi> --}}
        {{-- @dd($historyGaransi) --}}
        <livewire:edit-history-garansi :historyGaransi="$historyGaransi"></livewire:edit-history-garansi>

    </div>
</x-toko-layout>

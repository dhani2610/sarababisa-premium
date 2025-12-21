@section('title')
    Pelacakan Status Servis
@endsection

<x-authentication-layout>
    <h1 class="text-3xl text-slate-800 font-bold mb-6">{{ __('Garansi Servis') }} ✨</h1>

    <!-- Form -->
    <form method="GET" action="{{ route('garansi-servis-data') }}">
        @csrf
        <div class="space-y-6">
            <div>
                <x-jet-label for="no_invoice" value="{{ __('No Invoce') }}" />
                <x-jet-input id="no_invoice" type="number" name="no_invoice" value="no_invoice" required autofocus />
            </div>
            <x-jet-button class="w-full">
                {{ __('Garansi Servis') }}
            </x-jet-button>
        </div>
    </form>
</x-authentication-layout>

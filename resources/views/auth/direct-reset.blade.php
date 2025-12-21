<x-authentication-layout>
    <h1 class="text-3xl text-slate-800 font-bold mb-6">
        {{ isset($verified_email) ? __('Buat Password Baru') : __('Reset Password') }} ✨
    </h1>

    @if (session('status'))
        <div class="mb-4 font-medium text-sm text-green-600">
            {{ session('status') }}
        </div>
    @endif

    <x-jet-validation-errors class="mb-4" />

    @if (!isset($verified_email))
        {{-- TAMPILAN 1: INPUT EMAIL --}}
        <form method="POST" action="{{ route('direct.reset.check') }}">
            @csrf
            <div>
                <x-jet-label for="email" value="{{ __('Masukkan Email Anda') }}" />
                <x-jet-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            </div>

            <div class="flex items-center justify-end mt-4">
                <x-jet-button>
                    {{ __('Cek Email') }}
                </x-jet-button>
            </div>
        </form>
    @else
        {{-- TAMPILAN 2: INPUT PASSWORD BARU (Muncul jika email valid) --}}
        <div class="mb-4 text-sm text-gray-600">
            Reset password untuk email: <strong>{{ $verified_email }}</strong>
        </div>

        <form method="POST" action="{{ route('direct.reset.update') }}">
            @csrf

            {{-- Email dikirim secara hidden agar controller tau user mana yang diedit --}}
            <input type="hidden" name="email" value="{{ $verified_email }}">

            <div class="mt-4">
                <x-jet-label for="password" value="{{ __('Password Baru') }}" />
                <x-jet-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" autofocus />
            </div>

            <div class="mt-4">
                <x-jet-label for="password_confirmation" value="{{ __('Konfirmasi Password Baru') }}" />
                <x-jet-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <a class="underline text-sm text-gray-600 hover:text-gray-900 mr-4" href="{{ route('direct.reset.request') }}">
                    {{ __('Batal / Ganti Email') }}
                </a>
                <x-jet-button>
                    {{ __('Simpan Password') }}
                </x-jet-button>
            </div>
        </form>
    @endif
</x-authentication-layout>

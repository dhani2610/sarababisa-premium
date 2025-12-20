@section('title')
    Masuk Akun
@endsection

<x-authentication-layout>
    <h1 class="text-3xl text-slate-800 font-bold mb-6">{{ __('Selamat datang kembali!') }} ✨</h1>
    @if (session('status'))
        <div class="mb-4 font-medium text-sm text-green-600">
            {{ session('status') }}
        </div>
    @endif
    <!-- Form -->
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="space-y-4">
            <div>
                <x-jet-label for="username" value="{{ __('Nama Pengguna') }}" />
                <x-jet-input id="username" type="text" name="username" :value="old('username')" required autofocus />
            </div>
            <div>
                <x-jet-label for="password" value="{{ __('Kata Sandi') }}" />
                <x-jet-input id="password" type="password" name="password" required autocomplete="current-password" />
            </div>
        </div>
        <div class="flex items-center justify-between mt-6">
            @if (Route::has('password.request'))
                <div class="mr-1">
                    <a class="text-sm underline hover:no-underline" href="{{ route('direct.reset.request') }}">
                        {{ __('Lupa kata sandi?') }}
                    </a>
                </div>
            @endif
            <x-jet-button class="ml-3">
                {{ __('Masuk') }}
            </x-jet-button>
        </div>
    </form>
    <x-jet-validation-errors class="mt-4" />
    <!-- Footer -->
    {{-- <div class="pt-5 mt-6 border-t border-slate-200">
        <div class="text-sm">
            {{ __('Don\'t you have an account?') }} <a class="font-medium text-indigo-500 hover:text-indigo-600" href="{{ route('register') }}">{{ __('Sign Up') }}</a>
        </div>
    </div> --}}
</x-authentication-layout>

@props([
    'align' => 'right'
])

<div class="relative inline-flex" x-data="{ open: false }">
    <button
        class="inline-flex justify-center items-center group"
        aria-haspopup="true"
        @click.prevent="open = !open"
        :aria-expanded="open"
    >

        @php
            $cabangId = getCabangId();
            if ($cabangId == 1) {
                $kepalaToko = \App\Models\User::find(1);
            }else{
                $kepalaToko = \App\Models\User::where('cabang_id',$cabangId)->where('id','!=',1)->where('role','Kepala Toko')->orderBy('id','asc')->first();
            }
        @endphp
        @if (empty($kepalaToko))
            <img class="w-8 h-8 rounded-full" src="{{ Auth::user()->profile_photo_url }}" width="32" height="32" alt="{{ Auth::user()->name }}" />
        @else
        @if ($kepalaToko->profile_photo_path != null)
            <img class="w-8 h-8 rounded-full" src="{{ Storage::url($kepalaToko->profile_photo_path ) }}" width="32" height="32" alt="{{ getCabangNameUser() ?? Auth::user()->name }}" />
        @else
            <img class="w-8 h-8 rounded-full" src="{{ Auth::user()->profile_photo_url }}" width="32" height="32" alt="{{ Auth::user()->name }}" />
        @endif
        @endif
        <div class="flex items-center truncate">
            <span class="truncate ml-2 text-sm font-medium group-hover:text-slate-800">{{ getCabangNameUser() ?? Auth::user()->name  }}</span>
            <svg class="w-3 h-3 shrink-0 ml-1 fill-current text-slate-400" viewBox="0 0 12 12">
                <path d="M5.9 11.4L.5 6l1.4-1.4 4 4 4-4L11.3 6z" />
            </svg>
        </div>
    </button>
    <div
        class="origin-top-right z-10 absolute top-full min-w-44 bg-white border border-slate-200 py-1.5 rounded shadow-lg overflow-hidden mt-1 {{$align === 'right' ? 'right-0' : 'left-0'}}"
        @click.outside="open = false"
        @keydown.escape.window="open = false"
        x-show="open"
        x-transition:enter="transition ease-out duration-200 transform"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-out duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-cloak
    >
        <div class="pt-0.5 pb-2 px-3 mb-1 border-b border-slate-200">
            {{-- <div class="font-medium text-slate-800">{{ Auth::user()->name }}</div> --}}
            <div class="font-medium text-slate-800">{{ getCabangNameUser() ?? Auth::user()->name }}</div>
            <div class="text-xs text-slate-500 italic">{{ Auth::user()->role }}</div>
        </div>
        <ul>
            {{-- <li>
                <a class="font-medium text-sm text-indigo-500 hover:text-indigo-600 flex items-center py-1 px-3" href="{{ route('profile.show') }}" @click="open = false" @focus="open = true" @focusout="open = false">Pengaturan</a>
            </li> --}}
            @if (Auth::user()->role == 'Kepala Toko')
            <li class="px-3 py-2 border-b border-slate-200">
                <form action="{{ route('set.cabang') }}" method="POST">
                    @csrf
                    <label class="text-xs text-slate-500">Pilih Cabang</label>
                    <select name="cabang_id"
                            onchange="this.form.submit()"
                            class="mt-1 w-full text-sm border-slate-300 rounded py-1 px-2">
                        @foreach (getCabang() as $item)
                            @if ($item->expired_date >= date('Y-m-d'))
                            <option value="{{ $item->id }}"
                                {{ getCabangId() == $item->id ? 'selected' : '' }}>
                                {{ $item->nama_cabang }}
                            </option>
                            @endif
                        @endforeach
                    </select>
                </form>
            </li>
            @endif

            <li>
                <form method="POST" action="{{ route('logout') }}" x-data>
                    @csrf

                    <a class="font-medium text-sm text-indigo-500 hover:text-indigo-600 flex items-center py-1 px-3"
                        href="{{ route('logout') }}"
                        @click.prevent="$root.submit();"
                        @focus="open = true"
                        @focusout="open = false"
                    >
                        {{ __('Keluar') }}
                    </a>
                </form>
            </li>
        </ul>
    </div>
</div>

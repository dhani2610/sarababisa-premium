<div class="flex flex-nowrap overflow-x-scroll no-scrollbar md:block md:overflow-auto px-3 py-6 border-b md:border-b-0 md:border-r border-slate-200 min-w-60 md:space-y-3">
    <!-- Group 1 -->
    <div>
        <ul class="flex flex-nowrap md:block mr-3 md:mr-0">
            <li class="mr-0.5 md:mr-0 md:mb-0.5">
                <a class="flex items-center px-2.5 py-2 rounded whitespace-nowrap @if(Route::is('informasi-toko')){{ 'bg-indigo-50' }}@endif" href="{{ route('informasi-toko') }}">
                    <svg class="w-4 h-4 shrink-0 fill-current text-slate-400 mr-2 @if(Route::is('informasi-toko')){{ 'text-indigo-400' }}@endif" viewBox="0 0 16 16">
                        <path d="M12.311 9.527c-1.161-.393-1.85-.825-2.143-1.175A3.991 3.991 0 0012 5V4c0-2.206-1.794-4-4-4S4 1.794 4 4v1c0 1.406.732 2.639 1.832 3.352-.292.35-.981.782-2.142 1.175A3.942 3.942 0 001 13.26V16h14v-2.74c0-1.69-1.081-3.19-2.689-3.733zM6 4c0-1.103.897-2 2-2s2 .897 2 2v1c0 1.103-.897 2-2 2s-2-.897-2-2V4zm7 10H3v-.74c0-.831.534-1.569 1.33-1.838 1.845-.624 3-1.436 3.452-2.422h.436c.452.986 1.607 1.798 3.453 2.422A1.943 1.943 0 0113 13.26V14z" />
                    </svg>
                    <span class="text-sm font-medium @if(Route::is('informasi-toko')){{ 'text-indigo-500' }}@else{{ 'text-slate-600 hover:text-slate-700' }}@endif">Profil</span>
                </a>
            </li>
            <li class="mr-0.5 md:mr-0 md:mb-0.5">
                <a class="flex items-center px-2.5 py-2 rounded whitespace-nowrap @if(Route::is('sistem')){{ 'bg-indigo-50' }}@endif" href="{{ route('sistem') }}">
                    <svg class="w-4 h-4 shrink-0 fill-current text-slate-400 mr-2 @if(Route::is('sistem')){{ 'text-indigo-400' }}@endif" viewBox="0 0 16 16">
                        <path d="M12.311 9.527c-1.161-.393-1.85-.825-2.143-1.175A3.991 3.991 0 0012 5V4c0-2.206-1.794-4-4-4S4 1.794 4 4v1c0 1.406.732 2.639 1.832 3.352-.292.35-.981.782-2.142 1.175A3.942 3.942 0 001 13.26V16h14v-2.74c0-1.69-1.081-3.19-2.689-3.733zM6 4c0-1.103.897-2 2-2s2 .897 2 2v1c0 1.103-.897 2-2 2s-2-.897-2-2V4zm7 10H3v-.74c0-.831.534-1.569 1.33-1.838 1.845-.624 3-1.436 3.452-2.422h.436c.452.986 1.607 1.798 3.453 2.422A1.943 1.943 0 0113 13.26V14z" />
                    </svg>
                    <span class="text-sm font-medium @if(Route::is('sistem')){{ 'text-indigo-500' }}@else{{ 'text-slate-600 hover:text-slate-700' }}@endif">Sistem</span>
                </a>
            </li>
            <li class="mr-0.5 md:mr-0 md:mb-0.5">
                <a class="flex items-center px-2.5 py-2 rounded whitespace-nowrap @if(Route::is('syarat-ketentuan')){{ 'bg-indigo-50' }}@endif" href="{{ route('syarat-ketentuan') }}">
                    <svg class="w-4 h-4 shrink-0 fill-current text-slate-400 mr-2 @if(Route::is('syarat-ketentuan')){{ 'text-indigo-400' }}@endif" viewBox="0 0 16 16">
                        <path d="M14.3.3c.4-.4 1-.4 1.4 0 .4.4.4 1 0 1.4l-8 8c-.2.2-.4.3-.7.3-.3 0-.5-.1-.7-.3-.4-.4-.4-1 0-1.4l8-8zM15 7c.6 0 1 .4 1 1 0 4.4-3.6 8-8 8s-8-3.6-8-8 3.6-8 8-8c.6 0 1 .4 1 1s-.4 1-1 1C4.7 2 2 4.7 2 8s2.7 6 6 6 6-2.7 6-6c0-.6.4-1 1-1z" />
                    </svg>
                    <span class="text-sm font-medium @if(Route::is('syarat-ketentuan')){{ 'text-indigo-500' }}@else{{ 'text-slate-600 hover:text-slate-700' }}@endif">Syarat & Ketentuan</span>
                </a>
            </li>
            <li class="mr-0.5 md:mr-0 md:mb-0.5">
                <a class="flex items-center px-2.5 py-2 rounded whitespace-nowrap @if(Route::is('master-gallery.index')){{ 'bg-indigo-50' }}@endif" href="{{ route('master-gallery.index') }}">
                    <svg class="w-4 h-4 shrink-0 fill-current text-slate-400 mr-2 @if(Route::is('master-gallery.index')){{ 'text-indigo-400' }}@endif" viewBox="0 0 16 16">
                        <path d="M14.3.3c.4-.4 1-.4 1.4 0 .4.4.4 1 0 1.4l-8 8c-.2.2-.4.3-.7.3-.3 0-.5-.1-.7-.3-.4-.4-.4-1 0-1.4l8-8zM15 7c.6 0 1 .4 1 1 0 4.4-3.6 8-8 8s-8-3.6-8-8 3.6-8 8-8c.6 0 1 .4 1 1s-.4 1-1 1C4.7 2 2 4.7 2 8s2.7 6 6 6 6-2.7 6-6c0-.6.4-1 1-1z" />
                    </svg>
                    <span class="text-sm font-medium @if(Route::is('master-gallery.index')){{ 'text-indigo-500' }}@else{{ 'text-slate-600 hover:text-slate-700' }}@endif">Gallery</span>
                </a>
            </li>
        </ul>
    </div>
</div>
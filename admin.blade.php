<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') - Absensi KCD Lebak</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

    <div class="flex min-h-screen">

        {{-- Sidebar --}}
        <aside class="w-64 bg-gray-900 text-white flex-shrink-0">
            <div class="p-4 border-b border-gray-700">
                <h1 class="font-bold text-lg leading-tight">KCD Pendidikan</h1>
                <p class="text-xs text-gray-400">Kabupaten Lebak</p>
            </div>
            <nav class="p-3 space-y-1">
                <a href="{{ route('admin.index') }}"
                   class="block px-3 py-2 rounded text-sm {{ request()->routeIs('admin.index') ? 'bg-blue-700' : 'hover:bg-gray-800' }}">
                    Dashboard
                </a>
                <a href="{{ route('admin.rekap.harian') }}"
                   class="block px-3 py-2 rounded text-sm {{ request()->routeIs('admin.rekap.harian') ? 'bg-blue-700' : 'hover:bg-gray-800' }}">
                    Rekap Harian
                </a>
                <a href="{{ route('admin.rekap.bulanan') }}"
                   class="block px-3 py-2 rounded text-sm {{ request()->routeIs('admin.rekap.bulanan') ? 'bg-blue-700' : 'hover:bg-gray-800' }}">
                    Rekap Bulanan
                </a>
                                <a href="{{ route('admin.master.karyawan') }}"
                   class="block px-3 py-2 rounded text-sm {{ request()->routeIs('admin.master.karyawan') ? 'bg-blue-700' : 'hover:bg-gray-800' }}">
                    Master Karyawan
                </a>
                                <a href="{{ route('admin.hari-libur.index') }}"
                   class="block px-3 py-2 rounded text-sm {{ request()->routeIs('admin.hari-libur.index') ? 'bg-blue-700' : 'hover:bg-gray-800' }}">
                    Hari Libur
                </a>
                
                                <a href="{{ route('admin.pengajuan-cuti.index') }}"
                   class="block px-3 py-2 rounded text-sm {{ request()->routeIs('admin.pengajuan-cuti.*') ? 'bg-blue-700' : 'hover:bg-gray-800' }}">
                    Pengajuan Cuti
                </a>

                <form method="POST" action="{{ route('admin.logout') }}" class="pt-2 mt-2 border-t border-gray-700">
                    @csrf
                    <button type="submit" class="w-full text-left px-3 py-2 rounded text-sm text-red-400 hover:bg-gray-800">
                        Keluar (Logout)
                    </button>
                </form>
            </nav>
        </aside>

        {{-- Main Content --}}
        <div class="flex-1 flex flex-col">

            {{-- Topbar --}}
            <header class="bg-white shadow px-6 py-3 flex justify-between items-center">
                <h2 class="font-semibold text-gray-700">@yield('page-title', 'Dashboard')</h2>
                                <div class="flex items-center gap-4 text-sm text-gray-500">
                    <span>{{ auth()->user()->name }}</span>
                    <span class="flex items-center gap-2">
                        <span class="w-2 h-2 bg-green-500 rounded-full inline-block"></span>
                        Live Connection
                    </span>
                </div>
            </header>

            {{-- Konten Halaman --}}
            <main class="p-6 flex-1">

                @if (session('success'))
                    <div class="mb-4 p-3 bg-green-100 text-green-700 rounded text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 p-3 bg-red-100 text-red-700 rounded text-sm">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

</body>
</html>
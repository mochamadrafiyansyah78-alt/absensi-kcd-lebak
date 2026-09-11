<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Cuti - KCD Pendidikan Kab. Lebak</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen py-6">

    <div class="max-w-md mx-auto bg-white rounded-lg shadow-md overflow-hidden">

        <div class="bg-blue-700 text-white p-4 text-center">
            <h1 class="font-bold text-lg leading-tight">KCD Pendidikan Kab. Lebak</h1>
            <p class="text-xs opacity-90">Formulir Pengajuan Cuti</p>
        </div>

        <div class="p-5">

            <a href="{{ route('absen.index') }}" class="text-blue-600 text-xs underline block mb-4">&larr; Kembali ke Halaman Absensi</a>

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

            @if ($errors->any())
                <div class="mb-4 p-3 bg-red-100 text-red-700 rounded text-sm">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('cuti.store') }}" method="POST" id="form-cuti">
                @csrf

                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Pegawai</label>
                    <select name="karyawan_id" id="karyawan_id" class="w-full border rounded px-3 py-2" required>
                        <option value="">-- Pilih Nama --</option>
                        @foreach ($karyawans as $k)
                            <option value="{{ $k->id }}" data-nip="{{ $k->nip }}" data-jabatan="{{ $k->jabatan }}"
                                {{ old('karyawan_id') == $k->id ? 'selected' : '' }}>
                                {{ $k->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">NIP</label>
                    <input type="text" id="nip" class="w-full border rounded px-3 py-2 bg-gray-100" readonly>
                </div>

                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
                    <input type="text" id="jabatan" class="w-full border rounded px-3 py-2 bg-gray-100" readonly>
                </div>

                <div class="mb-3 grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" id="tanggal_mulai" value="{{ old('tanggal_mulai') }}"
                               class="w-full border rounded px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" id="tanggal_selesai" value="{{ old('tanggal_selesai') }}"
                               class="w-full border rounded px-3 py-2" required>
                    </div>
                </div>

                <p id="info-lama-cuti" class="text-xs text-gray-500 mb-3"></p>

                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan Cuti</label>
                    <textarea name="keterangan" rows="3" placeholder="Contoh: Cuti melahirkan, keperluan keluarga, dsb."
                              class="w-full border rounded px-3 py-2" required>{{ old('keterangan') }}</textarea>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Foto Bukti Pengajuan (Wajib difoto langsung)</label>

                    <button type="button" id="btn-buka-kamera" class="w-full bg-gray-700 text-white text-sm py-2 rounded mb-2">
                        Buka Kamera
                    </button>

                    <video id="video-preview" class="w-full rounded mb-2 hidden" autoplay playsinline></video>
                    <button type="button" id="btn-ambil-foto" class="w-full bg-blue-600 text-white text-sm py-2 rounded mb-2 hidden">
                        Ambil Foto
                    </button>

                    <canvas id="canvas-foto" class="hidden"></canvas>

                    <img id="preview-foto" class="w-full rounded mb-2 hidden" alt="Preview Foto">
                    <button type="button" id="btn-ulangi-foto" class="w-full bg-yellow-600 text-white text-sm py-2 rounded mb-2 hidden">
                        Ambil Ulang
                    </button>

                    <input type="hidden" name="foto_bukti" id="foto_bukti">

                    <p id="status-foto" class="text-xs text-gray-500">Belum ada foto diambil. Foto wajib diambil langsung dari kamera.</p>
                </div>

                <button type="submit" class="w-full bg-blue-700 text-white font-bold py-3 rounded hover:bg-blue-800">
                    KIRIM PENGAJUAN CUTI
                </button>
            </form>
        </div>
    </div>

    <script>
        // ================= AUTO-SYNC NIP & JABATAN =================
        const selectKaryawan = document.getElementById('karyawan_id');
        const inputNip = document.getElementById('nip');
        const inputJabatan = document.getElementById('jabatan');

        function syncKaryawan() {
            const selected = selectKaryawan.options[selectKaryawan.selectedIndex];
            inputNip.value = selected.dataset.nip || '';
            inputJabatan.value = selected.dataset.jabatan || '';
        }
        selectKaryawan.addEventListener('change', syncKaryawan);
        syncKaryawan();

        // ================= HITUNG LAMA CUTI =================
        const inputMulai = document.getElementById('tanggal_mulai');
        const inputSelesai = document.getElementById('tanggal_selesai');
        const infoLama = document.getElementById('info-lama-cuti');

        function hitungLamaCuti() {
            if (inputMulai.value && inputSelesai.value) {
                const mulai = new Date(inputMulai.value);
                const selesai = new Date(inputSelesai.value);
                const selisih = Math.round((selesai - mulai) / (1000 * 60 * 60 * 24)) + 1;
                infoLama.innerText = selisih > 0 ? `Lama cuti: ${selisih} hari` : 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.';
            } else {
                infoLama.innerText = '';
            }
        }
        inputMulai.addEventListener('change', hitungLamaCuti);
        inputSelesai.addEventListener('change', hitungLamaCuti);

        // ================= MODUL KAMERA =================
        const btnBukaKamera = document.getElementById('btn-buka-kamera');
        const btnAmbilFoto = document.getElementById('btn-ambil-foto');
        const btnUlangiFoto = document.getElementById('btn-ulangi-foto');
        const videoPreview = document.getElementById('video-preview');
        const canvasFoto = document.getElementById('canvas-foto');
        const previewFoto = document.getElementById('preview-foto');
        const inputFotoBase64 = document.getElementById('foto_bukti');
        const statusFoto = document.getElementById('status-foto');

        let streamKamera = null;

        async function bukaKamera() {
            try {
                streamKamera = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
                videoPreview.srcObject = streamKamera;
                videoPreview.classList.remove('hidden');
                btnAmbilFoto.classList.remove('hidden');
                btnBukaKamera.classList.add('hidden');
                previewFoto.classList.add('hidden');
                btnUlangiFoto.classList.add('hidden');
                statusFoto.innerText = 'Kamera aktif. Arahkan lalu tekan "Ambil Foto".';
            } catch (err) {
                statusFoto.innerText = 'Gagal mengakses kamera: ' + err.message;
            }
        }

        btnBukaKamera.addEventListener('click', bukaKamera);

        btnAmbilFoto.addEventListener('click', () => {
            const context = canvasFoto.getContext('2d');
            canvasFoto.width = videoPreview.videoWidth;
            canvasFoto.height = videoPreview.videoHeight;
            context.drawImage(videoPreview, 0, 0, canvasFoto.width, canvasFoto.height);

            const base64 = canvasFoto.toDataURL('image/jpeg', 0.8);
            inputFotoBase64.value = base64;

            previewFoto.src = base64;
            previewFoto.classList.remove('hidden');
            btnUlangiFoto.classList.remove('hidden');
            videoPreview.classList.add('hidden');
            btnAmbilFoto.classList.add('hidden');
            statusFoto.innerText = 'Foto berhasil diambil.';

            if (streamKamera) streamKamera.getTracks().forEach(track => track.stop());
        });

        btnUlangiFoto.addEventListener('click', () => {
            inputFotoBase64.value = '';
            previewFoto.classList.add('hidden');
            btnUlangiFoto.classList.add('hidden');
            btnBukaKamera.classList.remove('hidden');
            statusFoto.innerText = 'Silakan ambil ulang foto.';
            bukaKamera();
        });

        document.getElementById('form-cuti').addEventListener('submit', function (e) {
            if (!inputFotoBase64.value) {
                e.preventDefault();
                alert('Foto bukti wajib diambil langsung dari kamera.');
            }
        });
    </script>

</body>
</html>
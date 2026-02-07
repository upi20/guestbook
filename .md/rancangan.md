## Rancangan Aplikasi Web Buku Tamu (Tanpa Login)

### 1. Tujuan Aplikasi
- Mencatat tamu yang datang (per rombongan/keluarga) dengan jumlah orang.
- Menghitung total orang yang sudah datang secara otomatis.
- Menyediakan tampilan monitoring realtime untuk bagian lain (misalnya konsumsi, logistik) tanpa harus berada di gate.
- Aplikasi sangat sederhana, tanpa sistem akun dan tanpa login.

### 2. Teknologi yang Digunakan
- **Frontend**: Bootstrap 5 untuk layout dan komponen UI dasar (form, tombol, card, grid).
- **Interaksi**: jQuery untuk manipulasi DOM sederhana, submit form via AJAX, dan update tampilan.
- **Realtime**: WebSocket untuk mengirim update jumlah tamu dan daftar entri terbaru ke halaman monitoring secara realtime.
- **Backend**: (bebas, misalnya PHP/Laravel sesuai project sekarang) hanya menangani:
	- Penyimpanan data buku tamu ke database.
	- Perhitungan total orang yang sudah datang.
	- Broadcast data terbaru ke semua klien yang membuka halaman monitoring.

### 3. Struktur Halaman
Tidak ada konsep login/role. Aplikasi hanya punya 3 halaman utama:
1. Halaman Gate (Menu Utama)
2. Halaman Form Buku Tamu (dioperasikan oleh penjaga di gate)
3. Halaman Monitoring (dibuka oleh bagian lain untuk memantau)

Ketiga halaman ini bisa diakses lewat URL terpisah atau dari link di halaman Gate.

---

### 4. Halaman 1: Gate (Pilih Form / Monitoring)

**Tujuan**: Menjadi pintu masuk sederhana untuk memilih mau mengoperasikan form buku tamu atau hanya melihat monitoring.

**Elemen utama:**
- Judul aplikasi, misalnya: "Buku Tamu Acara X".
- Dua tombol besar (menggunakan Bootstrap 5):
	- Tombol 1: "Form Buku Tamu" → mengarah ke halaman form.
	- Tombol 2: "Monitoring" → mengarah ke halaman monitoring.
- (Opsional) Informasi singkat tentang total tamu saat ini (ditarik dari backend sekali saat load halaman, tidak perlu realtime di sini).

**Alur pengguna:**
- Penjaga di gate akan memilih tombol "Form Buku Tamu".
- Bagian lain (misalnya konsumsi) akan memilih tombol "Monitoring" dan membiarkan halaman itu terbuka.

---

### 5. Halaman 2: Form Buku Tamu

**Tujuan**: Memungkinkan penjaga menginput data tamu per rombongan/keluarga dengan cepat.

**Field yang diisi (contoh):**
- Nama Rombongan / Nama Keluarga / Nama Instansi (input teks).
- Jumlah Orang (input angka).
- Kategori Tamu (opsional, dropdown): misalnya "Keluarga", "VIP", "Umum", dll.
- Keterangan (opsional, textarea): catatan singkat bila diperlukan.
- Foto Rombongan (opsional):
	- Input file foto, dengan opsi membuka kamera langsung (menggunakan atribut HTML yang mendukung kamera di mobile, misalnya accept="image/*" dan capture bila browser mendukung).
	- Jika tidak diisi, entri tetap bisa disimpan.

**Data tambahan yang disimpan otomatis:**
- Waktu kedatangan (timestamp dari server).
- ID entri (auto increment).

**Tampilan UI (menggunakan Bootstrap 5):**
- Form sederhana dalam satu card di tengah layar.
- Desain mobile-friendly:
	- Menggunakan grid Bootstrap yang otomatis stack (satu kolom) di layar kecil.
	- Komponen dan font cukup besar untuk dioperasikan dengan satu tangan oleh penjaga di gate.
	- Tombol utama ("Simpan") dibuat penuh lebar (btn-block) di mobile.
- Input foto ditempatkan jelas sebagai opsi tambahan, dengan teks bantu seperti "Tambah foto (opsional)" agar tidak membingungkan dan tidak memperlambat input.
- Bagian di bawah form menampilkan:
	- Total orang yang sudah datang (angka besar, langsung terbaca di layar kecil).
	- (Opsional) Daftar singkat 5 entri terakhir untuk konfirmasi.

**Perilaku saat submit:**
- Penjaga mengisi form dan menekan tombol "Simpan".
- jQuery mengirim data form ke backend via AJAX (tanpa refresh halaman penuh).
- Backend:
	- Menyimpan data ke database.
	- Menghitung ulang total orang yang sudah datang.
	- Mengirim event lewat WebSocket yang berisi:
		- Total orang terbaru.
		- Data entri baru (nama rombongan, jumlah orang, kategori, waktu).
- Halaman Form bisa menampilkan notifikasi sederhana (alert Bootstrap) bahwa data berhasil disimpan, dan membersihkan form atau hanya mengosongkan field jumlah orang.

**Keterkaitan dengan Monitoring:**
- Setiap kali form disubmit dan data diterima backend, halaman Monitoring yang sedang terbuka akan langsung ter-update via WebSocket (tanpa perlu refresh).

---

### 6. Halaman 3: Monitoring (Realtime)

**Tujuan**: Memberikan tampilan ringkas dan realtime untuk memantau berapa banyak tamu yang sudah hadir, agar bagian lain bisa mengatur konsumsi/logistik.

**Elemen utama di layar:**
- Angka besar "Total Hadir" (total orang) di bagian atas.
- (Opsional) Ringkasan per kategori, misalnya:
	- Keluarga: X orang
	- VIP: Y orang
	- Umum: Z orang
- Tabel atau list singkat yang menampilkan entri terbaru, misalnya 10 entri terakhir:
	- Kolom: Waktu, Nama Rombongan, Jumlah Orang, Kategori, (opsional) ikon/foto kecil jika ada foto yang diupload.

**Perilaku realtime (WebSocket):**
- Saat halaman dibuka, frontend:
	- Meminta data awal (total dan beberapa entri terakhir) ke backend (REST/AJAX biasa).
	- Membuka koneksi WebSocket ke server.
- Setiap kali ada entri baru dari Form Buku Tamu:
	- Backend mengirim pesan ke semua klien monitoring berisi data terbaru.
	- jQuery menangani pesan WebSocket dan:
		- Mengupdate angka "Total Hadir".
		- Menambahkan baris baru di atas daftar entri terbaru.
		- (Opsional) Mengupdate ringkasan per kategori.

**Kebutuhan tampilan:**
- Layout sederhana, bisa full screen dengan background netral.
- Angka total dibuat besar dan jelas, bisa ditempatkan di dalam card Bootstrap.
- Tabel/list disesuaikan supaya tetap terbaca di layar kecil (mobile) dengan kemungkinan horizontal scroll jika kolom banyak.
- Halaman ini biasanya hanya dibuka dan dibiarkan menyala (misalnya di layar TV/monitor kantor), tetapi tetap responsif jika diakses dari smartphone atau tablet.

---

### 7. Alur Penggunaan Sederhana
1. Bagian IT men-deploy aplikasi di satu URL (misalnya di jaringan lokal).
2. Penjaga di gate membuka Halaman Gate, lalu masuk ke Halaman Form Buku Tamu.
3. Setiap rombongan datang, penjaga mengisi nama rombongan dan jumlah orang, lalu klik "Simpan".
4. Backend mencatat dan menghitung total, lalu mengirim update ke semua klien monitoring via WebSocket.
5. Bagian konsumsi/logistik membuka Halaman Gate, lalu masuk ke Halaman Monitoring dan melihat total hadir bertambah secara realtime.
6. Tidak ada proses login, jadi siapa pun yang tahu URL bisa mengakses (bisa diatasi secara fisik/network kalau perlu pembatasan).

---

### 8. Skema Data Sederhana (Konseptual)
- Tabel `tamu` (contoh kolom):
	- `id` (integer, primary key)
	- `nama_rombongan` (string)
	- `jumlah_orang` (integer)
	- `kategori` (string, nullable)
	- `keterangan` (text, nullable)
	- `waktu_datang` (datetime)

**Perhitungan total orang hadir:**
- Total = jumlah semua `jumlah_orang` dari tabel `tamu`.
- Bisa dihitung setiap kali ada insert baru, atau disimpan di satu tabel/config terpisah dan di-update secara incremental.

---

### 9. Catatan Non-Fungsional (Ringkas)
- **Tanpa login**: tidak ada akun pengguna, cukup 3 halaman statis + backend sederhana.
- **Kinerja**: Data yang dikirim lewat WebSocket hanya berupa total dan satu entri terakhir, sehingga ringan.
- **Keandalan**: Jika koneksi WebSocket putus, halaman monitoring masih bisa menampilkan data terakhir; bisa ada tombol "Refresh" untuk tarik data ulang.
- **Kesederhanaan**: Semua interaksi utama cukup dilakukan oleh satu orang penjaga di gate pada Halaman Form Buku Tamu.

---

### 10. Ringkasan
- Aplikasi terdiri dari 3 halaman: Gate, Form Buku Tamu, dan Monitoring.
- Tidak ada sistem login/role, hanya perbedaan fungsi halaman.
- Teknologi frontend: Bootstrap 5 + jQuery; realtime dengan WebSocket.
- Backend menyimpan entri buku tamu dan menghitung total orang hadir, lalu membroadcast update ke halaman Monitoring.
- Fokus utama: input cepat di gate dan monitoring jumlah tamu secara realtime untuk mengamankan konsumsi dan kebutuhan lain.


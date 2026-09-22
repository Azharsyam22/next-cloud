# UI/UX Design Brief — CloudCampus Storage

## 1. Prinsip Desain
1. **Familiar tapi ringan** — pola interaksi meniru Google Drive/OneDrive agar user tidak perlu belajar ulang.
2. **Cepat terasa** — feedback instan (progress bar, skeleton loading, optimistic UI untuk rename/hapus).
3. **Jelas soal kuota & keamanan** — user selalu tahu sisa kuota dan siapa yang punya akses ke file mereka.
4. **Dual identity yang mulus** — user akademik dan publik memakai UI yang sama, hanya beberapa elemen (badge "Akademik") yang membedakan.
5. **Mobile-friendly** — banyak mahasiswa akses lewat HP, layout harus responsif penuh.

## 2. Persona
| Persona | Kebutuhan Utama | Perangkat |
|---|---|---|
| Mahasiswa (Rani, 20) | Upload tugas, share ke dosen, akses cepat | HP + laptop kampus |
| Dosen (Pak Budi, 40) | Kelola banyak folder mata kuliah, share massal | Laptop |
| User Publik (Sari, 28) | Simpan file pribadi, share ke klien | HP |
| Admin Kampus (Tono) | Monitor kuota, kelola user bermasalah | Laptop, layar besar |

## 3. Information Architecture (Sitemap)
```
- Landing Page (publik)
  - Login
  - Register (publik)
  - Login SSO (redirect ke Sistem Akademik)
- Dashboard (setelah login)
  - Beranda "Semua File" (grid/list view)
  - Folder Saya (nested navigation + breadcrumb)
  - Dibagikan dengan Saya
  - Tautan Berbagi Saya (kelola link yang sudah dibuat)
  - Trash / Sampah
  - Pengaturan Akun
    - Profil
    - Kuota & Penyimpanan
    - Keamanan (ganti password — khusus akun publik)
- Admin Panel (role admin)
  - Dashboard Statistik
  - Manajemen User
  - Manajemen Storage/Kuota
  - Log Aktivitas
```

## 4. User Flow Utama

### 4.1 Login
```
[Landing] -> pilih "Login Akademik" -> redirect SSO -> callback token -> [Dashboard]
[Landing] -> pilih "Daftar/Login Umum" -> form email+password -> verifikasi email (opsional) -> [Dashboard]
```

### 4.2 Upload File
```
[Dashboard] -> klik "Upload" atau drag file ke area drop -> pilih tujuan folder (default: folder aktif)
-> progress bar per file -> selesai -> file muncul di list dengan ikon status "baru"
```

### 4.3 Berbagi File
```
[Pilih file] -> klik ikon "Share" -> modal muncul:
  - Tab "Buat Tautan": toggle publik/terbatas, pilih permission (lihat saja/unduh), set expiry
  - Tab "Bagikan ke Orang": input email/username, pilih permission
-> klik "Buat/Simpan" -> tautan tersalin otomatis ke clipboard
```

### 4.4 Kelola Folder
```
[Dashboard] -> klik kanan / tombol "..." pada folder -> menu: Rename, Pindah, Hapus, Bagikan, Detail
```

## 5. Daftar Layar (Screens) & Layout

### 5.1 Landing Page
- Hero section: judul singkat + 2 CTA besar ("Login Akademik" & "Daftar/Masuk Umum")
- Section fitur singkat (3 kolom ikon: Upload, Share, Aman)
- Footer sederhana

### 5.2 Dashboard / File Explorer
- **Sidebar kiri** (fixed, collapsible di mobile): Semua File, Folder Saya, Dibagikan, Tautan Saya, Trash, indikator kuota (progress bar "3.2GB dari 5GB")
- **Top bar**: search box, tombol "Upload" (dropdown: Upload File / Upload Folder / Buat Folder Baru), avatar user + badge role (Akademik/Publik)
- **Breadcrumb** di bawah top bar untuk navigasi folder
- **Konten utama**: toggle Grid/List view, tabel/list berisi Nama, Ukuran, Terakhir Diubah, Pemilik, Aksi (ikon titik tiga)
- **Drag-and-drop overlay**: highlight seluruh area saat file diseret dari luar browser
- **Panel detail** (slide-in dari kanan saat file dipilih): preview thumbnail, info metadata, tombol Share/Download/Delete

### 5.3 Modal Upload Progress
- Muncul di pojok kanan bawah (mirip Google Drive), list file dengan progress bar individual, bisa diminimize

### 5.4 Modal Berbagi (Share)
- Header: nama file/folder yang dibagikan
- Tab: "Tautan" & "Orang"
- Tab Tautan: switch aktif/nonaktif, dropdown permission, date-picker expiry, tombol "Salin Tautan"
- Tab Orang: input pencarian user + list orang yang sudah punya akses (dengan dropdown ubah permission/hapus akses)

### 5.5 Halaman Trash
- List file terhapus dengan kolom "Dihapus pada" + "Akan hilang permanen pada" (30 hari)
- Aksi: Pulihkan, Hapus Permanen

### 5.6 Pengaturan Akun
- Tab Profil: nama, email, foto
- Tab Kuota: visual donut chart pemakaian per tipe file (dokumen/gambar/video/lain)
- Tab Keamanan: ganti password (hanya untuk akun publik; akun akademik menampilkan info "dikelola oleh SSO")

### 5.7 Admin Panel
- Dashboard: kartu statistik (total user, total storage terpakai, upload hari ini)
- Tabel Manajemen User: filter by tipe akun (akademik/publik), aksi suspend/quota override
- Log Aktivitas: tabel dengan filter tanggal & jenis aksi

## 6. Komponen UI (Component List)
- Button (primary, secondary, ghost, danger)
- Input text, input search dengan ikon
- File/Folder row item (icon by mime-type, nama, size, tanggal, menu aksi)
- Progress bar (linear untuk upload, circular untuk kuota)
- Modal/Dialog (konfirmasi hapus, share, buat folder)
- Toast/Notification (sukses upload, gagal upload, link disalin)
- Breadcrumb
- Avatar + Badge role
- Empty state illustration (folder kosong, trash kosong, hasil pencarian kosong)
- Skeleton loader (saat memuat daftar file)

## 7. Style Guide (usulan awal)
- **Warna Primer**: Biru kampus (#2563EB) — bisa disesuaikan warna identitas kampus
- **Warna Aksen**: Hijau (#16A34A) untuk status sukses, Merah (#DC2626) untuk hapus/error, Kuning (#D97706) untuk peringatan kuota
- **Netral**: skala abu-abu (#F9FAFB s/d #111827) untuk background & teks
- **Tipografi**: Inter atau Poppins untuk UI, ukuran dasar 14px body / 12px caption / 20-24px heading
- **Radius**: rounded-lg (8px) konsisten di card, button, modal
- **Spacing**: skala 4px (4/8/12/16/24/32)
- **Ikon**: set ikon konsisten (mis. Lucide/Heroicons), 1 gaya saja (outline atau filled, jangan campur)

## 8. Responsive Breakpoints
- Mobile (<640px): sidebar jadi bottom-nav atau hamburger drawer, list view default (bukan grid)
- Tablet (640–1024px): sidebar collapsible, grid 2-3 kolom
- Desktop (>1024px): sidebar permanen, grid 4-6 kolom

## 9. Aksesibilitas
- Kontras warna minimal WCAG AA
- Semua aksi ikon punya label aria/tooltip
- Navigasi keyboard untuk modal (Esc menutup, Tab berpindah fokus)
- Alt text untuk empty-state illustration

## 10. Micro-interaction & Empty States
- Drag file di luar area drop → seluruh dashboard menampilkan overlay biru transparan dengan teks "Lepas untuk mengunggah"
- Folder kosong → ilustrasi + teks "Belum ada file di sini" + tombol cepat "Upload File"
- Kuota hampir penuh (>90%) → banner kuning persisten di dashboard

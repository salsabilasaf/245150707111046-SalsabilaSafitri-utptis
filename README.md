UTP TIS - E-commerce API

- Nama  : Salsabila Safitri
- NIM   : 245150707111046

Deskripsi: UTP kali ini adalah membuat backend API sederhana berbasis Laravel yang mensimulasikan sistem e-commerce menggunakan mock data JSON tanpa database. API ini mendukung operasi CRUD lengkap pada data barang.

---

Teknologi yang digunakan:

- PHP
- Laravel 
- Mock data: storage/app/private/items.json
- Dokumentasi: Swagger UI (L5-Swagger)
- Swagger path: http://localhost:8000/api/documentation#/Items/7f1ffbd9fa2b704c61ebcccb636ebb27

---

Instalasi dan Menjalankan Project

1. Membuat project baru dengan command “composer create-project
laravel/laravel 245150707111046-SalsabilaSafitri-utptis”

2. Buat file mock data

data awal dapat dituliskan di storage/app/private/items.json:

[
  {"id": 1, "nama": "Laptop", "harga": 15000000},
  {"id": 2, "nama": "Mouse", "harga": 150000},
  {"id": 3, "nama": "Keyboard", "harga": 350000}
]

---

Daftar Endpoint

1. GET /api/items
Adalah endpoint yang berfungsi menampilkan seluruh data barang.

Method  : GET
URL     : /api/items

Contoh Response saat berhasil(200):
{
  "status": "success",
  "message": "Daftar semua item",
  "data": [
    {"id": 1, "nama": "Laptop", "harga": 15000000},
    {"id": 2, "nama": "Mouse", "harga": 150000}
  ]
}

2. GET /api/items/{id}

Endpoint yang menampilkan data barang berdasarkan ID.

Method  : GET
URL     : /api/items/{id}

Contoh Response saat berhasil(200):
{
  "status": "success",
  "message": "Item ditemukan",
  "data": {"id": 1, "nama": "Laptop", "harga": 15000000}
}

Contoh Response saat gagal(404):
{
  "status": "error",
  "message": "Item dengan ID 99 tidak Ditemukan"
}

3. POST /api/items

Endpoint yang menambahkan data barang baru.

Method  : POST
URL     : /api/items
Headers :
  Content-Type : application/json
  Accept       : application/json

Contoh body (JSON):
{
  "nama": "Headset",
  "harga": 250000
}

Validasi yang digunakan:
- nama  : wajib diisi, bertipe string, maksimal 255 karakter
- harga : wajib diisi, bertipe angka, tidak boleh negatif

Contoh Response saat berhasil(201):
{
  "status": "success",
  "message": "Item berhasil ditambahkan",
  "data": {"id": 4, "nama": "Headset", "harga": 250000}
}

Contoh Response saat gagal(422):
{
  "message": "Nama item wajib diisi",
  "errors": {
    "nama": ["Nama item wajib diisi"],
    "harga": ["Harga item wajib diisi"]
  }
}

4. PUT /api/items/{id}
Mndpoint untuk mengupdate seluruh data barang berdasarkan ID. Semua field wajib dikirim.

Method  : PUT
URL     : /api/items/{id}
Headers :
  Content-Type : application/json
  Accept       : application/json

Contoh body (JSON):
{
  "nama": "Laptop Gaming",
  "harga": 20000000
}

Validasi yang digunakan:
- nama  : wajib diisi, bertipe string, maksimal 255 karakter
- harga : wajib diisi, bertipe angka, tidak boleh negatif

Contoh Response saat berhasil(200):
{
  "status": "success",
  "message": "Item berhasil diupdate (seluruh data)",
  "data": {"id": 1, "nama": "Laptop Gaming", "harga": 20000000}
}

Contoh Response saat gagal(404):
{
  "status": "error",
  "message": "Item dengan ID 99 tidak Ditemukan"
}

5. PATCH /api/items/{id}
Merupakan endpoint yang digunakan untuk mengupdate sebagian data barang berdasarkan ID dengan minimal satu field harus dikirim.

Method  : PATCH
URL     : /api/items/{id}
Headers :
  Content-Type : application/json
  Accept       : application/json

Contoh body (JSON):
{
  "harga": 18000000
}

Validasi yang digunakan:
- nama  : opsional, bertipe string, maksimal 255 karakter
- harga : opsional, bertipe angka, tidak boleh negatif
- minimal satu field harus dikirim

Contoh Response saat berhasil(200):
{
  "status": "success",
  "message": "Item berhasil diupdate (sebagian data)",
  "data": {"id": 1, "nama": "Laptop Gaming", "harga": 18000000}
}

Contoh Response saat gagal(422):
{
  "status": "error",
  "message": "Minimal satu field (nama atau harga) harus diisi"
}

6. DELETE /api/items/{id}
Endpoint untuk menghapus data barang berdasarkan ID.

Method  : DELETE
URL     : /api/items/{id}

Contoh Response saat berhasil(200):
{
  "status": "success",
  "message": "Item dengan ID 1 berhasil dihapus"
}

Contoh Response saat gagal(404):
{
  "status": "error",
  "message": "Item dengan ID 99 tidak Ditemukan"
}

---

1. Instalasi Swagger
composer require darkaonline/l5-swagger

2. Publish Config
php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"

3. Generate docs
php artisan l5-swagger:generate

---

Error Handling

Semua endpoint menerapkan validasi dan error handling dengan format response dan kode status seperti:

- 200 - Request berhasil
- 201 - Data berhasil dibuat
- 400 - ID tidak valid (bukan angka)
- 404 - Data tidak ditemukan
- 422 - Validasi gagal

---
Cara Kerja Sistem

- Data disimpan dalam file JSON di storage/app/private/items.json
- Setiap request akan membaca file JSON tersebut
- Untuk operasi:
  - GET: membaca data
  - POST: menambahkan data baru ke array
  - PUT/PATCH: mengubah data berdasarkan ID
  - DELETE: menghapus data dari array
- Setelah perubahan, data ditulis kembali ke file JSON

---

Struktur Project

- app/Http/Controllers/ItemController.php  
  Berisi seluruh logic CRUD dan validasi serta anotasi Swagger

- routes/api.php  
  Mendefinisikan endpoint API yang tersedia

- storage/app/private/items.json  
  Digunakan sebagai penyimpanan data sementara (mock database)

- api-docs/api-docs.json  
  File hasil generate dokumentasi Swagger

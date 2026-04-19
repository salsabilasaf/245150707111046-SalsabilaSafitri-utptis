<?php

use App\Http\Controllers\ItemController;
use Illuminate\Support\Facades\Route;

// ─── Item Routes ─────────────────────────────────────────────────────────────

// GET    /api/items          → Menampilkan semua barang (dengan filter opsional)
Route::get('/items', [ItemController::class, 'index']);

// GET    /api/items/{id}     → Menampilkan barang berdasarkan ID
Route::get('/items/{id}', [ItemController::class, 'show'])->whereNumber('id');

// POST   /api/items          → Membuat barang baru
Route::post('/items', [ItemController::class, 'store']);

// PUT    /api/items/{id}     → Mengedit SELURUH data barang (full update)
Route::put('/items/{id}', [ItemController::class, 'update'])->whereNumber('id');

// PATCH  /api/items/{id}     → Mengedit SEBAGIAN data barang (partial update)
Route::patch('/items/{id}', [ItemController::class, 'patch'])->whereNumber('id');

// DELETE /api/items/{id}     → Menghapus barang
Route::delete('/items/{id}', [ItemController::class, 'destroy'])->whereNumber('id');

// ─── Fallback untuk route tidak ditemukan ────────────────────────────────────
Route::fallback(function () {
    return response()->json([
        'status'  => 'error',
        'message' => 'Endpoint tidak ditemukan. Pastikan URL dan method HTTP sudah benar.',
        'available_endpoints' => [
            'GET    /api/items'         => 'Tampilkan semua barang',
            'GET    /api/items/{id}'    => 'Tampilkan barang berdasarkan ID',
            'POST   /api/items'         => 'Tambah barang baru',
            'PUT    /api/items/{id}'    => 'Update seluruh data barang',
            'PATCH  /api/items/{id}'    => 'Update sebagian data barang',
            'DELETE /api/items/{id}'    => 'Hapus barang',
        ],
    ], 404);
});
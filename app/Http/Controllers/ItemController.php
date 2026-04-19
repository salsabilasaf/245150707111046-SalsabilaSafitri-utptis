<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;

#[OA\Info(
    title: "UTP TIS",
    version: "1.0.0",
    description: "Salsabila Safitri - 245150707111046"
)]
#[OA\Server(
    url: "http://localhost:8000",
    description: "Local Development Server"
)]
class ItemController extends Controller
{
    private function getItems(): array
    {
        $path = storage_path('app/private/items.json');
        if (!file_exists($path)) {
            file_put_contents($path, json_encode([]));
        }
        $data = json_decode(file_get_contents($path), true);
        return is_array($data) ? $data : [];
    }

    private function saveItems(array $items): void
    {
        $path = storage_path('app/private/items.json');
        file_put_contents($path, json_encode(array_values($items), JSON_PRETTY_PRINT));
    }

    #[OA\Get(
        path: '/api/items',
        summary: 'Tampilkan semua item',
        tags: ['Items'],
        responses: [
            new OA\Response(response: 200, description: 'Daftar semua item berhasil ditampilkan')
        ]
    )]
    public function index()
    {
        $items = $this->getItems();
        return response()->json([
            'status'  => 'success',
            'message' => 'Daftar semua item',
            'data'    => $items
        ], 200);
    }

    #[OA\Get(
        path: '/api/items/{id}',
        summary: 'Tampilkan item berdasarkan ID',
        tags: ['Items'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID item',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'Item ditemukan'),
            new OA\Response(response: 404, description: 'Item tidak ditemukan'),
            new OA\Response(response: 400, description: 'ID tidak valid')
        ]
    )]
    public function show($id)
    {
        if (!is_numeric($id)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'ID harus berupa angka'
            ], 400);
        }

        $items = $this->getItems();
        $item  = collect($items)->firstWhere('id', (int) $id);

        if (!$item) {
            return response()->json([
                'status'  => 'error',
                'message' => "Item dengan ID {$id} tidak Ditemukan"
            ], 404);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Item ditemukan',
            'data'    => $item
        ], 200);
    }

    #[OA\Post(
        path: '/api/items',
        summary: 'Tambah item baru',
        tags: ['Items'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['nama', 'harga'],
                properties: [
                    new OA\Property(property: 'nama', type: 'string', example: 'Headset'),
                    new OA\Property(property: 'harga', type: 'number', example: 250000)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Item berhasil ditambahkan'),
            new OA\Response(response: 422, description: 'Validasi gagal')
        ]
    )]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'  => 'required|string|max:255',
            'harga' => 'required|numeric|min:0',
        ], [
            'nama.required'  => 'Nama item wajib diisi',
            'nama.string'    => 'Nama item harus berupa teks',
            'harga.required' => 'Harga item wajib diisi',
            'harga.numeric'  => 'Harga item harus berupa angka',
            'harga.min'      => 'Harga item tidak boleh negatif',
        ]);

        $items = $this->getItems();
        $newId = count($items) > 0 ? max(array_column($items, 'id')) + 1 : 1;

        $newItem = [
            'id'    => $newId,
            'nama'  => $validated['nama'],
            'harga' => (float) $validated['harga'],
        ];

        $items[] = $newItem;
        $this->saveItems($items);

        return response()->json([
            'status'  => 'success',
            'message' => 'Item berhasil ditambahkan',
            'data'    => $newItem
        ], 201);
    }

    #[OA\Put(
        path: '/api/items/{id}',
        summary: 'Update seluruh data item',
        tags: ['Items'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['nama', 'harga'],
                properties: [
                    new OA\Property(property: 'nama', type: 'string', example: 'Laptop Gaming'),
                    new OA\Property(property: 'harga', type: 'number', example: 20000000)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Item berhasil diupdate'),
            new OA\Response(response: 404, description: 'Item tidak ditemukan'),
            new OA\Response(response: 400, description: 'ID tidak valid')
        ]
    )]
    public function update(Request $request, $id)
    {
        if (!is_numeric($id)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'ID harus berupa angka'
            ], 400);
        }

        $validated = $request->validate([
            'nama'  => 'required|string|max:255',
            'harga' => 'required|numeric|min:0',
        ], [
            'nama.required'  => 'Nama item wajib diisi',
            'harga.required' => 'Harga item wajib diisi',
            'harga.numeric'  => 'Harga item harus berupa angka',
            'harga.min'      => 'Harga item tidak boleh negatif',
        ]);

        $items = $this->getItems();
        $index = collect($items)->search(fn($i) => $i['id'] === (int) $id);

        if ($index === false) {
            return response()->json([
                'status'  => 'error',
                'message' => "Item dengan ID {$id} tidak Ditemukan"
            ], 404);
        }

        $items[$index] = [
            'id'    => (int) $id,
            'nama'  => $validated['nama'],
            'harga' => (float) $validated['harga'],
        ];

        $this->saveItems($items);

        return response()->json([
            'status'  => 'success',
            'message' => 'Item berhasil diupdate (seluruh data)',
            'data'    => $items[$index]
        ], 200);
    }

    #[OA\Patch(
        path: '/api/items/{id}',
        summary: 'Update sebagian data item',
        tags: ['Items'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'nama', type: 'string', example: 'Laptop Pro'),
                    new OA\Property(property: 'harga', type: 'number', example: 18000000)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Item berhasil diupdate sebagian'),
            new OA\Response(response: 404, description: 'Item tidak ditemukan'),
            new OA\Response(response: 422, description: 'Tidak ada field yang dikirim'),
            new OA\Response(response: 400, description: 'ID tidak valid')
        ]
    )]
    public function patch(Request $request, $id)
    {
        if (!is_numeric($id)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'ID harus berupa angka'
            ], 400);
        }

        $validated = $request->validate([
            'nama'  => 'sometimes|string|max:255',
            'harga' => 'sometimes|numeric|min:0',
        ], [
            'nama.string'   => 'Nama item harus berupa teks',
            'harga.numeric' => 'Harga item harus berupa angka',
            'harga.min'     => 'Harga item tidak boleh negatif',
        ]);

        if (empty($validated)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Minimal satu field (nama atau harga) harus diisi'
            ], 422);
        }

        $items = $this->getItems();
        $index = collect($items)->search(fn($i) => $i['id'] === (int) $id);

        if ($index === false) {
            return response()->json([
                'status'  => 'error',
                'message' => "Item dengan ID {$id} tidak Ditemukan"
            ], 404);
        }

        $items[$index] = array_merge($items[$index], $validated);
        $this->saveItems($items);

        return response()->json([
            'status'  => 'success',
            'message' => 'Item berhasil diupdate (sebagian data)',
            'data'    => $items[$index]
        ], 200);
    }

    #[OA\Delete(
        path: '/api/items/{id}',
        summary: 'Hapus item',
        tags: ['Items'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'Item berhasil dihapus'),
            new OA\Response(response: 404, description: 'Item tidak ditemukan'),
            new OA\Response(response: 400, description: 'ID tidak valid')
        ]
    )]
    public function destroy($id)
    {
        if (!is_numeric($id)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'ID harus berupa angka'
            ], 400);
        }

        $items    = $this->getItems();
        $filtered = collect($items)->filter(fn($i) => $i['id'] !== (int) $id);

        if ($filtered->count() === collect($items)->count()) {
            return response()->json([
                'status'  => 'error',
                'message' => "Item dengan ID {$id} tidak Ditemukan"
            ], 404);
        }

        $this->saveItems($filtered->toArray());

        return response()->json([
            'status'  => 'success',
            'message' => "Item dengan ID {$id} berhasil dihapus"
        ], 200);
    }
}
<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

class ItemController extends Controller
{
    private string $dataFile;

    public function __construct()
    {
        $this->dataFile = storage_path('app/items.json');

        if (!file_exists($this->dataFile)) {
            $defaultData = [
                [
                    "id" => 1,
                    "nama_barang" => "Laptop Asus VivoBook",
                    "harga" => 8500000,
                    "kategori" => "Elektronik",
                    "stok" => 15,
                    "deskripsi" => "Laptop ringan untuk kebutuhan sehari-hari"
                ]
            ];
            file_put_contents($this->dataFile, json_encode($defaultData, JSON_PRETTY_PRINT));
        }
    }

    // ===== HELPER =====
    private function readData(): array
    {
        return json_decode(file_get_contents($this->dataFile), true) ?? [];
    }

    private function writeData(array $data): void
    {
        file_put_contents($this->dataFile, json_encode(array_values($data), JSON_PRETTY_PRINT));
    }

    private function findById(array $items, int $id)
    {
        foreach ($items as $item) {
            if ((int)$item['id'] === $id) return $item;
        }
        return null;
    }

    private function nextId(array $items): int
    {
        return empty($items) ? 1 : max(array_column($items, 'id')) + 1;
    }

    // ===== ENDPOINT =====

    /**
     * @OA\Get(
     *     path="/items",
     *     tags={"Items"},
     *     summary="Get all items",
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function index(): JsonResponse
    {
        return response()->json($this->readData(), 200);
    }

    /**
     * @OA\Get(
     *     path="/items/{id}",
     *     tags={"Items"},
     *     summary="Get item by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Success"),
     *     @OA\Response(response=404, description="Not Found")
     * )
     */
    public function show(int $id): JsonResponse
    {
        $item = $this->findById($this->readData(), $id);

        if (!$item) {
            return response()->json([
                'message' => "Item dengan ID $id tidak ditemukan"
            ], 404);
        }

        return response()->json($item, 200);
    }

    /**
     * @OA\Post(
     *     path="/items",
     *     tags={"Items"},
     *     summary="Create item",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nama_barang","harga"},
     *             @OA\Property(property="nama_barang", type="string"),
     *             @OA\Property(property="harga", type="number")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Created")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nama_barang' => 'required|string',
            'harga' => 'required|numeric'
        ]);

        $items = $this->readData();

        $newItem = [
            'id' => $this->nextId($items),
            'nama_barang' => $request->nama_barang,
            'harga' => $request->harga
        ];

        $items[] = $newItem;
        $this->writeData($items);

        return response()->json($newItem, 201);
    }

    /**
     * @OA\Put(
     *     path="/items/{id}",
     *     tags={"Items"},
     *     summary="Update full item"
     * )
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $items = $this->readData();

        foreach ($items as &$item) {
            if ($item['id'] == $id) {
                $item['nama_barang'] = $request->nama_barang;
                $item['harga'] = $request->harga;

                $this->writeData($items);
                return response()->json($item);
            }
        }

        return response()->json(['message' => 'Item tidak ditemukan'], 404);
    }

    /**
     * @OA\Patch(
     *     path="/items/{id}",
     *     tags={"Items"},
     *     summary="Update partial item"
     * )
     */
    public function patch(Request $request, int $id): JsonResponse
    {
        $items = $this->readData();

        foreach ($items as &$item) {
            if ($item['id'] == $id) {

                if ($request->has('nama_barang')) {
                    $item['nama_barang'] = $request->nama_barang;
                }

                if ($request->has('harga')) {
                    $item['harga'] = $request->harga;
                }

                $this->writeData($items);
                return response()->json($item);
            }
        }

        return response()->json(['message' => 'Item tidak ditemukan'], 404);
    }

    /**
     * @OA\Delete(
     *     path="/items/{id}",
     *     tags={"Items"},
     *     summary="Delete item"
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        $items = $this->readData();

        foreach ($items as $i => $item) {
            if ($item['id'] == $id) {
                array_splice($items, $i, 1);
                $this->writeData($items);

                return response()->json(['message' => 'Deleted']);
            }
        }

        return response()->json(['message' => 'Item tidak ditemukan'], 404);
    }
}
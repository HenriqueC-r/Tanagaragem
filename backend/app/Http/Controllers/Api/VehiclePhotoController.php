<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehiclePhoto;
use App\Services\ImageProcessor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class VehiclePhotoController extends Controller
{
    private const MAX_PHOTOS = 10;

    public function store(Request $request, int $id, ImageProcessor $images): JsonResponse
    {
        $vehicle = $this->ownVehicle($request, $id);
        $this->ensureEditable($vehicle);

        $request->validate([
            'photos' => ['required', 'array', 'min:1', 'max:' . self::MAX_PHOTOS],
            'photos.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ]);

        $files = $request->file('photos');
        $existing = $vehicle->photos()->count();

        if ($existing + count($files) > self::MAX_PHOTOS) {
            throw ValidationException::withMessages([
                'photos' => 'Limite de ' . self::MAX_PHOTOS . " fotos por anúncio (este já tem {$existing}).",
            ]);
        }

        // Processa tudo ANTES de gravar: se alguma imagem estiver
        // corrompida, nada é salvo.
        $processed = array_map(fn ($file) => $images->toJpeg($file), $files);

        $position = $existing > 0 ? ((int) $vehicle->photos()->max('position')) + 1 : 0;

        foreach ($processed as $binary) {
            $path = "vehicles/{$vehicle->id}/" . Str::uuid() . '.jpg';
            Storage::disk('public')->put($path, $binary);
            $vehicle->photos()->create(['path' => $path, 'position' => $position++]);
        }

        return response()->json([
            'message' => 'Fotos enviadas.',
            'photos' => $vehicle->photos()->get(),
        ], 201);
    }

    public function destroy(Request $request, int $id, int $photoId): JsonResponse
    {
        $vehicle = $this->ownVehicle($request, $id);
        $this->ensureEditable($vehicle);
        $photo = $vehicle->photos()->findOrFail($photoId);

        if ($vehicle->status === Vehicle::STATUS_ACTIVE && $vehicle->photos()->count() <= 1) {
            throw ValidationException::withMessages([
                'photos' => 'Um anúncio ativo precisa de pelo menos 1 foto. Pause o anúncio antes de remover a última.',
            ]);
        }

        $path = $photo->path;

        DB::transaction(function () use ($vehicle, $photo) {
            $photo->delete();

            // Reorganiza as posições: a primeira vira a capa.
            $vehicle->photos()->get()->each(fn (VehiclePhoto $p, int $i) => $p->update(['position' => $i]));
        });

        Storage::disk('public')->delete($path);

        return response()->json([
            'message' => 'Foto removida.',
            'photos' => $vehicle->photos()->get(),
        ]);
    }

    /** Reordena as fotos. A primeira da lista vira a capa. */
    public function order(Request $request, int $id): JsonResponse
    {
        $vehicle = $this->ownVehicle($request, $id);
        $this->ensureEditable($vehicle);

        $data = $request->validate([
            'photo_ids' => ['required', 'array', 'min:1'],
            'photo_ids.*' => ['integer', 'distinct'],
        ]);

        $sent = array_map('intval', $data['photo_ids']);
        $existing = $vehicle->photos()->pluck('id')->all();

        if (count($sent) !== count($existing)
            || array_diff($sent, $existing)
            || array_diff($existing, $sent)) {
            throw ValidationException::withMessages([
                'photo_ids' => 'Envie todas as fotos do anúncio, sem repetir e sem incluir fotos de outro anúncio.',
            ]);
        }

        DB::transaction(function () use ($vehicle, $sent) {
            foreach ($sent as $position => $photoId) {
                VehiclePhoto::whereKey($photoId)
                    ->where('vehicle_id', $vehicle->id)
                    ->update(['position' => $position]);
            }
        });

        return response()->json([
            'message' => 'Ordem das fotos atualizada.',
            'photos' => $vehicle->photos()->get(),
        ]);
    }

    /** Busca a partir do usuário: anúncio de outra pessoa responde 404. */
    private function ownVehicle(Request $request, int $id): Vehicle
    {
        return $request->user()->vehicles()->findOrFail($id);
    }

    private function ensureEditable(Vehicle $vehicle): void
    {
        if ($vehicle->status === Vehicle::STATUS_SOLD) {
            throw ValidationException::withMessages([
                'status' => 'Anúncio vendido não pode ser editado.',
            ]);
        }
    }
}

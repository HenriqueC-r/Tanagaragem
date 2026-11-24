<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/** A página do anúncio: o card mais os detalhes e um resumo público do vendedor. */
class VehicleDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $this->user;
        $isStore = $user?->type === User::TYPE_STORE;

        return array_merge(
            (new VehicleCardResource($this->resource))->toArray($request),
            [
                'version' => $this->version,
                'color' => $this->color,
                'description' => $this->description,
                'photos' => $this->photos->map(fn ($photo) => [
                    'id' => $photo->id,
                    'position' => $photo->position,
                    'url' => $photo->url,
                ])->values(),
                // Loja mostra o nome da empresa; pessoa, só o primeiro nome.
                'seller' => [
                    'type' => $user?->type,
                    'name' => $isStore
                        ? $user->company_name
                        : Str::before(trim((string) $user?->name), ' '),
                ],
                'published_at' => $this->created_at,
            ]
        );
    }
}

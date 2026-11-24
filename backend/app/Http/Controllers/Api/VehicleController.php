<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\VehicleRequest;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class VehicleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $vehicles = $request->user()
            ->vehicles()
            ->with('photos')
            ->latest()
            ->paginate(20);

        return response()->json($vehicles);
    }

    public function store(VehicleRequest $request): JsonResponse
    {
        // Nasce como rascunho (padrão do banco). O user_id vem da relação.
        $vehicle = $request->user()->vehicles()->create($request->validated());

        return response()->json([
            'message' => 'Rascunho criado. Adicione fotos e publique quando estiver pronto.',
            'vehicle' => $vehicle->fresh('photos'),
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        return response()->json([
            'vehicle' => $this->ownVehicle($request, $id),
        ]);
    }

    public function update(VehicleRequest $request, int $id): JsonResponse
    {
        $vehicle = $this->ownVehicle($request, $id);

        if ($vehicle->status === Vehicle::STATUS_SOLD) {
            throw ValidationException::withMessages([
                'status' => 'Anúncio vendido não pode ser editado.',
            ]);
        }

        $vehicle->update($request->validated());

        return response()->json([
            'message' => 'Anúncio atualizado.',
            'vehicle' => $vehicle->fresh('photos'),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $vehicle = $this->ownVehicle($request, $id);

        // O banco apaga as linhas das fotos (cascade); os ARQUIVOS apagamos aqui.
        $vehicle->delete();
        Storage::disk('public')->deleteDirectory("vehicles/{$vehicle->id}");

        return response()->json(['message' => 'Anúncio removido.']);
    }

    /**
     * Publicar (e reativar) é o ÚNICO lugar onde o limite do plano é checado.
     */
    public function publish(Request $request, int $id): JsonResponse
    {
        $vehicle = $this->ownVehicle($request, $id);

        if ($vehicle->status === Vehicle::STATUS_ACTIVE) {
            return response()->json([
                'message' => 'Este anúncio já está publicado.',
                'vehicle' => $vehicle,
            ]);
        }

        if ($vehicle->status === Vehicle::STATUS_SOLD) {
            throw ValidationException::withMessages([
                'status' => 'Anúncio vendido não pode ser publicado de novo.',
            ]);
        }

        if (! $vehicle->photos()->exists()) {
            throw ValidationException::withMessages([
                'photos' => 'Adicione pelo menos 1 foto antes de publicar.',
            ]);
        }

        DB::transaction(function () use ($request, $vehicle) {
            // Trava a linha do usuário: duas publicações ao mesmo tempo
            // não conseguem furar o limite.
            $user = User::whereKey($request->user()->id)->lockForUpdate()->first();

            if (! $user->canPublishVehicle()) {
                throw ValidationException::withMessages([
                    'limit' => $this->limitMessage($user),
                ]);
            }

            $vehicle->status = Vehicle::STATUS_ACTIVE;
            $vehicle->save();
        });

        return response()->json([
            'message' => 'Anúncio publicado!',
            'vehicle' => $vehicle->fresh('photos'),
        ]);
    }

    public function pause(Request $request, int $id): JsonResponse
    {
        return $this->transition(
            $request, $id,
            [Vehicle::STATUS_ACTIVE], Vehicle::STATUS_PAUSED,
            'Anúncio pausado.'
        );
    }

    public function sold(Request $request, int $id): JsonResponse
    {
        return $this->transition(
            $request, $id,
            [Vehicle::STATUS_ACTIVE, Vehicle::STATUS_PAUSED], Vehicle::STATUS_SOLD,
            'Anúncio marcado como vendido.'
        );
    }

    /** Busca a partir do usuário: anúncio de outra pessoa responde 404. */
    private function ownVehicle(Request $request, int $id): Vehicle
    {
        return $request->user()->vehicles()->with('photos')->findOrFail($id);
    }

    private function transition(Request $request, int $id, array $from, string $to, string $message): JsonResponse
    {
        $vehicle = $this->ownVehicle($request, $id);

        if (! in_array($vehicle->status, $from, true)) {
            throw ValidationException::withMessages([
                'status' => "Não é possível fazer isso agora (estado atual: {$vehicle->status}).",
            ]);
        }

        $vehicle->status = $to;
        $vehicle->save();

        return response()->json([
            'message' => $message,
            'vehicle' => $vehicle,
        ]);
    }

    private function limitMessage(User $user): string
    {
        $plan = $user->activePlan();

        if (! $plan) {
            return 'Sua loja ainda está em análise. Você poderá publicar assim que o CNPJ for aprovado.';
        }

        return "Você atingiu o limite de {$plan->vehicle_limit} anúncio(s) ativo(s) do plano {$plan->name}. "
            . 'Pause ou venda um anúncio, ou contrate um plano maior.';
    }
}

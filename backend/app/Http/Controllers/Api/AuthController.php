<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Services\CnpjLookup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request, CnpjLookup $lookup): JsonResponse
    {
        $data = $request->validated();
        $isStore = $data['type'] === User::TYPE_STORE;
        $approved = false;

        if ($isStore) {
            try {
                $company = $lookup->find($data['cnpj']);
            } catch (\Throwable $e) {
                // BrasilAPI fora do ar ou limitando: não bloqueia o cadastro,
                // a conta fica pendente e a análise acontece depois.
                report($e);
                $company = false;
            }

            if ($company === null) {
                throw ValidationException::withMessages([
                    'cnpj' => 'CNPJ não encontrado na Receita Federal.',
                ]);
            }

            if ($company !== false && ! $company['is_active']) {
                throw ValidationException::withMessages([
                    'cnpj' => 'Este CNPJ não está ativo na Receita Federal.',
                ]);
            }

            // Aprovação automática: CNPJ ativo + nome da pessoa confere
            // com a razão social (MEI/empresário individual) ou com os sócios.
            $approved = $company !== false
                && $lookup->nameMatches($company, $data['name']);
        }

        $user = User::create($data);

        if ($approved) {
            // cnpj_verified_at está fora do fillable de propósito:
            // só o nosso código pode aprovar, nunca o formulário.
            $user->forceFill(['cnpj_verified_at' => now()])->save();
        }

        $message = match (true) {
            ! $isStore => 'Conta criada com sucesso!',
            $approved => 'Cadastro aprovado! Sua loja já pode anunciar até 5 veículos grátis.',
            default => 'Cadastro recebido! Sua loja será liberada após a análise do CNPJ.',
        };

        return response()->json([
            'message' => $message,
            'store_status' => $isStore ? ($approved ? 'approved' : 'pending') : null,
            'user' => $user,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        // Mesma mensagem para e-mail inexistente e senha errada,
        // para ninguém descobrir quais e-mails têm conta.
        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'E-mail ou senha incorretos.',
            ]);
        }

        return response()->json([
            'token' => $user->createToken('web')->plainTextToken,
            'user' => $user,
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $plan = $user->activePlan();

        return response()->json([
            'user' => $user,
            'store_status' => $user->type === User::TYPE_STORE
                ? ($user->cnpj_verified_at ? 'approved' : 'pending')
                : null,
            'plan' => $plan?->only(['name', 'slug', 'vehicle_limit']),
            'active_vehicles' => $user->activeVehiclesCount(),
            'can_publish' => $user->canPublishVehicle(),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sessão encerrada.']);
    }
}

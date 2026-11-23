<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Services\CnpjLookup;
use Illuminate\Http\JsonResponse;
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
}

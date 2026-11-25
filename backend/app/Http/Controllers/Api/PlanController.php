<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlanController extends Controller
{
    /** Planos à venda, para a página de preços. ?for=person ou ?for=store filtra. */
    public function index(Request $request): JsonResponse
    {
        $request->validate(['for' => ['nullable', Rule::in(['person', 'store'])]]);

        $plans = Plan::where('is_active', true)
            ->when($request->input('for'), fn ($q, $type) => $q->whereIn('for_type', [$type, 'any']))
            ->orderBy('for_type')
            ->orderBy('vehicle_limit')
            ->orderBy('price_cents')
            ->get(['slug', 'name', 'tier', 'for_type', 'price_cents', 'vehicle_limit', 'featured_limit']);

        return response()->json(['data' => $plans]);
    }
}

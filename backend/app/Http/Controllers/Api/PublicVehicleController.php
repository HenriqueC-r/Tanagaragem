<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VehicleCardResource;
use App\Http\Resources\VehicleDetailResource;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PublicVehicleController extends Controller
{
    private const SORTS = ['recent', 'price_asc', 'price_desc', 'year_desc', 'mileage_asc'];

    public function index(Request $request)
    {
        $f = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', Rule::in(Vehicle::CATEGORIES)],
            'brand' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'alpha', 'size:2'],
            'min_price' => ['nullable', 'integer', 'min:0'],
            'max_price' => ['nullable', 'integer', 'min:0'],
            'min_year' => ['nullable', 'integer', 'between:1900,2100'],
            'max_year' => ['nullable', 'integer', 'between:1900,2100'],
            'fuel' => ['nullable', Rule::in(Vehicle::FUELS)],
            'transmission' => ['nullable', Rule::in(Vehicle::TRANSMISSIONS)],
            'seller_type' => ['nullable', Rule::in([User::TYPE_PERSON, User::TYPE_STORE])],
            'single_owner' => ['nullable', 'boolean'],
            'sort' => ['nullable', Rule::in(self::SORTS)],
            'per_page' => ['nullable', 'integer', 'between:1,48'],
        ]);

        [$minPrice, $maxPrice] = $this->ordered($f['min_price'] ?? null, $f['max_price'] ?? null);
        [$minYear, $maxYear] = $this->ordered($f['min_year'] ?? null, $f['max_year'] ?? null);

        $query = Vehicle::active()->with(['cover', 'user:id,type']);

        // Cada palavra da busca precisa aparecer em marca, modelo ou versão.
        foreach ($this->words($f['q'] ?? '') as $word) {
            $like = '%' . addcslashes($word, '%_\\') . '%';

            $query->where(function (Builder $w) use ($like) {
                $w->where('brand', 'like', $like)
                    ->orWhere('model', 'like', $like)
                    ->orWhere('version', 'like', $like);
            });
        }

        $query
            ->when($f['category'] ?? null, fn ($q, $v) => $q->where('category', $v))
            ->when($f['brand'] ?? null, fn ($q, $v) => $q->where('brand', $v))
            ->when($f['model'] ?? null, fn ($q, $v) => $q->where('model', $v))
            ->when($f['city'] ?? null, fn ($q, $v) => $q->where('city', $v))
            ->when($f['state'] ?? null, fn ($q, $v) => $q->where('state', strtoupper($v)))
            ->when($minPrice, fn ($q, $v) => $q->where('price_cents', '>=', $v))
            ->when($maxPrice, fn ($q, $v) => $q->where('price_cents', '<=', $v))
            ->when($minYear, fn ($q, $v) => $q->where('year_model', '>=', $v))
            ->when($maxYear, fn ($q, $v) => $q->where('year_model', '<=', $v))
            ->when($f['fuel'] ?? null, fn ($q, $v) => $q->where('fuel', $v))
            ->when($f['transmission'] ?? null, fn ($q, $v) => $q->where('transmission', $v))
            ->when($f['seller_type'] ?? null, fn ($q, $v) => $q->whereHas('user', fn ($u) => $u->where('type', $v)))
            ->when($request->boolean('single_owner'), fn ($q) => $q->where('single_owner', true));

        match ($f['sort'] ?? 'recent') {
            'price_asc' => $query->orderBy('price_cents'),
            'price_desc' => $query->orderByDesc('price_cents'),
            'year_desc' => $query->orderByDesc('year_model'),
            'mileage_asc' => $query->orderBy('mileage'),
            default => $query->orderByDesc('created_at'),
        };

        // Desempate estável, para a paginação não repetir nem pular itens.
        $query->orderByDesc('id');

        $vehicles = $query->paginate((int) ($f['per_page'] ?? 20))->withQueryString();

        return VehicleCardResource::collection($vehicles);
    }

    /** "Destaques da Semana": primeiro os destacados, depois os mais novos. */
    public function featured(Request $request)
    {
        $request->validate(['limit' => ['nullable', 'integer', 'between:1,24']]);
        $limit = (int) $request->input('limit', 8);

        $featured = Vehicle::active()
            ->where('featured_until', '>', now())
            ->with(['cover', 'user:id,type'])
            ->orderByDesc('featured_until')
            ->limit($limit)
            ->get();

        $missing = $limit - $featured->count();

        if ($missing > 0) {
            $fill = Vehicle::active()
                ->whereNotIn('id', $featured->pluck('id'))
                ->with(['cover', 'user:id,type'])
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->limit($missing)
                ->get();

            $featured = $featured->concat($fill);
        }

        return VehicleCardResource::collection($featured);
    }

    /** Para as "Categorias Populares" da home: cada categoria com a contagem. */
    public function categories(): JsonResponse
    {
        $counts = Vehicle::active()
            ->selectRaw('category, count(*) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        return response()->json([
            'data' => collect(Vehicle::CATEGORIES)->map(fn (string $key) => [
                'key' => $key,
                'total' => (int) ($counts[$key] ?? 0),
            ])->values(),
        ]);
    }

    public function show(int $id)
    {
        // Só anúncios ativos são públicos: rascunho, pausado e vendido dão 404.
        $vehicle = Vehicle::active()
            ->with(['photos', 'cover', 'user:id,name,type,company_name'])
            ->findOrFail($id);

        return new VehicleDetailResource($vehicle);
    }

    /** Até 5 palavras, ignorando espaços extras. */
    private function words(string $text): array
    {
        return array_slice(preg_split('/\s+/', trim($text), -1, PREG_SPLIT_NO_EMPTY), 0, 5);
    }

    /** Se vier mínimo maior que máximo, inverte em vez de devolver lista vazia. */
    private function ordered(mixed $a, mixed $b): array
    {
        $a = $a === null ? null : (int) $a;
        $b = $b === null ? null : (int) $b;

        if ($a !== null && $b !== null && $a > $b) {
            return [$b, $a];
        }

        return [$a, $b];
    }
}

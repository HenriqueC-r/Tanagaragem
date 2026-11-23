<?php

namespace App\Services;

use App\Support\Document;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CnpjLookup
{
    /**
     * Consulta o CNPJ na BrasilAPI.
     * - devolve um resumo se encontrou;
     * - devolve null se o CNPJ não existe;
     * - lança exceção se a API estiver fora do ar ou limitando acessos
     *   (quem chama decide o que fazer, por exemplo deixar a conta pendente).
     */
    public function find(string $cnpj): ?array
    {
        $cnpj = Document::cnpj($cnpj);

        $response = Http::timeout(10)
            ->acceptJson()
            ->get("https://brasilapi.com.br/api/cnpj/v1/{$cnpj}");

        if (in_array($response->status(), [400, 404], true)) {
            return null;
        }

        $response->throw();
        $data = $response->json();

        return [
            'cnpj' => $cnpj,
            'legal_name' => $data['razao_social'] ?? null,
            'status' => $data['descricao_situacao_cadastral'] ?? null,
            'is_active' => ($data['descricao_situacao_cadastral'] ?? null) === 'ATIVA',
            'is_mei' => (bool) ($data['opcao_pelo_mei'] ?? false),
            'legal_nature' => $data['natureza_juridica'] ?? null,
            'main_activity' => $data['cnae_fiscal_descricao'] ?? null,
            'partners' => collect($data['qsa'] ?? [])
                ->pluck('nome_socio')->filter()->values()->all(),
        ];
    }

    /**
     * Sugestão de vínculo: o nome da pessoa aparece na razão social
     * (caso de MEI e empresário individual) ou entre os sócios?
     * É só uma PISTA, não uma prova: quem aprova é uma pessoa.
     */
    public function nameMatches(array $lookup, string $personName): bool
    {
        $mine = explode(' ', $this->normalize($personName));

        if (count($mine) < 2) {
            return false;
        }

        $candidates = array_merge([$lookup['legal_name'] ?? ''], $lookup['partners'] ?? []);

        foreach ($candidates as $candidate) {
            $words = explode(' ', $this->normalize((string) $candidate));

            if (count(array_diff($mine, $words)) === 0) {
                return true;
            }
        }

        return false;
    }

    /** Maiúsculas, sem acento, sem números e sem pontuação. */
    private function normalize(string $name): string
    {
        $name = Str::upper(Str::ascii($name));
        $name = preg_replace('/[^A-Z ]/', ' ', $name);

        return trim(preg_replace('/\s+/', ' ', $name));
    }
}

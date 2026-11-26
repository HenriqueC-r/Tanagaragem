// Textos em português e formatadores usados em várias telas.

export const CATEGORY_LABELS = {
  car: 'Carros',
  motorcycle: 'Motos',
  utility: 'Utilitários',
  van: 'Vans',
  truck: 'Caminhões',
}

export const CATEGORY_ICONS = {
  car: 'directions_car',
  motorcycle: 'two_wheeler',
  utility: 'commute',
  van: 'airport_shuttle',
  truck: 'local_shipping',
}

export const FUEL_LABELS = {
  gasoline: 'Gasolina',
  ethanol: 'Etanol',
  flex: 'Flex',
  diesel: 'Diesel',
  electric: 'Elétrico',
  hybrid: 'Híbrido',
  cng: 'GNV',
}

export const TRANSMISSION_LABELS = {
  manual: 'Manual',
  automatic: 'Automático',
  cvt: 'CVT',
  automated: 'Automatizado',
}

export const SELLER_LABELS = {
  person: 'Particular',
  store: 'Loja',
}

// Valores em centavos, como a API trabalha.
export const PRICE_RANGES = [
  { key: 'ate-30', label: 'Até R$ 30 mil', min: null, max: 3000000 },
  { key: '30-60', label: 'R$ 30 mil a R$ 60 mil', min: 3000000, max: 6000000 },
  { key: '60-100', label: 'R$ 60 mil a R$ 100 mil', min: 6000000, max: 10000000 },
  { key: 'acima-100', label: 'Acima de R$ 100 mil', min: 10000000, max: null },
]

/** Descobre qual faixa de preço está selecionada a partir do endereço (?min_price=...). */
export function priceKeyFromQuery(query = {}) {
  const found = PRICE_RANGES.find(
    (range) =>
      String(range.min ?? '') === String(query.min_price ?? '') &&
      String(range.max ?? '') === String(query.max_price ?? ''),
  )

  return found ? found.key : null
}

const money = new Intl.NumberFormat('pt-BR', {
  style: 'currency',
  currency: 'BRL',
  maximumFractionDigits: 0,
})

export function formatPrice(cents) {
  return money.format(cents / 100)
}

export function formatKm(km) {
  return new Intl.NumberFormat('pt-BR').format(km) + ' km'
}

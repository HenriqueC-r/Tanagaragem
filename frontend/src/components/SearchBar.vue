<template>
  <q-form class="bg-white rounded-borders q-pa-sm" @submit="submit">
    <div class="row q-col-gutter-sm items-center">
      <div class="col-12 col-md-4">
        <q-input v-model="form.q" dense borderless placeholder="Buscar por marca, modelo...">
          <template #prepend><q-icon name="search" /></template>
        </q-input>
      </div>

      <div class="col-6 col-md-2">
        <q-select
          v-model="form.category"
          dense
          borderless
          clearable
          emit-value
          map-options
          label="Tipo"
          :options="categoryOptions"
        />
      </div>

      <div class="col-6 col-md-2">
        <q-input v-model="form.city" dense borderless label="Cidade" />
      </div>

      <div class="col-12 col-md-2">
        <q-select
          v-model="form.price"
          dense
          borderless
          clearable
          emit-value
          map-options
          label="Faixa de preço"
          :options="priceOptions"
        />
      </div>

      <div class="col-12 col-md-2">
        <q-btn
          type="submit"
          unelevated
          no-caps
          color="primary"
          icon="search"
          label="Buscar"
          class="full-width"
        />
      </div>
    </div>
  </q-form>
</template>

<script setup>
import { reactive } from 'vue'
import { CATEGORY_LABELS, PRICE_RANGES, priceKeyFromQuery } from '../utils/labels.js'

// "initial" vem do endereço da página (?q=gol&category=car...), para a barra
// já abrir preenchida quando a pessoa chega por um link de busca.
const props = defineProps({
  initial: { type: Object, default: () => ({}) },
})

const emit = defineEmits(['search'])

const categoryOptions = Object.entries(CATEGORY_LABELS).map(([value, label]) => ({ value, label }))
const priceOptions = PRICE_RANGES.map((range) => ({ value: range.key, label: range.label }))

const form = reactive({
  q: props.initial.q || '',
  category: props.initial.category || null,
  city: props.initial.city || '',
  price: priceKeyFromQuery(props.initial),
})

function submit() {
  const params = {}

  if (form.q.trim()) params.q = form.q.trim()
  if (form.category) params.category = form.category
  if (form.city.trim()) params.city = form.city.trim()

  const range = PRICE_RANGES.find((item) => item.key === form.price)
  if (range?.min != null) params.min_price = range.min
  if (range?.max != null) params.max_price = range.max

  emit('search', params)
}
</script>

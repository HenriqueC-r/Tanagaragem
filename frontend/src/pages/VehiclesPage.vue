<template>
  <q-page class="q-py-lg">
    <div class="container q-px-md">
      <search-bar :initial="route.query" class="shadow-3 q-mb-lg" @search="search" />

      <div class="row items-center q-mb-md q-col-gutter-sm">
        <div class="col-12 col-sm">
          <div class="text-h6 text-weight-bold">{{ heading }}</div>
        </div>

        <div class="col-12 col-sm-auto">
          <q-select
            :model-value="sort"
            dense
            outlined
            emit-value
            map-options
            label="Ordenar por"
            style="min-width: 220px"
            :options="sortOptions"
            @update:model-value="changeSort"
          />
        </div>
      </div>

      <q-banner v-if="error" rounded class="bg-red-1 text-red-9 q-mb-md">{{ error }}</q-banner>

      <div v-if="loading" class="row q-col-gutter-md">
        <div v-for="n in 8" :key="n" class="col-12 col-sm-6 col-md-3">
          <q-skeleton height="300px" />
        </div>
      </div>

      <div v-else-if="vehicles.length" class="row q-col-gutter-md">
        <div v-for="vehicle in vehicles" :key="vehicle.id" class="col-12 col-sm-6 col-md-3">
          <vehicle-card :vehicle="vehicle" />
        </div>
      </div>

      <div v-else-if="!error" class="text-center text-grey-7 q-py-xl">
        <q-icon name="search_off" size="56px" />
        <div class="text-h6 q-mt-sm">Nenhum veículo encontrado</div>
        <div>Tente mudar ou remover alguns filtros.</div>
        <q-btn flat no-caps color="primary" label="Limpar busca" class="q-mt-md" to="/veiculos" />
      </div>

      <div v-if="lastPage > 1" class="flex flex-center q-mt-xl">
        <q-pagination
          :model-value="page"
          :max="lastPage"
          :max-pages="7"
          direction-links
          boundary-links
          color="primary"
          @update:model-value="changePage"
        />
      </div>
    </div>
  </q-page>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import SearchBar from '../components/SearchBar.vue'
import VehicleCard from '../components/VehicleCard.vue'
import { getVehicles } from '../api/index.js'

const route = useRoute()
const router = useRouter()

const PER_PAGE = 8

const vehicles = ref([])
const total = ref(0)
const lastPage = ref(1)
const loading = ref(true)
const error = ref('')

const sortOptions = [
  { value: 'recent', label: 'Mais recentes' },
  { value: 'price_asc', label: 'Menor preço' },
  { value: 'price_desc', label: 'Maior preço' },
  { value: 'year_desc', label: 'Mais novos' },
  { value: 'mileage_asc', label: 'Menor quilometragem' },
]

// A página e a ordenação moram no endereço, não em variáveis soltas.
const page = computed(() => Number(route.query.page) || 1)
const sort = computed(() => route.query.sort || 'recent')

const heading = computed(() => {
  if (loading.value) return 'Buscando veículos...'
  return total.value === 1 ? '1 veículo encontrado' : `${total.value} veículos encontrados`
})

// Se a pessoa mudar o filtro enquanto uma busca ainda está em andamento,
// só a resposta da busca mais recente vale.
let lastRequest = 0

async function load() {
  const current = ++lastRequest
  loading.value = true
  error.value = ''

  try {
    const response = await getVehicles({ ...route.query, per_page: PER_PAGE })

    if (current !== lastRequest) return

    vehicles.value = response.data
    total.value = response.meta.total
    lastPage.value = response.meta.last_page
  } catch (e) {
    if (current !== lastRequest) return

    vehicles.value = []
    total.value = 0
    lastPage.value = 1
    error.value =
      e.status === 422
        ? 'Alguma opção da busca é inválida.'
        : 'Não foi possível carregar os veículos. Tente novamente em instantes.'
  } finally {
    if (current === lastRequest) loading.value = false
  }
}

// Toda vez que o endereço muda (busca, página, ordenação), recarrega.
watch(() => route.query, load, { immediate: true })

function search(params) {
  const query = { ...params }
  if (route.query.sort) query.sort = route.query.sort
  router.push({ path: '/veiculos', query })
}

function changeSort(value) {
  router.push({ query: { ...route.query, sort: value, page: undefined } })
}

function changePage(value) {
  router.push({ query: { ...route.query, page: value } })
  window.scrollTo({ top: 0, behavior: 'smooth' })
}
</script>

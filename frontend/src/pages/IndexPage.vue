<template>
  <q-page>
    <section class="hero">
      <div class="container q-px-md">
        <h1 class="hero-title">
          Encontre seu<br />
          próximo <span class="text-primary">veículo.</span>
        </h1>
        <p class="hero-sub">Carros, motos e muito mais, de particulares e lojas, em um só lugar!</p>

        <search-bar class="shadow-6" @search="search" />
      </div>
    </section>

    <section class="container q-px-md q-py-xl">
      <div class="row items-center q-mb-md">
        <q-icon name="local_fire_department" color="primary" size="32px" class="q-mr-sm" />
        <h2 class="section-title">Destaques da Semana</h2>
        <q-space />
        <q-btn
          flat
          no-caps
          color="primary"
          label="Ver todos os veículos"
          icon-right="arrow_forward"
          to="/veiculos"
        />
      </div>

      <q-banner v-if="error" rounded class="bg-red-1 text-red-9">{{ error }}</q-banner>

      <div v-else-if="loading" class="row q-col-gutter-md">
        <div v-for="n in 4" :key="n" class="col-12 col-sm-6 col-md-3">
          <q-skeleton height="300px" />
        </div>
      </div>

      <div v-else class="row q-col-gutter-md">
        <div v-for="vehicle in featured" :key="vehicle.id" class="col-12 col-sm-6 col-md-3">
          <vehicle-card :vehicle="vehicle" />
        </div>
      </div>
    </section>

    <section v-if="categories.length" class="container q-px-md q-pb-xl">
      <h2 class="section-title q-mb-md">Categorias Populares</h2>

      <div class="row q-col-gutter-md">
        <div v-for="category in categories" :key="category.key" class="col-6 col-sm-4 col-md">
          <q-card
            flat
            bordered
            class="category-card cursor-pointer text-center q-pa-md"
            @click="goCategory(category.key)"
          >
            <q-icon :name="CATEGORY_ICONS[category.key]" size="36px" color="dark" />
            <div class="text-weight-medium q-mt-sm">{{ CATEGORY_LABELS[category.key] }}</div>
            <div class="text-caption text-grey-7">{{ category.total }} anúncio(s)</div>
          </q-card>
        </div>
      </div>
    </section>
  </q-page>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import SearchBar from '../components/SearchBar.vue'
import VehicleCard from '../components/VehicleCard.vue'
import { getCategories, getFeatured } from '../api'
import { CATEGORY_ICONS, CATEGORY_LABELS } from '../utils/labels.js'

const router = useRouter()

const featured = ref([])
const categories = ref([])
const loading = ref(true)
const error = ref('')

onMounted(async () => {
  try {
    // As duas chamadas saem ao mesmo tempo, e a tela espera pelas duas.
    const [featuredResponse, categoriesResponse] = await Promise.all([
      getFeatured(8),
      getCategories(),
    ])

    featured.value = featuredResponse.data
    categories.value = categoriesResponse.data
  } catch {
    error.value = 'Não foi possível carregar os veículos. Tente novamente em instantes.'
  } finally {
    loading.value = false
  }
})

function search(params) {
  router.push({ path: '/veiculos', query: params })
}

function goCategory(key) {
  router.push({ path: '/veiculos', query: { category: key } })
}
</script>

<style scoped>
.hero {
  background: linear-gradient(135deg, #0f1624 0%, #1b2438 55%, #3a1418 100%);
  color: #fff;
  padding: 56px 0 72px;
}

.hero-title {
  font-size: clamp(2.2rem, 5vw, 3.6rem);
  line-height: 1.05;
  font-weight: 800;
  margin: 0 0 12px;
}

.hero-sub {
  font-size: 1.1rem;
  opacity: 0.85;
  margin: 0 0 28px;
  max-width: 520px;
}

.section-title {
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0;
}

.category-card:hover {
  border-color: var(--q-primary);
}
</style>

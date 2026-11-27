<template>
  <q-page>
    <section class="hero">
      <div class="hero-image-blend" aria-hidden="true" />
      <div class="hero-content">
        <div class="hero-main">
          <div class="hero-copy">
            <h1 class="hero-title">
              Encontre seu<br />
              próximo <span class="text-primary">veículo.</span>
            </h1>
            <p class="hero-sub">
              Carros, motos e muito mais, de particulares e lojas, em um só lugar!
            </p>
          </div>

          <search-bar class="shadow-6" @search="search" />
        </div>

        <aside class="hero-ad-slot" aria-label="Espaço reservado para anúncio" />
      </div>
    </section>

    <section class="container q-px-md featured-section">
      <div class="row items-center q-mb-md featured-heading">
        <q-icon name="local_fire_department" color="primary" size="32px" class="q-mr-sm" />
        <h2 class="section-title">Destaques da Semana</h2>
        <q-space />
        <q-btn
          flat
          no-caps
          color="primary"
          class="featured-link"
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

    <section v-if="categories.length" class="container q-px-md categories-section">
      <h2 class="section-title q-mb-md">Categorias Populares</h2>

      <div class="category-grid">
        <div v-for="category in categories" :key="category.key" class="category-grid-item">
          <q-card
            flat
            bordered
            class="category-card cursor-pointer text-center"
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
  position: relative;
  isolation: isolate;
  overflow: hidden;
  background-color: #0f1624;
  color: #fff;
}

.hero::before {
  content: '';
  position: absolute;
  inset: 0;
  z-index: 0;
  pointer-events: none;
  background: url('../assets/corolla-hero.png') no-repeat calc(100% + 24px) 85% / auto 150%;
  mask-image: linear-gradient(90deg, transparent calc(100% - 1150px), #000 calc(100% - 800px));
}

.hero::after {
  content: '';
  position: absolute;
  inset: 0;
  z-index: 0;
  pointer-events: none;
  background: linear-gradient(
    90deg,
    #0f1624 0%,
    rgba(15, 22, 36, 0.96) 28%,
    rgba(15, 22, 36, 0.68) 44%,
    rgba(15, 22, 36, 0.12) 68%,
    transparent 82%
  );
}

.hero-image-blend {
  position: absolute;
  inset: 0;
  z-index: 1;
  pointer-events: none;
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  mask-image: linear-gradient(
    90deg,
    transparent calc(100% - 1150px),
    rgba(0, 0, 0, 0.75) calc(100% - 1050px),
    #000 calc(100% - 980px),
    transparent calc(100% - 800px)
  );
}

.hero-content {
  position: relative;
  z-index: 2;
  width: 100%;
  max-width: none;
  margin: 0;
  display: grid;
  grid-template-columns: minmax(0, 1fr) clamp(190px, 20vw, 230px);
  gap: 20px;
  align-items: stretch;
  padding-left: max(16px, calc((100vw - 1200px) / 2 + 16px));
  padding-right: 0;
  padding-top: 16px;
  padding-bottom: 16px;
}

.hero-main {
  position: relative;
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 16px;
  min-width: 0;
  padding: 8px 0;
}

.hero-main > * {
  position: relative;
  z-index: 1;
}

.hero-ad-slot {
  min-height: 225px;
}

.hero-copy {
  position: relative;
  max-width: 560px;
}

.hero-title {
  font-size: clamp(2rem, 4vw, 2.7rem);
  line-height: 1.05;
  font-weight: 800;
  margin: 0 0 8px;
}

.hero-sub {
  font-size: 1rem;
  opacity: 0.85;
  margin: 8px 0 0;
  max-width: 440px;
}

.hero :deep(.q-form) {
  width: 100%;
}

.section-title {
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0;
}

.featured-section {
  padding-top: 48px;
  padding-bottom: 48px;
}

.categories-section {
  padding-bottom: 48px;
}

.category-card:hover {
  border-color: var(--q-primary);
  background-color: #fff8f8;
  transform: translateY(-2px);
}

.category-grid {
  display: grid;
  grid-template-columns: repeat(5, minmax(0, 1fr));
  gap: 12px;
}

.category-grid-item {
  min-width: 0;
}

.category-card {
  min-height: 112px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 4px;
  padding: 14px 8px;
  border-radius: 10px;
  background: #fff;
  transition:
    background-color 160ms ease,
    border-color 160ms ease,
    transform 160ms ease;
}

@media (max-width: 760px) {
  .hero::before {
    background-position: 40% -75px;
    background-size: auto 80%;
    mask-image: none;
  }

  .hero::after {
    background: linear-gradient(
      90deg,
      #0f1624 0%,
      rgba(15, 22, 36, 0.9) 38%,
      rgba(15, 22, 36, 0.35) 68%,
      rgba(15, 22, 36, 0.12) 100%
    );
  }

  .hero-image-blend {
    display: none;
  }

  .hero-content {
    display: block;
    padding-left: 16px;
    padding-right: 16px;
    padding-top: 24px;
    padding-bottom: 28px;
  }

  .hero-main {
    gap: 80px;
    min-height: 290px;
    padding: 8px 0;
  }

  .hero-ad-slot {
    display: none;
  }

  .hero-title {
    font-size: clamp(1.9rem, 8vw, 2.5rem);
  }

  .featured-section {
    padding-top: 32px;
    padding-bottom: 32px;
  }

  .categories-section {
    padding-bottom: 32px;
  }

  .category-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
  }
}

@media (max-width: 480px) {
  .hero-content {
    padding-top: 18px;
    padding-bottom: 20px;
  }

  .hero-main {
    min-height: 0;
  }

  .hero-title {
    font-size: 2rem;
  }

  .hero-sub {
    font-size: 0.95rem;
  }

  .featured-heading {
    row-gap: 8px;
  }

  .section-title {
    font-size: 1.25rem;
  }

  .featured-link {
    margin-left: auto;
  }

  .category-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .category-grid-item:last-child:nth-child(odd) {
    grid-column: 1 / -1;
    display: flex;
    justify-content: center;
  }

  .category-grid-item:last-child:nth-child(odd) .category-card {
    width: min(100%, 170px);
  }
}
</style>

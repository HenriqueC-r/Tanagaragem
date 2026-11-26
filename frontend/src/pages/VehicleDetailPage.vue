<template>
  <q-page class="q-py-lg">
    <div class="container q-px-md">
      <q-btn
        flat
        no-caps
        color="primary"
        icon="arrow_back"
        label="Voltar aos veículos"
        to="/veiculos"
        class="q-mb-md"
      />

      <div v-if="loading" class="row q-col-gutter-lg">
        <div class="col-12 col-md-8"><q-skeleton height="440px" /></div>
        <div class="col-12 col-md-4"><q-skeleton height="300px" /></div>
      </div>

      <div v-else-if="notFound" class="text-center text-grey-7 q-py-xl">
        <q-icon name="search_off" size="56px" />
        <div class="text-h6 q-mt-sm">Anúncio não encontrado</div>
        <div>Ele pode ter sido vendido, pausado ou removido.</div>
        <q-btn
          flat
          no-caps
          color="primary"
          label="Ver outros veículos"
          to="/veiculos"
          class="q-mt-md"
        />
      </div>

      <q-banner v-else-if="error" rounded class="bg-red-1 text-red-9">{{ error }}</q-banner>

      <div v-else-if="vehicle" class="row q-col-gutter-lg">
        <div class="col-12 col-md-8">
          <q-carousel
            v-if="vehicle.photos.length"
            v-model="slide"
            swipeable
            animated
            arrows
            infinite
            thumbnails
            :height="carouselHeight"
            class="rounded-borders"
          >
            <q-carousel-slide
              v-for="photo in vehicle.photos"
              :key="photo.id"
              :name="photo.id"
              :img-src="photo.url"
            />
          </q-carousel>

          <div v-else class="no-photo flex flex-center rounded-borders">
            <q-icon name="photo_camera" size="64px" color="grey-5" />
          </div>

          <h2 class="section-title q-mt-lg q-mb-md">Informações</h2>
          <div class="specs">
            <div v-for="spec in specs" :key="spec.label">
              <div class="spec-label">{{ spec.label }}</div>
              <div class="spec-value">{{ spec.value }}</div>
            </div>
          </div>

          <template v-if="vehicle.description">
            <h2 class="section-title q-mt-lg q-mb-md">Descrição</h2>
            <p class="description">{{ vehicle.description }}</p>
          </template>
        </div>

        <div class="col-12 col-md-4">
          <q-card flat bordered class="sidebar q-pa-md">
            <q-badge v-if="vehicle.featured" color="primary" class="q-mb-sm">
              <q-icon name="star" size="14px" class="q-mr-xs" />
              Destaque
            </q-badge>

            <h1 class="title">{{ vehicle.title }}</h1>

            <div class="text-grey-7">
              <q-icon name="place" size="16px" />
              {{ vehicle.city }} - {{ vehicle.state }}
            </div>

            <div class="text-h4 text-weight-bold text-positive q-my-md">
              {{ formatPrice(vehicle.price_cents) }}
            </div>

            <div class="q-gutter-xs">
              <q-chip dense square icon="calendar_today">{{ vehicle.year_model }}</q-chip>
              <q-chip dense square icon="speed">{{ formatKm(vehicle.mileage) }}</q-chip>
              <q-chip dense square icon="settings">
                {{ TRANSMISSION_LABELS[vehicle.transmission] }}
              </q-chip>
              <q-chip
                v-if="vehicle.single_owner"
                dense
                square
                icon="check"
                color="green-1"
                text-color="green-9"
              >
                Único dono
              </q-chip>
            </div>

            <q-separator class="q-my-md" />

            <div class="row items-center no-wrap">
              <q-avatar color="grey-3" text-color="grey-8" icon="storefront" size="40px" />
              <div class="q-ml-sm">
                <div class="text-weight-bold">{{ vehicle.seller.name }}</div>
                <div class="text-caption text-grey-7">
                  {{ SELLER_LABELS[vehicle.seller.type] }} · publicado em {{ publishedAt }}
                </div>
              </div>
            </div>

            <!-- Espaço reservado: o contato com o vendedor ainda será definido. -->
            <q-btn
              disable
              unelevated
              no-caps
              color="primary"
              icon="chat"
              label="Falar com o vendedor (em breve)"
              class="full-width q-mt-md"
            />
          </q-card>
        </div>
      </div>
    </div>
  </q-page>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useQuasar } from 'quasar'
import { useRoute } from 'vue-router'
import { getVehicle } from '../api'
import {
  CATEGORY_LABELS,
  FUEL_LABELS,
  SELLER_LABELS,
  TRANSMISSION_LABELS,
  formatKm,
  formatPrice,
} from '../utils/labels'

const $q = useQuasar()
const route = useRoute()

const vehicle = ref(null)
const slide = ref(null)
const loading = ref(true)
const notFound = ref(false)
const error = ref('')

const carouselHeight = computed(() => ($q.screen.lt.sm ? '260px' : '440px'))

const publishedAt = computed(() =>
  vehicle.value ? new Date(vehicle.value.published_at).toLocaleDateString('pt-BR') : '',
)

const specs = computed(() => {
  const v = vehicle.value
  if (!v) return []

  return [
    {
      label: 'Ano',
      value:
        v.year_manufacture === v.year_model
          ? String(v.year_model)
          : `${v.year_manufacture}/${v.year_model}`,
    },
    { label: 'Quilometragem', value: formatKm(v.mileage) },
    { label: 'Combustível', value: FUEL_LABELS[v.fuel] || v.fuel },
    { label: 'Câmbio', value: TRANSMISSION_LABELS[v.transmission] || v.transmission },
    { label: 'Cor', value: v.color || 'Não informada' },
    { label: 'Tipo', value: CATEGORY_LABELS[v.category] || v.category },
    { label: 'Único dono', value: v.single_owner ? 'Sim' : 'Não' },
  ]
})

// Se a pessoa trocar de anúncio enquanto outro ainda carrega,
// só a resposta mais recente vale.
let lastRequest = 0

async function load() {
  const current = ++lastRequest
  loading.value = true
  notFound.value = false
  error.value = ''
  vehicle.value = null

  try {
    const response = await getVehicle(route.params.id)

    if (current !== lastRequest) return

    vehicle.value = response.data
    slide.value = response.data.photos[0]?.id ?? null
    document.title = `${response.data.title} | Tá na Garagem`
  } catch (e) {
    if (current !== lastRequest) return

    if (e.status === 404) {
      notFound.value = true
    } else {
      error.value = 'Não foi possível carregar o anúncio. Tente novamente em instantes.'
    }
  } finally {
    if (current === lastRequest) loading.value = false
  }
}

watch(() => route.params.id, load, { immediate: true })
</script>

<style scoped>
.title {
  font-size: 1.6rem;
  font-weight: 800;
  line-height: 1.2;
  margin: 0 0 8px;
}

.section-title {
  font-size: 1.3rem;
  font-weight: 700;
}

.specs {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 16px;
}

.spec-label {
  font-size: 0.8rem;
  color: #757575;
}

.spec-value {
  font-weight: 600;
}

.description {
  white-space: pre-line;
  margin: 0;
}

.no-photo {
  aspect-ratio: 16 / 9;
  background: #f1f1f1;
}

@media (min-width: 1024px) {
  .sidebar {
    position: sticky;
    top: 90px;
  }
}
</style>

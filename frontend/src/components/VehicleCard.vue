<template>
  <q-card
    flat
    bordered
    class="vehicle-card cursor-pointer"
    tabindex="0"
    @click="open"
    @keyup.enter="open"
  >
    <div class="photo">
      <q-img v-if="vehicle.cover_url" :src="vehicle.cover_url" :ratio="4 / 3" />
      <div v-else class="no-photo flex flex-center">
        <q-icon name="photo_camera" size="48px" color="grey-5" />
      </div>

      <q-badge v-if="vehicle.featured" color="primary" class="featured-badge">
        <q-icon name="star" size="14px" class="q-mr-xs" />
        Destaque
      </q-badge>
    </div>

    <q-card-section>
      <div class="text-subtitle1 text-weight-bold ellipsis">{{ vehicle.title }}</div>

      <div class="text-caption text-grey-7">
        <q-icon name="place" size="14px" />
        {{ vehicle.city }} - {{ vehicle.state }}
        <q-icon name="calendar_today" size="14px" class="q-ml-sm" />
        {{ vehicle.year_model }}
        <q-icon name="speed" size="14px" class="q-ml-sm" />
        {{ formatKm(vehicle.mileage) }}
      </div>

      <div class="row items-center justify-between q-mt-sm">
        <div class="text-h6 text-weight-bold text-positive">
          {{ formatPrice(vehicle.price_cents) }}
        </div>

        <q-chip v-if="tag" dense square size="sm" color="green-1" text-color="green-9" icon="check">
          {{ tag }}
        </q-chip>
      </div>
    </q-card-section>
  </q-card>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { SELLER_LABELS, formatKm, formatPrice } from '../utils/labels.js'

const props = defineProps({
  vehicle: { type: Object, required: true },
})

const router = useRouter()

// Um selo só por card, do mais especial para o mais comum.
const tag = computed(() => {
  if (props.vehicle.single_owner) return 'Único dono'
  if (props.vehicle.transmission === 'automatic') return 'Automático'
  return SELLER_LABELS[props.vehicle.seller_type] || ''
})

function open() {
  router.push(`/veiculos/${props.vehicle.id}`)
}
</script>

<style scoped>
.vehicle-card {
  transition: box-shadow 0.2s;
}

.vehicle-card:hover,
.vehicle-card:focus {
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.14);
  outline: none;
}

.photo {
  position: relative;
}

.featured-badge {
  position: absolute;
  top: 8px;
  left: 8px;
  z-index: 1;
}

.no-photo {
  aspect-ratio: 4 / 3;
  background: #f1f1f1;
}
</style>

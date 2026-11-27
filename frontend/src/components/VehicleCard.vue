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

      <div class="vehicle-meta text-caption text-grey-7">
        <span class="ellipsis">
          <q-icon name="place" size="14px" />
          {{ vehicle.city }} - {{ vehicle.state }}
        </span>
        <span>
          <q-icon name="calendar_today" size="14px" />
          {{ vehicle.year_model }}
        </span>
        <span>
          <q-icon name="speed" size="14px" />
          {{ formatKm(vehicle.mileage) }}
        </span>
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

@media (max-width: 600px) {
  .vehicle-card {
    display: grid;
    grid-template-columns: minmax(110px, 38%) minmax(0, 1fr);
    min-height: 132px;
  }

  .photo {
    min-height: 100%;
    overflow: hidden;
  }

  .photo :deep(.q-img) {
    height: 100%;
  }

  .photo :deep(.q-img__container) {
    height: 100%;
    padding-bottom: 0 !important;
  }

  .no-photo {
    height: 100%;
    min-height: 132px;
    aspect-ratio: auto;
  }

  .featured-badge {
    top: 6px;
    left: 6px;
    font-size: 0.65rem;
  }

  .featured-badge :deep(.q-icon) {
    font-size: 12px;
  }

  .vehicle-card :deep(.q-card__section) {
    min-width: 0;
    padding: 10px;
    display: flex;
    flex-direction: column;
    justify-content: center;
  }

  .vehicle-card .text-subtitle1 {
    font-size: 0.95rem;
    line-height: 1.2;
  }

  .vehicle-meta {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    gap: 2px 8px;
    margin-top: 5px;
    line-height: 1.25;
  }

  .vehicle-meta > span {
    min-width: 0;
    white-space: nowrap;
  }

  .vehicle-card :deep(.q-mt-sm) {
    margin-top: 5px;
  }

  .vehicle-card .text-h6 {
    font-size: 1.05rem;
    line-height: 1.2;
  }

  .vehicle-card :deep(.q-chip) {
    margin: 0 0 0 6px;
    font-size: 0.65rem;
  }
}
</style>

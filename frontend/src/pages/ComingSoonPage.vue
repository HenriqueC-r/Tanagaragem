<template>
  <q-page class="flex flex-center q-pa-md">
    <div class="text-center">
      <q-icon name="construction" size="56px" color="primary" />
      <h1 class="text-h4 text-weight-bold q-mt-md q-mb-sm">{{ title }}</h1>
      <p class="text-body1 text-grey-7">{{ description }}</p>
      <q-btn color="primary" no-caps label="Voltar ao início" to="/" />
    </div>
  </q-page>
</template>

<script setup>
defineProps({
  title: { type: String, required: true },
  description: { type: String, required: true },
})
</script>

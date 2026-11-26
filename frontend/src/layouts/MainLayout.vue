<template>
  <q-layout view="hHh lpR fff">
    <q-header elevated class="bg-dark text-white">
      <q-toolbar class="container q-py-sm">
        <q-btn
          flat
          dense
          round
          icon="menu"
          class="lt-md q-mr-sm"
          aria-label="Abrir menu"
          @click="drawer = !drawer"
        />

        <router-link to="/" class="brand"
          >Tá na <span class="text-primary">Garagem</span></router-link
        >

        <div class="gt-sm q-ml-xl row q-gutter-sm">
          <q-btn
            v-for="link in links"
            :key="link.to"
            flat
            no-caps
            exact
            :label="link.label"
            :to="link.to"
          />
        </div>

        <q-space />

        <q-btn flat no-caps icon="person" label="Entrar" to="/entrar" class="gt-xs q-mr-sm" />
        <q-btn
          unelevated
          no-caps
          color="primary"
          icon="add"
          :label="$q.screen.lt.sm ? 'Anunciar' : 'Anunciar Agora'"
          to="/anunciar"
        />
      </q-toolbar>
    </q-header>

    <q-drawer
      v-model="drawer"
      overlay
      behavior="mobile"
      bordered
      :width="260"
      class="bg-dark text-white"
    >
      <q-list padding>
        <q-item
          v-for="link in links"
          :key="link.to"
          v-ripple
          clickable
          exact
          :to="link.to"
          @click="drawer = false"
        >
          <q-item-section avatar><q-icon :name="link.icon" /></q-item-section>
          <q-item-section>{{ link.label }}</q-item-section>
        </q-item>

        <q-item v-ripple clickable to="/entrar" @click="drawer = false">
          <q-item-section avatar><q-icon name="person" /></q-item-section>
          <q-item-section>Entrar</q-item-section>
        </q-item>
      </q-list>
    </q-drawer>

    <q-page-container>
      <router-view />
    </q-page-container>

    <q-footer class="bg-dark text-grey-5 text-center q-pa-md"> © 2026 Tá na Garagem </q-footer>
  </q-layout>
</template>

<script setup>
import { ref } from 'vue'

const drawer = ref(false)

const links = [
  { label: 'Início', to: '/', icon: 'home' },
  { label: 'Veículos', to: '/veiculos', icon: 'directions_car' },
]
</script>

<style scoped>
.brand {
  color: #fff;
  font-size: 1.5rem;
  font-weight: 800;
  letter-spacing: -0.5px;
  text-decoration: none;
}
</style>

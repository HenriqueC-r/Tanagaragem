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

        <q-btn
          v-if="!isAuthenticated"
          flat
          no-caps
          icon="person"
          label="Entrar"
          to="/entrar"
          class="gt-xs q-mr-sm"
        />
        <q-btn
          v-else
          flat
          round
          dense
          icon="account_circle"
          aria-label="Menu da conta"
          class="q-mr-sm"
        >
          <q-menu>
            <q-list style="min-width: 190px">
              <q-item-label header>{{ user?.name || 'Minha conta' }}</q-item-label>
              <q-separator />
              <q-item v-close-popup clickable @click="handleLogout">
                <q-item-section avatar><q-icon name="logout" /></q-item-section>
                <q-item-section>Sair</q-item-section>
              </q-item>
            </q-list>
          </q-menu>
        </q-btn>
        <q-btn
          v-if="!isAuthenticated"
          flat
          no-caps
          label="Cadastrar"
          to="/cadastrar"
          class="gt-xs q-mr-sm"
        />
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

        <q-item
          v-if="!isAuthenticated"
          v-ripple
          clickable
          to="/entrar"
          @click="drawer = false"
        >
          <q-item-section avatar><q-icon name="person" /></q-item-section>
          <q-item-section>Entrar</q-item-section>
        </q-item>
        <q-item
          v-if="!isAuthenticated"
          v-ripple
          clickable
          to="/cadastrar"
          @click="drawer = false"
        >
          <q-item-section avatar><q-icon name="person_add" /></q-item-section>
          <q-item-section>Cadastrar</q-item-section>
        </q-item>
        <q-item v-else v-ripple clickable @click="handleLogout">
          <q-item-section avatar><q-icon name="logout" /></q-item-section>
          <q-item-section>Sair da conta</q-item-section>
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
import { onMounted, ref } from 'vue'
import { Notify } from 'quasar'
import { useAuth } from '../stores/auth'

const drawer = ref(false)
const auth = useAuth()
const { isAuthenticated, user } = auth

const links = [
  { label: 'Início', to: '/', icon: 'home' },
  { label: 'Veículos', to: '/veiculos', icon: 'directions_car' },
]

onMounted(async () => {
  try {
    await auth.loadUser()
  } catch {
    Notify.create({
      type: 'negative',
      message: 'Não foi possível validar sua sessão. Verifique a conexão e tente novamente.',
    })
  }
})

async function handleLogout() {
  try {
    await auth.logout()
    drawer.value = false
    Notify.create({
      type: 'positive',
      message: 'Você saiu da sua conta.',
    })
  } catch {
    drawer.value = false
    Notify.create({
      type: 'warning',
      message: 'A sessão foi encerrada neste dispositivo, mas não foi possível confirmar com o servidor.',
    })
  }
}
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

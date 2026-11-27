<template>
  <q-page class="login-page flex flex-center q-pa-md">
    <div class="login-layout">
      <section class="login-intro">
        <router-link to="/" class="brand">
          Tá na <span class="text-primary">Garagem</span>
        </router-link>

        <div class="intro-copy">
          <div class="text-overline text-primary text-weight-bold">BEM-VINDO DE VOLTA</div>
          <h1 class="text-h3 text-weight-bold q-mt-sm q-mb-md">
            Seu próximo negócio começa aqui.
          </h1>
          <p class="text-body1 text-grey-5 q-mb-none">
            Entre na sua conta para continuar acompanhando seus veículos e anúncios.
          </p>
        </div>

        <div class="intro-decoration" aria-hidden="true">
          <q-icon name="directions_car" />
        </div>
      </section>

      <q-card flat class="login-card">
        <q-card-section class="q-pa-lg q-pa-md-xl">
          <div class="text-h5 text-weight-bold">Entrar na sua conta</div>
          <div class="text-body2 text-grey-7 q-mt-xs q-mb-lg">
            Informe seu e-mail e sua senha para acessar.
          </div>

          <q-banner v-if="errorMessage" rounded class="bg-red-1 text-negative q-mb-md">
            <template #avatar>
              <q-icon name="error_outline" />
            </template>
            {{ errorMessage }}
          </q-banner>

          <q-form class="column q-gutter-md" @submit.prevent="submit">
            <q-input
              v-model.trim="email"
              outlined
              label="E-mail"
              type="email"
              autocomplete="username"
              inputmode="email"
              :disable="loading"
              :rules="[
                (value) => !!value || 'Informe seu e-mail.',
                (value) => isValidEmail(value) || 'Informe um e-mail válido.',
              ]"
              lazy-rules
            >
              <template #prepend>
                <q-icon name="mail_outline" />
              </template>
            </q-input>

            <q-input
              v-model="password"
              outlined
              label="Senha"
              :type="showPassword ? 'text' : 'password'"
              autocomplete="current-password"
              :disable="loading"
              :rules="[(value) => !!value || 'Informe sua senha.']"
              lazy-rules
            >
              <template #prepend>
                <q-icon name="lock_outline" />
              </template>
              <template #append>
                <q-btn
                  flat
                  round
                  dense
                  :icon="showPassword ? 'visibility_off' : 'visibility'"
                  :aria-label="showPassword ? 'Ocultar senha' : 'Mostrar senha'"
                  @click="showPassword = !showPassword"
                />
              </template>
            </q-input>

            <q-btn
              unelevated
              color="primary"
              size="lg"
              no-caps
              type="submit"
              class="full-width q-mt-sm"
              :loading="loading"
              label="Entrar"
              icon-right="arrow_forward"
            />
          </q-form>

          <div class="text-center q-mt-lg">
            <div class="text-body2 q-mb-sm">
              Ainda não tem conta?
              <router-link to="/cadastrar" class="register-link">Cadastre-se</router-link>
            </div>
            <router-link to="/" class="back-link">Voltar para a página inicial</router-link>
          </div>
        </q-card-section>
      </q-card>
    </div>
  </q-page>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { Notify } from 'quasar'
import { ApiError } from '../api'
import { useAuth } from '../stores/auth'

const router = useRouter()
const auth = useAuth()

const email = ref('')
const password = ref('')
const showPassword = ref(false)
const loading = ref(false)
const errorMessage = ref('')

function isValidEmail(value) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)
}

async function submit() {
  loading.value = true
  errorMessage.value = ''

  try {
    await auth.login({ email: email.value, password: password.value })
    Notify.create({
      type: 'positive',
      message: 'Login realizado com sucesso!',
    })
    await router.replace('/')
  } catch (error) {
    if (error instanceof ApiError) {
      if (error.status === 429) {
        errorMessage.value = 'Muitas tentativas. Aguarde um minuto e tente novamente.'
      } else {
        errorMessage.value =
          error.data?.errors?.email?.[0] ||
          error.data?.errors?.password?.[0] ||
          error.message ||
          'Não foi possível entrar. Confira seus dados e tente novamente.'
      }
    } else if (error instanceof TypeError) {
      errorMessage.value = 'Não foi possível conectar ao servidor. Tente novamente em instantes.'
    } else {
      errorMessage.value = error.message || 'Ocorreu um erro inesperado ao entrar.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-page {
  min-height: 580px;
  background:
    radial-gradient(ellipse at 12% 18%, rgba(229, 57, 53, 0.1), transparent 34%), #f5f6f8;
}

.login-layout {
  width: min(100%, 980px);
  min-height: 520px;
  display: grid;
  grid-template-columns: minmax(0, 0.9fr) minmax(360px, 1fr);
  overflow: hidden;
  border-radius: 20px;
  background: #fff;
  box-shadow: 0 24px 70px rgba(15, 22, 36, 0.14);
}

.login-intro {
  position: relative;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  overflow: hidden;
  padding: 38px;
  background:
    linear-gradient(145deg, rgba(15, 22, 36, 0.96), rgba(15, 22, 36, 0.83)),
    url('../assets/corolla-hero.png') center / cover;
  color: #fff;
}

.brand {
  position: relative;
  z-index: 1;
  color: #fff;
  font-size: 1.4rem;
  font-weight: 800;
  letter-spacing: -0.5px;
  text-decoration: none;
}

.intro-copy {
  position: relative;
  z-index: 1;
  max-width: 380px;
  padding-bottom: 22px;
}

.intro-copy h1 {
  line-height: 1.1;
}

.intro-decoration {
  position: absolute;
  right: -40px;
  bottom: -82px;
  color: rgba(255, 255, 255, 0.06);
  font-size: 22rem;
  line-height: 1;
}

.login-card {
  display: flex;
  align-items: center;
  border-radius: 0;
  box-shadow: none;
}

.login-card > :deep(.q-card__section) {
  width: 100%;
  max-width: 500px;
  margin: 0 auto;
}

.back-link {
  color: #646b76;
  font-size: 0.9rem;
  text-decoration: none;
}

.register-link {
  color: var(--q-primary);
  font-weight: 700;
  text-decoration: none;
}

.back-link:hover,
.register-link:hover {
  text-decoration: underline;
}

.back-link:hover {
  color: var(--q-primary);
}

@media (max-width: 760px) {
  .login-page {
    min-height: 0;
    padding: 20px 16px;
  }

  .login-layout {
    min-height: 0;
    max-width: 520px;
    grid-template-columns: 1fr;
    border-radius: 16px;
  }

  .login-intro {
    min-height: 210px;
    padding: 24px;
  }

  .intro-copy {
    padding: 28px 0 0;
  }

  .intro-copy h1 {
    max-width: 360px;
    font-size: 1.8rem;
  }

  .intro-copy p {
    max-width: 370px;
    font-size: 0.9rem;
  }

  .intro-decoration {
    right: -20px;
    bottom: -82px;
    font-size: 15rem;
  }
}

@media (max-width: 420px) {
  .login-page {
    padding: 12px;
  }

  .login-intro {
    min-height: 190px;
    padding: 20px;
  }

  .login-card > :deep(.q-card__section) {
    padding: 24px 20px;
  }
}
</style>

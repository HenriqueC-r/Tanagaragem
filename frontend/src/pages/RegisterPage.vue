<template>
  <q-page class="register-page q-pa-md">
    <div class="register-layout">
      <section class="register-intro">
        <router-link to="/" class="brand">
          Tá na <span class="text-primary">Garagem</span>
        </router-link>

        <div class="intro-copy">
          <div class="text-overline text-primary text-weight-bold">CRIE SUA CONTA</div>
          <h1 class="text-h3 text-weight-bold q-mt-sm q-mb-md">
            Boas oportunidades começam com um cadastro.
          </h1>
          <p class="text-body1 text-grey-5 q-mb-none">
            Faça parte da comunidade Tá na Garagem e encontre seu próximo negócio.
          </p>
        </div>

        <div class="intro-decoration" aria-hidden="true">
          <q-icon name="directions_car" />
        </div>
      </section>

      <q-card flat class="register-card">
        <q-card-section class="q-pa-lg q-pa-md-xl">
          <template v-if="submitted">
            <div class="success-icon">
              <q-icon name="check_circle" color="positive" size="48px" />
            </div>
            <div class="text-h5 text-weight-bold q-mt-md">Cadastro recebido!</div>
            <p class="text-body1 text-grey-7 q-mt-sm">
              {{ successMessage }}
            </p>
            <q-banner
              v-if="isStore && storeStatus === 'pending'"
              rounded
              class="bg-orange-1 text-orange-10 q-mt-md"
            >
              <template #avatar>
                <q-icon name="pending_actions" />
              </template>
              Sua loja poderá anunciar depois da análise do CNPJ.
            </q-banner>
            <q-btn
              unelevated
              color="primary"
              no-caps
              label="Ir para o login"
              icon-right="arrow_forward"
              class="full-width q-mt-lg"
              to="/entrar"
            />
            <div class="text-center q-mt-md">
              <router-link to="/" class="back-link">Voltar para a página inicial</router-link>
            </div>
          </template>

          <template v-else>
            <div class="text-h5 text-weight-bold">Crie sua conta</div>
            <div class="text-body2 text-grey-7 q-mt-xs q-mb-lg">
              Preencha seus dados para começar.
            </div>

            <q-banner v-if="errorMessage" rounded class="bg-red-1 text-negative q-mb-md">
              <template #avatar>
                <q-icon name="error_outline" />
              </template>
              {{ errorMessage }}
            </q-banner>

            <q-form class="column q-gutter-sm" @submit.prevent="submit">
              <div class="text-subtitle2 text-weight-bold">Tipo de conta</div>
              <q-btn-toggle
                v-model="form.type"
                spread
                no-caps
                unelevated
                toggle-color="primary"
                color="grey-2"
                text-color="dark"
                :options="[
                  { label: 'Pessoa física', value: 'person', icon: 'person' },
                  { label: 'Loja', value: 'store', icon: 'storefront' },
                ]"
                :disable="loading"
                class="account-type-toggle q-mb-sm"
              />

              <q-input
                v-model.trim="form.name"
                outlined
                label="Nome completo"
                autocomplete="name"
                maxlength="255"
                :disable="loading"
                :rules="[(value) => !!value || 'Informe seu nome completo.']"
                lazy-rules
              >
                <template #prepend><q-icon name="person_outline" /></template>
              </q-input>

              <q-input
                v-if="isStore"
                v-model.trim="form.company_name"
                outlined
                label="Razão social"
                autocomplete="organization"
                maxlength="255"
                :disable="loading"
                :rules="[(value) => !!value || 'Informe a razão social da loja.']"
                lazy-rules
              >
                <template #prepend><q-icon name="business" /></template>
              </q-input>

              <div class="form-row">
                <q-input
                  v-model="form.cpf"
                  outlined
                  label="CPF"
                  mask="###.###.###-##"
                  autocomplete="off"
                  :disable="loading"
                  :rules="[(value) => digits(value).length === 11 || 'Informe um CPF válido.']"
                  lazy-rules
                >
                  <template #prepend><q-icon name="badge" /></template>
                </q-input>

                <q-input
                  v-if="isStore"
                  v-model="form.cnpj"
                  outlined
                  label="CNPJ"
                  mask="##.###.###/####-##"
                  autocomplete="off"
                  :disable="loading"
                  :rules="[(value) => digits(value).length === 14 || 'Informe um CNPJ válido.']"
                  lazy-rules
                >
                  <template #prepend><q-icon name="domain" /></template>
                </q-input>
              </div>

              <q-input
                v-model.trim="form.email"
                outlined
                label="E-mail"
                type="email"
                autocomplete="email"
                inputmode="email"
                maxlength="255"
                :disable="loading"
                :rules="[
                  (value) => !!value || 'Informe seu e-mail.',
                  (value) => isValidEmail(value) || 'Informe um e-mail válido.',
                ]"
                lazy-rules
              >
                <template #prepend><q-icon name="mail_outline" /></template>
              </q-input>

              <q-input
                :model-value="phoneDisplay"
                @update:model-value="updatePhone"
                outlined
                label="Telefone"
                type="tel"
                autocomplete="tel"
                inputmode="tel"
                :disable="loading"
                :rules="[
                  (value) => [10, 11].includes(digits(value).length) || 'Informe um telefone válido.',
                ]"
                lazy-rules
              >
                <template #prepend><q-icon name="phone" /></template>
              </q-input>
              <div class="text-caption text-grey-7 phone-hint">
                Ex.: (99) 9999-9999 ou (99) 99999-9999
              </div>

              <div class="form-row">
                <q-select
                  :key="cityOptions.length"
                  v-model="form.city"
                  outlined
                  label="Cidade"
                  :options="cityOptions"
                  :loading="cityLoading"
                  :disable="loading || !form.state || cityLoading || Boolean(cityLoadError)"
                  :hint="!form.state ? 'Selecione o estado primeiro.' : ''"
                  :rules="[(value) => !!value || 'Selecione sua cidade.']"
                  lazy-rules
                >
                  <template #prepend><q-icon name="location_city" /></template>
                  <template #no-option>
                    <q-item>
                      <q-item-section class="text-grey">
                        {{ cityLoading ? 'Carregando cidades...' : 'Nenhuma cidade encontrada.' }}
                      </q-item-section>
                    </q-item>
                  </template>
                </q-select>

                <q-select
                  v-model="form.state"
                  outlined
                  label="Estado"
                  :options="states"
                  emit-value
                  map-options
                  :disable="loading"
                  :rules="[(value) => !!value || 'Selecione seu estado.']"
                  lazy-rules
                />
              </div>
              <q-banner
                v-if="cityLoadError"
                rounded
                class="bg-orange-1 text-orange-10 q-mb-sm"
              >
                <template #avatar><q-icon name="warning" /></template>
                Não foi possível carregar as cidades. Confira sua conexão e tente novamente.
                <template #action>
                  <q-btn flat no-caps label="Tentar novamente" @click="retryCities" />
                </template>
              </q-banner>

              <div class="form-row">
                <q-input
                  v-model="form.password"
                  outlined
                  label="Senha"
                  :type="showPassword ? 'text' : 'password'"
                  autocomplete="new-password"
                  :disable="loading"
                  :rules="[
                    (value) => !!value || 'Crie uma senha.',
                    (value) => value.length >= 8 || 'A senha deve ter pelo menos 8 caracteres.',
                  ]"
                  lazy-rules
                >
                  <template #prepend><q-icon name="lock_outline" /></template>
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

                <q-input
                  v-model="form.password_confirmation"
                  outlined
                  label="Confirme a senha"
                  :type="showPassword ? 'text' : 'password'"
                  autocomplete="new-password"
                  :disable="loading"
                  :rules="[
                    (value) => !!value || 'Confirme sua senha.',
                    (value) => value === form.password || 'As senhas não coincidem.',
                  ]"
                  lazy-rules
                >
                  <template #prepend><q-icon name="lock_reset" /></template>
                </q-input>
              </div>

              <q-btn
                unelevated
                color="primary"
                size="lg"
                no-caps
                type="submit"
                class="full-width q-mt-sm"
                :loading="loading"
                label="Criar minha conta"
                icon-right="arrow_forward"
              />
            </q-form>

            <div class="text-center text-body2 q-mt-lg">
              Já tem uma conta?
              <router-link to="/entrar" class="login-link">Entrar</router-link>
            </div>
          </template>
        </q-card-section>
      </q-card>
    </div>
  </q-page>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { ApiError, register as requestRegister } from '../api'

const states = [
  { label: 'Acre (AC)', value: 'AC' },
  { label: 'Alagoas (AL)', value: 'AL' },
  { label: 'Amapá (AP)', value: 'AP' },
  { label: 'Amazonas (AM)', value: 'AM' },
  { label: 'Bahia (BA)', value: 'BA' },
  { label: 'Ceará (CE)', value: 'CE' },
  { label: 'Distrito Federal (DF)', value: 'DF' },
  { label: 'Espírito Santo (ES)', value: 'ES' },
  { label: 'Goiás (GO)', value: 'GO' },
  { label: 'Maranhão (MA)', value: 'MA' },
  { label: 'Mato Grosso (MT)', value: 'MT' },
  { label: 'Mato Grosso do Sul (MS)', value: 'MS' },
  { label: 'Minas Gerais (MG)', value: 'MG' },
  { label: 'Pará (PA)', value: 'PA' },
  { label: 'Paraíba (PB)', value: 'PB' },
  { label: 'Paraná (PR)', value: 'PR' },
  { label: 'Pernambuco (PE)', value: 'PE' },
  { label: 'Piauí (PI)', value: 'PI' },
  { label: 'Rio de Janeiro (RJ)', value: 'RJ' },
  { label: 'Rio Grande do Norte (RN)', value: 'RN' },
  { label: 'Rio Grande do Sul (RS)', value: 'RS' },
  { label: 'Rondônia (RO)', value: 'RO' },
  { label: 'Roraima (RR)', value: 'RR' },
  { label: 'Santa Catarina (SC)', value: 'SC' },
  { label: 'São Paulo (SP)', value: 'SP' },
  { label: 'Sergipe (SE)', value: 'SE' },
  { label: 'Tocantins (TO)', value: 'TO' },
]

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  type: 'person',
  cpf: '',
  phone: '',
  city: '',
  state: '',
  company_name: '',
  cnpj: '',
})

const isStore = computed(() => form.type === 'store')
const phoneDisplay = computed(() => formatPhone(form.phone))
const loading = ref(false)
const submitted = ref(false)
const showPassword = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const storeStatus = ref(null)
const cityOptions = ref([])
const cityLoading = ref(false)
const cityLoadError = ref(false)
const cityRetry = ref(0)

watch(
  [() => form.state, cityRetry],
  async ([state], _, onCleanup) => {
    form.city = ''
    cityOptions.value = []
    cityLoadError.value = false
    if (!state) {
      cityLoading.value = false
      return
    }

    const controller = new AbortController()
    onCleanup(() => controller.abort())
    cityLoading.value = true

    try {
      const response = await fetch(
        `https://servicodados.ibge.gov.br/api/v1/localidades/estados/${state}/municipios?orderBy=nome`,
        {
          headers: { Accept: 'application/json' },
          signal: controller.signal,
        },
      )

      if (!response.ok) {
        throw new Error(`IBGE request failed with status ${response.status}`)
      }

      const cities = await response.json()
      if (
        !Array.isArray(cities) ||
        cities.length === 0 ||
        cities.some((city) => typeof city.nome !== 'string')
      ) {
        throw new Error('IBGE returned an invalid city list')
      }

      cityOptions.value = cities.map((city) => city.nome)
    } catch (error) {
      if (error.name === 'AbortError') return
      cityLoadError.value = true
    } finally {
      if (!controller.signal.aborted) cityLoading.value = false
    }
  },
  { immediate: true },
)

function digits(value) {
  return String(value || '').replace(/\D/g, '')
}

function formatPhone(value) {
  const phone = digits(value)
  if (!phone) return ''

  const areaCode = phone.slice(0, 2)
  if (phone.length <= 2) return `(${areaCode}`

  const subscriber = phone.slice(2)
  const prefixLength = phone.length > 10 ? 5 : 4
  const prefix = subscriber.slice(0, prefixLength)
  const line = subscriber.slice(prefixLength)
  const formattedAreaCode = `(${areaCode}) `

  return line ? `${formattedAreaCode}${prefix}-${line}` : `${formattedAreaCode}${prefix}`
}

function updatePhone(value) {
  form.phone = digits(value).slice(0, 11)
}

function retryCities() {
  cityRetry.value += 1
}

function isValidEmail(value) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)
}

async function submit() {
  loading.value = true
  errorMessage.value = ''

  const payload = {
    ...form,
    cpf: digits(form.cpf),
    phone: formatPhone(form.phone),
    cnpj: isStore.value ? digits(form.cnpj) : null,
    company_name: isStore.value ? form.company_name : null,
  }

  try {
    const response = await requestRegister(payload)
    successMessage.value = response.message || 'Sua conta foi criada com sucesso.'
    storeStatus.value = response.store_status
    submitted.value = true
  } catch (error) {
    if (error instanceof ApiError) {
      if (error.status === 429) {
        errorMessage.value = 'Muitas tentativas de cadastro. Aguarde um pouco e tente novamente.'
      } else {
        const validationMessage = Object.values(error.data?.errors || {}).flat()[0]
        errorMessage.value =
          validationMessage ||
          error.message ||
          'Não foi possível criar sua conta. Confira os dados e tente novamente.'
      }
    } else if (error instanceof TypeError) {
      errorMessage.value = 'Não foi possível conectar ao servidor. Tente novamente em instantes.'
    } else {
      errorMessage.value ||=
        error.message || 'Ocorreu um erro inesperado ao tentar criar sua conta.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.register-page {
  min-height: 100%;
  background:
    radial-gradient(ellipse at 12% 18%, rgba(229, 57, 53, 0.1), transparent 34%), #f5f6f8;
}

.register-layout {
  width: min(100%, 1120px);
  min-height: 600px;
  margin: 20px auto;
  display: grid;
  grid-template-columns: minmax(0, 0.78fr) minmax(0, 1.22fr);
  overflow: hidden;
  border-radius: 20px;
  background: #fff;
  box-shadow: 0 24px 70px rgba(15, 22, 36, 0.14);
}

.register-intro {
  position: sticky;
  top: 24px;
  height: calc(100vh - 48px);
  min-height: 500px;
  max-height: 740px;
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

.register-card {
  min-width: 0;
  border-radius: 0;
  box-shadow: none;
}

.register-card > :deep(.q-card__section) {
  max-width: 700px;
  margin: 0 auto;
}

.form-row {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}

.account-type-toggle {
  border-radius: 8px;
}

.success-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 76px;
  height: 76px;
  border-radius: 50%;
  background: #e8f5e9;
}

.back-link,
.login-link {
  color: #646b76;
  font-size: 0.9rem;
  text-decoration: none;
}

.login-link {
  color: var(--q-primary);
  font-weight: 700;
}

.back-link:hover,
.login-link:hover {
  text-decoration: underline;
}

@media (max-width: 760px) {
  .register-page {
    padding: 12px;
  }

  .register-layout {
    max-width: 560px;
    min-height: 0;
    margin: 0 auto;
    grid-template-columns: 1fr;
    border-radius: 16px;
  }

  .register-intro {
    position: relative;
    top: auto;
    height: auto;
    min-height: 0;
    max-height: none;
    padding: 24px;
  }

  .intro-copy {
    padding: 28px 0 0;
  }

  .intro-copy h1 {
    max-width: 420px;
    font-size: 1.8rem;
  }

  .intro-copy p {
    max-width: 420px;
    font-size: 0.9rem;
  }

  .intro-decoration {
    right: -20px;
    bottom: -82px;
    font-size: 15rem;
  }
}

@media (max-width: 480px) {
  .register-page {
    padding: 8px;
  }

  .register-intro {
    padding: 20px;
  }

  .register-card > :deep(.q-card__section) {
    padding: 24px 18px;
  }

  .form-row {
    grid-template-columns: 1fr;
    gap: 0;
  }
}
</style>

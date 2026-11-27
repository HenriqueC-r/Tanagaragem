import { computed, ref } from 'vue'
import { getCurrentUser, login as requestLogin, logout as requestLogout } from '../api'

const TOKEN_STORAGE_KEY = 'tanagaragem.auth_token'

const token = ref(sessionStorage.getItem(TOKEN_STORAGE_KEY) || '')
const user = ref(null)
const isAuthenticated = computed(() => Boolean(token.value))

function clearSession() {
  token.value = ''
  user.value = null
  sessionStorage.removeItem(TOKEN_STORAGE_KEY)
}

async function login(credentials) {
  const response = await requestLogin(credentials)

  if (!response.token || !response.user) {
    throw new Error('A API retornou uma resposta de login inválida.')
  }

  token.value = response.token
  user.value = response.user
  sessionStorage.setItem(TOKEN_STORAGE_KEY, response.token)

  return response.user
}

async function loadUser() {
  if (!token.value) return

  try {
    const response = await getCurrentUser(token.value)
    user.value = response.user
  } catch (error) {
    if (error.status === 401) {
      clearSession()
      return
    }

    throw error
  }
}

async function logout() {
  const currentToken = token.value

  try {
    if (currentToken) await requestLogout(currentToken)
  } finally {
    clearSession()
  }
}

export function useAuth() {
  return {
    token,
    user,
    isAuthenticated,
    login,
    loadUser,
    logout,
  }
}

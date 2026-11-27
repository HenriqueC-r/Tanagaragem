// Tudo o que o site pede à API passa por aqui.
// Para apontar para outro servidor, defina VITE_API_URL num arquivo .env.

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8081/api'

export class ApiError extends Error {
  constructor(status, data) {
    super(data?.message || `Erro ${status}`)
    this.status = status
    this.data = data
  }
}

async function request(path, { params = {}, method = 'GET', body = null, token = null } = {}) {
  const url = new URL(API_URL + path)

  for (const [key, value] of Object.entries(params)) {
    if (value !== undefined && value !== null && value !== '') {
      url.searchParams.set(key, value)
    }
  }

  const headers = { Accept: 'application/json' }
  if (body) headers['Content-Type'] = 'application/json'
  if (token) headers.Authorization = `Bearer ${token}`

  const response = await fetch(url, {
    method,
    headers,
    body: body ? JSON.stringify(body) : null,
  })

  const data = await response.json().catch(() => null)

  if (!response.ok) {
    throw new ApiError(response.status, data)
  }

  return data
}

export const getFeatured = (limit = 8) => request('/vehicles/featured', { params: { limit } })
export const getVehicles = (params = {}) => request('/vehicles', { params })
export const getVehicle = (id) => request(`/vehicles/${id}`)
export const getCategories = () => request('/categories')
export const getPlans = (type) => request('/plans', { params: { for: type } })
export const login = (credentials) => request('/login', { method: 'POST', body: credentials })
export const register = (details) => request('/register', { method: 'POST', body: details })
export const getCurrentUser = (token) => request('/me', { token })
export const logout = (token) => request('/logout', { method: 'POST', token })

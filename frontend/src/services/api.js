const API_BASE = (import.meta.env.VITE_API_BASE_URL || '/api').replace(/\/+$/, '')
const ADMIN_TOKEN_KEY = 'tnm_admin_token'

function getAdminToken() {
  return localStorage.getItem(ADMIN_TOKEN_KEY) || ''
}

function setAdminToken(token) {
  const t = String(token || '').trim()
  if (t) {
    localStorage.setItem(ADMIN_TOKEN_KEY, t)
    localStorage.removeItem(CUSTOMER_TOKEN_KEY)
  } else {
    localStorage.removeItem(ADMIN_TOKEN_KEY)
  }

  if (typeof window !== 'undefined') {
    window.dispatchEvent(new Event('tnm-auth-changed'))
  }
}


async function request(path, options = {}) {
  const mergedHeaders = {
    Accept: 'application/json',
    ...(options.headers || {}),
  }

  const method = (options.method || 'GET').toUpperCase()
  const hasBody = options.body != null && method !== 'GET' && method !== 'HEAD'

  if (hasBody && !('Content-Type' in mergedHeaders)) {
    mergedHeaders['Content-Type'] = 'application/json'
  }

  const res = await fetch(`${API_BASE}${path}`, {
    ...options,
    method,
    headers: mergedHeaders,
  })

  const contentType = (res.headers.get('content-type') || '').toLowerCase()
  const text = await res.text()

  let data = null
  if (text) {
    if (contentType.includes('application/json')) {
      try {
        data = JSON.parse(text)
      } catch {
        data = { message: text }
      }
    } else {
      data = { message: text }
    }
  }

  if (!res.ok) {
    const msg = data?.message || `Request failed (${res.status})`
    throw new Error(msg)
  }

  return data
}

function adminHeaders() {
  const token = getAdminToken()
  return token ? { Authorization: `Bearer ${token}` } : {}
}

export const publicApi = {
  listProducts(q = '') {
    const qs = q ? `?q=${encodeURIComponent(q)}` : ''
    return request(`/products${qs}`, { method: 'GET' })
  },
  checkout(payload) {
    return request('/checkout', { method: 'POST', body: JSON.stringify(payload) })
  },
  getPayment(intentId) {
    return request(`/payments/${encodeURIComponent(intentId)}`, { method: 'GET' })
  },
  confirmPayment(intentId, payload) {
    return request(`/payments/${encodeURIComponent(intentId)}/confirm`, {
      method: 'POST',
      body: JSON.stringify(payload),
    })
  },
}

export const adminAuth = {
  async login(email, password) {
    const data = await request('/admin/login', {
      method: 'POST',
      body: JSON.stringify({ email, password }),
    })

    const token =
      data?.token ||
      data?.access_token ||
      data?.plainTextToken ||
      data?.data?.token ||
      data?.data?.access_token ||
      data?.data?.plainTextToken ||
      ''

    setAdminToken(token)
    return data
  },

  async me() {
    return request('/admin/me', { method: 'GET', headers: adminHeaders() })
  },

  async logout() {
    try {
      await request('/admin/logout', { method: 'POST', headers: adminHeaders() })
    } finally {
      setAdminToken('')
    }
  },

  getToken() {
    return getAdminToken()
  },

  clearToken() {
    setAdminToken('')
  },
}

export const adminApi = {
  listProducts() {
    return request('/admin/products', { method: 'GET', headers: adminHeaders() })
  },
  createProduct(payload) {
    return request('/admin/products', {
      method: 'POST',
      headers: adminHeaders(),
      body: JSON.stringify(payload),
    })
  },
  updateProduct(id, payload) {
    return request(`/admin/products/${id}`, {
      method: 'PUT',
      headers: adminHeaders(),
      body: JSON.stringify(payload),
    })
  },
  toggleProduct(id, is_enabled) {
    return request(`/admin/products/${id}/toggle`, {
      method: 'PATCH',
      headers: adminHeaders(),
      body: JSON.stringify({ is_enabled }),
    })
  },
  deleteProduct(id) {
    return request(`/admin/products/${id}`, {
      method: 'DELETE',
      headers: adminHeaders(),
    })
  },

  listOrders(params = {}) {
    const qs = new URLSearchParams(params).toString()
    return request(`/admin/orders${qs ? `?${qs}` : ''}`, { method: 'GET', headers: adminHeaders() })
  },
  getOrder(id) {
    return request(`/admin/orders/${id}`, { method: 'GET', headers: adminHeaders() })
  },
  shipOrder(id, payload) {
    return request(`/admin/orders/${id}/ship`, {
      method: 'PATCH',
      headers: adminHeaders(),
      body: JSON.stringify(payload),
    })
  },
  

  listAdminUsers() {
    return request('/admin/admin-users', { method: 'GET', headers: adminHeaders() })
  },
  createAdminUser(payload) {
    return request('/admin/admin-users', {
      method: 'POST',
      headers: adminHeaders(),
      body: JSON.stringify(payload),
    })
  },
  deleteAdminUser(id) {
    return request(`/admin/admin-users/${id}`, {
      method: 'DELETE',
      headers: adminHeaders(),
    })
  },
}

// --------------------
// Customer auth helpers
// --------------------
const CUSTOMER_TOKEN_KEY = 'tnm_customer_token'

function getCustomerToken() {
  return localStorage.getItem(CUSTOMER_TOKEN_KEY) || ''
}

function setCustomerToken(token) {
  const t = String(token || '').trim()
  if (t) {
    localStorage.setItem(CUSTOMER_TOKEN_KEY, t)
    localStorage.removeItem(ADMIN_TOKEN_KEY)
  } else {
    localStorage.removeItem(CUSTOMER_TOKEN_KEY)
  }

  if (typeof window !== 'undefined') {
    window.dispatchEvent(new Event('tnm-auth-changed'))
  }
}


function customerHeaders() {
  const token = getCustomerToken()
  return token ? { Authorization: `Bearer ${token}` } : {}
}

export const customerAuth = {
  async register(name, email, password, password_confirmation) {
    const data = await request('/auth/register', {
      method: 'POST',
      body: JSON.stringify({ name, email, password, password_confirmation }),
    })

    const token =
      data?.token ||
      data?.access_token ||
      data?.plainTextToken ||
      data?.data?.token ||
      data?.data?.access_token ||
      data?.data?.plainTextToken ||
      ''

    setCustomerToken(token)
    return data
  },


  async login(email, password) {
    const data = await request('/auth/login', {
      method: 'POST',
      body: JSON.stringify({ email, password }),
    })

    const token =
      data?.token ||
      data?.access_token ||
      data?.plainTextToken ||
      data?.data?.token ||
      data?.data?.access_token ||
      data?.data?.plainTextToken ||
      ''

    setCustomerToken(token)
    return data
  },

  async me() {
    return request('/auth/me', { method: 'GET', headers: customerHeaders() })
  },

  async logout() {
    try {
      await request('/auth/logout', { method: 'POST', headers: customerHeaders() })
    } finally {
      setCustomerToken('')
    }
  },

  getToken() {
    return getCustomerToken()
  },

  clearToken() {
    setCustomerToken('')
  },
}
// --------------------
// Customer API
// --------------------
export const customerApi = {
  listOrders(params = {}) {
    const qs = new URLSearchParams(params).toString()
    return request(`/auth/orders${qs ? `?${qs}` : ''}`, {
      method: 'GET',
      headers: customerHeaders(),
    })
  },

  getOrder(orderNumber) {
    return request(`/auth/orders/${encodeURIComponent(orderNumber)}`, {
      method: 'GET',
      headers: customerHeaders(),
    })
  },
}

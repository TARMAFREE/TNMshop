const API_BASE = import.meta.env.VITE_API_BASE_URL

async function request(path, options = {}) {
  const res = await fetch(`${API_BASE}${path}`, {
    headers: { 'Content-Type': 'application/json', ...(options.headers || {}) },
    ...options,
  })
  const text = await res.text()
  let data = null
  try { data = text ? JSON.parse(text) : null } catch { data = { message: text } }
  if (!res.ok) {
    const msg = data?.message || `Request failed (${res.status})`
    throw new Error(msg)
  }
  return data
}

export const publicApi = {
  listProducts(q = '') {
    const qs = q ? `?q=${encodeURIComponent(q)}` : ''
    return request(`/products${qs}`)
  },
  checkout(payload) {
    return request('/checkout', { method: 'POST', body: JSON.stringify(payload) })
  },
  getPayment(intentId) {
    return request(`/payments/${encodeURIComponent(intentId)}`)
  },
  confirmPayment(intentId, payload) {
    return request(`/payments/${encodeURIComponent(intentId)}/confirm`, { method: 'POST', body: JSON.stringify(payload) })
  },
}

export const adminApi = {
  listProducts(adminKey) {
    return request('/admin/products', { headers: { 'X-Admin-Key': adminKey } })
  },
  createProduct(adminKey, payload) {
    return request('/admin/products', { method: 'POST', headers: { 'X-Admin-Key': adminKey }, body: JSON.stringify(payload) })
  },
  updateProduct(adminKey, id, payload) {
    return request(`/admin/products/${id}`, { method: 'PUT', headers: { 'X-Admin-Key': adminKey }, body: JSON.stringify(payload) })
  },
  toggleProduct(adminKey, id, is_enabled) {
    return request(`/admin/products/${id}/toggle`, { method: 'PATCH', headers: { 'X-Admin-Key': adminKey }, body: JSON.stringify({ is_enabled }) })
  },
  deleteProduct(adminKey, id) {
    return request(`/admin/products/${id}`, { method: 'DELETE', headers: { 'X-Admin-Key': adminKey } })
  },
  listOrders(adminKey) {
    return request('/admin/orders', { headers: { 'X-Admin-Key': adminKey } })
  },
  getOrder(adminKey, id) {
    return request(`/admin/orders/${id}`, { headers: { 'X-Admin-Key': adminKey } })
  },
}

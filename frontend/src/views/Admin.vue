<template>
  <div class="page">
    <main class="container">

      <section v-if="!loggedIn" class="card">
        <h1 style="margin:0 0 10px 0;">Admin Login</h1>

        <div class="form">
          <div class="field">
            <label>Email</label>
            <input v-model.trim="email" type="email" autocomplete="username" placeholder="admin@tnmshop.local" />
            <div v-if="loginEmailError" class="error" style="margin-top:6px;">
              {{ loginEmailError }}
            </div>
          </div>

          <div class="field">
            <label>Password</label>
            <input
              v-model="password"
              type="password"
              autocomplete="current-password"
              placeholder="••••••••"
              @keyup.enter="login"
            />
            <div v-if="loginPasswordError" class="error" style="margin-top:6px;">
              {{ loginPasswordError }}
            </div>
          </div>

          <button class="btn primary" :disabled="loggingIn" @click="login">
            {{ loggingIn ? 'Logging in...' : 'Login' }}
          </button>

          <div v-if="loginError" class="error" style="margin-top:10px;">
            {{ loginError }}
          </div>
        </div>
      </section>

      <section v-else class="card">
        <div class="row" style="align-items:center;">
          <div>
            <h1 style="margin:0;">Admin</h1>
            <div class="muted">Manage product availability.</div>
          </div>

          <div style="margin-left:auto; display:flex; gap:10px;">
            <RouterLink class="btn" to="/admin/secret-create">Create Admin</RouterLink>
            <button class="btn" @click="refreshAll" :disabled="loadingProducts">Refresh</button>
            <button class="btn danger" @click="logout">Logout</button>
          </div>
        </div>

        <div v-if="error" class="error">{{ error }}</div>
        <div v-if="success" class="success">{{ success }}</div>
      </section>

      <section v-if="loggedIn" class="card full">
        <h2>Products</h2>
        <p class="muted">Toggle enabled to show/hide products in the shop.</p>

        <div v-if="loadingProducts" class="muted">Loading...</div>

        <div v-else class="list">
          <div v-for="p in products" :key="p.id" class="item">
            <div class="info">
              <div class="name">{{ p.name }}</div>
              <div class="meta">
                SKU: {{ p.sku }} · Stock: {{ p.stock_qty }}
              </div>
            </div>

            <div class="actions">
              <span class="pill" :class="p.is_enabled ? 'on' : 'off'">
                {{ p.is_enabled ? 'Enabled' : 'Disabled' }}
              </span>

              <button class="btn" @click="startEdit(p)" :disabled="busyId === p.id">
                Edit
              </button>

              <button class="btn" @click="toggle(p)" :disabled="busyId === p.id">
                {{ p.is_enabled ? 'Disable' : 'Enable' }}
              </button>

              <button class="btn danger" @click="remove(p)" :disabled="busyId === p.id">
                Delete
              </button>
            </div>
          </div>
        </div>

        <div v-if="editing" class="edit-box">
          <div class="row" style="align-items:center;">
            <h3 style="margin:0;">Edit product</h3>
            <span class="muted" style="margin-left:auto;">
              #{{ editForm.id }} · {{ editForm.sku }}
            </span>
          </div>

          <div class="form">
            <div class="field">
              <label>SKU</label>
              <input v-model="editForm.sku" placeholder="TNM-100" />
            </div>

            <div class="field">
              <label>Name</label>
              <input v-model="editForm.name" placeholder="Product name" />
            </div>

            <div class="field">
              <label>Description</label>
              <input v-model="editForm.description" placeholder="Short description" />
            </div>

            <div class="row">
              <div class="field grow">
                <label>Price</label>
                <input v-model.number="editForm.price" type="number" min="0" step="0.01" />
              </div>
              <div class="field grow">
                <label>Stock</label>
                <input v-model.number="editForm.stock_qty" type="number" min="0" step="1" />
              </div>
            </div>

            <div class="field">
              <label>Image URL (optional)</label>
              <input v-model="editForm.image_url" placeholder="https://..." />
            </div>

            <div class="row" style="align-items:center;">
              <div class="field" style="min-width: 180px;">
                <label>Enabled</label>
                <button class="btn" type="button" @click="editForm.is_enabled = !editForm.is_enabled">
                  {{ editForm.is_enabled ? 'Enabled' : 'Disabled' }}
                </button>
              </div>

              <div style="margin-left:auto; display:flex; gap:10px;">
                <button class="btn" @click="cancelEdit" :disabled="savingEdit">Cancel</button>
                <button class="btn primary" @click="saveEdit" :disabled="savingEdit">
                  Save changes
                </button>
              </div>
            </div>
          </div>
        </div>

        <hr class="sep" />

        <h3>Add product</h3>
        <div class="form">
          <div class="field">
            <label>SKU</label>
            <input v-model="form.sku" placeholder="TNM-100" />
          </div>

          <div class="field">
            <label>Name</label>
            <input v-model="form.name" placeholder="Product name" />
          </div>

          <div class="field">
            <label>Description</label>
            <input v-model="form.description" placeholder="Short description" />
          </div>

          <div class="row">
            <div class="field grow">
              <label>Price</label>
              <input v-model.number="form.price" type="number" min="0" step="0.01" />
            </div>
            <div class="field grow">
              <label>Stock</label>
              <input v-model.number="form.stock_qty" type="number" min="0" step="1" />
            </div>
          </div>

          <div class="field">
            <label>Image URL (optional)</label>
            <input v-model="form.image_url" placeholder="https://..." />
          </div>

          <button class="btn primary" @click="create" :disabled="creating">
            Create product
          </button>
        </div>
      </section>

      <section v-if="loggedIn" class="card full" style="margin-top:16px;">
        <div class="row" style="align-items:center;">
          <div>
            <h2 style="margin:0;">Orders</h2>
            <div class="muted">Paid orders and shipping status.</div>
          </div>

          <div style="margin-left:auto; display:flex; gap:8px; flex-wrap:wrap;">
            <button class="btn" :class="{ primary: orderTab === 'paid' }" @click="setOrderTab('paid')">
              Paid (not shipped)
            </button>
            <button class="btn" :class="{ primary: orderTab === 'shipped' }" @click="setOrderTab('shipped')">
              Shipped
            </button>
            <button class="btn" @click="loadOrders" :disabled="loadingOrders">Refresh orders</button>
          </div>
        </div>

        <div v-if="ordersError" class="error" style="margin-top:10px;">{{ ordersError }}</div>
        <div v-if="loadingOrders" class="muted" style="margin-top:10px;">Loading...</div>

        <div v-else style="margin-top:10px;">
          <div v-if="!orders.length" class="muted">No orders found.</div>

          <div v-else class="list">
            <div v-for="o in orders" :key="o.id" class="item" style="align-items:flex-start;">
              <div class="info" style="min-width: 0;">
                <div class="name" style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
                  <span>{{ o.order_number }}</span>
                  <span class="pill" :class="o.status === 'shipped' ? 'on' : 'off'">
                    {{ o.status === 'shipped' ? 'Shipped' : 'Paid' }}
                  </span>
                </div>

                <div class="meta">
                  {{ formatDate(o.created_at) }} · {{ o.customer_name }} · {{ o.customer_email }}<span v-if="o.customer_phone"> · {{ o.customer_phone }}</span>
                </div>

                <div class="meta" style="margin-top:6px; white-space:pre-wrap;">
                  <strong>Address:</strong> {{ o.shipping_address }}
                </div>

                <div class="meta" style="margin-top:6px;">
                  <strong>Total:</strong> {{ money(o.total) }} {{ o.currency }}
                </div>

                <div v-if="o.status === 'shipped'" class="meta" style="margin-top:6px;">
                  <strong>Carrier:</strong> {{ o.shipping_carrier || '-' }} · <strong>Tracking:</strong> {{ o.tracking_number || '-' }}
                </div>
              </div>

              <div class="actions" style="min-width: 320px; max-width: 420px; display:flex; flex-direction:column; align-items:stretch; gap:10px;">
                <template v-if="orderTab === 'paid'">
                  <div class="field" style="margin:0 0 8px 0;">
                    <label class="muted">Shipping company</label>
                    <input class="input" v-model.trim="shipForms[o.id].shipping_carrier" placeholder="e.g., Kerry / Flash / J&T" />
                  </div>

                  <div class="field" style="margin:0 0 10px 0;">
                    <label class="muted">Tracking number</label>
                    <input class="input" v-model.trim="shipForms[o.id].tracking_number" placeholder="Tracking number" />
                  </div>

                  <button class="btn primary" :disabled="shipBusyId === o.id" @click="markShipped(o)">
                    {{ shipBusyId === o.id ? 'Saving...' : 'Mark as shipped' }}
                  </button>

                  <div v-if="shipErrors[o.id]" class="muted" style="margin-top:8px; color:var(--danger);">
                    {{ shipErrors[o.id] }}
                  </div>
                </template>

                <template v-else>
                  <div class="muted" style="font-size:12px;">
                    Carrier: <strong>{{ o.shipping_carrier || '-' }}</strong>
                  </div>
                  <div class="muted" style="font-size:12px; margin-top:6px;">
                    Tracking: <strong>{{ o.tracking_number || '-' }}</strong>
                  </div>
                  <div class="muted" style="font-size:12px; margin-top:6px;">
                    Shipped at: <strong>{{ o.shipped_at ? formatDate(o.shipped_at) : '-' }}</strong>
                  </div>
                </template>
              </div>
            </div>
          </div>
        </div>
      </section>

    </main>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { adminApi, adminAuth } from '../services/api'

const email = ref('')
const password = ref('')
const loggedIn = ref(false)
const loggingIn = ref(false)

// ✅ login errors แยก field
const loginEmailError = ref('')
const loginPasswordError = ref('')
const loginError = ref('')

function clearLoginErrors() {
  loginEmailError.value = ''
  loginPasswordError.value = ''
  loginError.value = ''
}

function applyAdminAuthError(e) {
  const d = e?.data

  if (d?.field === 'email') {
    loginEmailError.value = d?.message || 'อีเมลผิด'
    return
  }
  if (d?.field === 'password') {
    loginPasswordError.value = d?.message || 'รหัสผ่านผิด'
    return
  }

  if (d?.errors) {
    if (Array.isArray(d.errors.email) && d.errors.email.length) loginEmailError.value = d.errors.email[0]
    if (Array.isArray(d.errors.password) && d.errors.password.length) loginPasswordError.value = d.errors.password[0]
    if (!loginEmailError.value && !loginPasswordError.value) loginError.value = d?.message || 'Login failed'
    return
  }

  loginError.value = e?.message || 'Login failed'
}

const products = ref([])
const orders = ref([])
const orderTab = ref('paid') // 'paid' | 'shipped'
const loadingOrders = ref(false)
const ordersError = ref('')

// Per-order shipping form state (for paid orders)
const shipForms = reactive({})
const shipErrors = reactive({})
const shipBusyId = ref(null)

function ensureShipForm(id) {
  if (!shipForms[id]) {
    shipForms[id] = { shipping_carrier: '', tracking_number: '' }
  }
  if (!shipErrors[id]) {
    shipErrors[id] = ''
  }
}

function money(v) {
  const n = Number(v || 0)
  return n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function formatDate(v) {
  if (!v) return ''
  const d = new Date(v)
  if (Number.isNaN(d.getTime())) return String(v)
  return d.toLocaleString()
}

async function loadOrders() {
  loadingOrders.value = true
  ordersError.value = ''
  try {
    const status = orderTab.value === 'paid' ? 'paid' : 'shipped'
    const res = await adminApi.listOrders({ status })
    orders.value = res?.data || []

    if (orderTab.value === 'paid') {
      for (const o of orders.value) ensureShipForm(o.id)
    }
  } catch (e) {
    if ((e.message || '').toLowerCase().includes('unauthorized')) {
      await logout()
      return
    }
    ordersError.value = e.message || 'Failed to load orders'
  } finally {
    loadingOrders.value = false
  }
}

async function setOrderTab(tab) {
  orderTab.value = tab
  await loadOrders()
}

async function markShipped(order) {
  shipErrors[order.id] = ''
  ensureShipForm(order.id)

  const shipping_carrier = String(shipForms[order.id].shipping_carrier || '').trim()
  const tracking_number = String(shipForms[order.id].tracking_number || '').trim()

  if (!shipping_carrier || !tracking_number) {
    shipErrors[order.id] = 'Please enter both shipping company and tracking number.'
    return
  }

  shipBusyId.value = order.id
  try {
    await adminApi.shipOrder(order.id, { shipping_carrier, tracking_number })
    await loadOrders()
    success.value = 'Order marked as shipped.'
    error.value = ''
  } catch (e) {
    shipErrors[order.id] = e.message || 'Failed to mark as shipped'
  } finally {
    shipBusyId.value = null
  }
}

const loadingProducts = ref(false)
const creating = ref(false)
const busyId = ref(null)

const error = ref('')
const success = ref('')

const form = reactive({
  sku: '',
  name: '',
  description: '',
  price: 0,
  stock_qty: 0,
  image_url: '',
})

const editing = ref(false)
const savingEdit = ref(false)
const editForm = reactive({
  id: null,
  sku: '',
  name: '',
  description: '',
  price: 0,
  currency: 'THB',
  image_url: '',
  stock_qty: 0,
  is_enabled: true,
})

function setError(msg) {
  error.value = msg || 'Request failed'
  success.value = ''
}

function setSuccess(msg) {
  success.value = msg || 'Done'
  error.value = ''
}

async function login() {
  loggingIn.value = true
  clearLoginErrors()
  error.value = ''
  success.value = ''

  if (!email.value) loginEmailError.value = 'กรุณากรอกอีเมล'
  if (!password.value) loginPasswordError.value = 'กรุณากรอกรหัสผ่าน'
  if (loginEmailError.value || loginPasswordError.value) {
    loggingIn.value = false
    return
  }

  try {
    await adminAuth.login(email.value, password.value)
    loggedIn.value = true
    password.value = ''
    await refreshAll()
  } catch (e) {
    loggedIn.value = false
    products.value = []
    orders.value = []
    applyAdminAuthError(e)
  } finally {
    loggingIn.value = false
  }
}

async function logout() {
  await adminAuth.logout()
  loggedIn.value = false
  products.value = []
  orders.value = []
  ordersError.value = ''
}

async function loadProducts() {
  loadingProducts.value = true
  try {
    products.value = (await adminApi.listProducts())?.data || []
  } catch (e) {
    if ((e.message || '').toLowerCase().includes('unauthorized')) {
      await logout()
      return
    }
    setError(e.message)
  } finally {
    loadingProducts.value = false
  }
}

async function refreshAll() {
  await loadProducts()
  await loadOrders()
}

async function create() {
  creating.value = true
  try {
    await adminApi.createProduct({
      sku: String(form.sku || '').trim(),
      name: String(form.name || '').trim(),
      description: String(form.description || '').trim(),
      price: Number(form.price || 0),
      currency: 'THB',
      image_url: String(form.image_url || '').trim() || null,
      stock_qty: Number(form.stock_qty || 0),
      is_enabled: true,
    })
    setSuccess('Created.')
    form.sku = ''
    form.name = ''
    form.description = ''
    form.price = 0
    form.stock_qty = 0
    form.image_url = ''
    await loadProducts()
  } catch (e) {
    setError(e.message)
  } finally {
    creating.value = false
  }
}

async function toggle(p) {
  busyId.value = p.id
  try {
    await adminApi.toggleProduct(p.id, !p.is_enabled)
    await loadProducts()
  } catch (e) {
    setError(e.message)
  } finally {
    busyId.value = null
  }
}

async function remove(p) {
  const ok = window.confirm(`Delete "${p.name}"?`)
  if (!ok) return

  busyId.value = p.id
  try {
    await adminApi.deleteProduct(p.id)
    setSuccess('Deleted.')
    await loadProducts()
  } catch (e) {
    setError(e.message)
  } finally {
    busyId.value = null
  }
}

function startEdit(p) {
  editing.value = true
  editForm.id = p.id
  editForm.sku = p.sku || ''
  editForm.name = p.name || ''
  editForm.description = p.description || ''
  editForm.price = Number(p.price || 0)
  editForm.currency = p.currency || 'THB'
  editForm.image_url = p.image_url || ''
  editForm.stock_qty = Number(p.stock_qty || 0)
  editForm.is_enabled = !!p.is_enabled
  success.value = ''
  error.value = ''
}

function cancelEdit() {
  editing.value = false
  savingEdit.value = false
}

async function saveEdit() {
  if (!editForm.id) return
  savingEdit.value = true
  try {
    await adminApi.updateProduct(editForm.id, {
      sku: String(editForm.sku || '').trim(),
      name: String(editForm.name || '').trim(),
      description: String(editForm.description || '').trim(),
      price: Number(editForm.price || 0),
      currency: editForm.currency || 'THB',
      image_url: String(editForm.image_url || '').trim() || null,
      stock_qty: Number(editForm.stock_qty || 0),
      is_enabled: !!editForm.is_enabled,
    })
    setSuccess('Updated.')
    editing.value = false
    await loadProducts()
  } catch (e) {
    setError(e.message)
  } finally {
    savingEdit.value = false
  }
}

onMounted(async () => {
  if (!adminAuth.getToken()) return
  try {
    await adminAuth.me()
    loggedIn.value = true
    await refreshAll()
  } catch {
    adminAuth.clearToken()
    loggedIn.value = false
  }
})
</script>

<style scoped>
.page { min-height: 100vh; background: #0b0c10; color: #e7eaf0; }
.container { max-width:1100px; margin:0 auto; padding:12px 24px 40px; }
.card { background:#0f111a; border:1px solid #1b1e2e; border-radius:18px; padding:18px; box-shadow:0 10px 30px rgba(0,0,0,.25); }
.full { margin-top:18px; }
.row { display:flex; gap:10px; align-items:end; flex-wrap: wrap; }
.field { display:flex; flex-direction:column; gap:8px; }
.grow { flex:1; }
label { font-size:12px; color:#9aa3b2; }
input { background:#0b0c10; border:1px solid #22263a; color:#e7eaf0; border-radius:14px; padding:12px 12px; outline:none; }
.btn { border:1px solid #2a2f45; background:#121426; color:#e7eaf0; padding:10px 14px; border-radius:14px; cursor:pointer; }
.btn.primary { background:#2e6bff; border-color:#2e6bff; }
.btn.danger { background:#2a0f14; border-color:#5a1f2b; color:#ffb3c0; }
.btn:disabled { opacity:.55; cursor:not-allowed; }
.muted { color:#9aa3b2; font-size:13px; margin-top:6px; }
.list { display:flex; flex-direction:column; gap:12px; margin-top:12px; }
.item { display:flex; justify-content:space-between; gap:12px; padding:12px; border-radius:16px; border:1px solid #1b1e2e; background:#0b0c10; }
.info .name { font-weight:650; }
.meta { color:#9aa3b2; font-size:12px; margin-top:4px; }
.actions { display:flex; gap:10px; align-items:center; flex-wrap: wrap; }
.pill { font-size:12px; padding:6px 10px; border-radius:999px; border:1px solid #2a2f45; }
.pill.on { color:#7dffb0; border-color:#1f6a3a; background:#0c1a12; }
.pill.off { color:#ffb37d; border-color:#6a3a1f; background:#1a120c; }
.sep { margin:16px 0; border:0; border-top:1px solid #1b1e2e; }
.form { display:flex; flex-direction:column; gap:12px; margin-top:10px; }
.error { margin-top:10px; color:#ff8697; }
.success { margin-top:10px; color:#7dffb0; }
.edit-box{
  margin-top:16px;
  padding:14px;
  border-radius:18px;
  border:1px solid #1b1e2e;
  background:#0b0c10;
}
</style>

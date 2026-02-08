<template>
  <div>
    <h1 style="margin:0 0 6px 0;">Admin</h1>
    <div class="muted">Manage product availability and view orders. (Admin Key required)</div>

    <div class="hr"></div>

    <div class="card">
      <div class="card-body">
        <div class="row" style="gap:12px; flex-wrap:wrap;">
          <div class="field" style="min-width:320px; flex:1;">
            <label class="muted">Admin Key</label>
            <input class="input" v-model="adminKey" placeholder="Enter ADMIN_KEY" />
          </div>
          <div style="display:flex; gap:10px; align-items:flex-end;">
            <button class="btn primary" @click="saveKey">Save key</button>
            <button class="btn" @click="refresh">Refresh</button>
          </div>
        </div>

        <div v-if="error" style="margin-top:12px; color:var(--danger);">{{ error }}</div>
      </div>
    </div>

    <div class="hr"></div>

    <div class="grid" style="grid-template-columns: 1fr 1fr;">
      <div class="card">
        <div class="card-body">
          <h2 style="margin:0 0 10px 0;">Products</h2>

          <div class="muted" style="font-size:12px; margin-bottom:10px;">
            Toggle enabled to show/hide products in the shop.
          </div>

          <div v-if="loadingProducts" class="muted">Loading products...</div>

          <div v-else>
            <div v-for="p in products" :key="p.id" style="padding:12px 0; border-bottom:1px solid var(--line);">
              <div class="row" style="gap:10px; align-items:flex-start;">
                <div style="flex:1;">
                  <div style="font-weight:700;">{{ p.name }}</div>
                  <div class="muted" style="font-size:12px;">SKU: {{ p.sku }} • Stock: {{ p.stock_qty }}</div>
                </div>

                <span class="badge" :class="p.is_enabled ? 'ok' : 'no'">
                  {{ p.is_enabled ? 'Enabled' : 'Disabled' }}
                </span>

                <button class="btn" @click="toggle(p)">{{ p.is_enabled ? 'Disable' : 'Enable' }}</button>
              </div>
            </div>
          </div>

          <div class="hr"></div>

          <h3 style="margin:0 0 10px 0;">Add product</h3>
          <div class="grid" style="grid-template-columns: 1fr 1fr; gap:12px;">
            <div class="field">
              <label class="muted">SKU</label>
              <input class="input" v-model="newP.sku" placeholder="TNM-100" />
            </div>
            <div class="field">
              <label class="muted">Name</label>
              <input class="input" v-model="newP.name" placeholder="Product name" />
            </div>
            <div class="field" style="grid-column: 1 / -1;">
              <label class="muted">Description</label>
              <input class="input" v-model="newP.description" placeholder="Short description" />
            </div>
            <div class="field">
              <label class="muted">Price</label>
              <input class="input" type="number" min="0" step="0.01" v-model.number="newP.price" />
            </div>
            <div class="field">
              <label class="muted">Stock</label>
              <input class="input" type="number" min="0" step="1" v-model.number="newP.stock_qty" />
            </div>
            <div class="field" style="grid-column: 1 / -1;">
              <label class="muted">Image URL (optional)</label>
              <input class="input" v-model="newP.image_url" placeholder="https://..." />
            </div>
          </div>

          <div style="margin-top:12px;">
            <button class="btn primary" :disabled="creating" @click="createProduct">
              {{ creating ? 'Creating...' : 'Create product' }}
            </button>
          </div>

          <div v-if="createMsg" style="margin-top:10px;" :style="{ color: createMsgOk ? 'var(--ok)' : 'var(--danger)' }">
            {{ createMsg }}
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-body">
          <h2 style="margin:0 0 10px 0;">Recent orders</h2>
          <div v-if="loadingOrders" class="muted">Loading orders...</div>

          <div v-else>
            <div v-for="o in orders" :key="o.id" style="padding:12px 0; border-bottom:1px solid var(--line);">
              <div class="row">
                <div style="flex:1;">
                  <div style="font-weight:700;">{{ o.order_number }}</div>
                  <div class="muted" style="font-size:12px;">{{ o.customer_email }}</div>
                </div>
                <span class="badge" :class="o.status === 'paid' ? 'ok' : 'no'">{{ o.status }}</span>
              </div>

              <div class="row" style="margin-top:8px;">
                <div class="muted">Total</div>
                <div style="font-weight:800;">{{ fmt(o.total) }} {{ o.currency }}</div>
              </div>
            </div>
          </div>

          <div class="hr"></div>

          <div class="muted" style="font-size:12px;">
            Tip: In production, replace admin key with proper auth (e.g., Sanctum/JWT).
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { adminApi } from '../services/api'

const adminKey = ref(localStorage.getItem('tnmshop_admin_key') || '')
const error = ref('')

const products = ref([])
const orders = ref([])

const loadingProducts = ref(false)
const loadingOrders = ref(false)

const creating = ref(false)
const createMsg = ref('')
const createMsgOk = ref(false)

const newP = reactive({
  sku: '',
  name: '',
  description: '',
  price: 0,
  stock_qty: 0,
  image_url: '',
  is_enabled: true,
  currency: 'THB',
})

function fmt(v){
  const n = Number(v || 0)
  return n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function saveKey(){
  localStorage.setItem('tnmshop_admin_key', adminKey.value.trim())
  refresh()
}

async function refresh(){
  error.value = ''
  if (!adminKey.value.trim()){
    error.value = 'Please enter Admin Key.'
    return
  }
  await Promise.all([loadProducts(), loadOrders()])
}

async function loadProducts(){
  loadingProducts.value = true
  try{
    const res = await adminApi.listProducts(adminKey.value.trim())
    products.value = res.data || []
  }catch(e){
    error.value = e.message || 'Failed to load products'
  }finally{
    loadingProducts.value = false
  }
}

async function loadOrders(){
  loadingOrders.value = true
  try{
    const res = await adminApi.listOrders(adminKey.value.trim())
    orders.value = res.data || []
  }catch(e){
    error.value = e.message || 'Failed to load orders'
  }finally{
    loadingOrders.value = false
  }
}

async function toggle(p){
  error.value = ''
  try{
    const res = await adminApi.toggleProduct(adminKey.value.trim(), p.id, !p.is_enabled)
    const updated = res.data
    const idx = products.value.findIndex(x => x.id === p.id)
    if (idx >= 0) products.value[idx] = updated
  }catch(e){
    error.value = e.message || 'Toggle failed'
  }
}

async function createProduct(){
  createMsg.value = ''
  error.value = ''
  if (!newP.sku || !newP.name) {
    createMsgOk.value = false
    createMsg.value = 'SKU and Name are required.'
    return
  }
  creating.value = true
  try{
    await adminApi.createProduct(adminKey.value.trim(), { ...newP })
    createMsgOk.value = true
    createMsg.value = 'Created.'
    newP.sku = ''
    newP.name = ''
    newP.description = ''
    newP.price = 0
    newP.stock_qty = 0
    newP.image_url = ''
    await loadProducts()
  }catch(e){
    createMsgOk.value = false
    createMsg.value = e.message || 'Create failed'
  }finally{
    creating.value = false
  }
}

refresh()
</script>

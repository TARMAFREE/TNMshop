<template>
  <div>
    <div class="row" style="align-items:flex-end; gap:16px; flex-wrap:wrap;">
      <div>
        <h1 style="margin:0 0 6px 0;">Shop</h1>
        <div class="muted">Minimal storefront. Add items to your cart and checkout.</div>
      </div>
      <div class="field" style="min-width:260px;">
        <label class="muted">Search</label>
        <input class="input" v-model="q" @input="debouncedLoad" placeholder="Search products..." />
      </div>
    </div>

    <div class="hr"></div>

    <div v-if="loading" class="muted">Loading products...</div>
    <div v-else-if="error" class="muted" style="color: var(--danger);">{{ error }}</div>

    <div v-else class="grid">
      <div v-for="p in products" :key="p.id" class="card">
        <img v-if="p.image_url" :src="p.image_url" alt="" style="width:100%; height:190px; object-fit:cover; background:#111;" />
        <div class="card-body">
          <div class="row" style="align-items:flex-start;">
            <div>
              <div style="font-weight:700;">{{ p.name }}</div>
              <div class="muted" style="margin-top:4px; line-height:1.35;">{{ p.description || '—' }}</div>
            </div>
            <div style="text-align:right; min-width:110px;">
              <div style="font-weight:700;">{{ fmt(p.price) }}</div>
              <div class="muted" style="font-size:12px;">{{ p.currency }}</div>
            </div>
          </div>

          <div class="hr"></div>

          <div class="row" style="gap:10px;">
            <span class="badge" :class="p.stock_qty > 0 ? 'ok' : 'no'">
              {{ p.stock_qty > 0 ? `In stock: ${p.stock_qty}` : 'Out of stock' }}
            </span>
            <button class="btn primary" :disabled="p.stock_qty <= 0" @click="add(p)">
              Add to cart
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { publicApi } from '../services/api'
import { useCartStore } from '../stores/cart'

const cart = useCartStore()
const products = ref([])
const loading = ref(false)
const error = ref('')
const q = ref('')

function fmt(v){
  const n = Number(v || 0)
  return n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

async function load(){
  loading.value = true
  error.value = ''
  try{
    const res = await publicApi.listProducts(q.value.trim())
    products.value = res.data || []
  }catch(e){
    error.value = e.message || 'Failed to load'
  }finally{
    loading.value = false
  }
}

function add(p){
  cart.add(p, 1)
}

let t = null
function debouncedLoad(){
  clearTimeout(t)
  t = setTimeout(load, 250)
}

load()
</script>

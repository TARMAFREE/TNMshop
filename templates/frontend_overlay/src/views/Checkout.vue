<template>
  <div>
    <h1 style="margin:0 0 6px 0;">Checkout</h1>
    <div class="muted">Enter shipping details and create an order.</div>

    <div class="hr"></div>

    <div v-if="!cart.items.length" class="card">
      <div class="card-body">
        <div>Your cart is empty.</div>
        <div class="hr"></div>
        <RouterLink class="btn primary" to="/">Go to shop</RouterLink>
      </div>
    </div>

    <div v-else class="grid" style="grid-template-columns: 1.2fr 0.8fr;">
      <div class="card">
        <div class="card-body">
          <h2 style="margin:0 0 10px 0;">Shipping</h2>

          <div class="field" style="margin-bottom:12px;">
            <label class="muted">Full name</label>
            <input class="input" v-model="form.customer_name" placeholder="John Doe" />
          </div>

          <div class="field" style="margin-bottom:12px;">
            <label class="muted">Email</label>
            <input class="input" v-model="form.customer_email" placeholder="john@example.com" />
          </div>

          <div class="field" style="margin-bottom:12px;">
            <label class="muted">Phone (optional)</label>
            <input class="input" v-model="form.customer_phone" placeholder="0812345678" />
          </div>

          <div class="field">
            <label class="muted">Shipping address</label>
            <textarea class="textarea" v-model="form.shipping_address" placeholder="Address..."></textarea>
          </div>

          <div class="hr"></div>

          <button class="btn primary" :disabled="loading" @click="submit">
            {{ loading ? 'Processing...' : 'Place order & pay' }}
          </button>

          <div v-if="error" style="margin-top:12px; color:var(--danger);">{{ error }}</div>
        </div>
      </div>

      <div class="card">
        <div class="card-body">
          <h2 style="margin:0 0 10px 0;">Summary</h2>
          <div v-for="it in cart.items" :key="it.product.id" class="row" style="margin-bottom:8px;">
            <div style="flex:1;">
              <div style="font-weight:600;">{{ it.product.name }}</div>
              <div class="muted" style="font-size:12px;">Qty: {{ it.quantity }}</div>
            </div>
            <div style="font-weight:700;">{{ fmt(Number(it.product.price) * it.quantity) }}</div>
          </div>

          <div class="hr"></div>

          <div class="row">
            <div class="muted">Subtotal</div>
            <div style="font-weight:800;">{{ fmt(cart.subtotal) }} THB</div>
          </div>

          <div class="muted" style="margin-top:10px; font-size:12px;">
            Tax and shipping are configured in backend environment variables.
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useCartStore } from '../stores/cart'
import { publicApi } from '../services/api'

const router = useRouter()
const cart = useCartStore()
const loading = ref(false)
const error = ref('')

const form = reactive({
  customer_name: '',
  customer_email: '',
  customer_phone: '',
  shipping_address: '',
})

function fmt(v){
  const n = Number(v || 0)
  return n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

async function submit(){
  error.value = ''
  if (!form.customer_name || !form.customer_email || !form.shipping_address) {
    error.value = 'Please fill in name, email, and shipping address.'
    return
  }

  const payload = {
    ...form,
    items: cart.items.map(it => ({ product_id: it.product.id, quantity: it.quantity })),
  }

  loading.value = true
  try{
    const res = await publicApi.checkout(payload)
    const url = res?.data?.redirect_url
    const intent = res?.data?.payment_intent_id
    if (!url || !intent) throw new Error('Checkout failed: missing redirect_url')
    // optional: clear cart after intent creation? keep until payment success:
    router.push({ path: '/payment', query: { intent } })
  }catch(e){
    error.value = e.message || 'Checkout failed'
  }finally{
    loading.value = false
  }
}
</script>

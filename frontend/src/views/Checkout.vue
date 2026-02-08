<template>
  <div>
    <h1 style="margin:0 0 6px 0;">Checkout</h1>
    <div class="muted">Enter shipping details and create an order.</div>

    <div class="hr"></div>

    <div v-if="!cart.items.length" class="card">
      <div class="card-body">
        <div>Your cart is empty.</div>
        <div class="hr"></div>
        <RouterLink class="btn primary" to="/shop">Go to shop</RouterLink>
      </div>
    </div>

    <div v-else class="grid" style="grid-template-columns: 1.2fr 0.8fr;">
      <div class="card">
        <div class="card-body">
          <h2 style="margin:0 0 10px 0;">Shipping</h2>

          <div class="field" style="margin-bottom:12px;">
            <label class="muted">Full name *</label>
            <input
              class="input"
              v-model.trim="form.customer_name"
              placeholder="John Doe"
              required
              autocomplete="name"
            />
          </div>

          <div class="field" style="margin-bottom:12px;">
            <label class="muted">Email *</label>
            <input
              class="input"
              v-model.trim="form.customer_email"
              type="email"
              inputmode="email"
              autocomplete="email"
              placeholder="john@example.com"
              required
            />
          </div>

          <div class="field" style="margin-bottom:12px;">
            <label class="muted">Phone *</label>
            <input
              class="input"
              v-model.trim="form.customer_phone"
              placeholder="0812345678"
              type="text"
              inputmode="numeric"
              autocomplete="tel"
              maxlength="10"
              pattern="[0-9]{10}"
              required
              @input="onPhoneInput"
            />
            <div class="muted" style="font-size:12px; margin-top:6px;">
              Please enter 10 digits (Thailand mobile format).
            </div>
          </div>

          <div class="field">
            <label class="muted">Shipping address *</label>
            <textarea
              class="textarea"
              v-model.trim="form.shipping_address"
              placeholder="Address..."
              required
            ></textarea>
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

function onPhoneInput(e) {
  const v = String(e.target.value || '').replace(/\D/g, '').slice(0, 10)
  e.target.value = v
  form.customer_phone = v
}

function isValidEmail(email) {
  const v = String(email || '').trim()
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)
}

function isValidThaiPhone(phone) {
  const v = String(phone || '').replace(/\D/g, '')
  return /^[0-9]{10}$/.test(v)
}

function fmt(v){
  const n = Number(v || 0)
  return n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

async function submit(){
  error.value = ''

  // ✅ required ทุกช่อง
  if (!form.customer_name?.trim() || !form.customer_email?.trim() || !form.customer_phone?.trim() || !form.shipping_address?.trim()) {
    error.value = 'Please fill in all required fields.'
    return
  }

  if (!isValidEmail(form.customer_email)) {
    error.value = 'Please enter a valid email address.'
    return
  }

  if (!isValidThaiPhone(form.customer_phone)) {
    error.value = 'Please enter a valid phone number (10 digits).'
    return
  }

  const payload = {
    ...form,
    items: cart.items.map(it => ({ product_id: it.product.id, quantity: it.quantity })),
  }

  loading.value = true
  try{
    const res = await publicApi.checkout(payload)
    const intent = res?.data?.payment_intent_id
    if (!intent) throw new Error('Checkout failed: missing payment_intent_id')
    router.push({ path: '/payment', query: { intent } })
  }catch(e){
    error.value = e.message || 'Checkout failed'
  }finally{
    loading.value = false
  }
}
</script>

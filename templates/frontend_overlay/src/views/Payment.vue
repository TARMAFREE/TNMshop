<template>
  <div>
    <h1 style="margin:0 0 6px 0;">Payment</h1>
    <div class="muted">Mock payment gateway. Use test card 4242 4242 4242 4242 (passes Luhn).</div>

    <div class="hr"></div>

    <div v-if="!intentId" class="card">
      <div class="card-body">
        <div style="color:var(--danger);">Missing payment intent.</div>
        <div class="hr"></div>
        <RouterLink class="btn" to="/">Back to shop</RouterLink>
      </div>
    </div>

    <div v-else class="grid" style="grid-template-columns: 1fr 0.8fr;">
      <div class="card">
        <div class="card-body">
          <h2 style="margin:0 0 10px 0;">Card details</h2>

          <div class="field" style="margin-bottom:12px;">
            <label class="muted">Card number</label>
            <input class="input" v-model="card.card_number" placeholder="4242 4242 4242 4242" />
          </div>

          <div class="row" style="gap:12px;">
            <div class="field" style="flex:1;">
              <label class="muted">Exp month</label>
              <input class="input" type="number" min="1" max="12" v-model.number="card.exp_month" />
            </div>
            <div class="field" style="flex:1;">
              <label class="muted">Exp year</label>
              <input class="input" type="number" min="2000" max="2100" v-model.number="card.exp_year" />
            </div>
            <div class="field" style="flex:1;">
              <label class="muted">CVC</label>
              <input class="input" v-model="card.cvc" placeholder="123" />
            </div>
          </div>

          <div class="hr"></div>

          <button class="btn primary" :disabled="loading || paid" @click="pay">
            {{ loading ? 'Processing...' : paid ? 'Paid' : 'Pay now' }}
          </button>

          <button class="btn" style="margin-left:10px;" :disabled="loading || paid" @click="simulate('success')">Simulate success</button>
          <button class="btn" style="margin-left:10px;" :disabled="loading || paid" @click="simulate('fail')">Simulate fail</button>

          <div v-if="message" style="margin-top:12px;" :style="{ color: paid ? 'var(--ok)' : 'var(--danger)' }">
            {{ message }}
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-body">
          <h2 style="margin:0 0 10px 0;">Order</h2>
          <div v-if="loadingOrder" class="muted">Loading order...</div>
          <div v-else-if="orderError" style="color:var(--danger);">{{ orderError }}</div>

          <div v-else>
            <div class="row">
              <div class="muted">Order</div>
              <div style="font-weight:800;">{{ order.order_number }}</div>
            </div>
            <div class="row" style="margin-top:6px;">
              <div class="muted">Total</div>
              <div style="font-weight:800;">{{ fmt(order.total) }} {{ order.currency }}</div>
            </div>

            <div class="hr"></div>

            <div v-for="it in order.items" :key="it.id" class="row" style="margin-bottom:8px;">
              <div style="flex:1;">
                <div style="font-weight:600;">{{ it.name }}</div>
                <div class="muted" style="font-size:12px;">Qty: {{ it.quantity }}</div>
              </div>
              <div style="font-weight:700;">{{ fmt(it.line_total) }}</div>
            </div>

            <div class="hr"></div>

            <div class="muted" style="font-size:12px;">
              Status: <span class="badge" :class="order.status === 'paid' ? 'ok' : 'no'">{{ order.status }}</span>
            </div>

            <div style="margin-top:12px;">
              <RouterLink class="btn" to="/">Back to shop</RouterLink>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { computed, reactive, ref } from 'vue'
import { useRoute } from 'vue-router'
import { publicApi } from '../services/api'
import { useCartStore } from '../stores/cart'

const route = useRoute()
const cartStore = useCartStore()

const intentId = computed(() => String(route.query.intent || '').trim() || '')
const loadingOrder = ref(false)
const orderError = ref('')
const order = ref(null)

const loading = ref(false)
const message = ref('')
const paid = ref(false)

const card = reactive({
  card_number: '4242 4242 4242 4242',
  exp_month: 12,
  exp_year: 2030,
  cvc: '123',
})

function fmt(v){
  const n = Number(v || 0)
  return n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

async function load(){
  if (!intentId.value) return
  loadingOrder.value = true
  orderError.value = ''
  try{
    const res = await publicApi.getPayment(intentId.value)
    order.value = res.data.order
    const pi = res.data.payment_intent
    paid.value = pi.status === 'succeeded'
    if (paid.value) cartStore.clear()
  }catch(e){
    orderError.value = e.message || 'Failed to load order'
  }finally{
    loadingOrder.value = false
  }
}

async function pay(extra = {}){
  if (!intentId.value) return
  loading.value = true
  message.value = ''
  try{
    const res = await publicApi.confirmPayment(intentId.value, { ...card, ...extra })
    const pi = res.data.payment_intent
    order.value = res.data.order
    if (pi.status === 'succeeded') {
      paid.value = true
      message.value = 'Payment successful. Thank you!'
      cartStore.clear()
    } else {
      paid.value = false
      message.value = 'Payment failed. Please try again.'
    }
  }catch(e){
    paid.value = false
    message.value = e.message || 'Payment failed'
  }finally{
    loading.value = false
  }
}

function simulate(which){
  pay({ simulate: which })
}

load()
</script>

<template>
  <div>
    <h1 style="margin:0 0 6px 0;">Cart</h1>
    <div class="muted">Review your items before checkout.</div>

    <div class="hr"></div>

    <div v-if="!cart.items.length" class="card">
      <div class="card-body">
        <div>Your cart is empty.</div>
        <div class="hr"></div>
        <RouterLink class="btn primary" to="/">Continue shopping</RouterLink>
      </div>
    </div>

    <div v-else class="card">
      <div class="card-body">
        <div v-for="it in cart.items" :key="it.product.id" style="padding:12px 0; border-bottom:1px solid var(--line);">
          <div class="row" style="align-items:flex-start; gap:14px;">
            <div style="flex:1;">
              <div style="font-weight:700;">{{ it.product.name }}</div>
              <div class="muted" style="font-size:12px;">SKU: {{ it.product.sku }}</div>
            </div>

            <div style="width:140px; text-align:right;">
              <div style="font-weight:700;">{{ fmt(it.product.price) }}</div>
              <div class="muted" style="font-size:12px;">{{ it.product.currency }}</div>
            </div>

            <div style="width:140px;">
              <div class="field">
                <label class="muted" style="font-size:12px;">Qty</label>
                <input class="input" type="number" min="1" v-model.number="it.quantity" @change="cart.setQty(it.product.id, it.quantity)" />
              </div>
            </div>

            <div style="width:120px; text-align:right;">
              <div style="font-weight:700;">{{ fmt(Number(it.product.price) * it.quantity) }}</div>
              <div class="muted" style="font-size:12px;">Line total</div>
            </div>

            <button class="btn danger" @click="cart.remove(it.product.id)">Remove</button>
          </div>
        </div>

        <div class="row" style="margin-top:14px;">
          <div class="muted">Subtotal</div>
          <div style="font-weight:800;">{{ fmt(cart.subtotal) }} THB</div>
        </div>

        <div class="hr"></div>

        <div class="row" style="flex-wrap:wrap; gap:10px;">
          <RouterLink class="btn" to="/">Continue shopping</RouterLink>
          <RouterLink class="btn primary" to="/checkout">Checkout</RouterLink>
          <button class="btn" @click="cart.clear()">Clear cart</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useCartStore } from '../stores/cart'
const cart = useCartStore()

function fmt(v){
  const n = Number(v || 0)
  return n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}
</script>

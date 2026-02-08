<script setup>
import { useCartStore } from '../stores/cart'

const cart = useCartStore()

function fmt(v){
  const n = Number(v || 0)
  return n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function stockOf(it){
  const s = Number(it?.product?.stock_qty)
  return Number.isFinite(s) ? s : null
}

function dec(it){
  const next = Math.max(1, Number(it.quantity) - 1)
  cart.setQty(it.product.id, next)
}

function inc(it){
  const st = stockOf(it)
  const nextRaw = Number(it.quantity) + 1
  const next = st == null ? nextRaw : Math.min(nextRaw, st)
  cart.setQty(it.product.id, next)
}

function removeItem(productId){
  cart.remove(productId)
  if (!cart.items.length) cart.closeCart()
}
</script>

<template>
  <div
    v-if="cart.isOpen"
    class="modal-overlay"
    @click.self="cart.closeCart()"
  >
    <div class="modal-panel card">
      <div class="card-body">
        <div class="row" style="align-items:center;">
          <div style="font-weight:800;">Cart</div>
          <button class="btn btn-close" @click="cart.closeCart()">Close</button>

        </div>

        <div class="hr"></div>

        <div v-if="!cart.items.length" class="muted">Your cart is empty.</div>

        <div v-else>
          <div
            v-for="it in cart.items"
            :key="it.product.id"
            class="item"
          >
            <div class="row" style="gap:10px; align-items:flex-start;">
              <div style="flex:1;">
                <div style="font-weight:700;">{{ it.product.name }}</div>
                <div class="muted" style="font-size:12px;" v-if="stockOf(it) != null">
                  Stock: {{ stockOf(it) }}
                </div>

                <div class="qty">
                  <button class="qtybtn" @click="dec(it)" :disabled="it.quantity <= 1">-</button>
                  <div class="qtynum">{{ it.quantity }}</div>
                  <button
                    class="qtybtn"
                    @click="inc(it)"
                    :disabled="stockOf(it) != null && it.quantity >= stockOf(it)"
                  >
                    +
                  </button>
                </div>
              </div>

              <div style="text-align:right; min-width:150px;">
                <div style="font-weight:800;">{{ fmt(Number(it.product.price) * it.quantity) }}</div>
                <div class="muted" style="font-size:12px;">Line total</div>

                <button
                  class="btn danger"
                  style="margin-top:10px; width:100%;"
                  @click="removeItem(it.product.id)"
                >
                  Remove
                </button>
              </div>
            </div>
          </div>

          <div class="row" style="margin-top:12px;">
            <div class="muted">Subtotal</div>
            <div style="font-weight:900;">{{ fmt(cart.subtotal) }} THB</div>
          </div>

          <div class="hr"></div>

          <div class="row" style="gap:10px; flex-wrap:wrap;">
            <RouterLink class="btn" to="/cart" @click="cart.closeCart()">View cart</RouterLink>
            <RouterLink class="btn primary" to="/checkout" @click="cart.closeCart()">Checkout</RouterLink>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.modal-overlay{
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.55);
  z-index: 1000;
  display: flex;
  justify-content: flex-end;
}
.modal-panel{
  width: min(440px, 92vw);
  height: 100%;
  border-left: 1px solid var(--line);
}
.item{
  padding: 12px 0;
  border-bottom: 1px solid var(--line);
}
.qty{
  margin-top: 10px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}
.qtybtn{
  width: 34px;
  height: 34px;
  border: 1px solid var(--line);
  background: #fff;
  cursor: pointer;
}
.qtybtn:disabled{
  opacity: .4;
  cursor: not-allowed;
}
.qtynum{
  width: 44px;
  height: 34px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: 1px solid var(--line);
  background: #000000;
  font-weight: 800;
}
.btn.danger{
  background: #fff;
  border: 1px solid var(--line);
}
.btn.danger:hover{
  opacity: .9;
}
.btn-close{
  background: transparent !important;
  color: #fff !important;
  border: 1px solid rgba(255,255,255,0.5) !important;
}
.btn-close:hover{
  border-color: rgba(255,255,255,0.85) !important;
  opacity: .95;
}

</style>

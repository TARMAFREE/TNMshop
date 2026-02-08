import { defineStore } from 'pinia'

function load() {
  try { return JSON.parse(localStorage.getItem('tnmshop_cart') || '[]') } catch { return [] }
}
function save(items) {
  localStorage.setItem('tnmshop_cart', JSON.stringify(items))
}

export const useCartStore = defineStore('cart', {
  state: () => ({
    items: load(), // [{ product, quantity }]
  }),
  getters: {
    count(state) {
      return state.items.reduce((s, it) => s + it.quantity, 0)
    },
    subtotal(state) {
      return state.items.reduce((s, it) => s + (Number(it.product.price) * it.quantity), 0)
    },
  },
  actions: {
    add(product, qty = 1) {
      const existing = this.items.find(i => i.product.id === product.id)
      if (existing) existing.quantity += qty
      else this.items.push({ product, quantity: qty })
      save(this.items)
    },
    remove(productId) {
      this.items = this.items.filter(i => i.product.id !== productId)
      save(this.items)
    },
    setQty(productId, qty) {
      const it = this.items.find(i => i.product.id === productId)
      if (!it) return
      it.quantity = Math.max(1, Number(qty || 1))
      save(this.items)
    },
    clear() {
      this.items = []
      save(this.items)
    }
  }
})

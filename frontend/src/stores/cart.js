import { defineStore } from 'pinia'
import { adminAuth, customerAuth } from '../services/api'

const CART_KEY = 'tnmshop_cart'

function canPersistCart() {
  // ให้จำ cart เฉพาะ customer ที่ล็อกอิน และไม่ใช่ admin
  return !!customerAuth.getToken() && !adminAuth.getToken()
}

function loadPersistedCart() {
  try {
    return JSON.parse(localStorage.getItem(CART_KEY) || '[]')
  } catch {
    return []
  }
}

function savePersistedCart(items) {
  localStorage.setItem(CART_KEY, JSON.stringify(items))
}

export const useCartStore = defineStore('cart', {
  state: () => ({
    // Guest จะเริ่มว่างเสมอ (ไม่โหลดจาก localStorage)
    // Customer ที่ล็อกอินจะโหลดจาก localStorage
    items: canPersistCart() ? loadPersistedCart() : [],
    isOpen: false,
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
    openCart() {
      this.isOpen = true
    },
    closeCart() {
      this.isOpen = false
    },

    // เรียกใช้ตอน role เปลี่ยนเป็น customer เพื่อโหลด cart ที่เคยจำไว้
    hydrate() {
      this.items = canPersistCart() ? loadPersistedCart() : []
    },

    // บันทึกเฉพาะตอน customer ล็อกอิน
    flush() {
      if (canPersistCart()) savePersistedCart(this.items)
    },

    // ล้างในหน่วยความจำอย่างเดียว (ไม่กระทบ localStorage)
    resetRuntime() {
      this.items = []
      this.isOpen = false
    },

    add(product, qty = 1) {
      const stock = Number(product?.stock_qty)
      const hasStock = Number.isFinite(stock)

      const existing = this.items.find(i => i.product.id === product.id)
      if (existing) {
        const next = existing.quantity + Number(qty || 1)
        existing.quantity = hasStock ? Math.min(next, stock) : next
      } else {
        const next = Number(qty || 1)
        this.items.push({ product, quantity: hasStock ? Math.min(next, stock) : next })
      }

      this.flush()
      this.openCart()
    },

    remove(productId) {
      this.items = this.items.filter(i => i.product.id !== productId)
      this.flush()
    },

    setQty(productId, qty) {
      const it = this.items.find(i => i.product.id === productId)
      if (!it) return

      const stock = Number(it.product?.stock_qty)
      const hasStock = Number.isFinite(stock)

      let next = Math.max(1, Number(qty || 1))
      if (hasStock) next = Math.min(next, stock)

      it.quantity = next
      this.flush()
    },

    clear() {
      this.items = []
      this.flush()
    },
  },
})

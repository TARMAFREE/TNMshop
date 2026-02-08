import { createRouter, createWebHistory } from 'vue-router'
import Shop from '../views/Shop.vue'
import Cart from '../views/Cart.vue'
import Checkout from '../views/Checkout.vue'
import Payment from '../views/Payment.vue'
import Admin from '../views/Admin.vue'

const routes = [
  { path: '/', name: 'shop', component: Shop },
  { path: '/cart', name: 'cart', component: Cart },
  { path: '/checkout', name: 'checkout', component: Checkout },
  { path: '/payment', name: 'payment', component: Payment },
  { path: '/admin', name: 'admin', component: Admin },
]

export default createRouter({
  history: createWebHistory(),
  routes,
})

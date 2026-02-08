import { createRouter, createWebHistory } from 'vue-router'

import Home from '../views/Home.vue'
import Login from '../views/Login.vue'
import Register from '../views/Register.vue'
import Account from '../views/Account.vue'

import Shop from '../views/Shop.vue'
import Cart from '../views/Cart.vue'
import Checkout from '../views/Checkout.vue'
import Payment from '../views/Payment.vue'

import Admin from '../views/Admin.vue'
import AdminSecretCreate from '../views/AdminSecretCreate.vue'

import { adminAuth, customerAuth } from '../services/api'

const routes = [
  // Public (ยังไม่ล็อกอิน)
  { path: '/', component: Home, meta: { allowUnauth: true, area: 'public' } },
  { path: '/login', component: Login, meta: { allowUnauth: true, area: 'public' } },
  { path: '/register', component: Register, meta: { allowUnauth: true, area: 'public' } },

  // Shopping (Guest + Customer เข้าได้, Admin เข้าไม่ได้)
  { path: '/shop', component: Shop, meta: { allowUnauth: true, area: 'shopping' } },
  { path: '/cart', component: Cart, meta: { allowUnauth: true, area: 'shopping' } },
  { path: '/checkout', component: Checkout, meta: { allowUnauth: true, area: 'shopping' } },
  { path: '/payment', component: Payment, meta: { allowUnauth: true, area: 'shopping' } },

  // Customer only
  { path: '/account', component: Account, meta: { area: 'customer' } },

  // Admin (หน้า /admin เป็นหน้า login+dashboard ในตัว)
  { path: '/admin', component: Admin, meta: { allowUnauth: true, area: 'admin' } },
  { path: '/admin/secret-create', component: AdminSecretCreate, meta: { area: 'admin' } },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

function getRole() {
  const isAdmin = !!adminAuth.getToken()
  const isCustomer = !isAdmin && !!customerAuth.getToken()
  if (isAdmin) return 'admin'
  if (isCustomer) return 'customer'
  return 'guest'
}

router.beforeEach((to) => {
  const role = getRole()

  // เข้า / แล้วถ้าล็อกอินอยู่ให้เด้งไปฝั่งตัวเอง
  if (to.path === '/') {
    if (role === 'admin') return '/admin'
    if (role === 'customer') return '/account'
    return true
  }

  // Admin logged in: เห็นเฉพาะ admin
  if (role === 'admin') {
    if (to.meta.area !== 'admin') return '/admin'
    return true
  }

  // Customer logged in: เห็น shopping + customer, ไม่เห็น admin
  if (role === 'customer') {
    if (to.meta.area === 'admin') return '/account'
    if (to.path === '/login' || to.path === '/register') return '/account'
    return true
  }

  // Guest: เข้าได้เฉพาะ public + shopping + /admin (เพื่อ admin login)
  if (to.meta.allowUnauth) return true

  // ที่เหลือ (เช่น /account, /admin/secret-create) ให้กลับหน้า Home
  return '/'
})

export default router

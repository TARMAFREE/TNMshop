<template>
  <div class="app">
    <header class="topbar">
      <div class="brand">
        <RouterLink to="/" class="logo">TNMshop</RouterLink>
      </div>

      <nav v-if="showNav" class="nav">
        <!-- Guest -->
        <template v-if="role === 'guest'">
          <!-- ✅ หน้า Login/Admin/Register: โชว์ Home อย่างเดียว -->
          <template v-if="showHomeNavOnly">
            <RouterLink to="/" class="navlink">Home</RouterLink>
          </template>

          <!-- ✅ หน้า Shopping (guest): เพิ่ม Home ด้วย -->
          <template v-else>
            <RouterLink to="/" class="navlink">Home</RouterLink>
            <RouterLink to="/shop" class="navlink">Shop</RouterLink>

            <a href="#" class="navlink" @click.prevent="cart.openCart()">
              Cart <span v-if="cart.count">({{ cart.count }})</span>
            </a>
          </template>
        </template>

        <!-- Customer -->
        <template v-else-if="role === 'customer'">
          <RouterLink to="/" class="navlink">Home</RouterLink>
          <RouterLink to="/shop" class="navlink">Shop</RouterLink>

          <a href="#" class="navlink" @click.prevent="cart.openCart()">
            Cart <span v-if="cart.count">({{ cart.count }})</span>
          </a>

          <RouterLink to="/account" class="navlink">Account</RouterLink>
          <a href="#" class="navlink" @click.prevent="logout">Logout</a>
        </template>

        <!-- Admin -->
        <template v-else-if="role === 'admin'">
          <RouterLink to="/" class="navlink">Home</RouterLink>
          <RouterLink to="/admin" class="navlink">Admin</RouterLink>
          <a href="#" class="navlink" @click.prevent="logout">Logout</a>
        </template>
      </nav>
    </header>

    <main class="container">
      <RouterView />
    </main>

    <footer class="footer">
      <div>© {{ year }} TNMshop</div>
    </footer>

    <!-- Guest + Customer ใช้ตะกร้าได้, Admin ไม่ต้องเห็น -->
    <CartModal v-if="role !== 'admin'" />
  </div>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useCartStore } from './stores/cart'
import CartModal from './components/CartModal.vue'
import { adminAuth, customerAuth } from './services/api'

const cart = useCartStore()
const router = useRouter()
const route = useRoute()
const year = computed(() => new Date().getFullYear())

// ให้ App อัปเดตทันทีหลัง login/logout
const authTick = ref(0)
const bumpAuth = () => (authTick.value += 1)

onMounted(() => window.addEventListener('tnm-auth-changed', bumpAuth))
onUnmounted(() => window.removeEventListener('tnm-auth-changed', bumpAuth))

const role = computed(() => {
  route.fullPath
  authTick.value
  if (adminAuth.getToken()) return 'admin'
  if (customerAuth.getToken()) return 'customer'
  return 'guest'
})

/**
 * ✅ โชว์ Home เฉพาะหน้า auth (หลังจากกด Customer/Admin)
 * - /login (customer login)
 * - /admin (admin login)
 * - /register (สมัคร)  ถ้าไม่อยากให้มีใน register ลบออกได้
 */
const showHomeNavOnly = computed(() => {
  if (role.value !== 'guest') return false
  return route.path === '/login' || route.path === '/admin' || route.path === '/register'
})

/**
 * ✅ แสดง Nav เมื่อ:
 * - customer/admin: แสดงเสมอ (ยกเว้นจะอยากซ่อนในหน้าไหนเพิ่มเอง)
 * - guest: แสดงเฉพาะหน้า shopping หรือหน้า auth
 * - หน้าแรก "/" จะไม่แสดง nav
 */
const showNav = computed(() => {
  if (role.value === 'guest') {
    return route.meta?.area === 'shopping' || showHomeNavOnly.value
  }
  return true
})

// ถ้า Guest กลับหน้า Home ให้รีเซ็ต cart ใน runtime
watch(
  () => route.path,
  (path) => {
    if (role.value === 'guest' && path === '/') {
      cart.resetRuntime()
    }
  }
)

// เวลา role เปลี่ยน: customer hydrate/flush, อื่นๆ reset
watch(
  role,
  (nextRole) => {
    if (nextRole === 'customer') {
      if (cart.items.length === 0) cart.hydrate()
      else cart.flush()
      return
    }
    cart.resetRuntime()
  },
  { immediate: true }
)

function handlePageHide() {
  if (role.value === 'guest') cart.resetRuntime()
}

onMounted(() => window.addEventListener('pagehide', handlePageHide))
onUnmounted(() => window.removeEventListener('pagehide', handlePageHide))

async function logout() {
  if (role.value === 'admin') await adminAuth.logout()
  if (role.value === 'customer') await customerAuth.logout()

  cart.resetRuntime()
  router.push('/')
}
</script>

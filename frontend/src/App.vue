<template>
  <div class="app">
    <header class="topbar">
      <div class="brand">
        <RouterLink to="/" class="logo">TNMshop</RouterLink>
      </div>

      <!-- แสดง Nav เมื่อ:
           - customer/admin (ล็อกอินแล้ว) แสดงเสมอ
           - guest แสดงเฉพาะหน้า shopping -->
      <nav v-if="showNav" class="nav">
        <!-- Guest -->
        <template v-if="role === 'guest'">
          <RouterLink to="/shop" class="navlink">Shop</RouterLink>

          <a href="#" class="navlink" @click.prevent="cart.openCart()">
            Cart <span v-if="cart.count">({{ cart.count }})</span>
          </a>
        </template>

        <!-- Customer -->
        <template v-else-if="role === 'customer'">
          <RouterLink to="/shop" class="navlink">Shop</RouterLink>

          <a href="#" class="navlink" @click.prevent="cart.openCart()">
            Cart <span v-if="cart.count">({{ cart.count }})</span>
          </a>

          <RouterLink to="/account" class="navlink">Account</RouterLink>

          <a href="#" class="navlink" @click.prevent="logout">Logout</a>
        </template>

        <!-- Admin -->
        <template v-else-if="role === 'admin'">
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

// ให้ App อัปเดตทันทีหลัง login/logout (โดยเฉพาะหน้า /admin ที่ไม่เปลี่ยน route)
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

const showNav = computed(() => {
  if (role.value === 'guest') return route.meta?.area === 'shopping'
  return role.value === 'customer' || role.value === 'admin'
})

// 1) ถ้า Guest กลับหน้า Home ให้รีเซ็ต cart ทันที
watch(
  () => route.path,
  (path) => {
    if (role.value === 'guest' && path === '/') {
      cart.resetRuntime()
    }
  }
)

// 2) เวลา role เปลี่ยน:
//    - เป็น customer: ถ้ามีของใน cart (มาจาก guest) ให้เริ่มบันทึก, ถ้าว่างให้โหลดจาก localStorage
//    - เป็น guest/admin: ล้าง cart ในหน่วยความจำ เพื่อกันข้ามฝั่ง
watch(
  role,
  (nextRole, prevRole) => {
    if (nextRole === 'customer') {
      if (cart.items.length === 0) cart.hydrate()
      else cart.flush()
      return
    }

    // ออกจาก customer ไปเป็น guest หรือเปลี่ยนเป็น admin ให้ล้างของที่ค้างในหน่วยความจำ
    cart.resetRuntime()
  },
  { immediate: true }
)

// 3) ถ้าออกจากเว็บ/ปิดแท็บ/ย้อนกลับจาก bfcache (บางเบราว์เซอร์) ให้ล้าง guest cart
function handlePageHide() {
  if (role.value === 'guest') cart.resetRuntime()
}

onMounted(() => window.addEventListener('pagehide', handlePageHide))
onUnmounted(() => window.removeEventListener('pagehide', handlePageHide))

async function logout() {
  if (role.value === 'admin') await adminAuth.logout()
  if (role.value === 'customer') await customerAuth.logout()

  // logout แล้วถือเป็น guest ต้องไม่เห็นของเดิมใน cart
  cart.resetRuntime()
  router.push('/')
}
</script>

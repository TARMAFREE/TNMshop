<template>
  <div class="app">
    <header class="topbar">
      <div class="brand">
        <RouterLink to="/" class="logo">TNMshop</RouterLink>
      </div>

      <nav class="nav">
        <RouterLink to="/" class="navlink">Shop</RouterLink>
        <RouterLink to="/cart" class="navlink">Cart <span v-if="cart.count">({{ cart.count }})</span></RouterLink>
        <RouterLink to="/admin" class="navlink">Admin</RouterLink>
      </nav>
    </header>

    <main class="container">
      <RouterView />
    </main>

    <footer class="footer">
      <div>© {{ year }} TNMshop</div>
    </footer>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useCartStore } from './stores/cart'

const cart = useCartStore()
const year = computed(() => new Date().getFullYear())
</script>

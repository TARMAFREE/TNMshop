<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { customerAuth } from '../services/api'

const router = useRouter()

const email = ref('')
const password = ref('')
const loading = ref(false)
const error = ref('')

async function submit() {
  error.value = ''
  if (!email.value || !password.value) return

  loading.value = true
  try {
    await customerAuth.login(email.value, password.value)
    router.push('/account')
  } catch (e) {
    error.value = e.message || 'Login failed'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="card" style="max-width:520px; margin:0 auto;">
    <div class="card-body">
      <h1 style="margin:0 0 6px 0;">Login</h1>
      <div class="muted">Access your account and points.</div>

      <div class="hr"></div>

      <!-- ✅ กด Enter เพื่อ Login ได้เลย -->
      <form @submit.prevent="submit">
        <div class="field" style="margin-bottom:12px;">
          <label class="muted">Email</label>
          <input
            class="input"
            v-model.trim="email"
            type="email"
            placeholder="you@example.com"
            autocomplete="email"
          />
        </div>

        <div class="field" style="margin-bottom:12px;">
          <label class="muted">Password</label>
          <input
            class="input"
            v-model="password"
            type="password"
            placeholder="Your password"
            autocomplete="current-password"
          />
        </div>

        <button class="btn primary" type="submit" :disabled="loading">
          {{ loading ? 'Signing in...' : 'Login' }}
        </button>

        <div v-if="error" style="margin-top:12px; color:var(--danger);">{{ error }}</div>

        <div class="hr"></div>
        <RouterLink class="btn" to="/register">No account? Register</RouterLink>
      </form>
    </div>
  </div>
</template>

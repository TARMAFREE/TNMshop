<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { customerAuth } from '../services/api'

const router = useRouter()

const email = ref('')
const password = ref('')
const loading = ref(false)

const emailError = ref('')
const passwordError = ref('')
const formError = ref('')

function clearErrors() {
  emailError.value = ''
  passwordError.value = ''
  formError.value = ''
}

function applyAuthError(e) {
  const d = e?.data

  // ✅ 401 จาก backend: แยก field มาแล้ว
  if (d?.field === 'email') {
    emailError.value = d?.message || 'อีเมลผิด'
    return
  }
  if (d?.field === 'password') {
    passwordError.value = d?.message || 'รหัสผ่านผิด'
    return
  }

  // ✅ 422 validation (Laravel)
  if (d?.errors) {
    if (Array.isArray(d.errors.email) && d.errors.email.length) emailError.value = d.errors.email[0]
    if (Array.isArray(d.errors.password) && d.errors.password.length) passwordError.value = d.errors.password[0]
    if (!emailError.value && !passwordError.value) formError.value = d?.message || 'Login failed'
    return
  }

  formError.value = e?.message || 'Login failed'
}

async function submit() {
  clearErrors()

  if (!email.value) emailError.value = 'กรุณากรอกอีเมล'
  if (!password.value) passwordError.value = 'กรุณากรอกรหัสผ่าน'
  if (emailError.value || passwordError.value) return

  loading.value = true
  try {
    await customerAuth.login(email.value, password.value)
    router.push('/account')
  } catch (e) {
    applyAuthError(e)
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
          <div v-if="emailError" style="margin-top:6px; color:var(--danger); font-size:12px;">
            {{ emailError }}
          </div>
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
          <div v-if="passwordError" style="margin-top:6px; color:var(--danger); font-size:12px;">
            {{ passwordError }}
          </div>
        </div>

        <button class="btn primary" type="submit" :disabled="loading">
          {{ loading ? 'Signing in...' : 'Login' }}
        </button>

        <div v-if="formError" style="margin-top:12px; color:var(--danger);">
          {{ formError }}
        </div>

        <div class="hr"></div>
        <RouterLink class="btn" to="/register">No account? Register</RouterLink>
      </form>
    </div>
  </div>
</template>

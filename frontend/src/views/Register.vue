<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { customerAuth } from '../services/api'

const router = useRouter()

const name = ref('')
const email = ref('')
const password = ref('')
const confirmPassword = ref('')

const loading = ref(false)
const error = ref('')

function isValidEmail(v) {
  const s = String(v || '').trim()
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(s)
}

function validatePassword(pw) {
  const v = String(pw || '')
  const rules = {
    minLen: v.length >= 8,
    upper: /[A-Z]/.test(v),
    lower: /[a-z]/.test(v),
    number: /[0-9]/.test(v),
    special: /[^A-Za-z0-9]/.test(v),
    noSpace: !/\s/.test(v),
  }
  const ok = Object.values(rules).every(Boolean)
  return { ok, rules }
}

const pw = computed(() => validatePassword(password.value))
const confirmOk = computed(() => String(password.value || '') === String(confirmPassword.value || ''))

function ruleStyle(ok) {
  return { color: ok ? 'var(--ok)' : 'var(--danger)' }
}

async function submit() {
  error.value = ''
  if (!name.value || !email.value || !password.value || !confirmPassword.value) return
  if (!isValidEmail(email.value)) {
    error.value = 'Please enter a valid email address.'
    return
  }
  if (!pw.value.ok) {
    error.value = 'Password does not meet security requirements.'
    return
  }
  if (!confirmOk.value) {
    error.value = 'Passwords do not match.'
    return
  }

  loading.value = true
  try {
    await customerAuth.register(name.value, email.value, password.value, confirmPassword.value)
    router.push('/account')
  } catch (e) {
    error.value = e.message || 'Register failed'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="card" style="max-width:520px; margin:0 auto;">
    <div class="card-body">
      <h1 style="margin:0 0 6px 0;">Create account</h1>
      <div class="muted">Register to start collecting points.</div>

      <div class="hr"></div>

      <div class="field" style="margin-bottom:12px;">
        <label class="muted">Name</label>
        <input class="input" v-model.trim="name" placeholder="Your name" />
      </div>

      <div class="field" style="margin-bottom:12px;">
        <label class="muted">Email</label>
        <input class="input" v-model.trim="email" type="email" placeholder="you@example.com" />
      </div>

      <div class="field" style="margin-bottom:12px;">
        <label class="muted">Password</label>
        <input class="input" v-model="password" type="password" placeholder="Create a strong password" />
        <ul class="muted" style="font-size:12px; margin:8px 0 0 0; padding-left:16px;">
          <li :style="ruleStyle(pw.rules.minLen)">At least 8 characters</li>
          <li :style="ruleStyle(pw.rules.upper)">At least 1 uppercase letter</li>
          <li :style="ruleStyle(pw.rules.lower)">At least 1 lowercase letter</li>
          <li :style="ruleStyle(pw.rules.number)">At least 1 number</li>
          <li :style="ruleStyle(pw.rules.special)">At least 1 special character</li>
          <li :style="ruleStyle(pw.rules.noSpace)">No spaces</li>
        </ul>
      </div>

      <div class="field" style="margin-bottom:12px;">
        <label class="muted">Confirm password</label>
        <input class="input" v-model="confirmPassword" type="password" placeholder="Re-enter password" />
        <div v-if="confirmPassword && !confirmOk" style="margin-top:6px; color:var(--danger); font-size:12px;">
          Passwords do not match.
        </div>
      </div>

      <button class="btn primary" :disabled="loading || !pw.ok || !confirmOk" @click="submit">
        {{ loading ? 'Creating...' : 'Register' }}
      </button>

      <div v-if="error" style="margin-top:12px; color:var(--danger);">{{ error }}</div>

      <div class="hr"></div>
      <RouterLink class="btn" to="/login">Already have an account? Login</RouterLink>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { adminAuth, adminApi } from '../services/api'

const router = useRouter()

const name = ref('')
const email = ref('')
const password = ref('')
const confirmPassword = ref('')

const loading = ref(false)
const loadingList = ref(false)
const deletingId = ref(null)

const error = ref('')
const success = ref('')
const admins = ref([])

function goBack() {
  router.push('/admin')
}

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
  return { color: ok ? 'var(--success)' : 'var(--danger)' }
}

async function loadAdmins() {
  loadingList.value = true
  error.value = ''
  try {
    const res = await adminApi.listAdminUsers()
    admins.value = res?.data || []
  } catch (e) {
    const msg = (e?.message || '').toLowerCase()
    if (msg.includes('unauthorized') || msg.includes('401')) {
      adminAuth.clearToken()
      router.push('/admin')
      return
    }
    error.value = e.message || 'Load failed'
  } finally {
    loadingList.value = false
  }
}

async function create() {
  error.value = ''
  success.value = ''

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
    await adminApi.createAdminUser({
      name: name.value,
      email: email.value,
      password: password.value,
    })
    success.value = 'Created.'
    name.value = ''
    email.value = ''
    password.value = ''
    confirmPassword.value = ''
    await loadAdmins()
  } catch (e) {
    const msg = (e?.message || '').toLowerCase()
    if (msg.includes('unauthorized') || msg.includes('401')) {
      adminAuth.clearToken()
      router.push('/admin')
      return
    }
    error.value = e.message || 'Create failed'
  } finally {
    loading.value = false
  }
}

async function removeAdmin(u) {
  if (admins.value.length <= 1) return

  const typed = window.prompt(
    `To delete admin "${u.name}" (${u.email}), type DELETE and press OK.`
  )
  if (String(typed || '').trim().toLowerCase() !== 'delete') return

  deletingId.value = u.id
  error.value = ''
  success.value = ''
  try {
    await adminApi.deleteAdminUser(u.id)
    success.value = 'Deleted.'
    await loadAdmins()
  } catch (e) {
    error.value = e.message || 'Delete failed'
  } finally {
    deletingId.value = null
  }
}

onMounted(async () => {
  if (!adminAuth.getToken()) {
    router.push('/admin')
    return
  }
  await loadAdmins()
})
</script>

<template>
  <div>
    <div class="row" style="align-items:center;">
      <h1 style="margin:0;">Create Admin Account</h1>
      <button class="btn" style="margin-left:auto;" @click="goBack">Back</button>
    </div>

    <div class="muted" style="margin-top:6px;">Admin-only page to create admin accounts.</div>
    <div class="hr"></div>

    <div class="card">
      <div class="card-body">
        <h2 style="margin:0 0 10px 0;">New Admin</h2>

        <div class="field" style="margin-bottom:12px;">
          <label class="muted">Name</label>
          <input class="input" v-model.trim="name" placeholder="Admin name" />
        </div>

        <div class="field" style="margin-bottom:12px;">
          <label class="muted">Email</label>
          <input class="input" v-model.trim="email" type="email" placeholder="admin@example.com" />
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

        <button class="btn primary" :disabled="loading || !pw.ok || !confirmOk" @click="create">
          {{ loading ? 'Creating...' : 'Create Admin' }}
        </button>

        <div v-if="error" style="margin-top:12px; color:var(--danger);">{{ error }}</div>
        <div v-if="success" style="margin-top:12px; color:var(--success);">{{ success }}</div>
      </div>
    </div>

    <div class="hr"></div>

    <div class="card">
      <div class="card-body">
        <div class="row" style="align-items:center;">
          <h2 style="margin:0;">Admin List</h2>
          <button
            class="btn btn-white"
            style="margin-left:auto;"
            :disabled="loadingList"
            @click="loadAdmins"
          >
            Refresh
          </button>
        </div>

        <div v-if="loadingList" class="muted" style="margin-top:10px;">Loading...</div>

        <div v-else style="margin-top:10px;">
          <div v-if="!admins.length" class="muted">No admins found.</div>

          <div
            v-for="u in admins"
            :key="u.id"
            class="row"
            style="padding:10px 0; border-bottom:1px solid var(--line); align-items:center;"
          >
            <div style="flex:1;">
              <div style="font-weight:700;">{{ u.name }}</div>
              <div class="muted" style="font-size:12px;">{{ u.email }}</div>
            </div>

            <button
              v-if="admins.length > 1"
              class="btn danger"
              :disabled="deletingId === u.id"
              @click="removeAdmin(u)"
            >
              {{ deletingId === u.id ? 'Deleting...' : 'Delete' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.btn-white{
  background: rgba(255,255,255,0.12);
  border: 1px solid rgba(255,255,255,0.25);
  color: #fff;
  transition: transform 140ms ease, background-color 140ms ease, border-color 140ms ease, opacity 140ms ease;
}
.btn-white:hover{
  transform: translateY(-1px);
  background: rgba(255,255,255,0.18);
  border-color: rgba(255,255,255,0.35);
}
.btn-white:active{
  transform: translateY(0px);
}
.btn-white:disabled{
  opacity: .55;
  cursor: not-allowed;
  transform: none;
}
</style>

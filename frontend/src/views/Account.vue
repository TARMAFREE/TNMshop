<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { customerAuth, customerApi } from '../services/api'

const router = useRouter()

const loading = ref(false)
const error = ref('')
const user = ref(null)

const ordersLoading = ref(false)
const ordersError = ref('')
const orders = ref([])

const copiedOrderId = ref(null)

function formatDate(v) {
  if (!v) return '-'
  const d = new Date(v)
  if (Number.isNaN(d.getTime())) return String(v)
  return d.toLocaleString()
}

function money(v) {
  const n = Number(v)
  if (Number.isFinite(n)) return n.toFixed(2)
  return v == null ? '0.00' : String(v)
}

function statusText(status) {
  const s = String(status || '').toLowerCase()
  if (s === 'shipped') return 'Shipped'
  if (s === 'paid') return 'Paid'
  return status || '-'
}

function isShipped(o) {
  return String(o?.status || '').toLowerCase() === 'shipped'
}

/**
 * ลิงก์ไปเช็ค tracking
 * ตอนนี้ใช้ Google search เพื่อให้ครอบคลุมหลายบริษัทขนส่ง
 * ถ้าต้องการลิงก์ตรงของแต่ละเจ้า สามารถเปลี่ยนฟังก์ชันนี้ได้
 */
function trackingSearchUrl(carrier, tracking) {
  const q = `${carrier || 'tracking'} ${tracking || ''}`.trim()
  return `https://www.google.com/search?q=${encodeURIComponent(q)}`
}

async function copyToClipboard(text) {
  const t = String(text || '')
  if (!t) return
  try {
    if (navigator?.clipboard?.writeText) {
      await navigator.clipboard.writeText(t)
      return
    }
  } catch (_) {}

  // Fallback
  const ta = document.createElement('textarea')
  ta.value = t
  ta.style.position = 'fixed'
  ta.style.opacity = '0'
  ta.style.pointerEvents = 'none'
  document.body.appendChild(ta)
  ta.select()
  document.execCommand('copy')
  document.body.removeChild(ta)
}

async function onCopyTracking(orderId, trackingNumber) {
  await copyToClipboard(trackingNumber)
  copiedOrderId.value = orderId
  setTimeout(() => {
    if (copiedOrderId.value === orderId) copiedOrderId.value = null
  }, 900)
}

async function loadMe() {
  loading.value = true
  error.value = ''
  try {
    const res = await customerAuth.me()
    user.value = res?.user || null
  } catch (e) {
    customerAuth.clearToken()
    router.push('/login')
  } finally {
    loading.value = false
  }
}

async function loadOrders() {
  ordersLoading.value = true
  ordersError.value = ''
  try {
    const res = await customerApi.listOrders({ per_page: 50 })
    orders.value = res?.data || []
  } catch (e) {
    ordersError.value = e?.message || 'Failed to load orders'
    orders.value = []
  } finally {
    ordersLoading.value = false
  }
}

async function logout() {
  await customerAuth.logout()
  router.push('/')
}

onMounted(async () => {
  if (!customerAuth.getToken()) {
    router.push('/login')
    return
  }
  await loadMe()
  await loadOrders()
})

// --- UI feedback (pop/bounce) ---
const logoutPop = ref(false)
const refreshPop = ref(false)

function pop(refVar, ms = 220) {
  // รีทริกเกอร์ animation ได้ทุกครั้ง
  refVar.value = false
  requestAnimationFrame(() => {
    refVar.value = true
    setTimeout(() => (refVar.value = false), ms)
  })
}

async function onLogoutClick() {
  pop(logoutPop)
  // หน่วงนิดเดียวให้เห็นเด้งก่อน redirect
  await new Promise((r) => setTimeout(r, 120))
  await logout()
}

async function onRefreshClick() {
  pop(refreshPop)
  await loadOrders()
}
</script>

<template>
  <div style="max-width: 820px; margin: 0 auto; display: flex; flex-direction: column; gap: 16px;">
    <!-- Account -->
    <div class="card">
      <div class="card-body">
        <div class="row" style="align-items: center;">
          <h1 style="margin: 0;">My Account</h1>
          <button
            class="btn btn-action"
            style="margin-left:auto;"
            :class="{ pop: logoutPop }"
            @click="onLogoutClick"
          >
            Log out
          </button>
        </div>

        <div class="hr"></div>

        <div v-if="loading" class="muted">Loading...</div>
        <div v-else-if="error" style="color: var(--danger);">{{ error }}</div>

        <div v-else-if="user">
          <div style="font-weight: 800;">{{ user.name }}</div>
          <div class="muted" style="font-size: 12px;">{{ user.email }}</div>

          <div class="hr"></div>

          <div class="row">
            <div class="muted">Points</div>
            <div style="font-weight: 900; font-size: 20px;">{{ user.points ?? 0 }}</div>
          </div>

          <div class="muted" style="margin-top: 10px; font-size: 12px;">
            Order history will match by your account email.
          </div>
        </div>
      </div>
    </div>

    <!-- Orders -->
    <div class="card">
      <div class="card-body">
        <div class="row" style="align-items: center;">
          <div>
            <h2 style="margin: 0 0 6px 0;">My Orders</h2>
            <div class="muted" style="font-size: 12px;">
              ระบบจะดึงออเดอร์จากอีเมลตอน Checkout ที่ตรงกับอีเมลบัญชีของคุณ
            </div>
          </div>

          <button
            class="btn btn-action"
            style="margin-left:auto;"
            :class="{ pop: refreshPop }"
            @click="onRefreshClick"
            :disabled="ordersLoading"
          >
            {{ ordersLoading ? 'Refreshing...' : 'Refresh' }}
          </button>
        </div>

        <div class="hr"></div>

        <div v-if="ordersLoading" class="muted">Loading orders...</div>
        <div v-else-if="ordersError" style="color: var(--danger);">{{ ordersError }}</div>
        <div v-else-if="orders.length === 0" class="muted">
          ยังไม่พบรายการสั่งซื้อสำหรับอีเมลนี้
        </div>

        <div v-else style="display: flex; flex-direction: column; gap: 12px;">
          <details
            v-for="o in orders"
            :key="o.id"
            class="card"
            style="background: var(--panel); border-radius: 14px; box-shadow: none;"
          >
            <summary style="cursor: pointer; padding: 12px; list-style: none;">
              <div class="row" style="align-items: flex-start;">
                <div style="min-width: 0;">
                  <div style="font-weight: 900;">{{ o.order_number }}</div>
                  <div class="muted" style="font-size: 12px;">
                    {{ formatDate(o.created_at) }}
                  </div>

                  <!-- Tracking (quick access) -->
                  <div
                    v-if="o.tracking_number"
                    class="muted"
                    style="font-size: 12px; margin-top: 6px; display:flex; gap:10px; flex-wrap:wrap; align-items:center;"
                  >
                    <span>
                      Tracking: <strong>{{ o.tracking_number }}</strong>
                    </span>
                    <button
                      class="btn btn-mini"
                      @click.stop.prevent="onCopyTracking(o.id, o.tracking_number)"
                      :aria-label="'Copy tracking number ' + o.tracking_number"
                    >
                      {{ copiedOrderId === o.id ? 'Copied' : 'Copy' }}
                    </button>
                    <a
                      class="link"
                      :href="trackingSearchUrl(o.shipping_carrier, o.tracking_number)"
                      target="_blank"
                      rel="noopener"
                      @click.stop
                    >
                      Check tracking
                    </a>
                  </div>
                </div>

                <div style="margin-left: auto; text-align: right;">
                  <div style="display: flex; gap: 8px; align-items: center; justify-content: flex-end;">
                    <span class="badge" :class="isShipped(o) ? 'ok' : 'no'">
                      {{ statusText(o.status) }}
                    </span>
                    <div style="font-weight: 900;">
                      {{ money(o.total) }} {{ o.currency }}
                    </div>
                  </div>
                  <div class="muted" style="font-size: 12px; margin-top: 4px;">
                    Subtotal {{ money(o.subtotal) }} · Ship {{ money(o.shipping_fee) }} · Tax {{ money(o.tax) }}
                  </div>
                </div>
              </div>
            </summary>

            <div style="padding: 12px; border-top: 1px solid var(--line);">
              <div class="muted" style="font-size: 12px; margin-bottom: 6px;">Shipping</div>
              <div style="white-space: pre-wrap; margin-bottom: 8px;">
                {{ o.shipping_address || '-' }}
              </div>

              <div
                v-if="o.tracking_number"
                class="muted"
                style="font-size: 12px; display:flex; gap:10px; flex-wrap:wrap; align-items:center;"
              >
                <span>Carrier: <strong>{{ o.shipping_carrier || '-' }}</strong></span>
                <span>· Tracking: <strong>{{ o.tracking_number }}</strong></span>
                <span v-if="o.shipped_at">· Shipped at: <strong>{{ formatDate(o.shipped_at) }}</strong></span>

                <button
                  class="btn btn-mini"
                  @click.stop.prevent="onCopyTracking(o.id, o.tracking_number)"
                >
                  {{ copiedOrderId === o.id ? 'Copied' : 'Copy tracking' }}
                </button>

                <a
                  class="link"
                  :href="trackingSearchUrl(o.shipping_carrier, o.tracking_number)"
                  target="_blank"
                  rel="noopener"
                  @click.stop
                >
                  Check tracking
                </a>
              </div>
              <div v-else class="muted" style="font-size: 12px;">
                ยังไม่จัดส่ง / รออัปเดต Tracking
              </div>

              <div class="hr"></div>

              <div class="muted" style="font-size: 12px; margin-bottom: 8px;">Items</div>
              <div v-if="!(o.items && o.items.length)" class="muted" style="font-size: 12px;">
                (No items)
              </div>

              <div
                v-for="it in (o.items || [])"
                :key="it.id"
                class="row"
                style="justify-content: space-between; align-items: flex-start; margin: 6px 0;"
              >
                <div style="min-width: 0;">
                  <div style="font-weight: 700;">{{ it.name }}</div>
                  <div class="muted" style="font-size: 12px;">
                    SKU: {{ it.sku || '-' }} · {{ money(it.unit_price) }} × {{ it.quantity }}
                  </div>
                </div>
                <div class="muted" style="font-size: 12px; white-space: nowrap;">
                  {{ money(it.line_total) }}
                </div>
              </div>
            </div>
          </details>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* ทำให้ตัวอักษรปุ่มเป็นสีขาว */
.btn-action {
  color: #fff !important;
}

/* ฟีลกดแล้ว “ยุบ” */
.btn-action:active {
  transform: translateY(1px) scale(0.98);
}

/* ปุ่มย่อ (ใช้กับ copy) */
.btn-mini {
  padding: 6px 10px;
  border-radius: 10px;
  font-size: 12px;
  line-height: 1;

  /* ✅ ให้คำว่า Copy / Copied เป็นสีขาว */
  color: #fff !important;
}

/* เผื่อภายในปุ่มมี element อื่น (เช่น span) */
.btn-mini * {
  color: #fff !important;
}

/* ลิงก์ไปเช็ค Tracking */
.link {
  color: var(--accent);
  text-decoration: underline;
}

/* เอฟเฟกต์เด้งตอบโต้ */
.pop {
  animation: pop 180ms ease-out;
}

@keyframes pop {
  0%   { transform: scale(1); }
  55%  { transform: scale(1.06); }
  100% { transform: scale(1); }
}
</style>

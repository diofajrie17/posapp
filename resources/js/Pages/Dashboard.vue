<template>
  <AppLayout title="Dashboard">
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <!-- Member Aktif -->
      <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
        <div class="text-sm opacity-90 mb-2">Member Aktif</div>
        <div class="text-3xl font-bold">{{ members.active }}</div>
        <div class="text-sm opacity-80 mt-2">Total member saat ini</div>
      </div>

      <!-- Member Expiring -->
      <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl shadow-lg p-6 text-white">
        <div class="text-sm opacity-90 mb-2">Akan Expired</div>
        <div class="text-3xl font-bold">{{ members.expiring_count }}</div>
        <div class="text-sm opacity-80 mt-2">Dalam 7 hari ke depan</div>
      </div>

      <!-- Check-in Hari Ini -->
      <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
        <div class="text-sm opacity-90 mb-2">Check-in Hari Ini</div>
        <div class="text-3xl font-bold">{{ checkIns.length }}</div>
        <div class="text-sm opacity-80 mt-2">Member yang datang</div>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-6">
      <!-- Expiring Members & Check-ins -->
      <div class="space-y-6">
        <!-- Member yang Akan Expired -->
        <div class="bg-white rounded-xl shadow-lg p-6">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-800">⏰ Member Akan Expired (7 Hari Ke Depan)</h2>
            <span class="px-3 py-1 bg-orange-100 text-orange-700 rounded-full text-sm font-semibold">
              {{ members.expiring_count }} member
            </span>
          </div>
          
          <div v-if="members.expiring_list && members.expiring_list.length > 0" class="space-y-3 max-h-96 overflow-y-auto">
            <div
              v-for="member in members.expiring_list"
              :key="member.id"
              class="flex items-center justify-between p-4 bg-orange-50 border border-orange-200 rounded-lg hover:bg-orange-100 transition"
            >
              <div class="flex-1">
                <div class="font-medium text-gray-800">{{ member.full_name }}</div>
                <div class="text-sm text-gray-600">{{ member.phone }}</div>
              </div>
              <div class="text-right">
                <div class="text-sm text-orange-600 font-semibold">{{ formatDate(member.membership_end) }}</div>
                <div class="text-xs text-gray-500">{{ daysUntilExpiration(member.membership_end) }} hari lagi</div>
              </div>
            </div>
          </div>
          <div v-else class="text-center py-8 text-gray-500">
            Tidak ada member yang akan expired dalam 7 hari ke depan
          </div>
        </div>

        <!-- Check-in Hari Ini -->
        <div class="bg-white rounded-xl shadow-lg p-6">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-800">✅ Check-in Hari Ini</h2>
            <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-semibold">
              {{ checkIns.length }} member
            </span>
          </div>
          
          <div v-if="checkIns && checkIns.length > 0" class="space-y-3 max-h-96 overflow-y-auto">
            <div
              v-for="checkIn in checkIns"
              :key="checkIn.id"
              class="flex items-center justify-between p-4 bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 transition"
            >
              <div class="flex-1">
                <div class="font-medium text-gray-800">
                  <span v-if="checkIn.member">{{ checkIn.member.full_name }}</span>
                  <span v-else>{{ checkIn.customer_name || 'Guest' }}</span>
                </div>
                <div class="text-sm text-gray-600">
                  <span v-if="checkIn.member">{{ checkIn.member.phone }}</span>
                  <span v-else>{{ checkIn.customer_phone || '-' }}</span>
                </div>
              </div>
              <div class="text-right">
                <div class="text-sm font-semibold text-green-600">{{ formatTime(checkIn.check_in_time) }}</div>
                <div v-if="checkIn.check_out_time" class="text-xs text-gray-500">Out: {{ formatTime(checkIn.check_out_time) }}</div>
                <div v-else class="text-xs text-gray-500">Masih di gym</div>
              </div>
            </div>
          </div>
          <div v-else class="text-center py-8 text-gray-500">
            Belum ada check-in hari ini
          </div>
        </div>
      </div>
    </div>

    <!-- Digital Clock -->
    <div class="mt-8 bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl shadow-2xl p-8 text-center">
      <div class="text-white">
        <div class="text-sm text-gray-400 mb-2 uppercase tracking-wider">Waktu Sekarang</div>
        <div class="text-6xl font-mono font-bold" id="digitalClock">{{ currentTime }}</div>
        <div class="text-lg text-gray-400 mt-2" id="digitalDate">{{ currentDate }}</div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { computed, ref, onMounted, onUnmounted } from 'vue'
import { formatPrice } from '@/composables/usePriceFormatter'

const props = defineProps({
  today: String,
  summary: Object,
  members: Object,
  checkIns: Array,
})

const currentTime = ref('')
const currentDate = ref('')
let timeInterval = null

function updateClock() {
  const now = new Date()
  const timeString = now.toLocaleTimeString('id-ID', {
    hour: '2-digit',
    minute: '2-digit',
    hour12: false
  })
  // Replace dot with colon (Indonesia uses dot, but we want colon)
  currentTime.value = timeString.replace(/\./g, ':')
  currentDate.value = now.toLocaleDateString('id-ID', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

onMounted(() => {
  updateClock()
  timeInterval = setInterval(updateClock, 1000)
})

onUnmounted(() => {
  if (timeInterval) {
    clearInterval(timeInterval)
  }
})

function rupiah(n) {
  return 'Rp ' + formatPrice(n || 0)
}

function formatDate(date) {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  })
}

function formatTime(time) {
  if (!time) return '-'
  const timeString = new Date(time).toLocaleTimeString('id-ID', {
    hour: '2-digit',
    minute: '2-digit',
    hour12: false
  })
  // Replace dot with colon (Indonesia uses dot, but we want colon)
  return timeString.replace(/\./g, ':')
}

function daysUntilExpiration(endDate) {
  if (!endDate) return null
  const now = new Date()
  const end = new Date(endDate)
  const diffTime = end - now
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  return diffDays > 0 ? diffDays : 0
}

const todayFormatted = computed(() => {
  if (!props.today) return '-'
  return new Date(props.today).toLocaleDateString('id-ID', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
})
</script>

<style scoped>
/* Smooth transitions */
.transition {
  transition: all 0.3s ease;
}

/* Custom scrollbar untuk overflow */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #555;
}
</style>

<template>
  <AppLayout title="Check-in">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <PageHeader
        title="Check-in"
        subtitle="Catat kehadiran member dan pelanggan harian"
      >
        <template #actions>
          <Button
            variant="secondary"
            href="/attendance/history"
          >
            📊 Riwayat Kehadiran
          </Button>
        </template>
      </PageHeader>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Check-in Form -->
        <Card title="Form Check-in">
          <form @submit.prevent="submit" class="space-y-6">
            <!-- Type Selection -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-3">
                Tipe <span class="text-red-500">*</span>
              </label>
              <div class="flex gap-4">
                <label class="flex items-center cursor-pointer">
                  <input
                    v-model="form.type"
                    type="radio"
                    value="member"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                    @change="resetForm"
                  />
                  <span class="ml-2 text-sm font-medium text-gray-700">Member</span>
                </label>
                <label class="flex items-center cursor-pointer">
                  <input
                    v-model="form.type"
                    type="radio"
                    value="daily"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                    @change="resetForm"
                  />
                  <span class="ml-2 text-sm font-medium text-gray-700">Pelanggan Harian</span>
                </label>
              </div>
            </div>

            <!-- Member Selection -->
            <div v-if="form.type === 'member'">
              <label for="member_search" class="block text-sm font-medium text-gray-700 mb-2">
                Cari Member <span class="text-red-500">*</span>
              </label>
              <input
                id="member_search"
                v-model="memberSearch"
                type="text"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Cari nama atau nomor HP"
                @input="searchMembers"
              />
              
              <!-- Member Search Results -->
              <div v-if="memberResults.length > 0" class="mt-2 border border-gray-300 rounded-lg max-h-60 overflow-y-auto">
                <button
                  v-for="member in memberResults"
                  :key="member.id"
                  type="button"
                  class="w-full px-4 py-3 text-left hover:bg-gray-50 border-b border-gray-200 last:border-b-0"
                  @click="selectMember(member)"
                >
                  <div class="font-medium text-gray-900">{{ member.full_name }}</div>
                  <div class="text-sm text-gray-600">{{ member.phone }}</div>
                  <div v-if="member.membership_end" class="text-xs" :class="getMembershipStatusClass(member)">
                    Expired: {{ formatDate(member.membership_end) }}
                  </div>
                </button>
              </div>

              <!-- Selected Member -->
              <div v-if="selectedMember" class="mt-3 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex justify-between items-start">
                  <div>
                    <div class="font-medium text-gray-900">{{ selectedMember.full_name }}</div>
                    <div class="text-sm text-gray-600">{{ selectedMember.phone }}</div>
                    <div v-if="selectedMember.membership_end" class="text-xs mt-1" :class="getMembershipStatusClass(selectedMember)">
                      Expired: {{ formatDate(selectedMember.membership_end) }}
                    </div>
                  </div>
                  <button type="button" @click="clearMember" class="text-red-600 hover:text-red-700">
                    ✕
                  </button>
                </div>
              </div>

              <div v-if="form.errors.member_id" class="text-red-500 text-sm mt-1">
                {{ form.errors.member_id }}
              </div>
            </div>

            <!-- Daily Customer Fields -->
            <div v-if="form.type === 'daily'" class="space-y-4">
              <div>
                <label for="customer_name" class="block text-sm font-medium text-gray-700 mb-2">
                  Nama Pelanggan <span class="text-red-500">*</span>
                </label>
                <input
                  id="customer_name"
                  v-model="form.customer_name"
                  type="text"
                  required
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Masukkan nama"
                />
                <div v-if="form.errors.customer_name" class="text-red-500 text-sm mt-1">
                  {{ form.errors.customer_name }}
                </div>
              </div>

              <div>
                <label for="customer_phone" class="block text-sm font-medium text-gray-700 mb-2">
                  No. HP <span class="text-red-500">*</span>
                </label>
                <input
                  id="customer_phone"
                  v-model="form.customer_phone"
                  type="text"
                  required
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Masukkan nomor HP"
                />
                <div v-if="form.errors.customer_phone" class="text-red-500 text-sm mt-1">
                  {{ form.errors.customer_phone }}
                </div>
              </div>
            </div>

            <!-- Notes -->
            <div>
              <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                Catatan
              </label>
              <textarea
                id="notes"
                v-model="form.notes"
                rows="2"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Catatan tambahan (opsional)"
              />
            </div>

            <!-- Submit Button -->
            <Button
              type="submit"
              variant="primary"
              :disabled="form.processing"
              class="w-full"
            >
              <span v-if="form.processing">Memproses...</span>
              <span v-else>✓ Check-in Sekarang</span>
            </Button>
          </form>
        </Card>

        <!-- Today's Attendances -->
        <Card title="Check-in Hari Ini">
          <div v-if="todayAttendances.length > 0" class="space-y-3 max-h-[600px] overflow-y-auto">
            <div
              v-for="attendance in todayAttendances"
              :key="attendance.id"
              class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50"
            >
              <div class="flex justify-between items-start">
                <div class="flex-1">
                  <div class="font-medium text-gray-900">
                    {{ attendance.type === 'member' ? attendance.member?.full_name : attendance.customer_name }}
                  </div>
                  <div class="text-sm text-gray-600">
                    {{ attendance.type === 'member' ? attendance.member?.phone : attendance.customer_phone }}
                  </div>
                  <div class="text-xs text-gray-500 mt-1">
                    Check-in: {{ formatTime(attendance.check_in_time) }}
                  </div>
                  <span :class="[
                    'inline-block mt-1 px-2 py-0.5 text-xs font-semibold rounded',
                    attendance.type === 'member' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'
                  ]">
                    {{ attendance.type === 'member' ? 'Member' : 'Harian' }}
                  </span>
                </div>
                <Button
                  v-if="!attendance.check_out_time"
                  variant="ghost"
                  size="sm"
                  @click="checkout(attendance.id)"
                >
                  Check-out
                </Button>
                <span v-else class="text-xs text-green-600">
                  ✓ {{ formatTime(attendance.check_out_time) }}
                </span>
              </div>
            </div>
          </div>
          <EmptyState
            v-else
            icon="📝"
            message="Belum ada check-in hari ini"
            subtitle="Check-in pertama akan muncul di sini"
          />
        </Card>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import axios from 'axios'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import Card from '@/Components/Card.vue'
import Button from '@/Components/Button.vue'
import EmptyState from '@/Components/EmptyState.vue'

const props = defineProps({
  todayAttendances: Array
})

const form = useForm({
  type: 'member',
  member_id: null,
  customer_name: '',
  customer_phone: '',
  notes: '',
})

const memberSearch = ref('')
const memberResults = ref([])
const selectedMember = ref(null)
let searchTimeout = null

function resetForm() {
  form.member_id = null
  form.customer_name = ''
  form.customer_phone = ''
  form.notes = ''
  memberSearch.value = ''
  memberResults.value = []
  selectedMember.value = null
}

function searchMembers() {
  clearTimeout(searchTimeout)
  
  if (memberSearch.value.length < 2) {
    memberResults.value = []
    return
  }

  searchTimeout = setTimeout(async () => {
    try {
      const response = await axios.get('/api/members/search', {
        params: { q: memberSearch.value }
      })
      memberResults.value = response.data
    } catch (error) {
      console.error('Error searching members:', error)
    }
  }, 300)
}

function selectMember(member) {
  selectedMember.value = member
  form.member_id = member.id
  memberResults.value = []
  memberSearch.value = ''
}

function clearMember() {
  selectedMember.value = null
  form.member_id = null
}

function getMembershipStatusClass(member) {
  if (!member.membership_end) return 'text-gray-600'
  
  const daysUntil = Math.ceil((new Date(member.membership_end) - new Date()) / (1000 * 60 * 60 * 24))
  
  if (daysUntil < 0) return 'text-red-600 font-semibold'
  if (daysUntil <= 7) return 'text-orange-600 font-semibold'
  return 'text-gray-600'
}

function formatDate(date) {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}

function formatTime(datetime) {
  if (!datetime) return '-'
  return new Date(datetime).toLocaleTimeString('id-ID', {
    hour: '2-digit',
    minute: '2-digit'
  })
}

function submit() {
  form.post('/attendance/checkin', {
    preserveScroll: true,
    onSuccess: () => {
      resetForm()
    }
  })
}

function checkout(attendanceId) {
  router.post(`/attendance/${attendanceId}/checkout`, {}, {
    preserveScroll: true
  })
}
</script>


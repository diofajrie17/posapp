<template>
  <AppLayout title="Perpanjang Membership">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <Card>
        <template #title>
          <h2 class="text-2xl font-bold text-gray-800">Perpanjang Membership</h2>
          <p class="text-sm text-gray-600 mt-1">Perpanjang keanggotaan member</p>
        </template>

        <form @submit.prevent="submit" class="space-y-6">
          <!-- Current Member Information (Read-only) -->
          <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Member</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Nama Lengkap</label>
                <p class="text-sm text-gray-900 font-medium">{{ member.full_name }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">No. HP</label>
                <p class="text-sm text-gray-900 font-medium">{{ member.phone }}</p>
              </div>
              <div v-if="member.membership_package">
                <label class="block text-sm font-medium text-gray-600 mb-1">Paket Saat Ini</label>
                <p class="text-sm text-gray-900 font-medium">{{ member.membership_package.name }}</p>
              </div>
              <div v-if="member.membership_end">
                <label class="block text-sm font-medium text-gray-600 mb-1">Berlaku Sampai</label>
                <p class="text-sm text-gray-900 font-medium">{{ formatDate(member.membership_end) }}</p>
              </div>
            </div>
          </div>

          <!-- Renewal Information -->
          <div class="border-t pt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Perpanjangan</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Paket Membership -->
              <div>
                <label for="membership_package_id" class="block text-sm font-medium text-gray-700 mb-2">
                  Paket Membership <span class="text-red-500">*</span>
                </label>
                <select
                  id="membership_package_id"
                  v-model="form.membership_package_id"
                  required
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  @change="updateNewEndDate"
                >
                  <option value="">Pilih Paket</option>
                  <option v-for="pkg in packages" :key="pkg.id" :value="pkg.id">
                    {{ pkg.name }} - {{ formatCurrency(pkg.price) }} ({{ pkg.duration_days }} hari)
                  </option>
                </select>
                <div v-if="form.errors.membership_package_id" class="text-red-500 text-sm mt-1">
                  {{ form.errors.membership_package_id }}
                </div>
              </div>

              <!-- Tanggal Mulai Perpanjangan (Optional) -->
              <div>
                <label for="renewal_start_date" class="block text-sm font-medium text-gray-700 mb-2">
                  Tanggal Mulai Perpanjangan (Opsional)
                </label>
                <input
                  id="renewal_start_date"
                  v-model="form.renewal_start_date"
                  type="date"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  @change="updateNewEndDate"
                />
                <p class="text-xs text-gray-500 mt-1">
                  Kosongkan untuk memperpanjang dari tanggal berakhir saat ini atau hari ini jika sudah expired
                </p>
                <div v-if="form.errors.renewal_start_date" class="text-red-500 text-sm mt-1">
                  {{ form.errors.renewal_start_date }}
                </div>
              </div>

              <!-- Tanggal Berakhir Baru (Calculated) -->
              <div>
                <label for="new_membership_end" class="block text-sm font-medium text-gray-700 mb-2">
                  Tanggal Berakhir Baru
                </label>
                <input
                  id="new_membership_end"
                  :value="calculatedEndDate"
                  type="text"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50"
                  readonly
                />
                <p class="text-xs text-gray-500 mt-1">Otomatis dihitung dari paket yang dipilih</p>
              </div>

              <!-- Payment Type -->
              <div>
                <label for="payment_type" class="block text-sm font-medium text-gray-700 mb-2">
                  Metode Pembayaran <span class="text-red-500">*</span>
                </label>
                <select
                  id="payment_type"
                  v-model="form.payment_type"
                  required
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                  <option value="">Pilih Metode</option>
                  <option value="Cash">Cash</option>
                  <option value="QR">QR Code</option>
                  <option value="Transfer">Transfer</option>
                </select>
                <div v-if="form.errors.payment_type" class="text-red-500 text-sm mt-1">
                  {{ form.errors.payment_type }}
                </div>
              </div>
            </div>

            <!-- Package Price Display -->
            <div v-if="selectedPackage" class="mt-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
              <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-700">Total Pembayaran:</span>
                <span class="text-lg font-bold text-blue-600">{{ formatCurrency(selectedPackage.price) }}</span>
              </div>
            </div>
          </div>

          <!-- Additional Information -->
          <div class="border-t pt-6">
            <div class="grid grid-cols-1 gap-6">
              <!-- Catatan -->
              <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                  Catatan
                </label>
                <textarea
                  id="notes"
                  v-model="form.notes"
                  rows="3"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Catatan tambahan (opsional)"
                />
                <div v-if="form.errors.notes" class="text-red-500 text-sm mt-1">
                  {{ form.errors.notes }}
                </div>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex gap-4 pt-4">
            <Button
              type="submit"
              variant="primary"
              :disabled="form.processing || !form.membership_package_id"
              class="flex-1"
            >
              <span v-if="form.processing">Memproses...</span>
              <span v-else>Perpanjang Membership</span>
            </Button>
            
            <Button
              type="button"
              variant="secondary"
              href="/members"
              class="flex-1"
            >
              Batal
            </Button>
          </div>
        </form>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Card from '@/Components/Card.vue'
import Button from '@/Components/Button.vue'

const props = defineProps({
  member: Object,
  packages: Array
})

const form = useForm({
  membership_package_id: '',
  payment_type: 'Cash',
  renewal_start_date: '',
  notes: '',
})

const selectedPackage = computed(() => {
  if (!form.membership_package_id) return null
  return props.packages.find(p => p.id === parseInt(form.membership_package_id))
})

const calculatedEndDate = computed(() => {
  if (!selectedPackage.value) return '-'
  
  let startDate
  
  if (form.renewal_start_date) {
    // Use provided start date
    startDate = new Date(form.renewal_start_date)
  } else {
    // Calculate from current membership_end or today
    if (props.member.membership_end) {
      const currentEnd = new Date(props.member.membership_end)
      if (currentEnd >= new Date()) {
        // Extend from current end date
        startDate = new Date(currentEnd)
        startDate.setDate(startDate.getDate() + 1)
      } else {
        // Start from today (expired)
        startDate = new Date()
      }
    } else {
      // No end date, start from today
      startDate = new Date()
    }
  }
  
  const endDate = new Date(startDate)
  endDate.setDate(endDate.getDate() + selectedPackage.value.duration_days)
  
  return formatDate(endDate.toISOString().split('T')[0])
})

function formatCurrency(value) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(value)
}

function formatDate(date) {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}

function updateNewEndDate() {
  // This is handled by the computed property, but we can trigger reactivity if needed
}

function submit() {
  form.post(`/members/${props.member.id}/renew`)
}
</script>


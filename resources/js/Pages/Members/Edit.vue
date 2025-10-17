<template>
  <AppLayout title="Edit Member">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <Card>
        <template #title>
          <h2 class="text-2xl font-bold text-gray-800">Edit Member</h2>
          <p class="text-sm text-gray-600 mt-1">Perbarui informasi member</p>
        </template>

        <form @submit.prevent="submit" class="space-y-6">
          <!-- Personal Information -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Nama Lengkap -->
            <div class="md:col-span-2">
              <label for="full_name" class="block text-sm font-medium text-gray-700 mb-2">
                Nama Lengkap <span class="text-red-500">*</span>
              </label>
              <input
                id="full_name"
                v-model="form.full_name"
                type="text"
                required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Masukkan nama lengkap"
              />
              <div v-if="form.errors.full_name" class="text-red-500 text-sm mt-1">
                {{ form.errors.full_name }}
              </div>
            </div>

            <!-- Nomor HP -->
            <div>
              <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                No. HP <span class="text-red-500">*</span>
              </label>
              <input
                id="phone"
                v-model="form.phone"
                type="text"
                required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="08123456789"
              />
              <div v-if="form.errors.phone" class="text-red-500 text-sm mt-1">
                {{ form.errors.phone }}
              </div>
            </div>

            <!-- Email -->
            <div>
              <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                Email
              </label>
              <input
                id="email"
                v-model="form.email"
                type="email"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="email@example.com"
              />
              <div v-if="form.errors.email" class="text-red-500 text-sm mt-1">
                {{ form.errors.email }}
              </div>
            </div>

            <!-- Gender -->
            <div>
              <label for="gender" class="block text-sm font-medium text-gray-700 mb-2">
                Jenis Kelamin
              </label>
              <select
                id="gender"
                v-model="form.gender"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="">Pilih</option>
                <option value="male">Laki-laki</option>
                <option value="female">Perempuan</option>
              </select>
              <div v-if="form.errors.gender" class="text-red-500 text-sm mt-1">
                {{ form.errors.gender }}
              </div>
            </div>
          </div>

          <!-- Membership Information -->
          <div class="border-t pt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Membership</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Paket Membership -->
              <div>
                <label for="membership_package_id" class="block text-sm font-medium text-gray-700 mb-2">
                  Paket Membership
                </label>
                <select
                  id="membership_package_id"
                  v-model="form.membership_package_id"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  @change="updateMembershipEnd"
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

              <!-- Tipe Membership -->
              <div>
                <label for="membership_type" class="block text-sm font-medium text-gray-700 mb-2">
                  Tipe Membership <span class="text-red-500">*</span>
                </label>
                <select
                  id="membership_type"
                  v-model="form.membership_type"
                  required
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                  <option value="harian">Harian</option>
                  <option value="bulanan">Bulanan</option>
                  <option value="tahunan">Tahunan</option>
                </select>
                <div v-if="form.errors.membership_type" class="text-red-500 text-sm mt-1">
                  {{ form.errors.membership_type }}
                </div>
              </div>

              <!-- Tanggal Mulai -->
              <div>
                <label for="membership_start" class="block text-sm font-medium text-gray-700 mb-2">
                  Tanggal Mulai
                </label>
                <input
                  id="membership_start"
                  v-model="form.membership_start"
                  type="date"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  @change="updateMembershipEnd"
                />
                <div v-if="form.errors.membership_start" class="text-red-500 text-sm mt-1">
                  {{ form.errors.membership_start }}
                </div>
              </div>

              <!-- Tanggal Berakhir -->
              <div>
                <label for="membership_end" class="block text-sm font-medium text-gray-700 mb-2">
                  Tanggal Berakhir
                </label>
                <input
                  id="membership_end"
                  v-model="form.membership_end"
                  type="date"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50"
                  readonly
                />
                <p class="text-xs text-gray-500 mt-1">Otomatis dihitung dari paket & tanggal mulai</p>
                <div v-if="form.errors.membership_end" class="text-red-500 text-sm mt-1">
                  {{ form.errors.membership_end }}
                </div>
              </div>
            </div>
          </div>

          <!-- Additional Information -->
          <div class="border-t pt-6">
            <div class="grid grid-cols-1 gap-6">
              <!-- Status Aktif -->
              <div class="flex items-center">
                <input
                  id="is_active"
                  v-model="form.is_active"
                  type="checkbox"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
                <label for="is_active" class="ml-2 block text-sm text-gray-700">
                  Member Aktif
                </label>
              </div>

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
              :disabled="form.processing"
              class="flex-1"
            >
              <span v-if="form.processing">Menyimpan...</span>
              <span v-else>Update Member</span>
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
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Card from '@/Components/Card.vue'
import Button from '@/Components/Button.vue'

const props = defineProps({
  member: Object,
  packages: Array
})

const form = useForm({
  full_name: props.member.full_name,
  phone: props.member.phone,
  email: props.member.email || '',
  gender: props.member.gender || '',
  membership_package_id: props.member.membership_package_id || '',
  membership_type: props.member.membership_type,
  membership_start: props.member.membership_start || '',
  membership_end: props.member.membership_end || '',
  is_active: props.member.is_active,
  notes: props.member.notes || '',
})

function formatCurrency(value) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(value)
}

function updateMembershipEnd() {
  if (form.membership_package_id && form.membership_start) {
    const pkg = props.packages.find(p => p.id === parseInt(form.membership_package_id))
    if (pkg) {
      const startDate = new Date(form.membership_start)
      const endDate = new Date(startDate)
      endDate.setDate(endDate.getDate() + pkg.duration_days)
      form.membership_end = endDate.toISOString().split('T')[0]
    }
  }
}

function submit() {
  form.put(`/members/${props.member.id}`)
}
</script>

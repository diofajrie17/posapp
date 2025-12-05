<template>
  <AppLayout title="Laporan Kehadiran">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <PageHeader
        title="Laporan Kehadiran"
        subtitle="Analisis dan laporan kehadiran member dan pelanggan harian"
      />

      <!-- Filters -->
      <Card class="mb-6">
        <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
              Dari Tanggal
            </label>
            <input
              id="start_date"
              v-model="filterForm.start_date"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <div>
            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
              Sampai Tanggal
            </label>
            <input
              id="end_date"
              v-model="filterForm.end_date"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <div class="flex items-end gap-2">
            <Button type="submit" variant="primary" class="flex-1">
              Filter
            </Button>
            <Button type="button" variant="secondary" @click="resetFilters">
              Reset
            </Button>
          </div>
        </form>
      </Card>

      <!-- Summary Statistics -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <Card>
          <div class="text-center">
            <h3 class="text-sm font-medium text-gray-600 mb-1">Total Kehadiran</h3>
            <p class="text-3xl font-bold text-blue-600">{{ stats.total }}</p>
          </div>
        </Card>
        <Card>
          <div class="text-center">
            <h3 class="text-sm font-medium text-gray-600 mb-1">Member</h3>
            <p class="text-3xl font-bold text-green-600">{{ stats.members }}</p>
          </div>
        </Card>
        <Card>
          <div class="text-center">
            <h3 class="text-sm font-medium text-gray-600 mb-1">Pelanggan Harian</h3>
            <p class="text-3xl font-bold text-purple-600">{{ stats.daily }}</p>
          </div>
        </Card>
        <Card>
          <div class="text-center">
            <h3 class="text-sm font-medium text-gray-600 mb-1">Rata-rata/Hari</h3>
            <p class="text-3xl font-bold text-orange-600">{{ stats.average_per_day }}</p>
          </div>
        </Card>
      </div>

      <!-- Charts Section -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Daily Attendance Chart -->
        <Card title="Kehadiran Harian">
          <div v-if="dailyData.length > 0" class="h-64">
            <canvas ref="dailyChart"></canvas>
          </div>
          <EmptyState
            v-else
            icon="📊"
            message="Tidak ada data"
            subtitle="Belum ada data untuk periode ini"
          />
        </Card>

        <!-- Type Distribution -->
        <Card title="Distribusi Tipe">
          <div v-if="stats.total > 0" class="h-64 flex items-center justify-center">
            <div class="text-center">
              <div class="relative inline-flex items-center justify-center w-48 h-48">
                <svg class="w-48 h-48 transform -rotate-90">
                  <circle
                    cx="96"
                    cy="96"
                    r="80"
                    :stroke-dasharray="`${memberPercentage * 5.024} 502.4`"
                    class="fill-none stroke-green-500"
                    stroke-width="32"
                  />
                  <circle
                    cx="96"
                    cy="96"
                    r="80"
                    :stroke-dasharray="`${dailyPercentage * 5.024} 502.4`"
                    :stroke-dashoffset="`${-memberPercentage * 5.024}`"
                    class="fill-none stroke-purple-500"
                    stroke-width="32"
                  />
                </svg>
                <div class="absolute">
                  <p class="text-3xl font-bold">{{ stats.total }}</p>
                  <p class="text-sm text-gray-600">Total</p>
                </div>
              </div>
              <div class="mt-4 space-y-2">
                <div class="flex items-center justify-center gap-2">
                  <div class="w-4 h-4 bg-green-500 rounded"></div>
                  <span class="text-sm">Member: {{ stats.members }} ({{ memberPercentage }}%)</span>
                </div>
                <div class="flex items-center justify-center gap-2">
                  <div class="w-4 h-4 bg-purple-500 rounded"></div>
                  <span class="text-sm">Harian: {{ stats.daily }} ({{ dailyPercentage }}%)</span>
                </div>
              </div>
            </div>
          </div>
          <EmptyState
            v-else
            icon="📊"
            message="Tidak ada data"
            subtitle="Belum ada data untuk periode ini"
          />
        </Card>
      </div>

      <!-- Top Members -->
      <Card title="Member Paling Aktif">
        <DataTable v-if="topMembers.length > 0">
          <template #header>
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Peringkat
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Nama Member
              </th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                Jumlah Kunjungan
              </th>
            </tr>
          </template>
          <tr v-for="(member, index) in topMembers" :key="member.member_id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap text-center">
              <span :class="[
                'inline-flex items-center justify-center w-8 h-8 rounded-full font-bold',
                index === 0 ? 'bg-yellow-100 text-yellow-800' : 
                index === 1 ? 'bg-gray-100 text-gray-800' : 
                index === 2 ? 'bg-orange-100 text-orange-800' : 
                'bg-blue-100 text-blue-800'
              ]">
                {{ index + 1 }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-medium text-gray-900">
                {{ member.member_name }}
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-center">
              <span class="px-3 py-1 text-sm font-semibold bg-blue-100 text-blue-800 rounded-full">
                {{ member.visit_count }} kali
              </span>
            </td>
          </tr>
        </DataTable>
        <EmptyState
          v-else
          icon="🏆"
          message="Tidak ada data"
          subtitle="Belum ada data member untuk periode ini"
        />
      </Card>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import Card from '@/Components/Card.vue'
import DataTable from '@/Components/DataTable.vue'
import Button from '@/Components/Button.vue'
import EmptyState from '@/Components/EmptyState.vue'

const props = defineProps({
  stats: Object,
  dailyData: Array,
  topMembers: Array,
  filters: Object
})

const filterForm = ref({
  start_date: props.filters?.start_date || '',
  end_date: props.filters?.end_date || ''
})

const dailyChart = ref(null)

const memberPercentage = computed(() => {
  if (props.stats.total === 0) return 0
  return Math.round((props.stats.members / props.stats.total) * 100)
})

const dailyPercentage = computed(() => {
  if (props.stats.total === 0) return 0
  return Math.round((props.stats.daily / props.stats.total) * 100)
})

function applyFilters() {
  router.get('/reports/attendance', filterForm.value, {
    preserveState: true,
    preserveScroll: true
  })
}

function resetFilters() {
  filterForm.value = {
    start_date: '',
    end_date: ''
  }
  applyFilters()
}

onMounted(() => {
  if (dailyChart.value && props.dailyData.length > 0) {
    // Simple canvas-based chart (you can replace with Chart.js if needed)
    const canvas = dailyChart.value
    const ctx = canvas.getContext('2d')
    canvas.width = canvas.offsetWidth
    canvas.height = 256
    
    const maxValue = Math.max(...props.dailyData.map(d => d.count))
    const barWidth = canvas.width / props.dailyData.length
    
    props.dailyData.forEach((data, index) => {
      const barHeight = (data.count / maxValue) * (canvas.height - 40)
      const x = index * barWidth
      const y = canvas.height - barHeight - 20
      
      ctx.fillStyle = '#3B82F6'
      ctx.fillRect(x + 5, y, barWidth - 10, barHeight)
      
      ctx.fillStyle = '#000'
      ctx.font = '10px Arial'
      ctx.textAlign = 'center'
      ctx.fillText(data.count, x + barWidth / 2, y - 5)
      ctx.fillText(new Date(data.date).getDate(), x + barWidth / 2, canvas.height - 5)
    })
  }
})
</script>


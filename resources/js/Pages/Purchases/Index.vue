<template>
  <AppLayout title="Pembelian">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <PageHeader 
        title="Pembelian" 
        subtitle="Kelola pembelian stok dari supplier"
      >
        <template #actions>
          <Button 
            :href="route('purchases.create')" 
            variant="primary"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Pembelian
          </Button>
        </template>
      </PageHeader>

      <!-- Filters -->
      <Card class="mb-6">
        <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <InputLabel value="Dari Tanggal" />
            <TextInput v-model="form.date_from" type="date" class="w-full" />
          </div>
          <div>
            <InputLabel value="Sampai Tanggal" />
            <TextInput v-model="form.date_to" type="date" class="w-full" />
          </div>
          <div>
            <InputLabel value="Supplier" />
            <TextInput v-model="form.supplier" placeholder="Nama supplier" class="w-full" />
          </div>
          <div class="md:col-span-3 flex gap-2">
            <Button type="submit" variant="primary">Terapkan Filter</Button>
            <Button @click="resetFilters" variant="secondary" type="button">Reset</Button>
          </div>
        </form>
      </Card>

      <!-- Table -->
      <Card>
        <DataTable>
          <template #header>
            <th class="px-4 py-3 text-left font-semibold">No. Pembelian</th>
            <th class="px-4 py-3 text-left font-semibold">Tanggal</th>
            <th class="px-4 py-3 text-left font-semibold">Supplier</th>
            <th class="px-4 py-3 text-right font-semibold">Total</th>
            <th class="px-4 py-3 text-center font-semibold">Dibuat Oleh</th>
            <th class="px-4 py-3 text-center font-semibold">Aksi</th>
          </template>

          <tr v-if="purchases.data.length === 0">
            <td colspan="6" class="p-0">
              <EmptyState 
                icon="🛒" 
                message="Belum ada data pembelian" 
                subtitle="Mulai tambahkan pembelian pertama Anda"
              />
            </td>
          </tr>

          <tr 
            v-for="purchase in purchases.data" 
            :key="purchase.id"
            class="hover:bg-gray-50 transition-colors"
          >
            <td class="px-4 py-3 font-medium text-indigo-600">{{ purchase.purchase_number }}</td>
            <td class="px-4 py-3">{{ formatDate(purchase.purchase_date) }}</td>
            <td class="px-4 py-3">
              <div class="font-medium">{{ purchase.supplier_name }}</div>
              <div class="text-xs text-gray-500" v-if="purchase.supplier_phone">
                {{ purchase.supplier_phone }}
              </div>
            </td>
            <td class="px-4 py-3 text-right font-semibold text-green-600">
              {{ formatRupiah(purchase.total_amount) }}
            </td>
            <td class="px-4 py-3 text-center text-sm text-gray-600">
              {{ purchase.creator?.name }}
            </td>
            <td class="px-4 py-3">
              <div class="flex items-center justify-center gap-2">
                <Button 
                  :href="route('purchases.show', purchase.id)" 
                  variant="ghost" 
                  size="sm"
                >
                  👁️ Lihat
                </Button>
                <Button 
                  @click="confirmDelete(purchase)" 
                  variant="danger" 
                  size="sm"
                >
                  🗑️
                </Button>
              </div>
            </td>
          </tr>
        </DataTable>

        <!-- Pagination -->
        <div v-if="purchases.data.length > 0" class="mt-4 flex justify-between items-center px-4 pb-4">
          <div class="text-sm text-gray-600">
            Showing {{ purchases.from }} to {{ purchases.to }} of {{ purchases.total }}
          </div>
          <div class="flex gap-2">
            <Button 
              v-for="(link, idx) in purchases.links" 
              :key="idx"
              :href="link.url"
              :disabled="!link.url"
              :variant="link.active ? 'primary' : 'secondary'"
              size="sm"
              v-html="link.label"
            />
          </div>
        </div>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import Card from '@/Components/Card.vue'
import DataTable from '@/Components/DataTable.vue'
import Button from '@/Components/Button.vue'
import EmptyState from '@/Components/EmptyState.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'

const props = defineProps({
  purchases: Object,
  filters: Object,
})

const form = ref({
  date_from: props.filters?.date_from || '',
  date_to: props.filters?.date_to || '',
  supplier: props.filters?.supplier || '',
})

const applyFilters = () => {
  router.get(route('purchases.index'), form.value, { preserveState: true })
}

const resetFilters = () => {
  form.value = { date_from: '', date_to: '', supplier: '' }
  applyFilters()
}

const confirmDelete = (purchase) => {
  if (confirm(`Hapus pembelian ${purchase.purchase_number}? Stok akan dikurangi kembali.`)) {
    router.delete(route('purchases.destroy', purchase.id))
  }
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}

const formatRupiah = (value) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(value)
}
</script>


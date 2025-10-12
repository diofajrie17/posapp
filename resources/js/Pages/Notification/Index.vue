<template>
  <div>
    <h1 class="text-2xl font-bold mb-4">Notifikasi Expired Member (H-5)</h1>

    <form @submit.prevent="apply" class="flex flex-wrap items-end gap-3 mb-4">
      <div>
        <label class="block text-sm">Tanggal Kirim</label>
        <input type="date" v-model="f.date" class="border rounded px-3 py-2" />
      </div>
      <div>
        <label class="block text-sm">Status</label>
        <select v-model="f.status" class="border rounded px-3 py-2">
          <option value="">Semua</option>
          <option value="pending">pending</option>
          <option value="sent">sent</option>
          <option value="failed">failed</option>
        </select>
      </div>
      <button class="px-4 py-2 rounded bg-gray-800 text-white">Terapkan</button>
      <a :href="waBulkLink" target="_blank" class="ml-auto px-4 py-2 rounded border">WA Bulk (manual)</a>
    </form>

    <div class="p-4 rounded-lg shadow bg-white">
      <table class="w-full">
        <thead>
          <tr class="text-left bg-gray-100">
            <th class="px-3 py-2">Tanggal</th>
            <th class="px-3 py-2">Member</th>
            <th class="px-3 py-2">Telepon</th>
            <th class="px-3 py-2">Status</th>
            <th class="px-3 py-2">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="n in notifications.data" :key="n.id" class="border-b">
            <td class="px-3 py-2">{{ n.send_date }}</td>
            <td class="px-3 py-2">{{ n.member?.full_name }}</td>
            <td class="px-3 py-2">{{ n.member?.phone || '-' }}</td>
            <td class="px-3 py-2">
              <span :class="badge(n.status)">{{ n.status }}</span>
            </td>
            <td class="px-3 py-2 space-x-2">
              <button v-if="n.status!=='sent'" @click="send(n.id)" class="px-3 py-1 rounded bg-blue-600 text-white">
                Kirim
              </button>
              <a v-if="n.member?.phone" :href="waLink(n.member.phone, n.message)" target="_blank"
                 class="px-3 py-1 rounded border">WA</a>
            </td>
          </tr>
          <tr v-if="notifications.data.length===0">
            <td colspan="5" class="px-3 py-6 text-center text-gray-500">Tidak ada data</td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div class="mt-3 flex gap-2">
        <Link v-for="link in notifications.links" :key="link.label" :href="link.url || '#'"
              v-html="link.label"
              :class="['px-3 py-1 rounded border', { 'bg-gray-800 text-white': link.active, 'opacity-50 pointer-events-none': !link.url }]"/>
      </div>
    </div>
  </div>
</template>

<script setup>
import { router, Link } from '@inertiajs/vue3'
import { reactive, computed } from 'vue'

const props = defineProps({
  notifications: Object,
  filters: Object
})

const f = reactive({
  date: props.filters.date || '',
  status: props.filters.status || ''
})

function apply(){
  router.get(route('notifications.index'), f, { preserveState: true, preserveScroll: true })
}

function send(id){
  router.post(route('notifications.send', id), {}, { preserveScroll:true })
}

function waLink(phone, msg){
  const p = phone.replace(/[^\d]/g,'') // bersihkan
  const text = encodeURIComponent(msg || '')
  return `https://wa.me/${p}?text=${text}`
}

const waBulkLink = computed(() => {
  // shortcut: buka WA tanpa nomor (untuk copy-paste manual)
  return 'https://web.whatsapp.com/'
})

function badge(status){
  return {
    'px-2 py-0.5 rounded text-xs': true,
    'bg-yellow-100 text-yellow-800': status==='pending',
    'bg-green-100 text-green-800': status==='sent',
    'bg-red-100 text-red-800': status==='failed',
  }
}
</script>

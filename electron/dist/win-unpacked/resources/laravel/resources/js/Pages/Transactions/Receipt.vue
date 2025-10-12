<template>
  <div class="receipt-wrapper">
    <div class="toolbar no-print">
      <Link :href="route('transactions.index')" class="btn">← Kembali</Link>
      <button @click="print" class="btn">🖨️ Print</button>
    </div>

    <div class="receipt">
      <h2 class="title">{{ meta.store_name }}</h2>
      <div class="center small">{{ meta.address }}</div>
      <div class="line"></div>

      <div class="row">
        <span>No</span><span class="right">{{ meta.trx_code }}</span>
      </div>
      <div class="row">
        <span>Tanggal</span><span class="right">{{ meta.printed_at }}</span>
      </div>
      <div class="row" v-if="transaction.member">
        <span>Member</span><span class="right">{{ transaction.member.full_name }}</span>
      </div>
      <div class="row">
        <span>Kasir</span><span class="right">{{ meta.cashier }}</span>
      </div>
      <div class="line"></div>

      <table class="items">
        <tbody>
          <tr v-for="it in transaction.items" :key="it.id">
            <td class="name">{{ it.product?.name || '-' }}</td>
            <td class="qty">{{ it.quantity }}x</td>
            <td class="price">{{ rupiah(it.price_each) }}</td>
            <td class="total">{{ rupiah(it.quantity * it.price_each) }}</td>
          </tr>
        </tbody>
      </table>

      <div class="line"></div>
      <div class="row">
        <span>Subtotal</span><span class="right">{{ rupiah(transaction.subtotal_amount) }}</span>
      </div>
      <div class="row" v-if="transaction.discount_amount && transaction.discount_amount > 0">
        <span>Diskon<span v-if="transaction.discount_type==='percent'"> ({{ transaction.discount_value }}%)</span></span>
        <span class="right">- {{ rupiah(transaction.discount_amount) }}</span>
      </div>
      <div class="row total">
        <span>Total</span><span class="right">{{ rupiah(transaction.total_amount) }}</span>
      </div>
      <div class="row" v-if="transaction.paid_amount !== null">
        <span>Dibayar</span><span class="right">{{ rupiah(transaction.paid_amount) }}</span>
      </div>
      <div class="row" v-if="transaction.change_amount !== null">
        <span>Kembali</span><span class="right">{{ rupiah(transaction.change_amount) }}</span>
      </div>
      <div class="row">
        <span>Bayar</span><span class="right">{{ transaction.payment_type }}</span>
      </div>

      <div class="center small thanks">Terima kasih & tetap sehat! 💪</div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
defineProps({
  transaction: Object,
  meta: Object
})

function rupiah(n){
  if(n===null || n===undefined) return '-'
  const val = Number(n)
  return 'Rp ' + val.toLocaleString('id-ID', {minimumFractionDigits: 0})
}

function print(){ window.print() }
</script>

<style>
/* Toolbar non-print */
.toolbar { display:flex; gap:.5rem; margin-bottom: .75rem; }
.btn { background:#111827; color:#fff; padding:.35rem .6rem; border-radius:.375rem; }

/* Ukuran thermal 58/80mm */
@media print {
  .no-print { display: none !important; }
  @page { size: 58mm auto; margin: 0; } /* ganti 80mm jika printer 80mm */
  body { margin:0; }
}

.receipt-wrapper { display:flex; justify-content:center; }
.receipt {
  width: 280px; /* kira-kira 58mm; untuk 80mm pakai ~380px */
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
  font-size: 12px;
  color:#111;
}
.title { text-align:center; margin:0; font-size:14px; font-weight:700; }
.center { text-align:center; }
.small { font-size:11px; }
.line { border-top:1px dashed #000; margin:.4rem 0; }
.row { display:flex; justify-content:space-between; margin: .1rem 0; }
.right { text-align:right; }

.items { width:100%; border-collapse:collapse; }
.items td { padding:2px 0; vertical-align:top; }
.items td.name { width:45%; }
.items td.qty { width:10%; text-align:right; }
.items td.price { width:20%; text-align:right; }
.items td.total { width:25%; text-align:right; font-weight:600; }

.total { font-weight:700; font-size:13px; }
.thanks { margin-top:.6rem; }
</style>

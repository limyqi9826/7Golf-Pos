<!-- receiptpreview.vue -->
<template>
    <div class="p-6 bg-white max-w-[400px] w-full text-sm" id="receipt">
      <!-- Header -->
      <div class="text-center mb-4">
        <div class="font-bold text-lg">7GOLF</div>
        <div>123 Golf Lane, Greenfield</div>
        <div>(123) 456-7890</div>
      </div>

      <hr class="my-2" />

      <!-- Transaction Info -->
      <div class="mb-2">
        <div>Date: {{ todayDate }}</div>
        <div>Time: {{ todayTime }}</div>
        <div>Transaction ID: {{ transactionCode }}</div>
      </div>

      <!-- Golf Info -->
      <div class="mb-2">
        <div>Hole Number: {{ transactionData.hole_number || '-' }}</div>
        <div>Buggy/Cart No: {{ transactionData.buggy_number || '-' }}</div>
        <div>Players: {{ transactionData.players || '-' }}</div>
      </div>

      <hr class="my-2" />

      <!-- Items List -->
      <div class="mb-2">
        <table class="w-full">
          <thead>
            <tr>
              <th class="text-left">Item</th>
              <th class="text-center">Qty</th>
              <th class="text-right">Total</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in transactionData.cart" :key="item.id">
              <td>{{ item.item_name || item.item_id }}</td>
              <td class="text-center">{{ item.quantity }}</td>
              <td class="text-right">${{ (item.unit_price * item.quantity).toFixed(2) }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <hr class="my-2" />

      <!-- Totals -->
      <div class="mb-2">
        <div class="flex justify-between">
          <span>Subtotal:</span>
          <span>${{ Number(transactionData.subtotal).toFixed(2) }}</span>
        </div>
        <div class="flex justify-between">
          <span>Tax:</span>
          <span>${{ Number(transactionData.tax).toFixed(2) }}</span>
        </div>
        <div class="flex justify-between font-bold text-lg">
          <span>Total:</span>
          <span>${{ Number(transactionData.total).toFixed(2) }}</span>
        </div>
      </div>

      <div class="mt-4">
        <div>Payment Method: <span class="font-semibold">{{ transactionData.payment_method }}</span></div>
      </div>

      <div class="text-center text-xs mt-6">
        Thank you for choosing our club! ⛳
      </div>

      <!-- Print Button -->
      <div class="flex justify-center mt-4">
        <button
        @click="printReceipt"
        class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2 rounded-full shadow-md transition-all duration-200"
        >
        🖨️ Print Receipt
        </button>
      </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'

// Props passed when opening this component
const props = defineProps({
  transactionData: { type: Object, required: true },
  transactionCode: { type: String, required: true },
})

const now = new Date()
const todayDate = now.toLocaleDateString()
const todayTime = now.toLocaleTimeString()

const printReceipt = () => {
    const printWindow = window.open('', '_blank')
    const receiptContent = document.getElementById('receipt').innerHTML

    printWindow.document.write(`
        <html>
            <head>
                <title>Receipt</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        padding: 20px;
                        margin: 0;
                        display: flex;
                        justify-content: center;
                    }
                    .text-center { text-align: center; }
                    .text-right { text-align: right; }
                    .text-left { text-align: left; }
                    .flex { display: flex; }
                    .justify-between { justify-content: space-between; }
                    .mb-2 { margin-bottom: 0.5rem; }
                    .mb-4 { margin-bottom: 1rem; }
                    .mt-4 { margin-top: 1rem; }
                    .mt-6 { margin-top: 1.5rem; }
                    .font-bold { font-weight: bold; }
                    .font-semibold { font-weight: 600; }
                    .text-lg { font-size: 1.125rem; }
                    .text-sm { font-size: 0.875rem; }
                    .text-xs { font-size: 0.75rem; }
                    .w-full { width: 100%; }
                    table { width: 100%; border-collapse: collapse; }
                    hr { border: none; border-top: 1px solid #ddd; margin: 8px 0; }
                    #receipt {
                        max-width: 400px;
                        width: 100%;
                        margin: 0 auto;
                        padding: 1.5rem;
                        box-sizing: border-box;
                    }
                    @media print {
                        button { display: none; }
                        body { padding: 0; }
                        #receipt {
                            width: 100%;
                            max-width: none;
                        }
                    }
                </style>
            </head>
            <body>
                <div id="receipt">
                    ${receiptContent}
                </div>
                <script>
                    window.onload = () => {
                        window.print();
                        setTimeout(() => { window.close(); }, 500);
                    };
                <\/script>
            </body>
        </html>
    `)
    printWindow.document.close()
}
</script>

<style scoped>
@media print {
    button {
        display: none;
    }
}
</style>

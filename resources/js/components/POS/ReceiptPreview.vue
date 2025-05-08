<template>
    <div class="p-6 bg-white w-[400px] text-sm" id="payment">
      <!-- Header -->
      <div class="text-center mb-4">
        <div class="font-bold text-lg">7GOLF</div>
        <div>123 Golf Lane, Greenfield</div>
        <div>(123) 456-7890</div>
      </div>

      <hr class="my-2" />

      <!-- Payment Methods -->
      <div class="mb-2">
        <div class="font-bold text-lg">💰 Payment</div>
        <div class="space-y-4">
          <div class="flex gap-4">
            <button
              v-for="method in paymentMethods"
              :key="method"
              @click="selectPaymentMethod(method)"
              :class="[
                'px-4 py-2 rounded-full border-4 font-bold transition-all shadow-md',
                selectedMethod === method
                  ? 'bg-pink-500 text-white border-black hover:bg-yellow-400 hover:text-black'
                  : 'bg-white text-black border-black hover:bg-pink-200'
              ]"
            >
              {{ method }}
            </button>
          </div>
        </div>

        <!-- Payment Section -->
        <div v-if="selectedMethod" class="mt-4">
          <label class="block mb-2 font-bold text-pink-600">Enter Amount:</label>
          <input
            v-model.number="amount"
            type="number"
            class="w-full p-3 border-4 border-black rounded-xl focus:outline-none focus:ring-4 focus:ring-yellow-300 mb-4"
            placeholder="Enter payment amount..."
          />

          <!-- Credit/Debit Card Details -->
          <div v-if="selectedMethod === 'Credit/Debit Card'" class="space-y-4">
            <div class="mb-2">
              <label class="font-bold text-pink-600">Card Number</label>
              <input
                v-model="cardDetails.cardNumber"
                type="text"
                placeholder="Enter card number"
                class="w-full p-3 border-4 border-black rounded-xl focus:outline-none focus:ring-4 focus:ring-yellow-300"
              />
            </div>
            <div class="mb-2">
              <label class="font-bold text-pink-600">Expiration Date</label>
              <input
                v-model="cardDetails.expiryDate"
                type="text"
                placeholder="MM/YY"
                class="w-full p-3 border-4 border-black rounded-xl focus:outline-none focus:ring-4 focus:ring-yellow-300"
              />
            </div>
            <div class="mb-2">
              <label class="font-bold text-pink-600">CVV</label>
              <input
                v-model="cardDetails.cvv"
                type="text"
                placeholder="Enter CVV"
                class="w-full p-3 border-4 border-black rounded-xl focus:outline-none focus:ring-4 focus:ring-yellow-300"
              />
            </div>
          </div>

          <!-- Payment details -->
          <div v-if="selectedMethod !== 'Split Payment'">
            <div class="flex justify-between font-bold">
              <span>Amount Due:</span>
              <span>${{ cartTotal.toFixed(2) }}</span>
            </div>

            <div v-if="amount >= cartTotal" class="text-green-600 font-bold mb-2 text-center">
              🎉 Payment complete!
            </div>
            <div v-if="amount < cartTotal" class="text-red-600 font-bold mb-2 text-center">
              Insufficient amount!
            </div>

            <button
              :disabled="amount < cartTotal"
              @click="confirmPayment"
              class="w-full bg-green-500 text-white p-3 rounded-full border-4 border-black font-bold shadow-md transition-transform transform hover:scale-105 hover:bg-yellow-400 hover:text-black disabled:opacity-50"
            >
              ✅ Confirm Payment
            </button>
          </div>
        </div>

        <!-- Receipt Preview Section -->
        <div v-if="showReceipt" class="mt-6 p-4 bg-white border-4 border-black rounded-xl shadow-md">
          <h3 class="text-lg font-bold text-pink-600 mb-4">🧾 Receipt Preview</h3>
          <div class="space-y-2">
            <div><strong>Payment Method:</strong> {{ selectedMethod }}</div>
            <div><strong>Amount Paid:</strong> ${{ amount.toFixed(2) }}</div>
            <div><strong>Items:</strong></div>
            <ul>
              <li v-for="item in store.cart" :key="item.id">{{ item.name }} - ${{ (item.price * item.quantity).toFixed(2) }}</li>
            </ul>
            <div class="text-right mt-4 font-bold">Subtotal: ${{ cartSubtotal.toFixed(2) }}</div>
            <div class="text-right mt-4 font-bold">Tax: ${{ cartTax.toFixed(2) }}</div>
            <div class="text-right mt-4 font-bold">Total: ${{ cartTotal.toFixed(2) }}</div>
          </div>

          <div class="flex gap-4 mt-4">
            <button
              @click="finalizeTransaction"
              class="bg-green-600 text-white px-4 py-2 rounded-full border-4 border-black font-bold hover:bg-yellow-400 hover:text-black transition-transform transform hover:scale-105"
            >
              💾 Save Transaction
            </button>
            <button
              @click="cancelPayment"
              class="bg-gray-400 text-white px-4 py-2 rounded-full border-4 border-black font-bold hover:bg-pink-300 transition-all"
            >
              ❌ Cancel
            </button>
          </div>
        </div>
      </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { usePOSStore } from '@/store/pos'

const store = usePOSStore()
const paymentMethods = ['Cash', 'Credit/Debit Card']
const selectedMethod = ref('')
const amount = ref(0)
const showReceipt = ref(false)
const cardDetails = ref({ cardNumber: '', expiryDate: '', cvv: '' })

const selectPaymentMethod = (method) => {
  selectedMethod.value = method
  amount.value = cartTotal.value // Set to total with tax
}

const confirmPayment = () => {
  showReceipt.value = true
}

const cartSubtotal = computed(() =>
  store.cart.reduce((total, item) => total + (item.price * item.quantity), 0)
)

const cartTax = computed(() => cartSubtotal.value * 0.08)

const cartTotal = computed(() => cartSubtotal.value + cartTax.value)

const finalizeTransaction = () => {
  alert('Transaction completed successfully! 🎉')
}

const cancelPayment = () => {
  alert('Payment cancelled!')
}
</script>


<style scoped>
#payment {
    font-family: 'Comic Neue', 'Comic Sans MS', cursive, sans-serif;
}

@media print {
    button {
        display: none;
    }
}
</style>

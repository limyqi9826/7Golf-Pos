<template>
    <PageHeader />
    <div class="min-h-screen p-6 bg-yellow-100 font-comic overflow-auto">
      <h2 class="text-2xl font-bold text-pink-600 mb-3 pb-1">💰 Payment</h2>

      <div class="space-y-4">
        <!-- Payment Methods -->
        <div class="flex gap-4">
            <button
                v-for="method in paymentMethods"
                :key="method"
                @click="selectPaymentMethod(method)"
                :disabled="paymentStatus === 'complete' || showReceipt"
                :class="[
                    'px-4 py-2 rounded-full border-4 font-bold transition-all shadow-md',
                    selectedMethod === method
                    ? 'bg-pink-500 text-white border-black hover:bg-yellow-400 hover:text-black'
                    : 'bg-white text-black border-black hover:bg-pink-200',
                    paymentStatus === 'complete' ? 'opacity-50 cursor-not-allowed' : ''
                ]"
                >
                {{ method }}
            </button>

        </div>

        <div v-if="selectedMethod" class="bg-white border-4 border-black rounded-xl p-4 shadow-md">
          <label class="block mb-2 font-bold text-pink-600">Enter Amount:</label>
          <input
            v-model.number="amount"
            type="number"
            class="w-full p-3 bg-yellow-100 border-4 border-black rounded-xl focus:outline-none focus:ring-4 focus:ring-yellow-300 mb-4"
            placeholder="Enter payment amount..."
          />

          <!-- Credit/Debit Card Details -->
        <div v-if="selectedMethod === 'Credit/Debit Card'" class="space-y-4">
            <div class="mb-2">
                <label class="font-bold text-pink-600">Card Number (First digit + Last 4 digits)</label>
                <input
                v-model="cardDetails.cardNumber"
                type="text"
                placeholder="Enter card number"
                class="w-full p-3 bg-yellow-100 border-4 border-black rounded-xl focus:outline-none focus:ring-4 focus:ring-yellow-300"
                maxlength="16"
                />
            </div>
        </div>

          <!-- Payment details -->
          <div v-if="selectedMethod !== 'Split Payment'">
            <div class="mb-2 flex justify-between font-bold">
              <span>Amount Due:</span>
              <span>${{ transactionData.total.toFixed(2) }}</span>
            </div>

            <!-- Payment Status -->
            <div v-if="paymentStatus" class="text-center mb-2">
              <div v-if="paymentStatus === 'complete'" class="text-green-600 font-bold">🎉 Payment complete!</div>
              <div v-if="paymentStatus === 'insufficient'" class="text-red-600 font-bold">Insufficient amount!</div>
            </div>

            <button
                :disabled="amount < transactionData.total"
                @click="confirmPayment"
                class="w-full bg-green-500 text-white p-3 rounded-full border-4 border-black font-bold shadow-md transition-transform transform hover:scale-105 hover:bg-yellow-400 hover:text-black disabled:opacity-50"
            >
              ✅ Confirm Payment
            </button>
          </div>
        </div>
      </div>

      <!-- Receipt Preview Section -->
        <div v-if="showReceipt" class="mt-6 p-4 bg-white border-4 border-black rounded-xl shadow-md">
            <h3 class="text-lg font-bold text-pink-600 mb-4">🧾 Receipt Preview</h3>
            <div class="space-y-2">
                <div><strong>Payment Method:</strong> {{ selectedMethod }}</div>
                <div><strong>Amount Paid:</strong> ${{ amount?.toFixed(2) || '0.00' }}</div>
                <div><strong>Items:</strong></div>
                <ul>
                <li v-for="item in transactionData.items" :key="item.id">
                    {{ item.name }} - ${{ (item.price * item.quantity).toFixed(2) }}
                </li>
                </ul>
                <div class="text-right mt-4 font-bold">Subtotal: ${{ transactionData.subtotal.toFixed(2) }}</div>
                <div class="text-right font-bold">Tax: ${{ transactionData.tax.toFixed(2) }}</div>
                <div class="text-right font-bold">Total: ${{ transactionData.total.toFixed(2) }}</div>
            </div>

            <div class="flex gap-4 mt-4">
                <button
                    v-if="!transactionFinalized"
                    @click="finalizeTransaction"
                    class="bg-green-600 text-white px-4 py-2 rounded-full border-4 border-black font-bold hover:bg-yellow-400 hover:text-black transition-transform transform hover:scale-105"
                    >
                💾 Save Transaction
                </button>

                <button
                    v-if="!transactionFinalized"
                    @click="cancelPayment"
                    class="bg-gray-400 text-white px-4 py-2 rounded-full border-4 border-black font-bold hover:bg-pink-300 transition-all"
                    >
                    ❌ Cancel
                </button>

                <button
                    v-if="transactionFinalized"
                    @click="showPrintModal = true"
                    class="bg-blue-600 text-white px-4 py-2 rounded-full border-4 border-black font-bold hover:bg-yellow-300 hover:text-black transition-transform transform hover:scale-105"
                    >
                    🖨️ Print
                </button>

                <button
                    v-if="transactionFinalized"
                    @click="router.push('/pos')"
                    class="bg-green-700 text-white px-4 py-2 rounded-full border-4 border-black font-bold hover:bg-red-400 hover:text-black transition-transform transform hover:scale-105"
                    >
                    ✅ Done
                </button>
            </div>
        </div>

        <!-- Print Receipt Modal -->
        <div
            v-if="showPrintModal"
            class="fixed inset-0 bg-black bg-opacity-70 flex justify-center items-center z-50 transition-opacity duration-300"
        >
            <div class="bg-white p-6 rounded-lg w-[90%] sm:w-[600px] max-h-[90vh] overflow-y-auto relative flex flex-col items-center justify-center">
                <ReceiptTemplate
                    :transaction-data="{
                        ...transactionData,
                        payment_method: selectedMethod,
                        amount_paid: amount,
                        cart: transactionData.items
                    }"
                    :transaction-code="transactionCode"
                />
                <button
                    @click="showPrintModal = false"
                    class="absolute top-2 right-2 bg-red-500 text-white px-3 py-1 rounded-full hover:bg-red-700"
                >
                    ✕
                </button>
            </div>
        </div>

      <!-- Add Custom Modal -->
      <CustomModal
            :show="showValidationModal"
            :message="modalMessage"
            :type="modalType"
            @confirm="handleModalConfirm"
        />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { usePOSStore } from '@/store/pos'
import CustomModal from './CustomModal.vue'
import PageHeader from '../Header.vue'
import ReceiptTemplate from './PrintReceipt.vue'

const route = useRoute()
const router = useRouter()
const store = usePOSStore()

const paymentMethods = ['Cash', 'Credit/Debit Card', 'Club Member Account']
const selectedMethod = ref('')
const amount = ref(0)
const showReceipt = ref(false)
const showSignature = ref(false)
const paymentStatus = ref('')
const transactionFinalized = ref(false)
const showValidationModal = ref(false)
const modalMessage = ref('')
const modalType = ref('warning')
const showPrintModal = ref(false)
const transactionCode = ref(generateTransactionCode())

function generateTransactionCode() {
    const date = new Date()
    const random = Math.floor(Math.random() * 10000).toString().padStart(4, '0')
    return `TX${date.getFullYear()}${(date.getMonth() + 1).toString().padStart(2, '0')}${date.getDate().toString().padStart(2, '0')}-${random}`
}

const cardDetails = ref({
    cardNumber: ''
})

const transactionData = ref({
    customer_id: null,
    hole_number: null,
    buggy_number: '',
    players: 0,
    subtotal: 0,
    tax: 0,
    total: 0,
    items: []
})

const selectPaymentMethod = (method) => {
    if (paymentStatus.value === 'complete' || showReceipt.value) return
    selectedMethod.value = method
    amount.value = transactionData.value.total
    showReceipt.value = false
}

const handleModalConfirm = () => {
    showValidationModal.value = false
}

const showError = (message) => {
    modalMessage.value = message
    modalType.value = 'error'
    showValidationModal.value = true
}

const confirmPayment = () => {
    if (selectedMethod.value === 'Credit/Debit Card') {
        if (!cardDetails.value.cardNumber) {
            showError('Please enter a card number')
            return
        }
        if (cardDetails.value.cardNumber.length < 5) {
            showError('Please enter a valid card number (at least 5 digits)')
            return
        }
        if (!/^\d+$/.test(cardDetails.value.cardNumber)) {
            showError('Card number must contain only digits')
            return
        }
        const network = getCardNetwork(cardDetails.value.cardNumber)
        if (network === 'Unknown') {
            showError('Invalid card number. Please check and try again.')
            return
        }
    }

    if (amount.value >= transactionData.value.total) {
        paymentStatus.value = 'complete'
        showSignature.value = true
        showReceipt.value = true
    } else {
        paymentStatus.value = 'insufficient'
        showError('Insufficient payment amount. Please enter the correct amount.')
    }
}

const finalizeTransaction = async () => {
  try {
    // Validate card details if credit card payment
    if (selectedMethod.value === 'Credit/Debit Card') {
      if (!cardDetails.value.cardNumber || cardDetails.value.cardNumber.length < 4) {
        alert('Please enter at least the last 4 digits of the card number');
        return;
      }
    }

    const finalData = {
        customer_id: transactionData.value.customer_id,
        hole_number: transactionData.value.hole_number,
        buggy_number: transactionData.value.buggy_number,
        players: transactionData.value.players,
        payment_method: selectedMethod.value,
        amount_paid: amount.value,
        subtotal: transactionData.value.subtotal,
        tax: transactionData.value.tax,
        total: transactionData.value.total,
        items: transactionData.value.items.map(item => ({
            product_id: item.id,
            quantity: item.quantity,
            price: item.price
        }))
    }

    // Add card details only if credit card payment
    if (selectedMethod.value === 'Credit/Debit Card') {
      finalData.card_payment = {
        card_last_four: cardDetails.value.cardNumber.slice(-4),
        card_network: getCardNetwork(cardDetails.value.cardNumber)
      }
    }

    await store.submitTransaction(finalData)
    paymentStatus.value = 'complete'
    store.clearCart()
    transactionFinalized.value = true
    alert('Transaction completed successfully! 🎉')
  } catch (error) {
    const errorMessage = error.response?.data?.message || 'Failed to process transaction. Please try again.'
    alert(errorMessage)
  }
}

const getCardNetwork = (cardNumber) => {
const cardNetworks = {
    '4': 'Visa',
    '5': 'MasterCard',
    '3': 'American Express'
}
return cardNetworks[cardNumber.charAt(0)] || 'Unknown'
}

const cancelPayment = () => {
router.push('/pos')
}

onMounted(() => {
    try {
        const queryData = route.query.transactionData
        if (queryData) {
        const parsedData = JSON.parse(queryData)
        transactionData.value = parsedData
        amount.value = parsedData.total
        } else {
        router.push('/pos')
        }
    } catch (error) {
        console.error('Error parsing transaction data:', error)
        router.push('/pos')
    }
})
</script>

<style scoped>
canvas {
    border: 1px solid #ccc;
}

.comic-font {
    font-family: 'Comic Neue', 'Comic Sans MS', cursive, sans-serif;
}

</style>

<template>
    <PageHeader />
    <div class="min-h-screen p-6 bg-yellow-100 font-comic overflow-auto">

        <nav class="text-sm mb-4 text-blue-800">
            <router-link to="/reports">
                <span class="text-2xl font-bold text-pink-600">📊 Reports</span>
            </router-link>
            <span class="mx-2">/</span>
            <span class="text-xl font-bold text-pink-600 mb-6">🧾 Transaction Details</span>
        </nav>

      <div class="bg-white border-4 border-black rounded-xl shadow-md p-6">
        <div v-if="transaction">
            <p class="text-lg font-bold text-yellow-700 mb-2">
            Transaction Code: <span class="text-black">{{ transaction.transaction_code }}</span>
            </p>
            <p class="text-lg font-bold text-yellow-700 mb-2">
            Customer: <span class="text-black">{{ transaction.customer_name }}</span>
            </p>
            <p class="text-lg font-bold text-yellow-700 mb-2">
            Subtotal: <span class="text-black">${{ Number(transaction.subtotal).toFixed(2) }}</span>
            </p>
            <p class="text-lg font-bold text-yellow-700 mb-2">
            Tax: <span class="text-black">${{ Number(transaction.tax).toFixed(2) }}</span>
            </p>
            <p class="text-lg font-bold text-yellow-700 mb-4">
            Total: <span class="text-black">${{ Number(transaction.total).toFixed(2) }}</span>
            </p>
            <p class="text-lg font-bold text-yellow-700 mb-4">
            Payment Method: <span class="text-black">
                {{ transaction.payment_method }}
                <span
                v-if="transaction.payment_method === 'Credit/Debit Card' && cardPayment"
                class="ml-2 px-2 py-1 bg-yellow-100 rounded-full text-sm"
                >
                {{ cardPayment.card_network }} (****{{ cardPayment.card_last_four }})
                </span>
            </span>
            </p>

        </div>

        <div v-if="items.length" class="overflow-x-auto">
          <table class="w-full border-collapse border-2 border-black bg-yellow-100">
            <thead class="bg-pink-200">
              <tr>
                <th class="p-3 border-2 border-black">#</th>
                <th class="p-3 border-2 border-black">Item</th>
                <th class="p-3 border-2 border-black">Quantity</th>
                <th class="p-3 border-2 border-black">Unit Price</th>
                <th class="p-3 border-2 border-black">Total</th>
                <th class="p-3 border-2 border-black">Date</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in items" :key="item.id" class="hover:bg-yellow-200 transition">
                <td class="p-3 border-2 border-black">{{ index + 1 }}</td>
                <td class="p-3 border-2 border-black">{{ item.item_name || item.item_id }}</td>
                <td class="p-3 border-2 border-black">{{ item.quantity }}</td>
                <td class="p-3 border-2 border-black">${{ Number(item.unit_price).toFixed(2) }}</td>
                <td class="p-3 border-2 border-black">${{ Number(item.total_price).toFixed(2) }}</td>
                <td class="p-3 border-2 border-black">{{ new Date(item.created_at).toLocaleString() }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <p v-else class="text-gray-600 mt-4">No transaction details available.</p>

        <!-- Buttons for Preview and Delete in the same row -->
        <div class="mt-6 flex justify-end gap-4">
          <!-- Delete Transaction Button -->
          <button
            @click="deleteTransaction"
            class="bg-red-600 text-white px-4 py-2 rounded-full border-4 border-black hover:bg-red-400"
          >
            🗑️ Delete Transaction
          </button>
          <!-- Preview Receipt Button -->
          <button
            @click="showReceipt = true"
            class="bg-green-600 text-white px-4 py-2 rounded-full border-4 border-black hover:bg-yellow-300 hover:text-black"
          >
            🧾 Preview Receipt
          </button>

          <button
            @click="router.push('/reports')"
            class="bg-yellow-400 text-black px-4 py-2 rounded-full border-4 border-black hover:bg-yellow-200 hover:text-black"
        >
            ✅ Complete
        </button>
        </div>
      </div>

      <!-- Receipt Modal -->
      <div
        v-if="showReceipt"
        class="fixed inset-0 bg-black bg-opacity-70 flex justify-center items-center z-50 transition-opacity duration-300"
      >
        <div class="bg-white p-6 rounded-lg w-[90%] sm:w-[600px] max-h-[90vh] overflow-y-auto relative flex flex-col items-center justify-center">
          <ReceiptTemplate
            :transaction-data="{ ...transaction, cart: items }"
            :transaction-code="transaction?.transaction_code"
          />
          <button
            @click="showReceipt = false"
            class="absolute top-2 right-2 bg-red-500 text-white px-3 py-1 rounded-full hover:bg-red-700"
          >
            ✕
          </button>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <CustomModal
      :show="showDeleteModal"
      message="Are you sure you want to delete this transaction? This action cannot be undone."
      type="warning"
      :showCancel="true"
      @confirm="confirmDelete"
      @cancel="cancelDelete"
    />
</template>


<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import CustomModal from '@/components/POS/CustomModal.vue'
import ReceiptTemplate from '@/components/POS/PrintReceipt.vue'
import PageHeader from '@/components/Header.vue'
import axios from 'axios'

const route = useRoute()
const router = useRouter()
const transactionId = route.params.id

const transaction = ref(null)
const items = ref([])
const cardPayment = ref(null)

onMounted(async () => {
  try {
    const response = await axios.get(`/api/reports/details/${transactionId}`)

    if (response.data.success) {
      transaction.value = response.data.transaction
      items.value = response.data.data

      // If it's a card payment, fetch the card details
      if (transaction.value?.payment_method === 'Credit/Debit Card') {
        const cardResponse = await getCardPaymentInfo(transactionId)
        if (cardResponse?.success) {
          cardPayment.value = cardResponse.data
          // Update transaction with card payment details
          transaction.value = {
            ...transaction.value,
            card_payment: cardResponse.data
          }
        }
      }
    } else {
      console.error('Transaction fetch failed:', response.data.message)
    }
  } catch (err) {
    console.error('Failed to fetch transaction details:', err)
  }
})

const showReceipt = ref(false)

const showDeleteModal = ref(false)

const deleteTransaction = () => {
  showDeleteModal.value = true
}

const confirmDelete = async () => {
  try {
    const response = await axios.delete(`/api/reports/${transactionId}`)
    if (response.data.success) {
      alert('Transaction deleted successfully.')
      router.push('/reports')
    } else {
      alert('Failed to delete transaction: ' + response.data.message)
    }
  } catch (err) {
    console.error('Error deleting transaction:', err)
    alert('An error occurred while trying to delete the transaction.')
  } finally {
    showDeleteModal.value = false
  }
}

const cancelDelete = () => {
  showDeleteModal.value = false
}

const getCardPaymentInfo = async (transactionId) => {
  try {
    const response = await axios.get(`/api/card-payment/${transactionId}`);
    // console.log('Card Payment Response:', response.data);

    if (response.data && response.data.data) {
      return {
        success: true,
        data: response.data.data
      };
    }

    return {
      success: false,
      message: 'No card payment details found'
    };
  } catch (error) {
    console.error('Error fetching card payment:', error.response?.data || error.message);
    return {
      success: false,
      message: error.response?.data?.message || 'Failed to fetch card payment details'
    };
  }
}

defineProps({
  id: {
    type: String,
    required: false
  }
})
</script>

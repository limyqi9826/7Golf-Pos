<template>
    <div class="flex flex-col h-full bg-blue-50 p-4 font-comic">
      <!-- Transaction Header -->
      <div class="mb-4">
        <h2 class="text-2xl font-bold text-pink-600 mb-3 border-b-4 border-black pb-1">🎟️ Transaction</h2>

        <!-- Customer Selection -->
        <div class="relative mb-4">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search Customer..."
            class="w-full p-3 border-4 border-black rounded-xl focus:outline-none focus:ring-4 focus:ring-yellow-300 bg-white shadow-md"
          />
          <ul v-if="filteredCustomers.length" class="absolute z-10 bg-white w-full max-h-40 overflow-y-auto border-4 border-black rounded-xl mt-1 shadow-md">
            <li
              v-for="customer in filteredCustomers"
              :key="customer.id"
              @click="selectCustomer(customer)"
              class="p-2 hover:bg-yellow-100 cursor-pointer"
            >
              {{ customer.name }} - {{ customer.phone }}
            </li>
          </ul>
        </div>

        <!-- Hole, Buggy, Players -->
        <div class="flex gap-4 mb-4">
          <select
            v-model.number="holeNumber"
            class="w-1/3 p-3 border-4 border-black rounded-xl focus:outline-none focus:ring-4 focus:ring-yellow-300 bg-white shadow-md"
          >
            <option value="" disabled selected>Hole #</option>
            <option value="9">9 Holes</option>
            <option value="18">18 Holes</option>
          </select>

          <input
            v-model="buggyNumber"
            type="text"
            placeholder="Buggy #"
            required
            class="w-1/3 p-3 border-4 border-black rounded-xl focus:outline-none focus:ring-4 focus:ring-yellow-300 bg-white shadow-md"
          />
          <input
            v-model.number="players"
            type="number"
            min="1"
            placeholder="# Players"
            class="w-1/3 p-3 border-4 border-black rounded-xl focus:outline-none focus:ring-4 focus:ring-yellow-300 bg-white shadow-md"
          />
        </div>
      </div>

      <!-- Cart Items -->
      <div class="flex-1 overflow-y-auto min-h-[200px]">
        <div
          v-for="item in store.cart"
          :key="item.id"
          class="flex justify-between items-center border-b-4 border-dashed border-black py-3 mb-2"
        >
          <div>
            <h3 class="font-bold text-pink-600 text-lg">{{ item.name }}</h3>
            <p class="text-xs text-gray-700">${{ (item.price || 0).toFixed(2) }} x {{ item.quantity }}</p>
          </div>
          <div class="flex items-center gap-2">
            <button
              @click="store.decreaseQuantity(item.id)"
              class="px-3 py-2 bg-yellow-300 border-4 border-black rounded-full hover:bg-pink-300 transition-all"
            >-</button>
            <span class="text-lg font-bold">{{ item.quantity }}</span>
            <button
              @click="store.increaseQuantity(item.id)"
              class="px-3 py-2 bg-yellow-300 border-4 border-black rounded-full hover:bg-pink-300 transition-all"
            >+</button>
            <button
              @click="store.removeItem(item.id)"
              class="text-red-500 text-lg font-extrabold ml-4 hover:text-red-700 transition-all"
            >×</button>
          </div>
        </div>
      </div>

      <!-- Totals -->
      <div class="mt-4 p-4 bg-white border-4 border-black rounded-xl shadow-lg">
        <div class="flex justify-between mb-2 text-gray-800">
          <span>Subtotal:</span>
          <span>${{ calculateSubtotal.toFixed(2) }}</span>
        </div>
        <div class="flex justify-between mb-2 text-gray-800">
          <span>Tax (8%):</span>
          <span>${{ calculateTax.toFixed(2) }}</span>
        </div>
        <div class="flex justify-between font-bold text-xl text-blue-600 border-t-2 pt-2 border-black">
          <span>Total:</span>
          <span>${{ calculateTotal.toFixed(2) }}</span>
        </div>

        <!-- Proceed Button -->
        <button
          @click="proceedToPayment"
          class="w-full mt-6 bg-pink-500 text-white p-3 rounded-full font-bold text-lg border-4 border-black shadow-md hover:bg-yellow-400 hover:text-black transition-transform transform hover:scale-105"
        >
          Proceed to Payment
        </button>
      </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { usePOSStore } from '@/store/pos'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const store = usePOSStore()

const holeNumber = ref('')
const buggyNumber = ref('')
const players = ref('')
const selectedCustomer = ref(null)
const customers = ref([])
const searchQuery = ref('')

const filteredCustomers = computed(() => {
    return customers.value.filter(customer =>
        customer.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
})

function selectCustomer(customer) {
    selectedCustomer.value = customer
    searchQuery.value = `${customer.name} - ${customer.phone}`
}

async function fetchCustomers() {
    try {
        const response = await axios.get('/api/customers')
        customers.value = Array.isArray(response.data) ? response.data : []
    } catch (error) {
        console.error('Error fetching customers:', error)
        customers.value = []
    }
}

const calculateSubtotal = computed(() => {
    return store.cart.reduce((total, item) => {
        return total + (item.price * item.quantity)
    }, 0)
})

const calculateTax = computed(() => {
    return calculateSubtotal.value * 0.08
})

const calculateTotal = computed(() => {
    return calculateSubtotal.value + calculateTax.value
})

const proceedToPayment = async () => {
    try {
        const validationErrors = []

        if (store.cart.length === 0) {
            alert('Please add items to cart before proceeding to payment')
            return
        }

        if (!selectedCustomer.value) {
            validationErrors.push('Customer selection is required')
        }

        if (holeNumber.value) {
            if (![9, 18].includes(Number(holeNumber.value))) {
                validationErrors.push('Hole number must be either 9 or 18')
            }
        }

        // if (!buggyNumber.value || !buggyNumber.value.trim()) {
        //     validationErrors.push('Buggy number is required')
        // }

        if (Number(players.value) < 0) {
            validationErrors.push('Invalid number of players')
        }

        if (validationErrors.length > 0) {
            alert(validationErrors.join('\n'))
            return
        }

        // Prepare transaction data
        const transactionData = {
            customer_id: selectedCustomer.value.id,
            hole_number: holeNumber.value,
            buggy_number: buggyNumber.value,
            players: players.value,
            subtotal: calculateSubtotal.value,
            tax: calculateTax.value,
            total: calculateTotal.value,
            items: store.cart
        }

        await router.push({
            path: '/payment',
            query: {
                transactionData: JSON.stringify(transactionData)
            }
        })
    } catch (error) {
        console.error('Transaction error:', error)
        alert('Error processing transaction. Please try again.')
    }
}

onMounted(async () => {
    store.clearCart()
    await fetchCustomers()
})
</script>

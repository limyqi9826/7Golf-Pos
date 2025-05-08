<template>

<PageHeader />
    <div class="min-h-screen p-6 bg-yellow-100 font-comic overflow-auto">

        <!-- Header -->
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold text-pink-600">📦 Transaction Records</h1>
        </div>

        <!-- Filters & Search Bar Section -->
        <div class="mb-6 bg-white border-4 border-black rounded-xl shadow-md p-4">
            <div class="flex flex-col md:flex-row md:items-center gap-4">

                <!-- Day Filter -->
                <div class="flex-1">
                <label class="block mb-1 font-semibold text-gray-700">📅 Day (1–31)</label>
                <input
                    type="number"
                    v-model="selectedDay"
                    min="1"
                    max="31"
                    placeholder="e.g. 7"
                    class="w-full border-4 border-black rounded-lg px-4 py-2 bg-yellow-100 focus:outline-none focus:ring-4 focus:ring-yellow-300"
                />
                </div>

                <!-- Month Filter -->
                <div class="flex-1">
                <label class="block mb-1 font-semibold text-gray-700">📅 Month</label>
                <select
                    v-model="selectedMonth"
                    class="w-full border-4 border-black rounded-lg px-4 py-2 bg-yellow-100 focus:outline-none focus:ring-4 focus:ring-yellow-300"
                >
                    <option disabled value="">Select Month</option>
                    <option v-for="(m, i) in 12" :key="i" :value="i + 1">
                    {{ new Date(0, i).toLocaleString('default', { month: 'long' }) }}
                    </option>
                </select>
                </div>

                <!-- Year Filter -->
                <div class="flex-1">
                <label class="block mb-1 font-semibold text-gray-700">📆 Year</label>
                <select
                    v-model="selectedYear"
                    class="w-full border-4 border-black rounded-lg px-4 py-2 bg-yellow-100 focus:outline-none focus:ring-4 focus:ring-yellow-300"
                >
                    <option disabled value="">Select Year</option>
                    <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
                </select>
                </div>

                <!-- Search -->
                <div class="flex-1">
                <label class="block mb-1 font-semibold text-gray-700">🔍 Search</label>
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Enter keyword..."
                    class="w-full p-3 border-4 border-black rounded-lg bg-yellow-100 focus:outline-none focus:ring-4 focus:ring-yellow-300 shadow-sm"
                />
                </div>

            </div>
        </div>


        <!-- Item Table -->
        <div class="overflow-x-auto border-4 border-black rounded-xl shadow-md bg-white">
            <table class="w-full text-left table-auto">
                <thead class="bg-yellow-200 border-b-4 border-black">
                    <tr>
                        <th class="p-3">#</th>
                        <th class="p-3">Transaction Code</th>
                        <th class="p-3">Customer</th>
                        <th class="p-3">Hole/Buggy</th>
                        <th class="p-3">Players</th>
                        <th class="p-3">Total</th>
                        <th class="p-3">Payment Method</th>
                        <th class="p-3">Date</th>
                        <th class="p-3"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(transaction, index) in paginatedItems" :key="transaction.id" class="border-b-2 border-dashed border-black hover:bg-yellow-100 transition-all">
                        <td class="p-3">{{ index + 1 + (currentPage - 1) * itemsPerPage }}</td>
                        <td class="p-3">{{ transaction.transaction_code }}</td>
                        <td class="p-3">{{ transaction.customer_name || 'Walk-in' }}</td>
                        <td class="p-3">{{ `H${transaction.hole_number || '-'}/B${transaction.buggy_number || '-'}` }}</td>
                        <td class="p-3">{{ transaction.players || '-' }}</td>
                        <td class="p-3">${{ Number(transaction.total).toFixed(2) }}</td>
                        <td class="p-3">{{ transaction.payment_method }}</td>
                        <td class="p-3">{{ new Date(transaction.created_at).toLocaleDateString() }}</td>
                        <td class="p-3">
                    <button
                        @click="viewTransaction(transaction)"
                        class="bg-blue-500 text-white px-3 py-1 rounded-full border-2 border-black hover:bg-yellow-300 hover:text-black"
                    >
                        👁️ View
                    </button>
                    </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="flex justify-between items-center mt-4">
            <button
                @click="currentPage--"
                :disabled="currentPage === 1"
                class="bg-gray-300 px-3 py-1 border-2 border-black rounded disabled:opacity-50"
            >
                ◀ Prev
            </button>
            <span class="font-bold">Page {{ currentPage }} / {{ totalPages }}</span>
            <button
                @click="currentPage++"
                :disabled="currentPage === totalPages"
                class="bg-gray-300 px-3 py-1 border-2 border-black rounded disabled:opacity-50"
            >
                Next ▶
            </button>
        </div>

        <!-- Print Button -->
        <div class="mt-6 text-right">
            <button @click="exportToExcel" class="bg-green-600 text-white px-4 py-2 rounded-full border-4 border-black hover:bg-yellow-300 hover:text-black">
                🖨️ Downloads All Transactions
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { usePOSStore } from '@/store/pos'
import { useRouter } from 'vue-router'
import PageHeader from '@/components/Header.vue'
import * as XLSX from 'xlsx'

const router = useRouter()
const store = usePOSStore()

const transactions = ref([]);
const searchQuery = ref('')
const currentPage = ref(1)
const itemsPerPage = 5
const selectedDay = ref('')
const selectedMonth = ref(new Date().getMonth() + 1)
const selectedYear = ref(new Date().getFullYear())

const availableYears = computed(() => {
  const years = store.transactions.map(t => new Date(t.created_at).getFullYear());
  return [...new Set(years)].sort((a, b) => b - a); // Sorted desc
})


const filteredItems = computed(() => {
  return store.transactions
    .filter(transaction => {
      const date = new Date(transaction.created_at)
      const matchesMonth = date.getMonth() + 1 === Number(selectedMonth.value)
      const matchesYear = date.getFullYear() === Number(selectedYear.value)
      const matchesDay = !selectedDay.value || date.getDate() === Number(selectedDay.value)

      const matchesSearch =
        transaction.transaction_code?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        (transaction.payment_method && transaction.payment_method?.toLowerCase().includes(searchQuery.value.toLowerCase())) ||
        (transaction.customer_name && transaction.customer_name.toLowerCase().includes(searchQuery.value.toLowerCase()))

      return matchesMonth && matchesYear && matchesDay && matchesSearch
    })
    .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
})

const totalPages = computed(() => {
    return Math.ceil(filteredItems.value.length / itemsPerPage)
})

const paginatedItems = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage
    return filteredItems.value.slice(start, start + itemsPerPage)
})

function exportToExcel() {
  const data = filteredItems.value.map((transaction, i) => ({
    '#': i + 1,
    'Transaction Code': transaction.transaction_code,
    'Customer ID': transaction.customer_name || 'Walk-in',
    'Hole Number': transaction.hole_number || '-',
    'Buggy Number': transaction.buggy_number || '-',
    'Players': transaction.players || '-',
    'Subtotal': Number(transaction.subtotal).toFixed(2),
    'Tax': Number(transaction.tax).toFixed(2),
    'Total': Number(transaction.total).toFixed(2),
    'Payment Method': transaction.payment_method,
    'Date': new Date(transaction.created_at).toLocaleDateString()
  }))

  const worksheet = XLSX.utils.json_to_sheet(data)
  const workbook = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(workbook, worksheet, 'Transactions')

  XLSX.writeFile(workbook, 'Transaction_Report.xlsx')
}

onMounted(async () => {
    await store.fetchTransactions();
    transactions.value = store.transactions;
    // console.log('All Transactions:', store.transactions);
    // if (store.transactions.length > 0) {
    //     console.log('Sample Transaction Customer Name:', store.transactions[0].customer_name);
    //     console.log('Sample Transaction Full Data:', store.transactions[0]);
    // }
});

const viewTransaction = (transaction) => {
    router.push(`/reports/details/${transaction.id}`);
}
</script>

<template>
    <div class="p-6 bg-yellow-100 min-h-screen font-comic">
      <h1 class="text-3xl font-bold text-pink-600 mb-6">📊 Dashboard Overview</h1>

      <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6 mb-6">
        <router-link to="/reports" class="bg-white border-4 border-black rounded-xl p-4 shadow-md hover:shadow-lg transition transform hover:scale-105 cursor-pointer block">
            <h2 class="text-lg font-bold text-gray-700">💰 Daily Sales</h2>
            <p class="text-3xl font-bold text-green-600 mt-2">${{ totalSales.toFixed(2) }}</p>
        </router-link>

        <router-link to="/reports" class="bg-white border-4 border-black rounded-xl p-4 shadow-md hover:shadow-lg transition transform hover:scale-105 cursor-pointer block">
        <h2 class="text-lg font-bold text-gray-700">🧾 Daily Transaction</h2>
        <p class="text-3xl font-bold text-blue-600 mt-2">{{ totalTransactions }}</p>
        </router-link>

        <router-link to="/customers" class="bg-white border-4 border-black rounded-xl p-4 shadow-md hover:shadow-lg transition transform hover:scale-105 cursor-pointer block">
        <h2 class="text-lg font-bold text-gray-700">👥 All Customers</h2>
        <p class="text-3xl font-bold text-purple-600 mt-2">{{ totalCustomers }}</p>
        </router-link>

        <router-link to="/pro-shop" class="bg-white border-4 border-black rounded-xl p-4 shadow-md hover:shadow-lg transition transform hover:scale-105 cursor-pointer block">
        <h2 class="text-lg font-bold text-gray-700">📦 Items in Stock</h2>
        <p class="text-3xl font-bold text-orange-500 mt-2">{{ totalItems }}</p>
        </router-link>

        <div class="bg-white border-4 border-black rounded-xl p-4 shadow-md">
          <h2 class="text-lg font-bold text-gray-700">💼 Inventory Value</h2>
          <p class="text-3xl font-bold text-yellow-600 mt-2">${{ inventoryValue.toFixed(2) }}</p>
        </div>
      </div>

      <!-- Charts Section -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2 bg-white border-4 border-black rounded-xl p-4 shadow-md">
            <h2 class="text-lg font-bold text-gray-700 mb-4">📅 Sales Over Time</h2>
            <LineChart v-if="salesChartData.datasets.length" :chart-data="salesChartData" />
        </div>

        <div class="bg-white border-4 border-black rounded-xl p-4 shadow-md">
            <h2 class="text-lg font-bold text-gray-700 mb-4">💳 Payment Methods</h2>
            <DoughnutChart v-if="paymentChartData.datasets.length" :chart-data="paymentChartData" />
        </div>
    </div>

    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import LineChart from '../charts/LineChart.vue'
import DoughnutChart from '../charts/DoughnutChart.vue'
import { usePOSStore } from '@/store/pos'
import axios from 'axios'

const store = usePOSStore()

const totalSales = ref(0)
const totalTransactions = ref(0)
const totalCustomers = ref(0)
const totalItems = ref(0)
const inventoryValue = ref(0)
const today = new Date().toISOString().slice(0, 10)

const salesChartData = ref({
labels: [],
datasets: []
})

const paymentChartData = ref({
labels: [],
datasets: []
})

onMounted(async () => {
  try {
    const response = await axios.get('/api/customers')

    // Check if data exists regardless of success flag
    if (response.data && Array.isArray(response.data)) {
      totalCustomers.value = response.data.length
    } else if (response.data?.data && Array.isArray(response.data.data)) {
      totalCustomers.value = response.data.data.length
    } else {
      totalCustomers.value = 0
    }

    await Promise.all([
      store.fetchTransactions(),
      store.fetchItems(),
      store.fetchCustomers()
    ])

    const transactions = store.transactions
    const items = store.items

    const now = new Date()
    const today = now.toISOString().slice(0, 10)
    const currentYear = now.getFullYear()
    const currentMonth = now.getMonth()

    // Today's data
    const todaysTransactions = transactions.filter(t => t.created_at?.startsWith(today))
    totalTransactions.value = todaysTransactions.length
    totalSales.value = todaysTransactions.reduce((sum, t) => sum + (parseFloat(t.total) || 0), 0)

    const paymentMethods = {}
    todaysTransactions.forEach(t => {
      const method = t.payment_method || 'Unknown'
      paymentMethods[method] = (paymentMethods[method] || 0) + 1
    })

    paymentChartData.value = {
      labels: Object.keys(paymentMethods),
      datasets: [{
        label: 'Payment Methods (Today)',
        backgroundColor: ['#34d399', '#f87171', '#60a5fa', '#fbbf24'],
        data: Object.values(paymentMethods)
      }]
    }

    // Inventory
    const relevantItems = items.filter(item =>
      ['Pro Shop', 'Golf Equipment'].includes(item.category)
    )
    totalItems.value = relevantItems.length
    inventoryValue.value = relevantItems.reduce((sum, item) =>
      sum + (parseFloat(item.price) * item.stock), 0)

    // Monthly Sales Chart
    const monthlyTransactions = transactions.filter(t => {
      const date = new Date(t.created_at)
      return date.getFullYear() === currentYear && date.getMonth() === currentMonth
    })

    const salesByDate = {}

    monthlyTransactions.forEach(t => {
      const day = new Date(t.created_at).getDate()
      const label = `${day}`
      salesByDate[label] = (salesByDate[label] || 0) + (parseFloat(t.total) || 0)
    })

    const sortedLabels = Object.keys(salesByDate).sort((a, b) => {
      const dayA = parseInt(a.split(' ')[1])
      const dayB = parseInt(b.split(' ')[1])
      return dayA - dayB
    })

    salesChartData.value = {
      labels: sortedLabels,
      datasets: [{
        label: 'Total Sales (This Month)',
        backgroundColor: '#f472b6',
        borderColor: '#ec4899',
        data: sortedLabels.map(label => salesByDate[label]),
        fill: false,
        tension: 0.4
      }]
    }

  } catch (error) {
    console.error('Error loading dashboard data:', error)
  }
})


</script>

<style scoped>
.font-comic {
    font-family: 'Comic Neue', 'Comic Sans MS', cursive, sans-serif;
}
</style>

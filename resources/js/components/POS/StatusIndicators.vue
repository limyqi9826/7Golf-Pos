<template>
    <div class="flex items-center space-x-4">
      <!-- Printer Status -->
      <div class="flex items-center space-x-1">
        <span :class="printerOnline ? 'bg-green-500' : 'bg-red-500'" class="w-3 h-3 rounded-full inline-block"></span>
        <span class="text-xs">Printer</span>
      </div>

      <!-- Network Status -->
      <div class="flex items-center space-x-1">
        <span :class="networkOnline ? 'bg-green-500' : 'bg-red-500'" class="w-3 h-3 rounded-full inline-block"></span>
        <span class="text-xs">Network</span>
      </div>

      <!-- Low Inventory -->
      <div v-if="lowInventoryCount > 0" @click="showLowInventoryModal = true" class="flex items-center space-x-1 cursor-pointer">
        <span class="bg-yellow-400 w-3 h-3 rounded-full inline-block"></span>
        <span class="text-xs underline">{{ lowInventoryCount }} Low Stock</span>
      </div>

      <!-- Modal -->
      <LowInventoryModal
        :show="showLowInventoryModal"
        :lowStockItems="lowStockItems"
        @close="showLowInventoryModal = false"
      />
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { usePOSStore } from '@/store/pos'
import LowInventoryModal from '@/components/POS/LowInventoryModal.vue'

const store = usePOSStore()

const printerOnline = ref(true)
const networkOnline = ref(true)
const showLowInventoryModal = ref(false)

const checkPrinterStatus = () => {
    printerOnline.value = Math.random() > 0.1
}

const checkNetworkStatus = () => {
    networkOnline.value = navigator.onLine
}

const lowStockItems = computed(() => {
    return store.items.filter(item => item.stock <= 5)
})

const lowInventoryCount = computed(() => lowStockItems.value.length)

onMounted(() => {
checkPrinterStatus()
setInterval(checkPrinterStatus, 10000)

checkNetworkStatus()
    window.addEventListener('online', checkNetworkStatus)
    window.addEventListener('offline', checkNetworkStatus)
})
</script>

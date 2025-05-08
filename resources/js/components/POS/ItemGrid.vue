<template>
    <div>
      <div class="flex items-center mb-4">
        <input v-model="search" type="text" placeholder="Search items or scan barcode..." class="w-full p-2 border rounded" />
      </div>

      <div class="flex gap-4 mb-4">
        <button v-for="category in categories" :key="category"
          @click="activeCategory = category"
          :class="['px-3 py-2 rounded', activeCategory === category ? 'bg-blue-500 text-white' : 'bg-gray-300']">
          {{ category }}
        </button>
      </div>

      <div class="grid grid-cols-3 gap-4">
        <div v-for="item in filteredItems" :key="item.id" class="p-4 bg-white rounded shadow cursor-pointer" @click="addItem(item)">
          <h3 class="font-semibold text-base">{{ item.name }}</h3>
          <p class="text-sm text-gray-500">{{ item.category }}</p>
          <p class="font-bold mt-2">${{ item.price.toFixed(2) }}</p>
          <p class="text-xs text-red-500" v-if="item.stock <= 5">Low Stock ({{ item.stock }})</p>
        </div>
      </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { usePOSStore } from '@/store/pos'

const store = usePOSStore()

const search = ref('')
const activeCategory = ref('Pro Shop')
const categories = ['Pro Shop', 'Golf Equipment', 'Rentals', 'Tee Times']

const filteredItems = computed(() => {
    return store.items
        .filter(item => item.category === activeCategory.value)
        .filter(item => item.name.toLowerCase().includes(search.value.toLowerCase()))
})

const addItem = (item) => {
    store.addItem(item)
}
</script>

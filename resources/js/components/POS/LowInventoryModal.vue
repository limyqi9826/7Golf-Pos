<template>
    <div v-if="show" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg w-80" style="font-family: 'Comic Neue', cursive;">
        <h2 class="text-lg font-bold mb-4">⚠️ Low Inventory Items</h2>

        <div v-if="lowStockItems.length > 0">
          <ul class="space-y-2">
            <li v-for="item in lowStockItems" :key="item.id" class="flex justify-between">
              <span>{{ item.name }}</span>
              <span class="font-bold">{{ item.stock }}</span>
            </li>
          </ul>
        </div>
        <div v-else>
          <p>No items are low in stock!</p>
        </div>

        <div class="flex justify-end mt-6">
          <button @click="$emit('close')" class="bg-blue-600 text-white px-4 py-2 rounded">
            Close
          </button>
        </div>
      </div>
    </div>
</template>


<script setup>
defineProps({
    show: Boolean,
    lowStockItems: { type: Array, default: () => [] }
})
</script>

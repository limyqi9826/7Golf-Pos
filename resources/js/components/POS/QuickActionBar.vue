<template>
    <div
      v-if="showActions"
      class="fixed bottom-0 left-0 right-0 bg-yellow-50 border-t-4 border-black p-3 flex justify-around z-50 shadow-inner font-comic transition-all ease-in-out duration-300"
    >
      <button
        v-for="action in actions"
        :key="action.label"
        @click="action.handler"
        class="bg-pink-500 hover:bg-yellow-300 text-white hover:text-black py-3 px-5 rounded-full text-sm font-bold border-4 border-black shadow-md transition-transform transform hover:scale-105 tracking-wider"
      >
        {{ action.label }}
      </button>
    </div>
  </template>

<script setup>
import { usePOSStore } from '@/store/pos'
import { ref, onMounted, onUnmounted } from 'vue'

const store = usePOSStore()
const heldTransaction = ref(null)
const showActions = ref(false)

// Track mouse movement
function handleMouseMove(event) {
    const threshold = window.innerHeight - 80;
    showActions.value = event.clientY >= threshold;
}

onMounted(() => {
    window.addEventListener('mousemove', handleMouseMove)
})

onUnmounted(() => {
    window.removeEventListener('mousemove', handleMouseMove)
})

const actions = [
    { label: 'Hold', handler: () => {
        heldTransaction.value = { ...store.cart }
        store.clearCart()
        alert('Transaction held successfully! 🛑')
    }},
    { label: 'Recall', handler: () => {
        if (heldTransaction.value) {
            store.cart = heldTransaction.value
            store.calculateTotals()
            heldTransaction.value = null
            alert('Transaction recalled! 🔄')
        } else {
            alert('No transaction held.')
        }
    }},
    { label: 'Void', handler: () => {
        if (confirm('Are you sure to void this transaction?')) {
            store.clearCart()
            alert('Transaction voided. 🗑️')
        }
    }},
    { label: 'Daily Summary', handler: () => {
        alert('Coming soon: Daily Summary report! 📈')
    }},
    { label: 'End Shift', handler: () => {
        if (confirm('Confirm End Shift?')) {
            alert('Shift ended successfully. 🔒')
            window.location.href = '/logout'
        }
    }},
    { label: 'Inventory Check', handler: () => {
        const query = prompt('Enter Item Name or Barcode:')
        if (query) {
            const found = store.items.find(item => item.name.toLowerCase().includes(query.toLowerCase()))
            if (found) {
            alert(`Item: ${found.name}\nStock: ${found.stock}`)
            } else {
            alert('Item not found.')
            }
        }
    }},
    { label: 'Help', handler: () => {
        alert('Contact Support at (123) 456-7890')
    }}
]
</script>

<style scoped>
button {
    min-width: 90px;
}

.bg-navy-600 {
    background-color: #34495e;
}

.hover\:border-4:hover {
    border-width: 4px;
}

.hover\:border-f3a261:hover {
    border-color: #f3a261;
}

.shadow-inner {
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.05);
}

.border-navy-600 {
    border-color: #34495e;
}

</style>

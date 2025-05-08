<template>
    <transition name="fade">
      <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div
          class="w-full max-w-sm bg-white border-4 border-black rounded-xl p-6 font-comic text-center shadow-xl relative animate-pop"
        >
          <!-- Icon based on type -->
          <div class="text-4xl mb-4">
            <span v-if="type === 'success'" class="text-green-500">✅</span>
            <span v-else-if="type === 'error'" class="text-red-500">❌</span>
            <span v-else-if="type === 'warning'" class="text-yellow-500">⚠️</span>
            <span v-else class="text-blue-500">💬</span>
          </div>

          <!-- Message -->
          <p class="text-lg text-black font-semibold mb-6">{{ message }}</p>

          <!-- Confirm Button -->
          <button
            @click="$emit('confirm')"
            class="bg-blue-500 hover:bg-yellow-400 hover:text-black text-white font-bold py-2 px-6 rounded-full border-4 border-black transition-all transform hover:scale-105"
          >
            OK
          </button>

          <!-- Cancel Button -->
          <button
            v-if="showCancel"
            @click="$emit('cancel')"
            class="ml-4 bg-gray-300 hover:bg-red-400 hover:text-white text-black font-bold py-2 px-6 rounded-full border-4 border-black transition-all transform hover:scale-105"
          >
            Cancel
          </button>
        </div>
      </div>
    </transition>
</template>

<script setup>
defineProps({
    show: Boolean,
    message: String,
    type: {
        type: String,
        default: 'info' // 'success', 'warning', 'error'
    },
    showCancel: {
        type: Boolean,
        default: false
    }
})
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.animate-pop {
animation: pop-in 0.3s ease-out;
}

@keyframes pop-in {
    0% {
        transform: scale(0.6);
        opacity: 0;
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}
</style>

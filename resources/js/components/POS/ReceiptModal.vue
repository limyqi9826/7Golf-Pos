<template>
    <div v-if="show" class="fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center">
      <div class="bg-white w-[800px] max-w-full p-6 rounded-lg shadow-xl max-h-[90vh] overflow-y-auto">
        <slot />
        <div class="flex justify-end mt-4">
          <button @click="$emit('close')" class="bg-red-600 text-white px-4 py-2 rounded">
            Close
          </button>
        </div>
      </div>
    </div>
</template>

<script setup>
defineProps({ show: Boolean })
</script>

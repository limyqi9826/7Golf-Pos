<template>

<PageHeader />
    <div class="min-h-screen p-6 bg-yellow-100 font-comic overflow-auto">

      <!-- Header -->
      <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold text-pink-600">🏌️ Tee Times Records</h1>
        <button @click="showAddForm = true" class="bg-pink-500 text-white px-4 py-2 rounded-full border-4 border-black hover:bg-yellow-300 hover:text-black transition">
          ➕ Add Tee Time
        </button>
      </div>

      <!-- Search Bar -->
      <input
        v-model="searchQuery"
        type="text"
        placeholder="🔍 Search tee times..."
        class="w-full p-3 mb-4 border-4 border-black rounded-xl bg-white shadow-md focus:outline-none focus:ring-4 focus:ring-yellow-300"
      />

      <!-- Table -->
      <div class="overflow-x-auto border-4 border-black rounded-xl shadow-md bg-white">
        <table class="w-full table-auto text-left">
          <thead class="bg-yellow-200 border-b-4 border-black">
            <tr>
              <th class="p-3">#</th>
              <th class="p-3">Name</th>
              <th class="p-3">Category</th>
              <th class="p-3">Price</th>
              <th class="p-3">Stock</th>
              <th class="p-3">Barcode</th>
              <th class="p-3">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(item, index) in paginatedItems"
              :key="item.id"
              class="border-b-2 border-dashed border-black hover:bg-yellow-100 transition-all"
            >
              <td class="p-3">{{ index + 1 + (currentPage - 1) * itemsPerPage }}</td>
              <td class="p-3">{{ item.name }}</td>
              <td class="p-3">{{ item.category }}</td>
              <td class="p-3">${{ Number(item.price).toFixed(2) }}</td>
              <td class="p-3">{{ item.stock }}</td>
              <td class="p-3">{{ item.barcode }}</td>
              <td class="p-3 flex gap-2">
                <button @click="editItem(item)" class="bg-blue-500 text-white px-3 py-1 rounded border-2 border-black hover:bg-yellow-300 hover:text-black">✏️</button>
                <button @click="deleteItem(item.id)" class="bg-red-500 text-white px-3 py-1 rounded border-2 border-black hover:bg-yellow-300 hover:text-black">🗑️</button>
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

      <!-- Export -->
      <div class="mt-6 text-right">
        <button @click="exportToExcel" class="bg-green-600 text-white px-4 py-2 rounded-full border-4 border-black hover:bg-yellow-300 hover:text-black">
          🖨️ Download All Tee Times
        </button>
      </div>


    </div>

    <!-- Add/Edit Modal -->
      <div v-if="showAddForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 font-comic">
        <div class="bg-yellow-50 p-6 rounded-2xl w-96 border-4 border-black shadow-lg">
            <h2 class="text-xl font-bold text-pink-600 mb-4 border-b-4 border-black pb-2">
            {{ editForm ? '✏️ Edit Item' : '➕ Add New Item' }}
            </h2>

            <form @submit.prevent="editForm ? updateItem() : addItem()" class="space-y-4">
            <div>
                <label class="block text-sm font-bold text-gray-800 mb-1">📛 Name</label>
                <input v-model="formData.name" type="text" required class="w-full p-3 border-4 border-black rounded-xl bg-white shadow-inner focus:outline-none focus:ring-4 focus:ring-yellow-300" />
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-800 mb-1">🏷️ Category</label>
                <select v-model="formData.category" required class="w-full p-3 border-4 border-black rounded-xl bg-white shadow-inner focus:outline-none focus:ring-4 focus:ring-yellow-300">
                <option disabled value="">Select Category</option>
                <option value="Tee Times">Tee Times</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-800 mb-1">💲 Price</label>
                <input v-model.number="formData.price" type="number" step="0.01" required class="w-full p-3 border-4 border-black rounded-xl bg-white shadow-inner focus:outline-none focus:ring-4 focus:ring-yellow-300" />
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-800 mb-1">📦 Stock</label>
                <input v-model.number="formData.stock" type="number" required class="w-full p-3 border-4 border-black rounded-xl bg-white shadow-inner focus:outline-none focus:ring-4 focus:ring-yellow-300" />
            </div>

            <div class="flex gap-4 pt-4">
                <button type="submit" class="flex-1 bg-pink-500 text-white py-2 rounded-full font-bold border-4 border-black shadow-md hover:bg-yellow-400 hover:text-black transition-transform transform hover:scale-105">
                💾 Save
                </button>
                <button type="button" @click="closeForm" class="flex-1 bg-gray-300 text-black py-2 rounded-full font-bold border-4 border-black shadow-md hover:bg-pink-200 transition-transform transform hover:scale-105">
                ❌ Cancel
                </button>
            </div>
            </form>
        </div>
    </div>

    <!-- Add Delete Confirmation Modal -->
    <CustomModal
        :show="showDeleteModal"
        message="Are you sure you want to delete this tee time? This action cannot be undone."
        type="warning"
        :showCancel="true"
        @confirm="confirmDelete"
        @cancel="cancelDelete"
    />
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { usePOSStore } from '@/store/pos'
import CustomModal from '../components/POS/CustomModal.vue'
import PageHeader from '../components/Header.vue'
import * as XLSX from 'xlsx'
import axios from 'axios'

const store = usePOSStore()

const searchQuery = ref('')
const showAddForm = ref(false)
const editForm = ref(false)
const currentPage = ref(1)
const showDeleteModal = ref(false)
const itemToDelete = ref(null)
const itemsPerPage = 5

const formData = ref({
    id: null,
    name: '',
    category: 'tee times',
    price: 0,
    stock: 0,
    barcode: ''
})

const filteredItems = computed(() => {
    const items = store.items.filter(item => item.category.toLowerCase() === 'tee times')
    return items.filter(item =>
        item.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
})

const totalPages = computed(() => Math.ceil(filteredItems.value.length / itemsPerPage))

const paginatedItems = computed(() => {
const start = (currentPage.value - 1) * itemsPerPage
return filteredItems.value.slice(start, start + itemsPerPage)
})

async function addItem() {
    formData.value.category = 'tee times'
    try {
        await axios.post('/api/items', formData.value)
        await store.fetchItems()
        resetForm()
        showAddForm.value = false
        alert('✅ Item added successfully!')
    } catch (err) {
        console.error('Error adding item:', err)
    }
}

async function updateItem() {
    formData.value.category = 'tee times'
    try {
        await axios.put(`/api/items/${formData.value.id}`, formData.value)
        await store.fetchItems()
        resetForm()
        showAddForm.value = false
        editForm.value = false
        alert('✅ Item updated successfully!')
    } catch (err) {
        console.error('Error updating item:', err)
    }
}

async function deleteItem(id) {
    itemToDelete.value = id
    showDeleteModal.value = true
}

async function confirmDelete() {
    try {
        await axios.delete(`/api/items/${itemToDelete.value}`)
        await store.fetchItems()
        showDeleteModal.value = false
        itemToDelete.value = null
        alert('Item deleted successfully!')
    } catch (err) {
        console.error('Error deleting item:', err)
        alert('Failed to delete item. Please try again.')
    }
}

function cancelDelete() {
    showDeleteModal.value = false
    itemToDelete.value = null
}

function resetForm() {
    formData.value = {
        id: null,
        name: '',
        category: 'tee times',
        price: 0,
        stock: 0,
        barcode: ''
    }
}

function closeForm() {
    resetForm()
    showAddForm.value = false
    editForm.value = false
}

function editItem(item) {
    formData.value = { ...item }
    editForm.value = true
    showAddForm.value = true
}

function exportToExcel() {
    const data = filteredItems.value.map((item, index) => ({
        '#': index + 1,
        Name: item.name,
        Category: item.category,
        Price: Number(item.price).toFixed(2),
        Stock: item.stock,
        Barcode: item.barcode || ''
    }))
    const worksheet = XLSX.utils.json_to_sheet(data)
    const workbook = XLSX.utils.book_new()
    XLSX.utils.book_append_sheet(workbook, worksheet, 'Tee Times')
    XLSX.writeFile(workbook, 'Tee_Times_List.xlsx')
}

onMounted(async () => {
    await store.fetchItems()
})
</script>

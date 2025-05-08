<template>
    <PageHeader />
    <div class="min-h-screen p-6 bg-yellow-100 font-comic overflow-auto">

      <!-- Header -->
      <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold text-pink-600">👥 Customers</h1>
        <button
          @click="openAddForm"
          class="bg-pink-500 text-white px-4 py-2 rounded-full border-4 border-black hover:bg-yellow-300 hover:text-black transition-all"
        >
          ➕ Add New Customer
        </button>
      </div>

      <!-- Search Bar -->
      <input
        v-model="searchQuery"
        type="text"
        placeholder="🔍 Search cutomers..."
        class="w-full p-3 mb-4 border-4 border-black rounded-xl bg-white focus:outline-none focus:ring-4 focus:ring-yellow-300 shadow-md"
      />

      <!-- Customer Table -->
      <div class="overflow-x-auto border-4 border-black rounded-xl shadow-md bg-white">
        <table class="w-full text-left table-auto">
          <thead class="bg-yellow-200 border-b-4 border-black">
            <tr>
              <th class="p-3">#</th>
              <th class="p-3">Name</th>
              <th class="p-3">Email</th>
              <th class="p-3">Phone</th>
              <th class="p-3">Joined Date</th>
              <th class="p-3">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(customer, index) in paginatedCustomers"
              :key="customer.id"
              class="border-b-2 border-dashed border-black hover:bg-yellow-100 transition-all"
            >
              <td class="p-3">{{ index + 1 + (currentPage - 1) * itemsPerPage }}</td>
              <td class="p-3">{{ customer.name }}</td>
              <td class="p-3">{{ customer.email }}</td>
              <td class="p-3">{{ customer.phone }}</td>
              <td class="p-3">{{ new Date(customer.created_at).toLocaleDateString() }}</td>
              <td class="p-3 flex gap-2">
                <button
                  @click="editCustomer(customer)"
                  class="bg-blue-500 text-white px-3 py-1 rounded border-2 border-black hover:bg-yellow-300 hover:text-black"
                >✏️</button>
                <button
                  @click="deleteCustomer(customer.id)"
                  class="bg-red-500 text-white px-3 py-1 rounded border-2 border-black hover:bg-yellow-300 hover:text-black"
                >🗑️</button>
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

      <!-- Export Button -->
      <div class="mt-6 text-right">
        <button
          @click="exportToExcel"
          class="bg-green-600 text-white px-4 py-2 rounded-full border-4 border-black hover:bg-yellow-300 hover:text-black"
        >
          🖨️ Download Customer List
        </button>
      </div>

      <!-- Add/Edit Modal -->
      <div v-if="showAddForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 font-comic">
        <div class="bg-yellow-100 p-6 rounded-3xl w-[90%] max-w-lg border-4 border-black shadow-[8px_8px_0px_0px_black] relative">

          <!-- Close button -->
          <button
            @click="closeForm"
            class="absolute top-3 right-3 text-xl font-bold text-black hover:text-pink-500 transition-transform transform hover:scale-125"
          >
            ❌
          </button>

          <!-- Modal Title -->
          <h2 class="text-xl font-extrabold text-pink-600 mb-5 flex items-center gap-2 border-b-4 border-black pb-3">
            <span v-if="isEditMode">✏️ Edit Customer</span>
            <span v-else>➕ Add New Customer</span>
          </h2>

          <!-- Form -->
          <form @submit.prevent="isEditMode ? updateCustomer() : addCustomer()" class="space-y-4">
            <div>
              <label class="block text-sm font-bold text-gray-800 mb-1">👤 Name</label>
              <input v-model="formData.name" type="text" required class="w-full p-3 border-4 border-black rounded-xl bg-white shadow-inner focus:outline-none focus:ring-4 focus:ring-yellow-300" />
            </div>
            <div>
              <label class="block text-sm font-bold text-gray-800 mb-1">📧 Email</label>
              <input v-model="formData.email" type="email" required class="w-full p-3 border-4 border-black rounded-xl bg-white shadow-inner focus:outline-none focus:ring-4 focus:ring-yellow-300" />
            </div>
            <div>
              <label class="block text-sm font-bold text-gray-800 mb-1">📱 Phone</label>
              <input v-model="formData.phone" type="tel" required class="w-full p-3 border-4 border-black rounded-xl bg-white shadow-inner focus:outline-none focus:ring-4 focus:ring-yellow-300" />
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

      <!-- Delete Modal -->
      <CustomModal
        :show="showDeleteModal"
        message="Are you sure you want to delete this customer? This action cannot be undone."
        type="warning"
        :showCancel="true"
        @confirm="confirmDelete"
        @cancel="cancelDelete"
      />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import * as XLSX from 'xlsx'
import axios from 'axios'
import CustomModal from '../components/POS/CustomModal.vue'
import PageHeader from '@/components/Header.vue'

const searchQuery = ref('')
const showAddForm = ref(false)
const isEditMode = ref(false)
const currentPage = ref(1)
const loading = ref(false)
const itemsPerPage = 5
const customers = ref([])
const showDeleteModal = ref(false)
const customerToDelete = ref(null)

const formData = ref({
  id: null,
  name: '',
  email: '',
  phone: '',
  membership_type: 'Regular'
})

const filteredCustomers = computed(() => {
  return customers.value.filter(customer =>
    customer.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    customer.email.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    customer.phone.includes(searchQuery.value)
  )
})

const totalPages = computed(() => Math.ceil(filteredCustomers.value.length / itemsPerPage))

const paginatedCustomers = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  return filteredCustomers.value.slice(start, start + itemsPerPage)
})

function openAddForm() {
  resetForm()
  showAddForm.value = true
  isEditMode.value = false
}

function editCustomer(customer) {
  formData.value = { ...customer }
  isEditMode.value = true
  showAddForm.value = true
}

function resetForm() {
  formData.value = {
    id: null,
    name: '',
    email: '',
    phone: '',
    membership_type: 'Regular'
  }
}

function closeForm() {
  resetForm()
  showAddForm.value = false
  isEditMode.value = false
}

async function fetchCustomers() {
  try {
    const response = await axios.get('/api/customers')
    customers.value = Array.isArray(response.data) ? response.data :
                     (response.data?.data && Array.isArray(response.data.data)) ? response.data.data : []
  } catch (error) {
    console.error('Error fetching customers:', error)
    customers.value = []
  }
}

async function addCustomer() {
  loading.value = true
  try {
    await axios.post('/api/customers', formData.value)
    await fetchCustomers()
    closeForm()
    alert('Customer added successfully!')
  } catch (error) {
    console.error('Error adding customer:', error)
    alert('Failed to add customer. Please try again.')
  } finally {
    loading.value = false
  }
}

async function updateCustomer() {
  loading.value = true
  try {
    await axios.put(`/api/customers/${formData.value.id}`, formData.value)
    await fetchCustomers()
    closeForm()
    alert('Customer updated successfully!')
  } catch (error) {
    console.error('Error updating customer:', error)
    alert('Failed to update customer. Please try again.')
  } finally {
    loading.value = false
  }
}

async function deleteCustomer(id) {
  customerToDelete.value = id
  showDeleteModal.value = true
}

async function confirmDelete() {
  try {
    await axios.delete(`/api/customers/${customerToDelete.value}`)
    await fetchCustomers()
    showDeleteModal.value = false
    customerToDelete.value = null
    alert('Customer deleted successfully!')
  } catch (error) {
    console.error('Error deleting customer:', error)
    alert('Failed to delete customer. Please try again.')
  }
}

function cancelDelete() {
  showDeleteModal.value = false
  customerToDelete.value = null
}

function exportToExcel() {
  const data = filteredCustomers.value.map((customer, i) => ({
    '#': i + 1,
    Name: customer.name,
    Email: customer.email,
    Phone: customer.phone,
    Joined_Date: new Date(customer.created_at).toLocaleDateString()
  }))

  const worksheet = XLSX.utils.json_to_sheet(data)
  const workbook = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(workbook, worksheet, 'Customers')

  XLSX.writeFile(workbook, 'Customer_List.xlsx')
}

onMounted(async () => {
  await fetchCustomers()
})
</script>

<template>
    <PageHeader />

    <div class="min-h-screen bg-yellow-100 py-10 px-4 font-comic">
      <div class="max-w-2xl mx-auto bg-white border-4 border-black rounded-xl shadow-lg p-8">
        <h1 class="text-4xl text-center text-blue-700 font-bold mb-8">👩‍🎤 My Profile</h1>

        <!-- View Mode -->
        <div v-if="!editMode" class="space-y-5 text-lg text-gray-800">
          <p><span class="font-semibold">Name:</span> {{ user.name }}</p>
          <p><span class="font-semibold">Email:</span> {{ user.email }}</p>
          <p><span class="font-semibold">Joined:</span> {{ formatDate(user.created_at) }}</p>

          <div class="text-center mt-6">
            <button
              @click="editMode = true"
              class="bg-yellow-300 hover:bg-yellow-400 text-black font-bold py-2 px-5 border-2 border-black rounded-full transition"
            >
              ✏️ Edit Profile
            </button>
          </div>
        </div>

        <!-- Edit Mode -->
        <div v-else>
          <form @submit.prevent="updateProfile" class="space-y-4">
            <div>
              <label class="block font-bold mb-1">Name</label>
              <input
                v-model="form.name"
                type="text"
                class="w-full px-4 py-2 border-2 border-black rounded-lg focus:outline-none"
              />
            </div>

            <div>
              <label class="block font-bold mb-1">Email</label>
              <input
                v-model="form.email"
                type="email"
                class="w-full px-4 py-2 border-2 border-black rounded-lg focus:outline-none"
              />
            </div>

            <div class="flex justify-center gap-4 pt-4">
              <button
                type="submit"
                class="bg-green-400 hover:bg-green-500 text-black font-bold py-2 px-6 border-2 border-black rounded-full transition"
              >
                💾 Save
              </button>
              <button
                @click="cancelEdit"
                type="button"
                class="bg-red-300 hover:bg-red-400 text-black font-bold py-2 px-6 border-2 border-black rounded-full transition"
              >
                ❌ Cancel
              </button>
            </div>
          </form>
        </div>

        <!-- Messages -->
        <div v-if="message" class="mt-6 text-green-700 text-center font-bold">
          ✅ {{ message }}
        </div>
        <div v-if="error" class="mt-6 text-red-700 text-center font-bold">
          ⚠️ {{ error }}
        </div>
      </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import PageHeader from '@/components/Header.vue'
import axios from 'axios'

const user = ref({
    id: null,
    name: '',
    email: '',
    created_at: '',
    updated_at: ''
})

const form = reactive({
    name: '',
    email: ''
})

const editMode = ref(false)
const message = ref('')
const error = ref('')

const formatDate = date => {
    if (!date) return 'Not available'
    return new Date(date).toLocaleString()
}

onMounted(() => {
const storedUser = localStorage.getItem('user')
    if (storedUser) {
        const parsedUser = JSON.parse(storedUser)
        user.value = {
        ...parsedUser,
        created_at: parsedUser.created_at || new Date().toISOString()
        }
        form.name = user.value.name
        form.email = user.value.email
    } else {
        error.value = 'No user data found in local storage.'
    }
})

const updateProfile = async () => {
try {
    // Get the user token from localStorage
    const userData = JSON.parse(localStorage.getItem('user'))
    const token = userData.token

    const response = await axios.put(`/api/users/${user.value.id}`, {
        name: form.name,
        email: form.email
    }, {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    })

    if (response.data.success) {
      // Update local storage with the response data
      const updatedUser = {
        ...user.value,
        name: form.name,
        email: form.email,
        updated_at: response.data.user.updated_at || new Date().toISOString()
      }

      // Update the user ref and localStorage
      user.value = updatedUser
      localStorage.setItem('user', JSON.stringify(updatedUser))

      message.value = 'Profile updated successfully!'
      error.value = ''
      editMode.value = false
    } else {
      throw new Error(response.data.message || 'Failed to update profile')
    }
  } catch (err) {
    console.error('Profile update error:', err)
    error.value = err.response?.data?.message || 'Failed to update profile. Please try again.'
    message.value = ''
  }
}

const cancelEdit = () => {
    form.name = user.value.name
    form.email = user.value.email
    editMode.value = false
    error.value = ''
    message.value = ''
}
</script>

<style scoped>
.font-comic {
    font-family: 'Comic Neue', 'Comic Sans MS', cursive, sans-serif;
}
</style>

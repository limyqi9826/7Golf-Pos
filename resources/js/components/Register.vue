<template>
    <div class="min-h-screen flex items-center justify-center bg-yellow-50 font-comic">
      <div class="w-full max-w-md p-8 bg-white border-4 border-black rounded-xl shadow-lg">
        <h2 class="text-3xl font-bold text-pink-600 mb-6 text-center">📝 Register</h2>
        <form @submit.prevent="register">
          <input v-model="name" type="text" placeholder="Name"
            class="w-full mb-4 p-3 border-4 border-black rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-300" required />
          <input v-model="email" type="email" placeholder="Email"
            class="w-full mb-4 p-3 border-4 border-black rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-300" required />
          <input v-model="password" type="password" placeholder="Password"
            class="w-full mb-4 p-3 border-4 border-black rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-300" required />
          <button type="submit"
            class="w-full bg-pink-500 text-white p-3 font-bold rounded-full border-4 border-black hover:bg-yellow-400 hover:text-black transition-transform transform hover:scale-105">
            🎉 Register
          </button>
        </form>
        <p class="text-sm text-center mt-4">Already have an account?
          <router-link to="/" class="text-blue-600 underline">Login</router-link>
        </p>
      </div>
    </div>

    <CustomModal
      :show="showModal"
      :type="modalType"
      :message="modalMessage"
      :showCancel="false"
      @confirm="closeModal"
    />
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import CustomModal from './POS/CustomModal.vue'

const router = useRouter()
const name = ref('')
const email = ref('')
const password = ref('')
const showModal = ref(false)
const modalType = ref('info')
const modalMessage = ref('')

const closeModal = () => {
  showModal.value = false
}

const showMessage = (message, type = 'info') => {
  modalMessage.value = message
  modalType.value = type
  showModal.value = true
}

const register = async () => {
    if (!name.value.trim()) {
        showMessage('Please enter your name', 'warning')
        return
    }

    if (!email.value.trim()) {
        showMessage('Please enter your email', 'warning')
        return
    }

    if (!password.value || password.value.length < 6) {
        showMessage('Password must be at least 6 characters long', 'warning')
        return
    }

    try {
        const response = await axios.post('/api/register', {
            name: name.value.trim(),
            email: email.value.trim(),
            password: password.value
        })

        if (response.data.success) {
            showMessage('Registration successful! Please login.', 'success')

            // Clear form
            name.value = ''
            email.value = ''
            password.value = ''

            // Redirect to login page after modal is closed
            setTimeout(() => {
                router.push('/')
            }, 1500)
        } else {
            showMessage(response.data.message || 'Registration failed. Please try again.', 'error')
        }
    } catch (error) {
        if (error.response) {
            const errorMessage = error.response.data.message || 'Registration failed'
            showMessage(errorMessage, 'error')
        } else {
            showMessage('Network error. Please check your connection and try again.', 'error')
        }
    }
}
</script>

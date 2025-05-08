<template>
    <div class="min-h-screen flex items-center justify-center bg-yellow-50 font-comic">
      <div class="w-full max-w-md p-8 bg-white border-4 border-black rounded-xl shadow-lg">
        <h2 class="text-3xl font-bold text-blue-600 mb-6 text-center">🔐 Login</h2>
        <form @submit.prevent="login">
          <input v-model="email" type="email" placeholder="Email"
            class="w-full mb-4 p-3 border-4 border-black rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-300" required />
          <input v-model="password" type="password" placeholder="Password"
            class="w-full mb-4 p-3 border-4 border-black rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-300" required />
          <button type="submit"
            class="w-full bg-blue-500 text-white p-3 font-bold rounded-full border-4 border-black hover:bg-yellow-400 hover:text-black transition-transform transform hover:scale-105">
            🚀 Login
          </button>
        </form>
        <p class="text-sm text-center mt-4">Don't have an account?
          <router-link to="/register" class="text-pink-600 underline">Register</router-link>
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

const email = ref('')
const password = ref('')
const router = useRouter()

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

const login = async () => {
    if (!email.value.trim()) {
        showMessage('Please enter your email', 'warning')
        return
    }

    if (!password.value) {
        showMessage('Please enter your password', 'warning')
        return
    }

    try {
        const response = await axios.post('/api/login', {
            email: email.value.trim(),
            password: password.value
        })

        if (response.data.success) {
            // Store user details in localStorage
            const userData = {
                id: response.data.user.id,
                name: response.data.user.name,
                email: response.data.user.email,
                token: response.data.token,
                created_at: response.data.user.created_at || new Date().toISOString()
            }
            localStorage.setItem('user', JSON.stringify(userData))

            // Set authorization header for future requests
            axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`
            showMessage('Login successful! Welcome back! 🎉', 'success')

            // Clear form
            email.value = ''
            password.value = ''

            // Redirect to home page after modal is closed
            setTimeout(() => {
                router.push('/home')
            }, 1500)
        } else {
            showMessage(response.data.message || 'Login failed. Please check your credentials.', 'error')
        }
    } catch (error) {
        if (error.response) {
            showMessage(error.response.data.message || 'Login failed', 'error')
        } else {
            showMessage('Network error. Please check your connection and try again.', 'error')
        }
        console.error('Login error:', error)
    }
}
</script>

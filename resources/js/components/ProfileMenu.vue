<template>
    <div class="relative inline-block" @click.stop="toggleMenu">
      <img
        src="../assets/profile.png"
        alt="User"
        class="h-10 w-10 border-2 border-black rounded-full cursor-pointer shadow comic-font"
      />

      <div
        v-if="showMenu"
        class="fixed right-4 mt-2 w-52 bg-yellow-100 border-2 border-black rounded-lg shadow-xl z-[9999] comic-font overflow-visible"
        @click.stop
      >
        <div class="px-4 py-2 border-b-2 border-black text-sm text-black">
          <div class="flex flex-col">
            <span>Signed in as</span>
            <strong class="text-right">{{ username }}</strong>
          </div>
        </div>
        <ul class="text-sm text-black">
          <li>
            <router-link to="/profile" class="block px-4 py-2 hover:bg-blue-200 hover:font-bold transition-all">Profile</router-link>
          </li>
          <!-- <li>
            <router-link to="/settings" class="block px-4 py-2 hover:bg-blue-200 hover:font-bold transition-all">Settings</router-link>
          </li> -->
          <li>
            <button
              @click="logout"
              class="w-full text-left px-4 py-2 hover:bg-red-200 text-red-600 font-bold transition-all"
            >
              Logout
            </button>
          </li>
        </ul>
      </div>
    </div>
    <CustomModal
      :show="showModal"
      :type="modalType"
      :message="modalMessage"
      :showCancel="showConfirmation"
      @confirm="handleConfirm"
      @cancel="closeModal"
    />
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import CustomModal from './POS/CustomModal.vue'

const router = useRouter()
const showMenu = ref(false)
const username = ref('')
const showModal = ref(false)
const modalType = ref('info')
const modalMessage = ref('')
const showConfirmation = ref(false)
let pendingAction = null

onMounted(() => {
    // Get username from localStorage on component mount
    const userData = JSON.parse(localStorage.getItem('user'))
    username.value = userData?.name || 'Guest'

    // Close menu when clicking outside
    document.addEventListener('click', (e) => {
        if (showMenu.value) {
            showMenu.value = false
        }
    })
})

const toggleMenu = () => {
    showMenu.value = !showMenu.value
}

const closeModal = () => {
  showModal.value = false
  showConfirmation.value = false
  pendingAction = null
}

const showMessage = (message, type = 'info', confirmation = false) => {
  modalMessage.value = message
  modalType.value = type
  showConfirmation.value = confirmation
  showModal.value = true
}

const handleConfirm = () => {
  if (pendingAction) {
    pendingAction()
  }
  closeModal()
}

const logout = () => {
    showConfirmation.value = true
    modalMessage.value = 'Are you sure you want to logout?'
    modalType.value = 'warning'
    showModal.value = true
    pendingAction = () => {
        // Remove user data from localStorage
        localStorage.removeItem('user')

        // Close the menu
        showMenu.value = false

        // Show success message and redirect
        showMessage('Successfully logged out! 👋', 'success')
        setTimeout(() => {
            router.push('/')
        }, 1500)
    }
}
</script>

<style scoped>
.comic-font {
    font-family: 'Comic Neue', 'Comic Sans MS', cursive, sans-serif;
}
</style>

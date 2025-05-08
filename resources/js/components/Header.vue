<template>
    <header class="flex items-center justify-between px-6 py-4 bg-yellow-100 border-b-4 border-black shadow-lg rounded-b-xl comic-font relative">
      <!-- Logo + Menu Toggle -->
      <div class="flex items-center gap-4">
        <img
        src="../assets/logo.png"
        alt="Club Logo"
        class="h-12 border-2 border-black rounded-full cursor-pointer"
        @click="handleLogoClick"
        />
      </div>

      <!-- Navigation Menu (Desktop) -->
      <nav class="hidden lg:flex gap-6 text-black font-bold text-lg">
        <router-link to="/home" class="nav-link">Dashboard</router-link>
        <router-link to="/pos" class="nav-link">POS</router-link>
        <router-link to="/pro-shop" class="nav-link">Pro Shop</router-link>
        <router-link to="/tee-time" class="nav-link">Tee Time</router-link>
        <router-link to="/rentals" class="nav-link">Rentals</router-link>
        <router-link to="/customers" class="nav-link">Customers</router-link>
        <router-link to="/reports" class="nav-link">Reports</router-link>
      </nav>

      <!-- Right Section -->
      <div class="flex items-center gap-4">
        <span class="text-gray-700 font-semibold hidden sm:inline">{{ currentTime }}</span>
        <button class="relative text-black hover:text-red-500 text-xl">
          <i class="fas fa-bell"></i>
          <span
            :class="[
              'absolute top-0 right-0 h-2 w-2 border border-black rounded-full',
              isOnline ? 'bg-green-500' : 'bg-red-600'
            ]"
          ></span>
        </button>
        <ProfileMenu />
      </div>

      <!-- Mobile Nav Dropdown with Slide Animation -->
        <Transition name="slide-down">
            <div v-if="showMobileMenu" class="absolute top-full left-0 w-full bg-yellow-50 border-t-4 border-black shadow-lg rounded-b-xl lg:hidden z-40 overflow-hidden">
                <nav class="flex flex-col p-4 gap-4 text-black font-bold text-lg">
                <router-link to="/home" class="nav-link" @click="showMobileMenu = false">Dashboard</router-link>
                <router-link to="/pos" class="nav-link" @click="showMobileMenu = false">POS</router-link>
                <router-link to="/pro-shop" class="nav-link" @click="showMobileMenu = false">Pro Shop</router-link>
                <router-link to="/tee-time" class="nav-link" @click="showMobileMenu = false">Tee Time</router-link>
                <router-link to="/rentals" class="nav-link" @click="showMobileMenu = false">Rentals</router-link>
                <router-link to="/customers" class="nav-link" @click="showMobileMenu = false">Customers</router-link>
                <router-link to="/reports" class="nav-link" @click="showMobileMenu = false">Reports</router-link>
                </nav>
            </div>
        </Transition>
    </header>
</template>

<script setup>
import ProfileMenu from './ProfileMenu.vue'
import { ref, onMounted, onBeforeUnmount } from 'vue'

const isOnline = ref(navigator.onLine)
const currentTime = ref('')
const showMobileMenu = ref(false)

const updateTime = () => {
  const now = new Date()
  currentTime.value = now.toLocaleTimeString()
}

const updateOnlineStatus = () => {
  isOnline.value = navigator.onLine
}

onMounted(() => {
  updateTime()
  setInterval(updateTime, 1000)
  window.addEventListener('online', updateOnlineStatus)
  window.addEventListener('offline', updateOnlineStatus)
})

onBeforeUnmount(() => {
  window.removeEventListener('online', updateOnlineStatus)
  window.removeEventListener('offline', updateOnlineStatus)
})

// Toggle the mobile menu
const handleLogoClick = () => {
  if (window.innerWidth < 1024) {
    showMobileMenu.value = !showMobileMenu.value
  }
}

</script>

<style scoped>
.comic-font {
  font-family: 'Comic Sans MS', cursive, sans-serif;
}

/* Slide-down transition */
.slide-down-enter-from,
.slide-down-leave-to {
  transform: scaleY(0);
  opacity: 0;
  transform-origin: top;
}

.slide-down-enter-active,
.slide-down-leave-active {
  transition: transform 0.3s ease-out, opacity 0.3s ease-out;
}

.slide-down-enter-to,
.slide-down-leave-from {
  transform: scaleY(1);
  opacity: 1;
}

.nav-link {
  position: relative;
  transition: color 0.3s ease;
}

.nav-link:hover,
.router-link-active,
.router-link-exact-active {
  color: #d97706;
}

/* Underline animation for both hover and active */
.nav-link::after {
  content: '';
  position: absolute;
  bottom: -6px;
  left: 0;
  width: 100%;
  height: 3px;
  background-color: #facc15;
  border-radius: 2px;
  transform: scaleX(0);
  transform-origin: left;
  transition: transform 0.3s ease-out;
}

/* Animate underline on hover or active */
.nav-link:hover::after,
.router-link-exact-active.nav-link::after {
  transform: scaleX(1);
}

</style>

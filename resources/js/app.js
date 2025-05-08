import axios from 'axios'
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import POS from './views/POSView.vue'
import router from './router'

const app = createApp(POS)

app.use(createPinia())
app.use(router)
app.mount('#app')

// Add both headers
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;
axios.defaults.headers.common['Content-Type'] = 'application/json';

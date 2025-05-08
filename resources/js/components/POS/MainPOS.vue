<template>

<PageHeader />
    <div class="h-screen bg-yellow-100 font-comic flex flex-col">

        <div class="flex-1 flex flex-col lg:flex-row overflow-hidden">
        <!-- Left Panel (Items) -->
        <div class="w-full lg:w-3/5 p-6 overflow-y-auto h-full rounded-2xl shadow-xl border-r-4 border-black">
            <!-- Header Buttons -->
            <div class="mb-6 flex justify-end">
                <button
                    @click="showNewItemForm = true"
                    class="flex items-center gap-2 bg-purple-200 text-black px-5 py-2.5 rounded-full font-semibold uppercase border-4 border-black shadow hover:bg-yellow-200 transition-all hover:scale-105"
                >
                    🎨 + Add Item
                </button>
            </div>

            <!-- Category Tabs -->
            <div class="flex flex-wrap gap-4 mb-6">
                <button
                    v-for="category in categories"
                    :key="category"
                    :class="[
                    'px-5 py-2 rounded-full text-sm font-bold border-4',
                    selectedCategory === category
                        ? 'bg-pink-500 text-white border-black shadow-lg'
                        : 'bg-white text-black border-black hover:bg-yellow-200 shadow'
                    ]"
                    @click="selectedCategory = category"
                >
                    {{ category }}
                </button>
            </div>

            <!-- Search -->
            <div class="mb-6">
                <input
                    type="text"
                    v-model="searchTerm"
                    placeholder="🔍 Search items..."
                    class="w-full p-4 text-lg font-semibold border-4 border-black rounded-xl focus:ring-4 focus:ring-yellow-300 bg-white shadow-inner"
                />
            </div>

            <!-- Items Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
                <div
                    v-for="item in filteredItems"
                    :key="item.id"
                    @click="addItemToCart(item)"
                    class="cursor-pointer bg-white border-4 border-black rounded-2xl p-4 shadow-md hover:shadow-xl transition-transform hover:scale-105"
                >
                    <h3 class="text-lg font-bold text-pink-600">{{ item.name }}</h3>
                    <p class="text-sm text-gray-800 mt-1">📦 Stock: {{ item.stock }}</p>
                    <div class="text-right mt-4 text-blue-600 font-bold">${{ item.price.toFixed(2) }}</div>
                </div>
            </div>
        </div>

        <!-- Right Panel -->
        <div class="w-full lg:w-2/5 bg-blue-50 border-l-4 border-black p-6 rounded-2xl shadow-xl h-full overflow-y-auto">
            <TransactionPanel />
        </div>

        <!-- New Item Modal -->
        <div v-if="showNewItemForm" class="fixed inset-0 bg-black bg-opacity-40 z-50 flex items-center justify-center">
            <div class="bg-white w-11/12 sm:w-96 p-6 rounded-2xl border-4 border-black shadow-2xl">
                <h2 class="text-xl font-bold text-pink-600 border-b-2 border-black pb-2 mb-4">➕ Add New Item</h2>
                <form @submit.prevent="addNewItem" class="space-y-4">
                    <div>
                        <label class="block font-bold text-sm text-gray-700 mb-1">📛 Name</label>
                        <input v-model="newItem.name" type="text" required
                               class="w-full p-3 border-4 border-black rounded-xl shadow-inner focus:ring-4 focus:ring-yellow-300" />
                    </div>
                    <div>
                        <label class="block font-bold text-sm text-gray-700 mb-1">🏷️ Category</label>
                        <select v-model="newItem.category" required
                               class="w-full p-3 border-4 border-black rounded-xl shadow-inner focus:ring-4 focus:ring-yellow-300">
                            <option value="" disabled selected>Select Category</option>
                            <option v-for="category in categories" :key="category" :value="category">{{ category }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-sm text-gray-700 mb-1">💲 Price</label>
                        <input v-model.number="newItem.price" type="number" step="0.01" required
                               class="w-full p-3 border-4 border-black rounded-xl shadow-inner focus:ring-4 focus:ring-yellow-300" />
                    </div>
                    <div>
                        <label class="block font-bold text-sm text-gray-700 mb-1">📦 Stock</label>
                        <input v-model.number="newItem.stock" type="number" required
                               class="w-full p-3 border-4 border-black rounded-xl shadow-inner focus:ring-4 focus:ring-yellow-300" />
                    </div>
                    <div class="flex gap-3 pt-3">
                        <button type="submit"
                                class="flex-1 bg-pink-500 text-white py-2 rounded-full font-bold border-4 border-black shadow hover:bg-yellow-400 hover:text-black transition-all hover:scale-105">
                            💾 Save
                        </button>
                        <button type="button" @click="showNewItemForm = false"
                                class="flex-1 bg-gray-200 text-black py-2 rounded-full font-bold border-4 border-black shadow hover:bg-pink-200 transition-all hover:scale-105">
                            ❌ Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Low Inventory -->
        <LowInventoryModal
            :show="showLowInventoryModal"
            :lowStockItems="lowStockItems"
            @close="showLowInventoryModal = false"
        />
    </div>

</div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { usePOSStore } from '@/store/pos'
import TransactionPanel from '@/components/POS/TransactionPanel.vue'
import LowInventoryModal from '@/components/POS/LowInventoryModal.vue'
import PageHeader from '@/components/Header.vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const store = usePOSStore()

const categories = ['Pro Shop', 'Golf Equipment', 'Rentals', 'Tee Times']
const selectedCategory = ref('Pro Shop')
const searchTerm = ref('')
const showLowInventoryModal = ref(false)

// Low Inventory Modal
const lowStockItems = computed(() => {
    if (!store.items || !Array.isArray(store.items)) {
        return [];
    }
    return store.items.filter(item => item.stock < 3);
})

onMounted(async () => {
    await store.fetchItems()
    if (lowStockItems.value.length > 0) {
        showLowInventoryModal.value = true
    }
})

const filteredItems = computed(() => {
    if (!store.items || !Array.isArray(store.items)) {
        return [];
    }

    return store.items.filter(item => {
        try {
            item.price = Number(item.price);
            const categoryMatch = item.category === selectedCategory.value;
            const searchMatch = item.name.toLowerCase().includes(searchTerm.value.toLowerCase());
            return categoryMatch && searchMatch;
        } catch (error) {
            console.error('Error filtering item:', error);
            return false;
        }
    });
})

const addItemToCart = (item) => {
    store.addItemToCart(item)
}

const showNewItemForm = ref(false)
const newItem = ref({
    name: '',
    category: 'Pro Shop',
    price: 0,
    stock: 0,
    barcode: null
})

const addNewItem = async () => {
    try {
        const response = await axios.post('/api/items', newItem.value);

        // If successful, add to store
        if (response.data.success) {
            store.items.push(response.data.item);

            // Reset form and close modal
            newItem.value = {
                name: '',
                category: 'Pro Shop',
                price: 0,
                stock: 0,
                barcode: null
            };
            showNewItemForm.value = false;
            alert('Item added successfully!');
        }
    } catch (error) {
        console.error('Error adding new item:', error);
        alert('Failed to add item. Please try again.');
    }
}
</script>

<style scoped>
.bg-comic {
    background: url('https://example.com/professional-comic-pattern.png') repeat center center;
    background-size: 200px;
}

.item-card {
    border: 3px solid #f3a261; /* Comic-like border but more subtle */
    background: linear-gradient(to bottom, #f8f8f8, #f3f4f6);
    box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
}

.item-card:hover {
    box-shadow: 0px 8px 25px rgba(0, 0, 0, 0.2);
    transform: translateY(-5px);
}

.font-comic {
    font-family: 'Comic Neue', 'Comic Sans MS', cursive, sans-serif;
}

button {
    position: relative;
}

.comic-font {
    font-family: 'Comic Sans MS', cursive, sans-serif;
}

.text-navy-600 {
    color: #2c3e50;
}

.text-yellow-600 {
    color: #f39c12;
}

.bg-navy-600 {
    background-color: #34495e;
}

.bg-navy-100 {
    background-color: #ecf0f1;
}

</style>

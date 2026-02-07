<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="stock-balance" />

        <div class="ml-64 p-8">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-white">Stock Balance</h2>
                    <p class="text-slate-400">Current inventory levels by item</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-slate-800 rounded-xl p-4 mb-6">
                <form @submit.prevent="applyFilters" class="flex gap-4">
                    <select v-model="filterForm.item"
                        class="bg-slate-700 border-0 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-emerald-500">
                        <option value="">All Items</option>
                        <option v-for="item in items" :key="item.id" :value="item.id">{{ item.item }}</option>
                    </select>

                    <select v-model="filterForm.min_balance"
                        class="bg-slate-700 border-0 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-emerald-500">
                        <option value="">All Stock Levels</option>
                        <option value="0">Low Stock (0 or less)</option>
                        <option value="100">Below 100</option>
                        <option value="500">Below 500</option>
                    </select>

                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded-lg">Filter</button>
                    <a href="/stock-balance" class="bg-slate-600 hover:bg-slate-500 text-white px-6 py-2 rounded-lg flex items-center">Reset</a>
                </form>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-4 gap-4 mb-6">
                <div class="bg-slate-800 rounded-xl p-4">
                    <p class="text-slate-400 text-sm">Total Items</p>
                    <p class="text-2xl font-bold text-white">{{ stockBalances.length }}</p>
                </div>
                <div class="bg-slate-800 rounded-xl p-4">
                    <p class="text-slate-400 text-sm">In Stock</p>
                    <p class="text-2xl font-bold text-green-400">{{ inStockCount }}</p>
                </div>
                <div class="bg-slate-800 rounded-xl p-4">
                    <p class="text-slate-400 text-sm">Low Stock</p>
                    <p class="text-2xl font-bold text-yellow-400">{{ lowStockCount }}</p>
                </div>
                <div class="bg-slate-800 rounded-xl p-4">
                    <p class="text-slate-400 text-sm">Out of Stock</p>
                    <p class="text-2xl font-bold text-red-400">{{ outOfStockCount }}</p>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-slate-800 rounded-xl overflow-hidden shadow-lg">
                <table class="w-full">
                    <thead class="bg-slate-700">
                        <tr>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Item</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Category</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Unit</th>
                            <th class="px-4 py-4 text-right text-xs font-medium text-slate-300 uppercase">Incoming</th>
                            <th class="px-4 py-4 text-right text-xs font-medium text-slate-300 uppercase">Outgoing</th>
                            <th class="px-4 py-4 text-right text-xs font-medium text-slate-300 uppercase">Current Balance</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Status</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        <tr v-if="stockBalances.length === 0">
                            <td colspan="8" class="px-4 py-8 text-center text-slate-400">
                                No stock balance records found
                            </td>
                        </tr>
                        <tr v-for="stock in stockBalances" :key="stock.item_id" class="hover:bg-slate-700/50">
                            <td class="px-4 py-4 text-white font-medium">{{ stock.item_name }}</td>
                            <td class="px-4 py-4 text-slate-300">{{ stock.item_category }}</td>
                            <td class="px-4 py-4 text-slate-300">{{ stock.unit }}</td>
                            <td class="px-4 py-4 text-right text-emerald-400">{{ formatNumber(stock.incoming) }}</td>
                            <td class="px-4 py-4 text-right text-red-400">{{ formatNumber(stock.outgoing) }}</td>
                            <td class="px-4 py-4 text-right font-bold" :class="balanceClass(stock.current_balance)">
                                {{ formatNumber(stock.current_balance) }}
                            </td>
                            <td class="px-4 py-4">
                                <span :class="statusClass(stock.current_balance)" class="px-2 py-1 rounded text-xs">
                                    {{ stockStatus(stock.current_balance) }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <a :href="'/stock-balance/' + stock.item_id" class="text-blue-400 hover:text-blue-300">View Details</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import Sidebar from '../../Components/Sidebar.vue';

const props = defineProps({
    stockBalances: { type: Array, default: () => [] },
    items: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) }
});

const filterForm = ref({
    item: props.filters.item || '',
    min_balance: props.filters.min_balance || ''
});

const applyFilters = () => {
    router.get('/stock-balance', filterForm.value, { preserveState: true });
};

const formatNumber = (num) => (parseFloat(num) || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const balanceClass = (balance) => {
    if (balance <= 0) return 'text-red-400';
    if (balance < 100) return 'text-yellow-400';
    return 'text-green-400';
};

const statusClass = (balance) => {
    if (balance <= 0) return 'bg-red-500/20 text-red-400';
    if (balance < 100) return 'bg-yellow-500/20 text-yellow-400';
    return 'bg-green-500/20 text-green-400';
};

const stockStatus = (balance) => {
    if (balance <= 0) return 'Out of Stock';
    if (balance < 100) return 'Low Stock';
    return 'In Stock';
};

const inStockCount = computed(() => props.stockBalances.filter(s => s.current_balance >= 100).length);
const lowStockCount = computed(() => props.stockBalances.filter(s => s.current_balance > 0 && s.current_balance < 100).length);
const outOfStockCount = computed(() => props.stockBalances.filter(s => s.current_balance <= 0).length);
</script>

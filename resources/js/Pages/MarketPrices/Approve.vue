<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="market-prices" />
        <div class="ml-64 p-8">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h2 class="text-3xl font-bold text-white mb-2">Approve Market Prices</h2>
                    <p class="text-slate-400">Review and approve pending market prices</p>
                </div>
                <router-link to="/market-prices" class="text-emerald-400 hover:text-emerald-300">← Back to List</router-link>
            </div>

            <!-- Filters -->
            <div class="bg-slate-800 rounded-xl p-6 mb-6">
                <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-slate-400 text-sm mb-2">Search</label>
                        <input v-model="filters.search" type="text" placeholder="Item, Agreement No..." class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg">Apply</button>
                        <button type="button" @click="clearFilters" class="flex-1 bg-slate-600 hover:bg-slate-500 text-white px-4 py-2 rounded-lg">Clear</button>
                    </div>
                </form>
            </div>

            <!-- Summary Card -->
            <div class="bg-slate-800 rounded-xl p-6 mb-6">
                <p class="text-slate-400 text-sm mb-1">Pending Approvals</p>
                <p class="text-3xl font-bold text-yellow-400">{{ pendingPrices.total }}</p>
            </div>

            <!-- Table -->
            <div class="bg-slate-800 rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Item</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Unit</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Unit Price</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Tax %</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Price with VAT</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Type</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Agreement No</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Registered By</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            <tr v-for="price in pendingPrices.data" :key="price.id" class="hover:bg-slate-700/50">
                                <td class="px-4 py-3 text-white">{{ price.item }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ price.unit }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ formatCurrency(price.unit_price) }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ price.tax_p }}%</td>
                                <td class="px-4 py-3 text-emerald-400 font-medium">{{ formatCurrency(priceWithVAT(price)) }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ price.price_type }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ price.agg_no || '-' }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ price.registered_by }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ price.registered_date }}</td>
                                <td class="px-4 py-3">
                                    <button @click="approvePrice(price.id)" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1 rounded text-sm">Approve</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div v-if="pendingPrices.links && pendingPrices.links.length > 3" class="p-4 border-t border-slate-700 flex justify-center gap-2">
                    <a v-for="link in pendingPrices.links" :key="link.label" 
                       :href="link.url || '#'" 
                       @click.prevent="link.url && loadPage(link.url)"
                       v-html="link.label"
                       :class="[
                           'px-4 py-2 rounded-lg',
                           link.active ? 'bg-emerald-600 text-white' : 'bg-slate-700 text-slate-300 hover:bg-slate-600',
                           !link.url && 'opacity-50 cursor-not-allowed'
                       ]"></a>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Sidebar from '../../Components/Sidebar.vue';

const props = defineProps({
    pendingPrices: Object,
    filters: Object
});

const filters = ref(props.filters || {
    search: ''
});

const formatCurrency = (num) => num ? 'ETB ' + parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 2 }) : 'ETB 0.00';

const priceWithVAT = (price) => {
    const basePrice = parseFloat(price.unit_price) || 0;
    const tax = parseFloat(price.tax_p) || 0;
    return basePrice * (1 + tax / 100);
};

const applyFilters = () => {
    router.get('/market-prices/approve', filters.value, {
        preserveState: true,
        preserveScroll: true
    });
};

const clearFilters = () => {
    filters.value = { search: '' };
    applyFilters();
};

const loadPage = (url) => {
    const urlObj = new URL(url);
    router.get(urlObj.pathname + urlObj.search, {}, {
        preserveState: true,
        preserveScroll: true
    });
};

const approvePrice = (id) => {
    if (confirm('Are you sure you want to approve this market price?')) {
        router.put(`/market-prices/${id}/approve`, {}, {
            preserveState: false
        });
    }
};
</script>

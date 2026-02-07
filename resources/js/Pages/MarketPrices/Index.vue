<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="market-prices" />
        <div class="ml-64 p-8">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h2 class="text-3xl font-bold text-white mb-2">Market Prices</h2>
                    <p class="text-slate-400">Sales price list and management</p>
                </div>
                <div class="flex gap-3">
                    <a href="/market-prices/create" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg">
                        + Update Sales Price
                    </a>
                    <a href="/market-prices/approve" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                        Approve Prices
                    </a>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-slate-800 rounded-xl p-6 mb-6">
                <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-slate-400 text-sm mb-2">Search</label>
                        <input v-model="filters.search" type="text" placeholder="Item, Agreement No..." class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                    </div>
                    <div>
                        <label class="block text-slate-400 text-sm mb-2">Status</label>
                        <select v-model="filters.status" class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="Approved">Approved</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-400 text-sm mb-2">Customer</label>
                        <select v-model="filters.customer" class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white">
                            <option value="">All Customers</option>
                            <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.company_name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-400 text-sm mb-2">Price Type</label>
                        <select v-model="filters.price_type" class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white">
                            <option value="">All Types</option>
                            <option value="agreement">Agreement</option>
                            <option value="market">Market</option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg">Apply</button>
                        <button type="button" @click="clearFilters" class="flex-1 bg-slate-600 hover:bg-slate-500 text-white px-4 py-2 rounded-lg">Clear</button>
                    </div>
                </form>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-slate-800 rounded-xl p-6">
                    <p class="text-slate-400 text-sm mb-1">Total Prices</p>
                    <p class="text-3xl font-bold text-white">{{ marketPrices.total }}</p>
                </div>
                <div class="bg-slate-800 rounded-xl p-6">
                    <p class="text-slate-400 text-sm mb-1">Pending Approval</p>
                    <p class="text-3xl font-bold text-yellow-400">{{ pendingCount }}</p>
                </div>
                <div class="bg-slate-800 rounded-xl p-6">
                    <p class="text-slate-400 text-sm mb-1">Approved</p>
                    <p class="text-3xl font-bold text-emerald-400">{{ approvedCount }}</p>
                </div>
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
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Customer</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Type</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Agreement No</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            <tr v-for="price in marketPrices.data" :key="price.id" class="hover:bg-slate-700/50">
                                <td class="px-4 py-3 text-white">{{ price.item }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ price.unit }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ formatCurrency(price.unit_price) }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ price.tax_p }}%</td>
                                <td class="px-4 py-3 text-emerald-400 font-medium">{{ formatCurrency(priceWithVAT(price)) }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ getCustomerName(price.customer) }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ price.price_type }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ price.agg_no || '-' }}</td>
                                <td class="px-4 py-3">
                                    <span :class="statusClass(price.status)" class="px-2 py-1 rounded text-xs">{{ price.status }}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-300">{{ price.registered_date }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-2">
                                        <a :href="`/market-prices/${price.id}/edit`" class="text-blue-400 hover:text-blue-300 text-sm">Edit</a>
                                        <button @click="deletePrice(price.id)" class="text-red-400 hover:text-red-300 text-sm">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div v-if="marketPrices.links && marketPrices.links.length > 3" class="p-4 border-t border-slate-700 flex justify-center gap-2">
                    <a v-for="link in marketPrices.links" :key="link.label" 
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
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import Sidebar from '../../Components/Sidebar.vue';

const props = defineProps({
    marketPrices: Object,
    customers: Array,
    filters: Object
});

const filters = ref(props.filters || {
    search: '',
    status: '',
    customer: '',
    price_type: ''
});

const pendingCount = computed(() => {
    return props.marketPrices?.data?.filter(p => p.status === 'pending').length || 0;
});

const approvedCount = computed(() => {
    return props.marketPrices?.data?.filter(p => p.status === 'Approved').length || 0;
});

const statusClass = (status) => {
    if (status === 'Approved') return 'bg-emerald-500/20 text-emerald-400';
    return 'bg-yellow-500/20 text-yellow-400';
};

const formatCurrency = (num) => num ? 'ETB ' + parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 2 }) : 'ETB 0.00';

const priceWithVAT = (price) => {
    const basePrice = parseFloat(price.unit_price) || 0;
    const tax = parseFloat(price.tax_p) || 0;
    return basePrice * (1 + tax / 100);
};

const getCustomerName = (customerId) => {
    if (!customerId) return '-';
    const customer = props.customers.find(c => c.id == customerId);
    return customer?.company_name || '-';
};

const applyFilters = () => {
    router.get('/market-prices', filters.value, {
        preserveState: true,
        preserveScroll: true
    });
};

const clearFilters = () => {
    filters.value = { search: '', status: '', customer: '', price_type: '' };
    applyFilters();
};

const loadPage = (url) => {
    const urlObj = new URL(url);
    router.get(urlObj.pathname + urlObj.search, {}, {
        preserveState: true,
        preserveScroll: true
    });
};

const deletePrice = (id) => {
    if (confirm('Are you sure you want to delete this market price?')) {
        router.delete(`/market-prices/${id}`, {
            preserveState: false
        });
    }
};
</script>

<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="reports" />
        <div class="ml-64 p-8">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h2 class="text-3xl font-bold text-white mb-2">Delivered Items Report</h2>
                    <p class="text-slate-400">All delivered items by date range</p>
                </div>
                <a href="/reports" class="text-emerald-400 hover:text-emerald-300">← Back to Reports</a>
            </div>

            <!-- Filters -->
            <div class="bg-slate-800 rounded-xl p-6 mb-6">
                <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-slate-400 text-sm mb-2">From Date</label>
                        <input v-model="filters.from_date" type="date" lang="en" class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                    </div>
                    <div>
                        <label class="block text-slate-400 text-sm mb-2">To Date</label>
                        <input v-model="filters.to_date" type="date" lang="en" class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                    </div>
                    <div>
                        <label class="block text-slate-400 text-sm mb-2">Customer</label>
                        <select v-model="filters.customer" class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white">
                            <option value="">All Customers</option>
                            <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.company_name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-400 text-sm mb-2">Category</label>
                        <select v-model="filters.category" class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white">
                            <option value="">All Categories</option>
                            <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg">Apply Filters</button>
                    </div>
                </form>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-slate-800 rounded-xl p-6">
                    <p class="text-slate-400 text-sm mb-1">Total Deliveries</p>
                    <p class="text-3xl font-bold text-white">{{ deliveries.total }}</p>
                </div>
                <div class="bg-slate-800 rounded-xl p-6">
                    <p class="text-slate-400 text-sm mb-1">Total Quantity</p>
                    <p class="text-3xl font-bold text-emerald-400">{{ formatNumber(totalQuantity) }}</p>
                </div>
                <div class="bg-slate-800 rounded-xl p-6">
                    <p class="text-slate-400 text-sm mb-1">Total Amount</p>
                    <p class="text-3xl font-bold text-emerald-400">{{ formatCurrency(totalAmount) }}</p>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-slate-800 rounded-xl overflow-hidden">
                <div class="p-4 border-b border-slate-700 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-white">Deliveries</h3>
                    <div class="flex gap-3">
                        <button @click="exportCSV" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm">Export CSV</button>
                        <button @click="printReport" class="bg-slate-600 hover:bg-slate-500 text-white px-4 py-2 rounded-lg text-sm">Print</button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">D.No</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Customer</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Item</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Category</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Quantity</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Unit Price</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            <tr v-for="delivery in deliveries.data" :key="delivery.id" class="hover:bg-slate-700/50">
                                <td class="px-4 py-3 text-slate-300">{{ delivery.d_no }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ delivery.issue_date }}</td>
                                <td class="px-4 py-3 text-white">{{ delivery.customer?.company_name || delivery.project }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ delivery.item }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ delivery.category }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ formatNumber(delivery.quantity) }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ formatCurrency(delivery.unit_price) }}</td>
                                <td class="px-4 py-3 text-emerald-400 font-medium">{{ formatCurrency(delivery.total) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div v-if="deliveries.links && deliveries.links.length > 3" class="p-4 border-t border-slate-700 flex justify-center gap-2">
                    <a v-for="link in deliveries.links" :key="link.label" 
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
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import Sidebar from '../../Components/Sidebar.vue';

const props = defineProps({
    deliveries: Object,
    customers: Array,
    categories: Array,
    filters: Object
});

const filters = ref(props.filters || {
    from_date: '',
    to_date: '',
    customer: '',
    category: ''
});

const totalQuantity = computed(() => {
    return props.deliveries?.data?.reduce((sum, d) => sum + parseFloat(d.quantity || 0), 0) || 0;
});

const totalAmount = computed(() => {
    return props.deliveries?.data?.reduce((sum, d) => sum + parseFloat(d.total || 0), 0) || 0;
});

const formatNumber = (num) => num ? parseFloat(num).toLocaleString('en-US') : '0';
const formatCurrency = (num) => num ? 'ETB ' + parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 2 }) : 'ETB 0.00';

const applyFilters = () => {
    router.get('/reports/delivered-items', filters.value, {
        preserveState: true,
        preserveScroll: true
    });
};

const loadPage = (url) => {
    const urlObj = new URL(url);
    router.get(urlObj.pathname + urlObj.search, {}, {
        preserveState: true,
        preserveScroll: true
    });
};

const exportCSV = () => {
    const headers = ['D.No', 'Date', 'Customer', 'Item', 'Category', 'Quantity', 'Unit Price', 'Total'];
    const rows = props.deliveries.data.map(d => [
        d.d_no,
        d.issue_date,
        d.customer?.company_name || d.project,
        d.item,
        d.category,
        d.quantity,
        d.unit_price,
        d.total
    ]);
    const csv = [headers.join(','), ...rows.map(r => r.map(c => `"${c || ''}"`).join(','))].join('\n');
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `delivered_items_${new Date().toISOString().split('T')[0]}.csv`;
    a.click();
    URL.revokeObjectURL(url);
};

const printReport = () => {
    window.print();
};
</script>

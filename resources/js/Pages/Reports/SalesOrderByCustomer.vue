<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="reports" />
        <div class="ml-64 p-8">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h2 class="text-3xl font-bold text-white mb-2">Sales Order by Customer Report</h2>
                    <p class="text-slate-400">Sales orders grouped by customer</p>
                </div>
                <a href="/reports" class="text-emerald-400 hover:text-emerald-300">← Back to Reports</a>
            </div>

            <!-- Filters -->
            <div class="bg-slate-800 rounded-xl p-6 mb-6">
                <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-slate-400 text-sm mb-2">From Date</label>
                        <input v-model="filters.from_date" type="date" lang="en" class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                    </div>
                    <div>
                        <label class="block text-slate-400 text-sm mb-2">To Date</label>
                        <input v-model="filters.to_date" type="date" lang="en" class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg">Apply Filters</button>
                    </div>
                </form>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-slate-800 rounded-xl p-6">
                    <p class="text-slate-400 text-sm mb-1">Total Customers</p>
                    <p class="text-3xl font-bold text-white">{{ reportData.length }}</p>
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
                    <h3 class="text-lg font-semibold text-white">Sales Orders by Customer</h3>
                    <div class="flex gap-3">
                        <button @click="exportCSV" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm">Export CSV</button>
                        <button @click="printReport" class="bg-slate-600 hover:bg-slate-500 text-white px-4 py-2 rounded-lg text-sm">Print</button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Customer</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Orders Count</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Total Quantity</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Total Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            <tr v-for="item in reportData" :key="item.customer_id" class="hover:bg-slate-700/50">
                                <td class="px-4 py-3 text-white font-medium">{{ item.customer_name }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ item.count }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ formatNumber(item.total_quantity) }}</td>
                                <td class="px-4 py-3 text-emerald-400 font-medium">{{ formatCurrency(item.total_amount) }}</td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-slate-700">
                            <tr>
                                <td colspan="2" class="px-4 py-3 text-right text-white font-semibold">Total:</td>
                                <td class="px-4 py-3 text-emerald-400 font-bold">{{ formatNumber(totalQuantity) }}</td>
                                <td class="px-4 py-3 text-emerald-400 font-bold">{{ formatCurrency(totalAmount) }}</td>
                            </tr>
                        </tfoot>
                    </table>
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
    reportData: Array,
    filters: Object
});

const filters = ref(props.filters || {
    from_date: '',
    to_date: ''
});

const totalQuantity = computed(() => {
    return props.reportData?.reduce((sum, item) => sum + parseFloat(item.total_quantity || 0), 0) || 0;
});

const totalAmount = computed(() => {
    return props.reportData?.reduce((sum, item) => sum + parseFloat(item.total_amount || 0), 0) || 0;
});

const formatNumber = (num) => num ? parseFloat(num).toLocaleString('en-US') : '0';
const formatCurrency = (num) => num ? 'ETB ' + parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 2 }) : 'ETB 0.00';

const applyFilters = () => {
    router.get('/reports/sales-order-by-customer', filters.value, {
        preserveState: true,
        preserveScroll: true
    });
};

const exportCSV = () => {
    const headers = ['Customer', 'Orders Count', 'Total Quantity', 'Total Amount'];
    const rows = props.reportData.map(item => [
        item.customer_name,
        item.count,
        item.total_quantity,
        item.total_amount
    ]);
    const csv = [headers.join(','), ...rows.map(r => r.map(c => `"${c || ''}"`).join(','))].join('\n');
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `sales_order_by_customer_${new Date().toISOString().split('T')[0]}.csv`;
    a.click();
    URL.revokeObjectURL(url);
};

const printReport = () => {
    window.print();
};
</script>

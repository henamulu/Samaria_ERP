<template>
    <div class="min-h-screen bg-slate-900" lang="en">
        <Sidebar active="reports" />

        <div class="ml-64 p-8">
            <div class="mb-8">
                <a href="/reports" class="text-slate-400 hover:text-white mb-2 inline-block">&larr; Back to Reports</a>
                <h2 class="text-3xl font-bold text-white">Sales & Purchase Summary Report</h2>
                <p class="text-slate-400">Summary by date range and category</p>
            </div>

            <!-- Filter Form -->
            <div class="bg-slate-800 rounded-xl p-6 mb-6 max-w-3xl">
                <form @submit.prevent="generateReport" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-slate-300 mb-2">From Date *</label>
                        <input v-model="form.from_date" type="date" required lang="en"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">To Date *</label>
                        <input v-model="form.to_date" type="date" required lang="en"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Category</label>
                        <select v-model="form.category" multiple
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500 h-32">
                            <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                        </select>
                        <p class="text-slate-500 text-xs mt-1">Hold Ctrl to select multiple</p>
                    </div>

                    <div class="md:col-span-3">
                        <button type="submit" :disabled="loading"
                            class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-lg font-medium disabled:opacity-50">
                            {{ loading ? 'Generating...' : 'Generate Report' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Results -->
            <div v-if="reportData" class="space-y-6">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-slate-800 rounded-xl p-6">
                        <p class="text-slate-400 text-sm mb-1">Total Sales</p>
                        <p class="text-2xl font-bold text-emerald-400">{{ formatCurrency(reportData.total_sales) }}</p>
                    </div>
                    <div class="bg-slate-800 rounded-xl p-6">
                        <p class="text-slate-400 text-sm mb-1">Total Purchases</p>
                        <p class="text-2xl font-bold text-blue-400">{{ formatCurrency(reportData.total_purchases) }}</p>
                    </div>
                    <div class="bg-slate-800 rounded-xl p-6">
                        <p class="text-slate-400 text-sm mb-1">Total Deliveries</p>
                        <p class="text-2xl font-bold text-amber-400">{{ reportData.total_deliveries }}</p>
                    </div>
                    <div class="bg-slate-800 rounded-xl p-6">
                        <p class="text-slate-400 text-sm mb-1">Net Profit</p>
                        <p class="text-2xl font-bold" :class="reportData.net_profit >= 0 ? 'text-green-400' : 'text-red-400'">
                            {{ formatCurrency(reportData.net_profit) }}
                        </p>
                    </div>
                </div>

                <!-- Sales Details Table -->
                <div class="bg-slate-800 rounded-xl p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-bold text-white">Sales by Category</h3>
                        <button @click="exportCSV('sales')" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm">
                            Export CSV
                        </button>
                    </div>
                    <table class="w-full">
                        <thead class="bg-slate-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Category</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Quantity</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Unit</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Total Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            <tr v-for="item in reportData.sales_by_category" :key="item.category" class="hover:bg-slate-700/50">
                                <td class="px-4 py-3 text-white">{{ item.category || 'Uncategorized' }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ formatNumber(item.quantity) }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ item.unit }}</td>
                                <td class="px-4 py-3 text-emerald-400 font-medium">{{ formatCurrency(item.total) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Purchases Details Table -->
                <div class="bg-slate-800 rounded-xl p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-bold text-white">Purchases by Category</h3>
                        <button @click="exportCSV('purchases')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                            Export CSV
                        </button>
                    </div>
                    <table class="w-full">
                        <thead class="bg-slate-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Category</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Quantity</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Unit</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Total Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            <tr v-for="item in reportData.purchases_by_category" :key="item.category" class="hover:bg-slate-700/50">
                                <td class="px-4 py-3 text-white">{{ item.category || 'Uncategorized' }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ formatNumber(item.quantity) }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ item.unit }}</td>
                                <td class="px-4 py-3 text-blue-400 font-medium">{{ formatCurrency(item.total) }}</td>
                            </tr>
                        </tbody>
                    </table>
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
    categories: { type: Array, default: () => [] },
    reportData: { type: Object, default: null }
});

const form = ref({
    from_date: '',
    to_date: '',
    category: []
});

const loading = ref(false);

const generateReport = () => {
    loading.value = true;
    router.get('/reports/summary', form.value, {
        preserveState: true,
        onFinish: () => loading.value = false
    });
};

const formatCurrency = (val) => {
    return 'ETB ' + (parseFloat(val) || 0).toLocaleString('en-US', { minimumFractionDigits: 2 });
};

const formatNumber = (val) => {
    return (parseFloat(val) || 0).toLocaleString('en-US', { minimumFractionDigits: 2 });
};

const exportCSV = (type) => {
    const data = type === 'sales' ? props.reportData?.sales_by_category : props.reportData?.purchases_by_category;
    if (!data) return;
    
    const csv = [
        ['Category', 'Quantity', 'Unit', 'Total Amount'],
        ...data.map(item => [item.category || 'Uncategorized', item.quantity, item.unit, item.total])
    ].map(row => row.join(',')).join('\n');
    
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `${type}_summary_${form.value.from_date}_${form.value.to_date}.csv`;
    a.click();
};
</script>

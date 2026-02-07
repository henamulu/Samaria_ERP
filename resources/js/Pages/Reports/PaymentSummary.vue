<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="reports" />
        <div class="ml-64 p-8">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h2 class="text-3xl font-bold text-white mb-2">Payment Summary Report</h2>
                    <p class="text-slate-400">Payment transactions summary</p>
                </div>
                <a href="/reports" class="text-emerald-400 hover:text-emerald-300">← Back to Reports</a>
            </div>

            <!-- Filters -->
            <div class="bg-slate-800 rounded-xl p-6 mb-6">
                <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-slate-400 text-sm mb-2">From Date</label>
                        <input v-model="filters.from_date" type="date" lang="en" class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                    </div>
                    <div>
                        <label class="block text-slate-400 text-sm mb-2">To Date</label>
                        <input v-model="filters.to_date" type="date" lang="en" class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                    </div>
                    <div>
                        <label class="block text-slate-400 text-sm mb-2">Payment Type</label>
                        <select v-model="filters.p_type" class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white">
                            <option value="">All Types</option>
                            <option value="po">Purchase Order</option>
                            <option value="advance">Advance</option>
                            <option value="credit">Credit</option>
                            <option value="transporter">Transporter</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg">Apply Filters</button>
                    </div>
                </form>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-slate-800 rounded-xl p-6">
                    <p class="text-slate-400 text-sm mb-1">Total Payments</p>
                    <p class="text-3xl font-bold text-white">{{ payments.total }}</p>
                </div>
                <div class="bg-slate-800 rounded-xl p-6">
                    <p class="text-slate-400 text-sm mb-1">Total Amount</p>
                    <p class="text-3xl font-bold text-emerald-400">{{ formatCurrency(summary.total_amount) }}</p>
                </div>
            </div>

            <!-- Summary by Type -->
            <div class="bg-slate-800 rounded-xl p-6 mb-6">
                <h3 class="text-lg font-semibold text-white mb-4">Summary by Payment Type</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div v-for="item in summary.by_type" :key="item.p_type" class="bg-slate-700 rounded-lg p-4">
                        <p class="text-slate-400 text-sm mb-1">{{ item.p_type.toUpperCase() }}</p>
                        <p class="text-2xl font-bold text-emerald-400">{{ formatCurrency(item.total) }}</p>
                        <p class="text-xs text-slate-500 mt-1">{{ item.count }} payments</p>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-slate-800 rounded-xl overflow-hidden">
                <div class="p-4 border-b border-slate-700 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-white">Payment Transactions</h3>
                    <div class="flex gap-3">
                        <button @click="exportCSV" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm">Export CSV</button>
                        <button @click="printReport" class="bg-slate-600 hover:bg-slate-500 text-white px-4 py-2 rounded-lg text-sm">Print</button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">P.No</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Supplier</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Type</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Description</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Amount</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Bank</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            <tr v-for="payment in payments.data" :key="payment.id" class="hover:bg-slate-700/50">
                                <td class="px-4 py-3 text-slate-300">{{ payment.p_no }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ payment.payment_date }}</td>
                                <td class="px-4 py-3 text-white">{{ payment.supplier?.supplier_name || '-' }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ payment.p_type }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ payment.payment_description || '-' }}</td>
                                <td class="px-4 py-3 text-emerald-400 font-medium">{{ formatCurrency(payment.net_amount) }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ payment.bank || '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div v-if="payments.links && payments.links.length > 3" class="p-4 border-t border-slate-700 flex justify-center gap-2">
                    <a v-for="link in payments.links" :key="link.label" 
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
    payments: Object,
    summary: Object,
    filters: Object
});

const filters = ref(props.filters || {
    from_date: '',
    to_date: '',
    p_type: ''
});

const formatCurrency = (num) => num ? 'ETB ' + parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 2 }) : 'ETB 0.00';

const applyFilters = () => {
    router.get('/reports/payment-summary', filters.value, {
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
    const headers = ['P.No', 'Date', 'Supplier', 'Type', 'Description', 'Amount', 'Bank'];
    const rows = props.payments.data.map(p => [
        p.p_no,
        p.payment_date,
        p.supplier?.supplier_name || '-',
        p.p_type,
        p.payment_description || '-',
        p.net_amount,
        p.bank || '-'
    ]);
    const csv = [headers.join(','), ...rows.map(r => r.map(c => `"${c || ''}"`).join(','))].join('\n');
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `payment_summary_${new Date().toISOString().split('T')[0]}.csv`;
    a.click();
    URL.revokeObjectURL(url);
};

const printReport = () => {
    window.print();
};
</script>

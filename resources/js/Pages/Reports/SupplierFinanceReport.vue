<template>
    <div class="min-h-screen bg-slate-900" lang="en">
        <Sidebar active="reports" />

        <div class="ml-64 p-8">
            <div class="mb-8">
                <a href="/reports" class="text-slate-400 hover:text-white mb-2 inline-block">&larr; Back to Reports</a>
                <h2 class="text-3xl font-bold text-white">Supplier Financial Report</h2>
                <p class="text-slate-400">Cash purchases, advance payments, credit purchases analysis</p>
            </div>

            <!-- Filter Form -->
            <div class="bg-gradient-to-r from-cyan-600 to-cyan-700 rounded-xl p-6 mb-6">
                <form @submit.prevent="generateReport" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                    <div>
                        <label class="block text-white mb-2">Supplier *</label>
                        <select v-model="form.supplier" required
                            class="w-full bg-white/90 border-0 rounded-lg px-4 py-3 text-slate-900 focus:ring-2 focus:ring-white">
                            <option value="">Select Supplier</option>
                            <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.supplier_name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-white mb-2">From Date</label>
                        <input v-model="form.from_date" type="date" :disabled="form.all_dates" lang="en"
                            class="w-full bg-white/90 border-0 rounded-lg px-4 py-3 text-slate-900 focus:ring-2 focus:ring-white disabled:opacity-50" />
                    </div>

                    <div>
                        <label class="block text-white mb-2">To Date</label>
                        <input v-model="form.to_date" type="date" :disabled="form.all_dates" lang="en"
                            class="w-full bg-white/90 border-0 rounded-lg px-4 py-3 text-slate-900 focus:ring-2 focus:ring-white disabled:opacity-50" />
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" v-model="form.all_dates" id="all_dates" class="w-5 h-5 rounded">
                        <label for="all_dates" class="text-white">All Dates</label>
                    </div>

                    <div>
                        <button type="submit" :disabled="loading"
                            class="w-full bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-3 rounded-lg font-medium disabled:opacity-50">
                            {{ loading ? 'Loading...' : 'Generate Report' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Selected Supplier Info -->
            <div v-if="selectedSupplier" class="mb-6">
                <h3 class="text-2xl font-bold text-white">{{ selectedSupplier.supplier_name }}</h3>
                <p class="text-slate-400 text-sm">Note: For unpaid items, amounts are VAT-inclusive and do not account for withholding.</p>
            </div>

            <!-- Summary Cards -->
            <div v-if="reportData" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-slate-800 rounded-xl p-6 shadow-lg">
                    <h5 class="text-slate-400 mb-2">Total Cash Purchases</h5>
                    <p class="text-2xl font-bold text-emerald-400">{{ formatCurrency(reportData.total_cash_purchase) }}</p>
                </div>
                <div class="bg-slate-800 rounded-xl p-6 shadow-lg">
                    <h5 class="text-slate-400 mb-2">Total Advance Payments</h5>
                    <p class="text-2xl font-bold text-blue-400">{{ formatCurrency(reportData.total_advance) }}</p>
                </div>
                <div class="bg-slate-800 rounded-xl p-6 shadow-lg">
                    <h5 class="text-slate-400 mb-2">Total Credit Purchases</h5>
                    <p class="text-2xl font-bold text-amber-400">{{ formatCurrency(reportData.total_credit_purchase) }}</p>
                </div>
                <div class="bg-slate-800 rounded-xl p-6 shadow-lg">
                    <h5 class="text-slate-400 mb-2">Outstanding Credits</h5>
                    <p class="text-2xl font-bold text-red-400">{{ formatCurrency(reportData.total_outstanding_credit) }}</p>
                </div>
            </div>

            <!-- Detailed Tables -->
            <div v-if="reportData" class="space-y-6">
                <!-- Cash Purchases Table -->
                <div class="bg-slate-800 rounded-xl p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-bold text-white">Cash Purchases</h3>
                        <button @click="exportTable('cash')" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm">
                            Export Excel
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-blue-600">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">PO No</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Item</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Description</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Amount</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Quantity</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Balance</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700">
                                <tr v-for="item in reportData.cash_purchases" :key="item.id" class="hover:bg-slate-700/50">
                                    <td class="px-4 py-3 text-slate-300">{{ item.payment_date }}</td>
                                    <td class="px-4 py-3 text-white">{{ item.po_no }}</td>
                                    <td class="px-4 py-3 text-slate-300">{{ item.item }}</td>
                                    <td class="px-4 py-3 text-slate-300">{{ item.description }}</td>
                                    <td class="px-4 py-3 text-emerald-400">{{ formatCurrency(item.amount) }}</td>
                                    <td class="px-4 py-3 text-slate-300">{{ formatNumber(item.quantity) }}</td>
                                    <td class="px-4 py-3 text-slate-300">{{ formatNumber(item.balance) }}</td>
                                </tr>
                                <tr v-if="!reportData.cash_purchases?.length">
                                    <td colspan="7" class="px-4 py-8 text-center text-slate-500">No cash purchases found</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Advance Payments Table -->
                <div class="bg-slate-800 rounded-xl p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-bold text-white">Advance Payments</h3>
                        <button @click="exportTable('advance')" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm">
                            Export Excel
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-green-600">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Payment No</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Description</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Amount</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Remaining</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700">
                                <tr v-for="item in reportData.advance_payments" :key="item.id" class="hover:bg-slate-700/50">
                                    <td class="px-4 py-3 text-slate-300">{{ item.payment_date }}</td>
                                    <td class="px-4 py-3 text-white">{{ item.p_no }}</td>
                                    <td class="px-4 py-3 text-slate-300">{{ item.description }}</td>
                                    <td class="px-4 py-3 text-blue-400">{{ formatCurrency(item.amount) }}</td>
                                    <td class="px-4 py-3 text-amber-400">{{ formatCurrency(item.remaining) }}</td>
                                </tr>
                                <tr v-if="!reportData.advance_payments?.length">
                                    <td colspan="5" class="px-4 py-8 text-center text-slate-500">No advance payments found</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Credit Purchases Table -->
                <div class="bg-slate-800 rounded-xl p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-bold text-white">Credit Purchases</h3>
                        <button @click="exportTable('credit')" class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg text-sm">
                            Export Excel
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-amber-600">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Payment No</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Description</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700">
                                <tr v-for="item in reportData.credit_purchases" :key="item.id" class="hover:bg-slate-700/50">
                                    <td class="px-4 py-3 text-slate-300">{{ item.date }}</td>
                                    <td class="px-4 py-3 text-white">{{ item.payment_no }}</td>
                                    <td class="px-4 py-3 text-slate-300">{{ item.description }}</td>
                                    <td class="px-4 py-3 text-amber-400">{{ formatCurrency(item.amount) }}</td>
                                </tr>
                                <tr v-if="!reportData.credit_purchases?.length">
                                    <td colspan="4" class="px-4 py-8 text-center text-slate-500">No credit purchases found</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
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
    suppliers: { type: Array, default: () => [] },
    reportData: { type: Object, default: null },
    selectedSupplier: { type: Object, default: null }
});

const form = ref({
    supplier: '',
    from_date: '',
    to_date: '',
    all_dates: false
});

const loading = ref(false);

const generateReport = () => {
    loading.value = true;
    router.get('/reports/supplier-finance', form.value, {
        preserveState: true,
        onFinish: () => loading.value = false
    });
};

const formatCurrency = (val) => {
    return (parseFloat(val) || 0).toLocaleString('en-US', { minimumFractionDigits: 2 });
};

const formatNumber = (val) => {
    return (parseFloat(val) || 0).toLocaleString('en-US', { minimumFractionDigits: 2 });
};

const exportTable = (type) => {
    let data = [];
    let headers = [];
    let filename = '';
    
    switch(type) {
        case 'cash':
            headers = ['Date', 'PO No', 'Item', 'Description', 'Amount', 'Quantity', 'Balance'];
            data = props.reportData?.cash_purchases?.map(item => [
                item.payment_date, item.po_no, item.item, item.description, item.amount, item.quantity, item.balance
            ]) || [];
            filename = 'cash_purchases';
            break;
        case 'advance':
            headers = ['Date', 'Payment No', 'Description', 'Amount', 'Remaining'];
            data = props.reportData?.advance_payments?.map(item => [
                item.payment_date, item.p_no, item.description, item.amount, item.remaining
            ]) || [];
            filename = 'advance_payments';
            break;
        case 'credit':
            headers = ['Date', 'Payment No', 'Description', 'Amount'];
            data = props.reportData?.credit_purchases?.map(item => [
                item.date, item.payment_no, item.description, item.amount
            ]) || [];
            filename = 'credit_purchases';
            break;
    }
    
    const csv = [headers, ...data].map(row => row.join(',')).join('\n');
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `${filename}_${props.selectedSupplier?.supplier_name || 'report'}.csv`;
    a.click();
};
</script>

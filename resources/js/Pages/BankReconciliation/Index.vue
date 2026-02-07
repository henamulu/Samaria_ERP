<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="bank-reconciliation" />

        <div class="ml-64 p-8">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-white">Bank Reconciliation</h2>
                    <p class="text-slate-400">Reconcile bank statements with records</p>
                </div>
                <a href="/bank-reconciliation/create" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Reconciliation
                </a>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-4 gap-4 mb-6">
                <div class="bg-slate-800 rounded-xl p-4">
                    <p class="text-slate-400 text-sm">Total Records</p>
                    <p class="text-2xl font-bold text-white">{{ reconciliations.total }}</p>
                </div>
                <div class="bg-slate-800 rounded-xl p-4">
                    <p class="text-slate-400 text-sm">Pending</p>
                    <p class="text-2xl font-bold text-yellow-400">{{ stats.pending }}</p>
                </div>
                <div class="bg-slate-800 rounded-xl p-4">
                    <p class="text-slate-400 text-sm">Reconciled</p>
                    <p class="text-2xl font-bold text-green-400">{{ stats.approved }}</p>
                </div>
                <div class="bg-slate-800 rounded-xl p-4">
                    <p class="text-slate-400 text-sm">Difference</p>
                    <p class="text-2xl font-bold text-red-400">{{ formatCurrency(stats.difference) }}</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-slate-800 rounded-xl p-4 mb-6">
                <form @submit.prevent="applyFilters" class="flex gap-4">
                    <select v-model="filterForm.bank"
                        class="bg-slate-700 border-0 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-emerald-500">
                        <option value="">All Banks</option>
                        <option v-for="bank in banks" :key="bank.id" :value="bank.bank_name">{{ bank.bank_name }}</option>
                    </select>

                    <select v-model="filterForm.status"
                        class="bg-slate-700 border-0 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-emerald-500">
                        <option value="">All Status</option>
                        <option value="Pending">Pending</option>
                        <option value="Checked">Checked</option>
                        <option value="Approved">Approved</option>
                    </select>

                    <select v-model="filterForm.month"
                        class="bg-slate-700 border-0 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-emerald-500">
                        <option value="">All Months</option>
                        <option value="1">January</option>
                        <option value="2">February</option>
                        <option value="3">March</option>
                        <option value="4">April</option>
                        <option value="5">May</option>
                        <option value="6">June</option>
                        <option value="7">July</option>
                        <option value="8">August</option>
                        <option value="9">September</option>
                        <option value="10">October</option>
                        <option value="11">November</option>
                        <option value="12">December</option>
                    </select>

                    <select v-model="filterForm.year"
                        class="bg-slate-700 border-0 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-emerald-500">
                        <option value="">All Years</option>
                        <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
                    </select>

                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded-lg">Filter</button>
                </form>
            </div>

            <!-- Table -->
            <div class="bg-slate-800 rounded-xl overflow-hidden shadow-lg">
                <table class="w-full">
                    <thead class="bg-slate-700">
                        <tr>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">BR No</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Bank</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Period</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Bank Balance</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Book Balance</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Difference</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Status</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        <tr v-if="reconciliations.data.length === 0">
                            <td colspan="8" class="px-4 py-8 text-center text-slate-400">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-12 h-12 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="text-lg">No reconciliations found</p>
                                    <p class="text-sm">Create a new reconciliation to get started</p>
                                </div>
                            </td>
                        </tr>
                        <tr v-for="r in reconciliations.data" :key="r.id" class="hover:bg-slate-700/50">
                            <td class="px-4 py-4 text-white font-medium">{{ r.br_no }}</td>
                            <td class="px-4 py-4 text-slate-300">{{ r.bank_name }}</td>
                            <td class="px-4 py-4 text-slate-300">{{ r.month }}/{{ r.year }}</td>
                            <td class="px-4 py-4 text-blue-400">{{ formatCurrency(r.bank_balance) }}</td>
                            <td class="px-4 py-4 text-emerald-400">{{ formatCurrency(r.book_balance) }}</td>
                            <td class="px-4 py-4" :class="r.difference == 0 ? 'text-green-400' : 'text-red-400'">
                                {{ formatCurrency(r.difference) }}
                            </td>
                            <td class="px-4 py-4"><span :class="statusClass(r.status)" class="px-2 py-1 rounded text-xs">{{ r.status }}</span></td>
                            <td class="px-4 py-4">
                                <div class="flex gap-2">
                                    <a :href="'/bank-reconciliation/' + r.id" class="text-blue-400 hover:text-blue-300">View</a>
                                    <button v-if="r.status === 'Pending'" @click="checkRecon(r)" class="text-yellow-400 hover:text-yellow-300">Check</button>
                                    <button v-if="r.status === 'Checked'" @click="approveRecon(r)" class="text-green-400 hover:text-green-300">Approve</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex justify-between items-center mt-6 text-slate-400">
                <span v-if="reconciliations.total > 0">
                    Showing {{ reconciliations.from }}-{{ reconciliations.to }} of {{ reconciliations.total }}
                </span>
                <span v-else>
                    No records found
                </span>
                <div class="flex gap-2">
                    <a v-if="reconciliations.prev_page_url" :href="reconciliations.prev_page_url" class="px-4 py-2 bg-slate-700 rounded hover:bg-slate-600">Previous</a>
                    <a v-if="reconciliations.next_page_url" :href="reconciliations.next_page_url" class="px-4 py-2 bg-slate-700 rounded hover:bg-slate-600">Next</a>
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
    reconciliations: Object,
    banks: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
    stats: { type: Object, default: () => ({ pending: 0, approved: 0, difference: 0 }) }
});

const currentYear = new Date().getFullYear();
const startYear = 2000;
const endYear = currentYear + 10;
const years = Array.from({ length: endYear - startYear + 1 }, (_, i) => endYear - i);

const filterForm = ref({
    bank: props.filters.bank || '',
    status: props.filters.status || '',
    month: props.filters.month || '',
    year: props.filters.year || ''
});

const applyFilters = () => {
    router.get('/bank-reconciliation', filterForm.value, { preserveState: true });
};

const formatCurrency = (val) => (parseFloat(val) || 0).toLocaleString('en-US', { minimumFractionDigits: 2 });

const statusClass = (status) => ({
    'Approved': 'bg-green-500/20 text-green-400',
    'Checked': 'bg-blue-500/20 text-blue-400',
    'Pending': 'bg-yellow-500/20 text-yellow-400'
}[status] || 'bg-slate-500/20 text-slate-400');

const checkRecon = (item) => {
    router.put('/bank-reconciliation/' + item.id + '/check');
};

const approveRecon = (item) => {
    router.put('/bank-reconciliation/' + item.id + '/approve');
};
</script>

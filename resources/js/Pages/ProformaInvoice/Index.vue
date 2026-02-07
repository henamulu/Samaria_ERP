<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="proforma-invoices" />

        <div class="ml-64 p-8">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-white">Proforma Invoices</h2>
                    <p class="text-slate-400">Generate invoices before delivery</p>
                </div>
                <a href="/proforma-invoices/create" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Proforma
                </a>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-4 gap-4 mb-6">
                <div class="bg-slate-800 rounded-xl p-4">
                    <p class="text-slate-400 text-sm">Pending</p>
                    <p class="text-2xl font-bold text-yellow-400">{{ stats.pending }}</p>
                </div>
                <div class="bg-slate-800 rounded-xl p-4">
                    <p class="text-slate-400 text-sm">Checked</p>
                    <p class="text-2xl font-bold text-blue-400">{{ stats.checked }}</p>
                </div>
                <div class="bg-slate-800 rounded-xl p-4">
                    <p class="text-slate-400 text-sm">Approved</p>
                    <p class="text-2xl font-bold text-green-400">{{ stats.approved }}</p>
                </div>
                <div class="bg-slate-800 rounded-xl p-4">
                    <p class="text-slate-400 text-sm">Void</p>
                    <p class="text-2xl font-bold text-red-400">{{ stats.void }}</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-slate-800 rounded-xl p-4 mb-6">
                <form @submit.prevent="applyFilters" class="flex gap-4">
                    <input v-model="filterForm.search" type="text" placeholder="Search PI No, Item, Customer..."
                        class="flex-1 bg-slate-700 border-0 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-emerald-500" />
                    
                    <select v-model="filterForm.status"
                        class="bg-slate-700 border-0 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-emerald-500">
                        <option value="">All Status</option>
                        <option value="Pending">Pending</option>
                        <option value="Checked">Checked</option>
                        <option value="Approved">Approved</option>
                        <option value="Void">Void</option>
                    </select>

                    <select v-model="filterForm.customer"
                        class="bg-slate-700 border-0 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-emerald-500">
                        <option value="">All Customers</option>
                        <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.company_name || c.firstname + ' ' + c.lastname }}</option>
                    </select>

                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded-lg">Filter</button>
                </form>
            </div>

            <!-- Table -->
            <div class="bg-slate-800 rounded-xl overflow-hidden shadow-lg">
                <table class="w-full">
                    <thead class="bg-slate-700">
                        <tr>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">PI No</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Customer</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Item</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Quantity</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Unit Price</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Line Total</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Status</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        <tr v-if="proformas.data.length === 0">
                            <td colspan="8" class="px-4 py-8 text-center text-slate-400">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-12 h-12 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="text-lg">No proforma invoices found</p>
                                </div>
                            </td>
                        </tr>
                        <tr v-for="pi in proformas.data" :key="pi.id" class="hover:bg-slate-700/50">
                            <td class="px-4 py-4 text-white font-medium">{{ pi.pi_no }}</td>
                            <td class="px-4 py-4 text-slate-300">{{ pi.customer_name || 'N/A' }}</td>
                            <td class="px-4 py-4 text-slate-300">{{ pi.item || pi.item_name }}</td>
                            <td class="px-4 py-4 text-slate-300">{{ formatNumber(pi.quantity) }} {{ pi.unit }}</td>
                            <td class="px-4 py-4 text-slate-300">{{ formatCurrency(pi.unit_price) }}</td>
                            <td class="px-4 py-4 text-slate-300">{{ formatCurrency(pi.line_total) }}</td>
                            <td class="px-4 py-4">
                                <span :class="statusClass(pi.status)" class="px-2 py-1 rounded text-xs">
                                    {{ pi.status }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex gap-2">
                                    <a :href="'/proforma-invoices/' + pi.id + '/edit'" class="text-blue-400 hover:text-blue-300">Edit</a>
                                    <button v-if="pi.status === 'Pending'" @click="checkPI(pi)" class="text-yellow-400 hover:text-yellow-300">Check</button>
                                    <button v-if="pi.status === 'Checked'" @click="approvePI(pi)" class="text-green-400 hover:text-green-300">Approve</button>
                                    <button v-if="pi.status === 'Approved'" @click="convertToSO(pi)" class="text-emerald-400 hover:text-emerald-300">Convert to SO</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex justify-between items-center mt-6 text-slate-400">
                <span v-if="proformas.total > 0">
                    Showing {{ proformas.from }}-{{ proformas.to }} of {{ proformas.total }}
                </span>
                <span v-else>No records found</span>
                <div class="flex gap-2">
                    <a v-if="proformas.prev_page_url" :href="proformas.prev_page_url" class="px-4 py-2 bg-slate-700 rounded hover:bg-slate-600">Previous</a>
                    <a v-if="proformas.next_page_url" :href="proformas.next_page_url" class="px-4 py-2 bg-slate-700 rounded hover:bg-slate-600">Next</a>
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
    proformas: Object,
    customers: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
    stats: { type: Object, default: () => ({ pending: 0, checked: 0, approved: 0, void: 0 }) }
});

const filterForm = ref({
    search: props.filters.search || '',
    status: props.filters.status || '',
    customer: props.filters.customer || ''
});

const applyFilters = () => {
    router.get('/proforma-invoices', filterForm.value, { preserveState: true });
};

const formatNumber = (num) => (parseFloat(num) || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const formatCurrency = (num) => 'ETB ' + formatNumber(num);

const statusClass = (status) => ({
    'Approved': 'bg-green-500/20 text-green-400',
    'Checked': 'bg-blue-500/20 text-blue-400',
    'Pending': 'bg-yellow-500/20 text-yellow-400',
    'Void': 'bg-red-500/20 text-red-400'
}[status] || 'bg-slate-500/20 text-slate-400');

const checkPI = (item) => {
    router.put('/proforma-invoices/' + item.id + '/check');
};

const approvePI = (item) => {
    router.put('/proforma-invoices/' + item.id + '/approve');
};

const convertToSO = (item) => {
    router.put('/proforma-invoices/' + item.id + '/convert-to-so');
};
</script>

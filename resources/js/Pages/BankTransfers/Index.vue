<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="bank-transfers" />

        <div class="ml-64 p-8">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-white">Bank Transfers</h2>
                    <p class="text-slate-400">Manage inter-bank transfers</p>
                </div>
                <a href="/bank-transfers/create" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Transfer
                </a>
            </div>

            <!-- Filters -->
            <div class="bg-slate-800 rounded-xl p-4 mb-6">
                <form @submit.prevent="applyFilters" class="flex gap-4">
                    <input v-model="filterForm.search" type="text" placeholder="Search by BTR No., Bank..." 
                        class="flex-1 bg-slate-700 border-0 rounded-lg px-4 py-2 text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500" />
                    
                    <select v-model="filterForm.status"
                        class="bg-slate-700 border-0 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-emerald-500">
                        <option value="">All Status</option>
                        <option value="Pending">Pending</option>
                        <option value="Checked">Checked</option>
                        <option value="Approved">Approved</option>
                    </select>

                    <input v-model="filterForm.date_from" type="date"
                        class="bg-slate-700 border-0 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-emerald-500" />
                    
                    <input v-model="filterForm.date_to" type="date"
                        class="bg-slate-700 border-0 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-emerald-500" />

                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded-lg">Filter</button>
                </form>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-4 gap-4 mb-6">
                <div class="bg-slate-800 rounded-xl p-4">
                    <p class="text-slate-400 text-sm">Total Transfers</p>
                    <p class="text-2xl font-bold text-white">{{ transfers.total }}</p>
                </div>
                <div class="bg-slate-800 rounded-xl p-4">
                    <p class="text-slate-400 text-sm">Pending</p>
                    <p class="text-2xl font-bold text-yellow-400">{{ stats.pending }}</p>
                </div>
                <div class="bg-slate-800 rounded-xl p-4">
                    <p class="text-slate-400 text-sm">Approved</p>
                    <p class="text-2xl font-bold text-green-400">{{ stats.approved }}</p>
                </div>
                <div class="bg-slate-800 rounded-xl p-4">
                    <p class="text-slate-400 text-sm">Total Amount</p>
                    <p class="text-2xl font-bold text-emerald-400">{{ formatCurrency(stats.totalAmount) }}</p>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-slate-800 rounded-xl overflow-hidden shadow-lg">
                <table class="w-full">
                    <thead class="bg-slate-700">
                        <tr>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">BTR No</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Date</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">From Bank</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">To Bank</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Amount</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Status</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Registered By</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        <tr v-for="t in transfers.data" :key="t.id" class="hover:bg-slate-700/50">
                            <td class="px-4 py-4 text-white font-medium">{{ t.btr_no }}</td>
                            <td class="px-4 py-4 text-slate-300">{{ t.transfer_date }}</td>
                            <td class="px-4 py-4 text-slate-300">{{ t.from_bank }}<br/><span class="text-xs text-slate-500">{{ t.from_account }}</span></td>
                            <td class="px-4 py-4 text-slate-300">{{ t.to_bank }}<br/><span class="text-xs text-slate-500">{{ t.to_account }}</span></td>
                            <td class="px-4 py-4 text-emerald-400 font-bold">{{ formatCurrency(t.amount) }}</td>
                            <td class="px-4 py-4"><span :class="statusClass(t.status)" class="px-2 py-1 rounded text-xs">{{ t.status }}</span></td>
                            <td class="px-4 py-4 text-slate-400 text-sm">{{ t.registered_by }}</td>
                            <td class="px-4 py-4">
                                <div class="flex gap-2">
                                    <a :href="'/bank-transfers/' + t.id + '/edit'" class="text-blue-400 hover:text-blue-300">Edit</a>
                                    <button v-if="t.status === 'Pending'" @click="checkTransfer(t)" class="text-yellow-400 hover:text-yellow-300">Check</button>
                                    <button v-if="t.status === 'Checked'" @click="approveTransfer(t)" class="text-green-400 hover:text-green-300">Approve</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex justify-between items-center mt-6 text-slate-400">
                <span>Showing {{ transfers.from }}-{{ transfers.to }} of {{ transfers.total }}</span>
                <div class="flex gap-2">
                    <a v-if="transfers.prev_page_url" :href="transfers.prev_page_url" class="px-4 py-2 bg-slate-700 rounded hover:bg-slate-600">Previous</a>
                    <a v-if="transfers.next_page_url" :href="transfers.next_page_url" class="px-4 py-2 bg-slate-700 rounded hover:bg-slate-600">Next</a>
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
    transfers: Object,
    filters: { type: Object, default: () => ({}) },
    stats: { type: Object, default: () => ({ pending: 0, approved: 0, totalAmount: 0 }) }
});

const filterForm = ref({
    search: props.filters.search || '',
    status: props.filters.status || '',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || ''
});

const applyFilters = () => {
    router.get('/bank-transfers', filterForm.value, { preserveState: true });
};

const formatCurrency = (val) => (parseFloat(val) || 0).toLocaleString('en-US', { minimumFractionDigits: 2 });

const statusClass = (status) => ({
    'Approved': 'bg-green-500/20 text-green-400',
    'Checked': 'bg-blue-500/20 text-blue-400',
    'Pending': 'bg-yellow-500/20 text-yellow-400',
    'Void': 'bg-red-500/20 text-red-400'
}[status] || 'bg-slate-500/20 text-slate-400');

const checkTransfer = (item) => {
    router.put('/bank-transfers/' + item.id + '/check');
};

const approveTransfer = (item) => {
    router.put('/bank-transfers/' + item.id + '/approve');
};
</script>

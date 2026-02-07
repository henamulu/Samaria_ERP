<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="settlements" />

        <div class="ml-64 p-8">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-white">Settlements</h2>
                    <p class="text-slate-400">Manage PO, Petty Cash, CR and Advance settlements</p>
                </div>
            </div>

            <!-- Settlement Type Tabs -->
            <div class="flex gap-2 mb-6 flex-wrap">
                <button @click="activeTab = 'pos'" :class="activeTab === 'pos' ? 'bg-emerald-600 text-white' : 'bg-slate-700 text-slate-300'" class="px-6 py-2 rounded-lg">
                    PO Settlement
                </button>
                <button @click="activeTab = 'pcs'" :class="activeTab === 'pcs' ? 'bg-blue-600 text-white' : 'bg-slate-700 text-slate-300'" class="px-6 py-2 rounded-lg">
                    Petty Cash
                </button>
                <button @click="activeTab = 'crs'" :class="activeTab === 'crs' ? 'bg-amber-600 text-white' : 'bg-slate-700 text-slate-300'" class="px-6 py-2 rounded-lg">
                    CR Settlement
                </button>
                <button @click="activeTab = 'advance'" :class="activeTab === 'advance' ? 'bg-purple-600 text-white' : 'bg-slate-700 text-slate-300'" class="px-6 py-2 rounded-lg">
                    Advance Settlement
                </button>
                <button @click="activeTab = 'transporter'" :class="activeTab === 'transporter' ? 'bg-teal-600 text-white' : 'bg-slate-700 text-slate-300'" class="px-6 py-2 rounded-lg">
                    Transporter Settlement
                </button>
            </div>

            <!-- Action Button -->
            <div class="mb-6">
                <a :href="'/settlements/create?type=' + activeTab" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg inline-flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Create {{ tabLabels[activeTab] }}
                </a>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-4 gap-4 mb-6">
                <div class="bg-slate-800 rounded-xl p-4">
                    <p class="text-slate-400 text-sm">Total Settlements</p>
                    <p class="text-2xl font-bold text-white">{{ settlements.total }}</p>
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
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Settlement No</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Type</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Date</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Reference</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Amount</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Settled Amount</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Balance</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Status</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        <tr v-for="s in settlements.data" :key="s.id" class="hover:bg-slate-700/50">
                            <td class="px-4 py-4 text-white font-medium">{{ s.settlement_no }}</td>
                            <td class="px-4 py-4"><span :class="typeClass(s.type)" class="px-2 py-1 rounded text-xs">{{ s.type }}</span></td>
                            <td class="px-4 py-4 text-slate-300">{{ s.settlement_date }}</td>
                            <td class="px-4 py-4 text-slate-300">{{ s.reference_no }}</td>
                            <td class="px-4 py-4 text-blue-400">{{ formatCurrency(s.original_amount) }}</td>
                            <td class="px-4 py-4 text-emerald-400">{{ formatCurrency(s.settled_amount) }}</td>
                            <td class="px-4 py-4" :class="s.balance > 0 ? 'text-red-400' : 'text-green-400'">{{ formatCurrency(s.balance) }}</td>
                            <td class="px-4 py-4"><span :class="statusClass(s.status)" class="px-2 py-1 rounded text-xs">{{ s.status }}</span></td>
                            <td class="px-4 py-4">
                                <div class="flex gap-2">
                                    <a :href="'/settlements/' + s.id" class="text-blue-400 hover:text-blue-300">View</a>
                                    <button v-if="s.status === 'Pending'" @click="approveSettlement(s)" class="text-green-400 hover:text-green-300">Approve</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex justify-between items-center mt-6 text-slate-400">
                <span>Showing {{ settlements.from }}-{{ settlements.to }} of {{ settlements.total }}</span>
                <div class="flex gap-2">
                    <a v-if="settlements.prev_page_url" :href="settlements.prev_page_url" class="px-4 py-2 bg-slate-700 rounded hover:bg-slate-600">Previous</a>
                    <a v-if="settlements.next_page_url" :href="settlements.next_page_url" class="px-4 py-2 bg-slate-700 rounded hover:bg-slate-600">Next</a>
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
    settlements: Object,
    filters: { type: Object, default: () => ({}) },
    stats: { type: Object, default: () => ({ pending: 0, approved: 0, totalAmount: 0 }) }
});

const activeTab = ref(props.filters.type || 'pos');

const tabLabels = {
    pos: 'PO Settlement',
    pcs: 'Petty Cash Settlement',
    crs: 'CR Settlement',
    advance: 'Advance Settlement',
    transporter: 'Transporter Settlement'
};

const formatCurrency = (val) => (parseFloat(val) || 0).toLocaleString('en-US', { minimumFractionDigits: 2 });

const typeClass = (type) => ({
    'POS': 'bg-emerald-500/20 text-emerald-400',
    'PCS': 'bg-blue-500/20 text-blue-400',
    'CRS': 'bg-amber-500/20 text-amber-400',
    'Advance': 'bg-purple-500/20 text-purple-400',
    'Transporter': 'bg-teal-500/20 text-teal-400'
}[type] || 'bg-slate-500/20 text-slate-400');

const statusClass = (status) => ({
    'Approved': 'bg-green-500/20 text-green-400',
    'Partial': 'bg-blue-500/20 text-blue-400',
    'Pending': 'bg-yellow-500/20 text-yellow-400'
}[status] || 'bg-slate-500/20 text-slate-400');

const approveSettlement = (item) => {
    router.put('/settlements/' + item.id + '/approve');
};
</script>

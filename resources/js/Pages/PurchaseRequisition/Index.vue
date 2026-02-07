<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="purchase-requisitions" />

        <div class="ml-64 p-8">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-white">Purchase Requisitions</h2>
                    <p class="text-slate-400">Request materials before creating Purchase Orders</p>
                </div>
                <a href="/purchase-requisitions/create" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New PR
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
                    <p class="text-slate-400 text-sm">Converted to PO</p>
                    <p class="text-2xl font-bold text-emerald-400">{{ stats.po_done }}</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-slate-800 rounded-xl p-4 mb-6">
                <form @submit.prevent="applyFilters" class="flex gap-4">
                    <input v-model="filterForm.search" type="text" placeholder="Search PR No, Item, Request From..."
                        class="flex-1 bg-slate-700 border-0 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-emerald-500" />
                    
                    <select v-model="filterForm.status"
                        class="bg-slate-700 border-0 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-emerald-500">
                        <option value="">All Status</option>
                        <option value="Pending">Pending</option>
                        <option value="Checked">Checked</option>
                        <option value="Approved">Approved</option>
                    </select>

                    <select v-model="filterForm.pr_status"
                        class="bg-slate-700 border-0 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-emerald-500">
                        <option value="">All PR Status</option>
                        <option value="pr_pending">Pending</option>
                        <option value="po_done">Converted to PO</option>
                    </select>

                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded-lg">Filter</button>
                </form>
            </div>

            <!-- Table -->
            <div class="bg-slate-800 rounded-xl overflow-hidden shadow-lg">
                <table class="w-full">
                    <thead class="bg-slate-700">
                        <tr>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">PR No</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Item</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Quantity</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Unit</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Request From</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Used For</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Status</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">PR Status</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        <tr v-if="requisitions.data.length === 0">
                            <td colspan="9" class="px-4 py-8 text-center text-slate-400">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-12 h-12 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="text-lg">No purchase requisitions found</p>
                                </div>
                            </td>
                        </tr>
                        <tr v-for="pr in requisitions.data" :key="pr.id" class="hover:bg-slate-700/50">
                            <td class="px-4 py-4 text-white font-medium">{{ pr.pr_no }}</td>
                            <td class="px-4 py-4 text-slate-300">{{ pr.item_desc || pr.item_name }}</td>
                            <td class="px-4 py-4 text-slate-300">{{ formatNumber(pr.pr_quantity) }}</td>
                            <td class="px-4 py-4 text-slate-300">{{ pr.unit }}</td>
                            <td class="px-4 py-4 text-slate-300">{{ pr.request_from || 'N/A' }}</td>
                            <td class="px-4 py-4 text-slate-300">{{ pr.used_for || 'N/A' }}</td>
                            <td class="px-4 py-4">
                                <span :class="statusClass(pr.status)" class="px-2 py-1 rounded text-xs">
                                    {{ pr.status }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <span :class="prStatusClass(pr.pr_status)" class="px-2 py-1 rounded text-xs">
                                    {{ prStatusText(pr.pr_status) }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex gap-2">
                                    <a :href="'/purchase-requisitions/' + pr.id + '/edit'" class="text-blue-400 hover:text-blue-300">Edit</a>
                                    <button v-if="pr.status === 'Pending'" @click="checkPR(pr)" class="text-yellow-400 hover:text-yellow-300">Check</button>
                                    <button v-if="pr.status === 'Checked'" @click="approvePR(pr)" class="text-green-400 hover:text-green-300">Approve</button>
                                    <button v-if="pr.status === 'Approved' && pr.pr_status !== 'po_done'" @click="convertToPO(pr)" class="text-emerald-400 hover:text-emerald-300">Convert to PO</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex justify-between items-center mt-6 text-slate-400">
                <span v-if="requisitions.total > 0">
                    Showing {{ requisitions.from }}-{{ requisitions.to }} of {{ requisitions.total }}
                </span>
                <span v-else>No records found</span>
                <div class="flex gap-2">
                    <a v-if="requisitions.prev_page_url" :href="requisitions.prev_page_url" class="px-4 py-2 bg-slate-700 rounded hover:bg-slate-600">Previous</a>
                    <a v-if="requisitions.next_page_url" :href="requisitions.next_page_url" class="px-4 py-2 bg-slate-700 rounded hover:bg-slate-600">Next</a>
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
    requisitions: Object,
    filters: { type: Object, default: () => ({}) },
    stats: { type: Object, default: () => ({ pending: 0, checked: 0, approved: 0, po_done: 0 }) }
});

const filterForm = ref({
    search: props.filters.search || '',
    status: props.filters.status || '',
    pr_status: props.filters.pr_status || ''
});

const applyFilters = () => {
    router.get('/purchase-requisitions', filterForm.value, { preserveState: true });
};

const formatNumber = (num) => (parseFloat(num) || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const statusClass = (status) => ({
    'Approved': 'bg-green-500/20 text-green-400',
    'Checked': 'bg-blue-500/20 text-blue-400',
    'Pending': 'bg-yellow-500/20 text-yellow-400'
}[status] || 'bg-slate-500/20 text-slate-400');

const prStatusClass = (status) => ({
    'po_done': 'bg-emerald-500/20 text-emerald-400',
    'pr_pending': 'bg-yellow-500/20 text-yellow-400'
}[status] || 'bg-slate-500/20 text-slate-400');

const prStatusText = (status) => {
    if (status === 'po_done') return 'PO Created';
    if (status === 'pr_pending') return 'Pending';
    return 'Pending';
};

const checkPR = (item) => {
    router.put('/purchase-requisitions/' + item.id + '/check');
};

const approvePR = (item) => {
    router.put('/purchase-requisitions/' + item.id + '/approve');
};

const convertToPO = (item) => {
    router.put('/purchase-requisitions/' + item.id + '/convert-to-po');
};
</script>

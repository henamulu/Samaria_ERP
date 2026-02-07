<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="store-requisitions" />

        <div class="ml-64 p-8">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-white">Store Requisitions</h2>
                    <p class="text-slate-400">Internal material requests</p>
                </div>
                <a href="/store-requisitions/create" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New SR
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
                    <p class="text-slate-400 text-sm">PR Created</p>
                    <p class="text-2xl font-bold text-emerald-400">{{ stats.pr_created }}</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-slate-800 rounded-xl p-4 mb-6">
                <form @submit.prevent="applyFilters" class="flex gap-4">
                    <input v-model="filterForm.search" type="text" placeholder="Search SR No, Item, From..."
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
                        <option value="">No PR</option>
                        <option value="pr_created">PR Created</option>
                    </select>

                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded-lg">Filter</button>
                </form>
            </div>

            <!-- Table -->
            <div class="bg-slate-800 rounded-xl overflow-hidden shadow-lg">
                <table class="w-full">
                    <thead class="bg-slate-700">
                        <tr>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">SR No</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Item</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Quantity</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Unit</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">From</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Priority</th>
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
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                    <p class="text-lg">No store requisitions found</p>
                                </div>
                            </td>
                        </tr>
                        <tr v-for="sr in requisitions.data" :key="sr.id" class="hover:bg-slate-700/50">
                            <td class="px-4 py-4 text-white font-medium">{{ sr.sr_no }}</td>
                            <td class="px-4 py-4 text-slate-300">{{ sr.item_desc || sr.item_name }}</td>
                            <td class="px-4 py-4 text-slate-300">{{ formatNumber(sr.sr_quantity) }}</td>
                            <td class="px-4 py-4 text-slate-300">{{ sr.unit }}</td>
                            <td class="px-4 py-4 text-slate-300">{{ sr.sr_from || 'N/A' }}</td>
                            <td class="px-4 py-4">
                                <span :class="priorityClass(sr.priority)" class="px-2 py-1 rounded text-xs">
                                    {{ sr.priority }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <span :class="statusClass(sr.status)" class="px-2 py-1 rounded text-xs">
                                    {{ sr.status }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <span :class="prStatusClass(sr.pr_status)" class="px-2 py-1 rounded text-xs">
                                    {{ sr.pr_status ? 'PR Created' : 'No PR' }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex gap-2">
                                    <a :href="'/store-requisitions/' + sr.id + '/edit'" class="text-blue-400 hover:text-blue-300">Edit</a>
                                    <button v-if="sr.status === 'Pending'" @click="checkSR(sr)" class="text-yellow-400 hover:text-yellow-300">Check</button>
                                    <button v-if="sr.status === 'Checked'" @click="approveSR(sr)" class="text-green-400 hover:text-green-300">Approve</button>
                                    <button v-if="sr.status === 'Approved' && !sr.pr_status" @click="convertToPR(sr)" class="text-emerald-400 hover:text-emerald-300">Convert to PR</button>
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
    stats: { type: Object, default: () => ({ pending: 0, checked: 0, approved: 0, pr_created: 0 }) }
});

const filterForm = ref({
    search: props.filters.search || '',
    status: props.filters.status || '',
    pr_status: props.filters.pr_status || ''
});

const applyFilters = () => {
    router.get('/store-requisitions', filterForm.value, { preserveState: true });
};

const formatNumber = (num) => (parseFloat(num) || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const statusClass = (status) => ({
    'Approved': 'bg-green-500/20 text-green-400',
    'Checked': 'bg-blue-500/20 text-blue-400',
    'Pending': 'bg-yellow-500/20 text-yellow-400'
}[status] || 'bg-slate-500/20 text-slate-400');

const priorityClass = (priority) => ({
    'High': 'bg-red-500/20 text-red-400',
    'Medium': 'bg-yellow-500/20 text-yellow-400',
    'Normal': 'bg-blue-500/20 text-blue-400',
    'Low': 'bg-slate-500/20 text-slate-400'
}[priority] || 'bg-slate-500/20 text-slate-400');

const prStatusClass = (status) => status ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-500/20 text-slate-400';

const checkSR = (item) => {
    router.put('/store-requisitions/' + item.id + '/check');
};

const approveSR = (item) => {
    router.put('/store-requisitions/' + item.id + '/approve');
};

const convertToPR = (item) => {
    router.put('/store-requisitions/' + item.id + '/convert-to-pr');
};
</script>

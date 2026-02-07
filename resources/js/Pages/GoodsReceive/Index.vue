<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="goods-receive" />

        <div class="ml-64 p-8">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-white">Goods Receive (GRN)</h2>
                    <p class="text-slate-400">Manage goods received notes</p>
                </div>
                <a href="/goods-receive/create" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Create GRN
                </a>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-4 gap-4 mb-6">
                <div class="bg-slate-800 rounded-xl p-4">
                    <p class="text-slate-400 text-sm">Total GRN</p>
                    <p class="text-2xl font-bold text-white">{{ goodsReceives.total }}</p>
                </div>
                <div class="bg-slate-800 rounded-xl p-4">
                    <p class="text-slate-400 text-sm">Pending Inspection</p>
                    <p class="text-2xl font-bold text-yellow-400">{{ stats.pending }}</p>
                </div>
                <div class="bg-slate-800 rounded-xl p-4">
                    <p class="text-slate-400 text-sm">Accepted</p>
                    <p class="text-2xl font-bold text-green-400">{{ stats.accepted }}</p>
                </div>
                <div class="bg-slate-800 rounded-xl p-4">
                    <p class="text-slate-400 text-sm">Total Items</p>
                    <p class="text-2xl font-bold text-emerald-400">{{ stats.totalItems }}</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-slate-800 rounded-xl p-4 mb-6">
                <form @submit.prevent="applyFilters" class="flex gap-4">
                    <input v-model="filterForm.search" type="text" placeholder="Search by GRN No., Supplier..." 
                        class="flex-1 bg-slate-700 border-0 rounded-lg px-4 py-2 text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500" />
                    
                    <select v-model="filterForm.status"
                        class="bg-slate-700 border-0 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-emerald-500">
                        <option value="">All Status</option>
                        <option value="Pending">Pending</option>
                        <option value="Inspected">Inspected</option>
                        <option value="Accepted">Accepted</option>
                        <option value="Rejected">Rejected</option>
                    </select>

                    <input v-model="filterForm.date_from" type="date"
                        class="bg-slate-700 border-0 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-emerald-500" />
                    
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded-lg">Filter</button>
                </form>
            </div>

            <!-- Table -->
            <div class="bg-slate-800 rounded-xl overflow-hidden shadow-lg">
                <table class="w-full">
                    <thead class="bg-slate-700">
                        <tr>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">GRN No</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Date</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Supplier</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">PO No</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Item</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Qty Received</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Qty Accepted</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Status</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        <tr v-for="gr in goodsReceives.data" :key="gr.id" class="hover:bg-slate-700/50">
                            <td class="px-4 py-4 text-white font-medium">{{ gr.grn_no }}</td>
                            <td class="px-4 py-4 text-slate-300">{{ gr.receive_date }}</td>
                            <td class="px-4 py-4 text-slate-300">{{ gr.supplier_name }}</td>
                            <td class="px-4 py-4 text-slate-400">{{ gr.po_no }}</td>
                            <td class="px-4 py-4 text-slate-300">{{ gr.item_name }}</td>
                            <td class="px-4 py-4 text-blue-400">{{ gr.qty_received }} {{ gr.unit }}</td>
                            <td class="px-4 py-4 text-emerald-400">{{ gr.qty_accepted }} {{ gr.unit }}</td>
                            <td class="px-4 py-4"><span :class="statusClass(gr.status)" class="px-2 py-1 rounded text-xs">{{ gr.status }}</span></td>
                            <td class="px-4 py-4">
                                <div class="flex gap-2">
                                    <a :href="'/goods-receive/' + gr.id + '/edit'" class="text-blue-400 hover:text-blue-300">Edit</a>
                                    <button v-if="gr.status === 'Pending'" @click="inspectGRN(gr)" class="text-yellow-400 hover:text-yellow-300">Inspect</button>
                                    <button v-if="gr.status === 'Inspected'" @click="acceptGRN(gr)" class="text-green-400 hover:text-green-300">Accept</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex justify-between items-center mt-6 text-slate-400">
                <span>Showing {{ goodsReceives.from }}-{{ goodsReceives.to }} of {{ goodsReceives.total }}</span>
                <div class="flex gap-2">
                    <a v-if="goodsReceives.prev_page_url" :href="goodsReceives.prev_page_url" class="px-4 py-2 bg-slate-700 rounded hover:bg-slate-600">Previous</a>
                    <a v-if="goodsReceives.next_page_url" :href="goodsReceives.next_page_url" class="px-4 py-2 bg-slate-700 rounded hover:bg-slate-600">Next</a>
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
    goodsReceives: Object,
    filters: { type: Object, default: () => ({}) },
    stats: { type: Object, default: () => ({ pending: 0, accepted: 0, totalItems: 0 }) }
});

const filterForm = ref({
    search: props.filters.search || '',
    status: props.filters.status || '',
    date_from: props.filters.date_from || ''
});

const applyFilters = () => {
    router.get('/goods-receive', filterForm.value, { preserveState: true });
};

const statusClass = (status) => ({
    'Accepted': 'bg-green-500/20 text-green-400',
    'Inspected': 'bg-blue-500/20 text-blue-400',
    'Pending': 'bg-yellow-500/20 text-yellow-400',
    'Rejected': 'bg-red-500/20 text-red-400'
}[status] || 'bg-slate-500/20 text-slate-400');

const inspectGRN = (item) => {
    router.put('/goods-receive/' + item.id + '/inspect');
};

const acceptGRN = (item) => {
    router.put('/goods-receive/' + item.id + '/accept');
};
</script>

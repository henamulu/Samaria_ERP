<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="goods-in-transit" />

        <div class="ml-64 p-8">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-white">Goods In Transit (GIT)</h2>
                    <p class="text-slate-400">Track goods being transported</p>
                </div>
                <a href="/goods-in-transit/create" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Create GIT
                </a>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-5 gap-4 mb-6">
                <div class="bg-slate-800 rounded-xl p-4">
                    <p class="text-slate-400 text-sm">Total GIT</p>
                    <p class="text-2xl font-bold text-white">{{ gits.total }}</p>
                </div>
                <div class="bg-slate-800 rounded-xl p-4">
                    <p class="text-slate-400 text-sm">In Transit</p>
                    <p class="text-2xl font-bold text-blue-400">{{ stats.inTransit }}</p>
                </div>
                <div class="bg-slate-800 rounded-xl p-4">
                    <p class="text-slate-400 text-sm">Delivered</p>
                    <p class="text-2xl font-bold text-green-400">{{ stats.delivered }}</p>
                </div>
                <div class="bg-slate-800 rounded-xl p-4">
                    <p class="text-slate-400 text-sm">On Hold</p>
                    <p class="text-2xl font-bold text-yellow-400">{{ stats.onHold }}</p>
                </div>
                <div class="bg-slate-800 rounded-xl p-4">
                    <p class="text-slate-400 text-sm">Total Qty</p>
                    <p class="text-2xl font-bold text-emerald-400">{{ stats.totalQty }}</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-slate-800 rounded-xl p-4 mb-6">
                <form @submit.prevent="applyFilters" class="flex gap-4">
                    <input v-model="filterForm.search" type="text" placeholder="Search by GIT No., Customer, Supplier..." 
                        class="flex-1 bg-slate-700 border-0 rounded-lg px-4 py-2 text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500" />
                    
                    <select v-model="filterForm.status"
                        class="bg-slate-700 border-0 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-emerald-500">
                        <option value="">All Status</option>
                        <option value="Pending">Pending</option>
                        <option value="In Transit">In Transit</option>
                        <option value="Delivered">Delivered</option>
                        <option value="On Hold">On Hold</option>
                    </select>

                    <input v-model="filterForm.date_from" type="date"
                        class="bg-slate-700 border-0 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-emerald-500" />
                    
                    <input v-model="filterForm.date_to" type="date"
                        class="bg-slate-700 border-0 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-emerald-500" />

                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded-lg">Filter</button>
                </form>
            </div>

            <!-- Table -->
            <div class="bg-slate-800 rounded-xl overflow-hidden shadow-lg">
                <table class="w-full">
                    <thead class="bg-slate-700">
                        <tr>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">GIT No</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Date</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Supplier</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Customer</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Item</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Qty</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Transporter</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Plate No</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Status</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        <tr v-for="g in gits.data" :key="g.id" class="hover:bg-slate-700/50">
                            <td class="px-4 py-4 text-white font-medium">{{ g.git_no }}</td>
                            <td class="px-4 py-4 text-slate-300">{{ g.git_date }}</td>
                            <td class="px-4 py-4 text-slate-300">{{ g.supplier_name }}</td>
                            <td class="px-4 py-4 text-slate-300">{{ g.customer_name }}</td>
                            <td class="px-4 py-4 text-slate-300">{{ g.item_name }}</td>
                            <td class="px-4 py-4 text-emerald-400 font-bold">{{ g.quantity }} {{ g.unit }}</td>
                            <td class="px-4 py-4 text-slate-300">{{ g.transporter_name }}</td>
                            <td class="px-4 py-4 text-slate-400">{{ g.plate_no }}</td>
                            <td class="px-4 py-4"><span :class="statusClass(g.status)" class="px-2 py-1 rounded text-xs">{{ g.status }}</span></td>
                            <td class="px-4 py-4">
                                <div class="flex gap-2">
                                    <a :href="'/goods-in-transit/' + g.id + '/edit'" class="text-blue-400 hover:text-blue-300">Edit</a>
                                    <button v-if="g.status === 'In Transit'" @click="markDelivered(g)" class="text-green-400 hover:text-green-300">Delivered</button>
                                    <button v-if="g.status !== 'On Hold'" @click="holdGIT(g)" class="text-yellow-400 hover:text-yellow-300">Hold</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex justify-between items-center mt-6 text-slate-400">
                <span>Showing {{ gits.from }}-{{ gits.to }} of {{ gits.total }}</span>
                <div class="flex gap-2">
                    <a v-if="gits.prev_page_url" :href="gits.prev_page_url" class="px-4 py-2 bg-slate-700 rounded hover:bg-slate-600">Previous</a>
                    <a v-if="gits.next_page_url" :href="gits.next_page_url" class="px-4 py-2 bg-slate-700 rounded hover:bg-slate-600">Next</a>
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
    gits: Object,
    filters: { type: Object, default: () => ({}) },
    stats: { type: Object, default: () => ({ inTransit: 0, delivered: 0, onHold: 0, totalQty: 0 }) }
});

const filterForm = ref({
    search: props.filters.search || '',
    status: props.filters.status || '',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || ''
});

const applyFilters = () => {
    router.get('/goods-in-transit', filterForm.value, { preserveState: true });
};

const statusClass = (status) => ({
    'Done': 'bg-green-500/20 text-green-400',
    'Delivered': 'bg-green-500/20 text-green-400',
    'In Transit': 'bg-blue-500/20 text-blue-400',
    'pending': 'bg-yellow-500/20 text-yellow-400',
    'Pending': 'bg-yellow-500/20 text-yellow-400',
    'On Hold': 'bg-red-500/20 text-red-400',
    'hold': 'bg-red-500/20 text-red-400',
    'Void': 'bg-slate-500/20 text-slate-400'
}[status] || 'bg-slate-500/20 text-slate-400');

const markDelivered = (item) => {
    router.put('/goods-in-transit/' + item.id + '/delivered');
};

const holdGIT = (item) => {
    router.put('/goods-in-transit/' + item.id + '/hold');
};
</script>

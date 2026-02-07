<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="sales-orders" />

        <div class="ml-64 p-8">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-white">Sales Orders</h2>
                    <p class="text-slate-400">Track all sales order records</p>
                </div>
                <a href="/sales-orders/create" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Sales Order
                </a>
            </div>

            <!-- Filters -->
            <div class="bg-slate-800 rounded-xl p-4 mb-6">
                <form @submit.prevent="applyFilters" class="flex gap-4">
                    <input v-model="filterForm.search" type="text" placeholder="Search by SO No., Item..." 
                        class="flex-1 bg-slate-700 border-0 rounded-lg px-4 py-2 text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500" />
                    
                    <select v-model="filterForm.status"
                        class="bg-slate-700 border-0 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-emerald-500">
                        <option value="">All Status</option>
                        <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
                    </select>

                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded-lg">Filter</button>
                    <a href="/sales-orders" class="bg-slate-600 hover:bg-slate-500 text-white px-4 py-2 rounded-lg">Clear</a>
                </form>
            </div>

            <!-- Table -->
            <div class="bg-slate-800 rounded-xl overflow-hidden shadow-lg">
                <table class="w-full">
                    <thead class="bg-slate-700">
                        <tr>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">SO No</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Date</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Item</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Quantity</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Unit Price</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Total</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Status</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        <tr v-for="so in salesOrders.data" :key="so.id" class="hover:bg-slate-700/50">
                            <td class="px-4 py-4 text-white font-medium">{{ so.so_no }}</td>
                            <td class="px-4 py-4 text-slate-300">{{ so.registered_date }}</td>
                            <td class="px-4 py-4 text-slate-300">{{ so.item }}</td>
                            <td class="px-4 py-4 text-slate-300">{{ so.quantity }} {{ so.unit }}</td>
                            <td class="px-4 py-4 text-slate-300">{{ formatCurrency(so.unit_price) }}</td>
                            <td class="px-4 py-4 text-emerald-400 font-bold">{{ formatCurrency(so.total) }}</td>
                            <td class="px-4 py-4"><span :class="statusClass(so.status)" class="px-2 py-1 rounded text-xs">{{ so.status }}</span></td>
                            <td class="px-4 py-4">
                                <div class="flex gap-2">
                                    <a :href="'/sales-orders/' + so.id + '/edit'" class="text-blue-400 hover:text-blue-300">Edit</a>
                                    <button @click="confirmDelete(so)" class="text-red-400 hover:text-red-300">Delete</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex justify-between items-center mt-6 text-slate-400">
                <span>Showing {{ salesOrders.from }}-{{ salesOrders.to }} of {{ salesOrders.total }}</span>
                <div class="flex gap-2">
                    <a v-if="salesOrders.prev_page_url" :href="salesOrders.prev_page_url" class="px-4 py-2 bg-slate-700 rounded hover:bg-slate-600">Previous</a>
                    <a v-if="salesOrders.next_page_url" :href="salesOrders.next_page_url" class="px-4 py-2 bg-slate-700 rounded hover:bg-slate-600">Next</a>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-slate-800 rounded-xl p-6 max-w-md w-full mx-4">
                <h3 class="text-xl font-bold text-white mb-4">Confirm Delete</h3>
                <p class="text-slate-300 mb-6">Are you sure you want to delete sales order "{{ deleteTarget?.so_no }}"?</p>
                <div class="flex gap-4 justify-end">
                    <button @click="showDeleteModal = false" class="px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-500">Cancel</button>
                    <button @click="deleteItem" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-500">Delete</button>
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
    salesOrders: Object,
    filters: { type: Object, default: () => ({}) },
    statuses: { type: Array, default: () => [] }
});

const filterForm = ref({
    search: props.filters.search || '',
    status: props.filters.status || ''
});

const showDeleteModal = ref(false);
const deleteTarget = ref(null);

const applyFilters = () => {
    router.get('/sales-orders', filterForm.value, { preserveState: true });
};

const formatCurrency = (val) => val ? parseFloat(val).toLocaleString('en-US', { minimumFractionDigits: 2 }) : '0.00';

const statusClass = (status) => ({
    'Approved': 'bg-green-500/20 text-green-400',
    'Pending': 'bg-yellow-500/20 text-yellow-400',
    'Done': 'bg-blue-500/20 text-blue-400',
    'Completed': 'bg-blue-500/20 text-blue-400'
}[status] || 'bg-slate-500/20 text-slate-400');

const confirmDelete = (item) => {
    deleteTarget.value = item;
    showDeleteModal.value = true;
};

const deleteItem = () => {
    router.delete('/sales-orders/' + deleteTarget.value.id, {
        onSuccess: () => {
            showDeleteModal.value = false;
            deleteTarget.value = null;
        }
    });
};
</script>

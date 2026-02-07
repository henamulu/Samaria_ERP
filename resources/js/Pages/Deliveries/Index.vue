<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="deliveries" />

        <div class="ml-64 p-8">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-white">Deliveries</h2>
                    <p class="text-slate-400">Track all delivery records</p>
                </div>
                <a href="/deliveries/create" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Delivery
                </a>
            </div>

            <!-- Filters -->
            <div class="bg-slate-800 rounded-xl p-4 mb-6">
                <form @submit.prevent="applyFilters" class="grid grid-cols-5 gap-4">
                    <input v-model="filterForm.search" type="text" placeholder="Search by No., Item, Plate..." 
                        class="bg-slate-700 border-0 rounded-lg px-4 py-2 text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500" />
                    
                    <select v-model="filterForm.status"
                        class="bg-slate-700 border-0 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-emerald-500">
                        <option value="">All Status</option>
                        <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
                    </select>

                    <input v-model="filterForm.date_from" type="date" placeholder="From Date"
                        class="bg-slate-700 border-0 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-emerald-500" />

                    <input v-model="filterForm.date_to" type="date" placeholder="To Date"
                        class="bg-slate-700 border-0 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-emerald-500" />

                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg">Filter</button>
                        <a href="/deliveries" class="bg-slate-600 hover:bg-slate-500 text-white px-4 py-2 rounded-lg">Clear</a>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="bg-slate-800 rounded-xl overflow-hidden shadow-lg">
                <table class="w-full">
                    <thead class="bg-slate-700">
                        <tr>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">No.</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Date</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Item</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Quantity</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Total</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Plate No</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Status</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        <tr v-for="d in deliveries.data" :key="d.id" class="hover:bg-slate-700/50">
                            <td class="px-4 py-4 text-slate-300">{{ d.d_no }}</td>
                            <td class="px-4 py-4 text-slate-300">{{ d.issue_date }}</td>
                            <td class="px-4 py-4 text-white font-medium">{{ d.item }}</td>
                            <td class="px-4 py-4 text-slate-300">{{ d.quantity }} {{ d.unit }}</td>
                            <td class="px-4 py-4 text-emerald-400 font-medium">{{ formatCurrency(d.total) }}</td>
                            <td class="px-4 py-4 text-slate-300">{{ d.truck_plate_no }}</td>
                            <td class="px-4 py-4"><span :class="statusClass(d.status)" class="px-2 py-1 rounded text-xs">{{ d.status }}</span></td>
                            <td class="px-4 py-4">
                                <div class="flex gap-2">
                                    <a :href="'/deliveries/' + d.id + '/edit'" class="text-blue-400 hover:text-blue-300">Edit</a>
                                    <button @click="confirmDelete(d)" class="text-red-400 hover:text-red-300">Delete</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex justify-between items-center mt-6 text-slate-400">
                <span>Showing {{ deliveries.from }}-{{ deliveries.to }} of {{ deliveries.total }}</span>
                <div class="flex gap-2">
                    <a v-if="deliveries.prev_page_url" :href="deliveries.prev_page_url" class="px-4 py-2 bg-slate-700 rounded hover:bg-slate-600">Previous</a>
                    <a v-if="deliveries.next_page_url" :href="deliveries.next_page_url" class="px-4 py-2 bg-slate-700 rounded hover:bg-slate-600">Next</a>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-slate-800 rounded-xl p-6 max-w-md w-full mx-4">
                <h3 class="text-xl font-bold text-white mb-4">Confirm Delete</h3>
                <p class="text-slate-300 mb-6">Are you sure you want to delete delivery "{{ deleteTarget?.d_no }}"?</p>
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
    deliveries: Object,
    filters: { type: Object, default: () => ({}) },
    statuses: { type: Array, default: () => [] }
});

const filterForm = ref({
    search: props.filters.search || '',
    status: props.filters.status || '',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || ''
});

const showDeleteModal = ref(false);
const deleteTarget = ref(null);

const applyFilters = () => {
    router.get('/deliveries', filterForm.value, { preserveState: true });
};

const formatCurrency = (val) => val ? parseFloat(val).toLocaleString('en-US', { minimumFractionDigits: 2 }) : '0.00';

const statusClass = (status) => ({
    'Done': 'bg-green-500/20 text-green-400',
    'Pending': 'bg-yellow-500/20 text-yellow-400',
    'Approved': 'bg-blue-500/20 text-blue-400'
}[status] || 'bg-slate-500/20 text-slate-400');

const confirmDelete = (item) => {
    deleteTarget.value = item;
    showDeleteModal.value = true;
};

const deleteItem = () => {
    router.delete('/deliveries/' + deleteTarget.value.id, {
        onSuccess: () => {
            showDeleteModal.value = false;
            deleteTarget.value = null;
        }
    });
};
</script>

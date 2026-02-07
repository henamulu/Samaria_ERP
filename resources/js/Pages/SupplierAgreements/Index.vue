<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="supplier-agreements" />

        <div class="ml-64 p-8">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-white">Supplier Agreements</h2>
                    <p class="text-slate-400">Manage supplier price agreements</p>
                </div>
                <a href="/supplier-agreements/create" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Agreement
                </a>
            </div>

            <!-- Filters -->
            <div class="bg-slate-800 rounded-xl p-4 mb-6">
                <form @submit.prevent="applyFilters" class="flex gap-4">
                    <input v-model="filterForm.search" type="text" placeholder="Search by Agreement No., Supplier..." 
                        class="flex-1 bg-slate-700 border-0 rounded-lg px-4 py-2 text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500" />
                    
                    <select v-model="filterForm.status"
                        class="bg-slate-700 border-0 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-emerald-500">
                        <option value="">All Status</option>
                        <option value="Done">Done</option>
                        <option value="Checked">Checked</option>
                        <option value="Approved">Approved</option>
                        <option value="Void">Void</option>
                    </select>

                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded-lg">Filter</button>
                    <a href="/supplier-agreements" class="bg-slate-600 hover:bg-slate-500 text-white px-4 py-2 rounded-lg">Clear</a>
                </form>
            </div>

            <!-- Table -->
            <div class="bg-slate-800 rounded-xl overflow-hidden shadow-lg">
                <table class="w-full">
                    <thead class="bg-slate-700">
                        <tr>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">#</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Agreement No</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Supplier</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Item</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Invoice Type</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Unit</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Unit Price</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Status</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Registered</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        <tr v-for="(agg, index) in agreements.data" :key="agg.id" class="hover:bg-slate-700/50">
                            <td class="px-4 py-4 text-slate-300">{{ index + 1 }}</td>
                            <td class="px-4 py-4 text-white font-medium">{{ agg.agg_no }}</td>
                            <td class="px-4 py-4 text-slate-300">{{ agg.supplier?.supplier_name }}</td>
                            <td class="px-4 py-4 text-slate-300">{{ agg.item }}</td>
                            <td class="px-4 py-4 text-slate-300">{{ agg.invoice_type }}</td>
                            <td class="px-4 py-4 text-slate-300">{{ agg.unit }}</td>
                            <td class="px-4 py-4 text-emerald-400 font-medium">{{ formatCurrency(agg.unit_price) }}</td>
                            <td class="px-4 py-4"><span :class="statusClass(agg.status)" class="px-2 py-1 rounded text-xs">{{ agg.status }}</span></td>
                            <td class="px-4 py-4 text-slate-400 text-sm">{{ agg.registered_date }}</td>
                            <td class="px-4 py-4">
                                <div class="flex gap-2">
                                    <a :href="'/supplier-agreements/' + agg.id + '/edit'" class="text-blue-400 hover:text-blue-300">Edit</a>
                                    <button v-if="agg.status !== 'Void'" @click="confirmVoid(agg)" class="text-red-400 hover:text-red-300">Void</button>
                                    <span v-else class="text-slate-500">Voided</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex justify-between items-center mt-6 text-slate-400">
                <span>Showing {{ agreements.from }}-{{ agreements.to }} of {{ agreements.total }}</span>
                <div class="flex gap-2">
                    <a v-if="agreements.prev_page_url" :href="agreements.prev_page_url" class="px-4 py-2 bg-slate-700 rounded hover:bg-slate-600">Previous</a>
                    <a v-if="agreements.next_page_url" :href="agreements.next_page_url" class="px-4 py-2 bg-slate-700 rounded hover:bg-slate-600">Next</a>
                </div>
            </div>
        </div>

        <!-- Void Modal -->
        <div v-if="showVoidModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-slate-800 rounded-xl p-6 max-w-md w-full mx-4">
                <h3 class="text-xl font-bold text-white mb-4">Confirm Void Agreement</h3>
                <p class="text-slate-300 mb-6">Are you sure you want to void agreement "{{ voidTarget?.agg_no }}"?</p>
                <div class="flex gap-4 justify-end">
                    <button @click="showVoidModal = false" class="px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-500">Cancel</button>
                    <button @click="voidAgreement" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-500">Void Agreement</button>
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
    agreements: Object,
    filters: { type: Object, default: () => ({}) }
});

const filterForm = ref({
    search: props.filters.search || '',
    status: props.filters.status || ''
});

const showVoidModal = ref(false);
const voidTarget = ref(null);

const applyFilters = () => {
    router.get('/supplier-agreements', filterForm.value, { preserveState: true });
};

const formatCurrency = (val) => val ? parseFloat(val).toLocaleString('en-US', { minimumFractionDigits: 2 }) : '0.00';

const statusClass = (status) => ({
    'Approved': 'bg-green-500/20 text-green-400',
    'Done': 'bg-blue-500/20 text-blue-400',
    'Checked': 'bg-yellow-500/20 text-yellow-400',
    'Void': 'bg-red-500/20 text-red-400'
}[status] || 'bg-slate-500/20 text-slate-400');

const confirmVoid = (item) => {
    voidTarget.value = item;
    showVoidModal.value = true;
};

const voidAgreement = () => {
    router.put('/supplier-agreements/' + voidTarget.value.id + '/void', {}, {
        onSuccess: () => {
            showVoidModal.value = false;
            voidTarget.value = null;
        }
    });
};
</script>

<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="budgets" />
        <div class="ml-64 p-8">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h2 class="text-3xl font-bold text-white mb-2">Budget Requests</h2>
                    <p class="text-slate-400">Create and manage budget requests</p>
                </div>
                <a href="/budget-requests/create" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg">
                    + New Request
                </a>
            </div>

            <!-- Filters -->
            <div class="bg-slate-800 rounded-xl p-6 mb-6">
                <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-slate-400 text-sm mb-2">Search</label>
                        <input v-model="filters.search" type="text" placeholder="Search by BR No, Project, Item..." class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                    </div>
                    <div>
                        <label class="block text-slate-400 text-sm mb-2">Status</label>
                        <select v-model="filters.status" class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="Done">Done</option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg">Apply</button>
                        <button type="button" @click="clearFilters" class="flex-1 bg-slate-600 hover:bg-slate-500 text-white px-4 py-2 rounded-lg">Clear</button>
                    </div>
                </form>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-slate-800 rounded-xl p-6">
                    <p class="text-slate-400 text-sm mb-1">Total Requests</p>
                    <p class="text-3xl font-bold text-white">{{ budgetRequests.total }}</p>
                </div>
                <div class="bg-slate-800 rounded-xl p-6">
                    <p class="text-slate-400 text-sm mb-1">Pending</p>
                    <p class="text-3xl font-bold text-yellow-400">{{ pendingCount }}</p>
                </div>
                <div class="bg-slate-800 rounded-xl p-6">
                    <p class="text-slate-400 text-sm mb-1">Completed</p>
                    <p class="text-3xl font-bold text-emerald-400">{{ doneCount }}</p>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-slate-800 rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">BR No</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Item</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Quantity</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Unit Price</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Amount</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Registered By</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            <tr v-for="br in budgetRequests.data" :key="br.id" class="hover:bg-slate-700/50">
                                <td class="px-4 py-3 text-slate-300">{{ br.b_no || '-' }}</td>
                                <td class="px-4 py-3 text-white">{{ br.item }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ formatNumber(br.quantity) }} {{ br.unit }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ formatCurrency(br.unit_price) }}</td>
                                <td class="px-4 py-3 text-emerald-400 font-medium">{{ formatCurrency(br.amount) }}</td>
                                <td class="px-4 py-3">
                                    <span :class="statusClass(br.status)" class="px-2 py-1 rounded text-xs">{{ br.status }}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-300">{{ br.registered_by }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-2">
                                        <button @click="editRequest(br.id)" class="text-blue-400 hover:text-blue-300 text-sm">Edit</button>
                                        <button v-if="br.status === 'pending'" @click="completeRequest(br)" class="text-emerald-400 hover:text-emerald-300 text-sm">Complete</button>
                                        <button @click="deleteRequest(br.id)" class="text-red-400 hover:text-red-300 text-sm">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div v-if="budgetRequests.links && budgetRequests.links.length > 3" class="p-4 border-t border-slate-700 flex justify-center gap-2">
                    <a v-for="link in budgetRequests.links" :key="link.label" 
                       :href="link.url || '#'" 
                       @click.prevent="link.url && loadPage(link.url)"
                       v-html="link.label"
                       :class="[
                           'px-4 py-2 rounded-lg',
                           link.active ? 'bg-emerald-600 text-white' : 'bg-slate-700 text-slate-300 hover:bg-slate-600',
                           !link.url && 'opacity-50 cursor-not-allowed'
                       ]"></a>
                </div>
            </div>
        </div>

        <!-- Complete Modal -->
        <div v-if="showCompleteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-slate-800 rounded-xl p-6 w-full max-w-md">
                <h3 class="text-xl font-bold text-white mb-4">Complete Budget Request</h3>
                <form @submit.prevent="submitComplete">
                    <div class="mb-4">
                        <label class="block text-slate-400 text-sm mb-2">Budget Request No (BR No)</label>
                        <input v-model="completeForm.b_no" type="text" required class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg">Complete</button>
                        <button type="button" @click="showCompleteModal = false" class="flex-1 bg-slate-600 hover:bg-slate-500 text-white px-4 py-2 rounded-lg">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import Sidebar from '../../Components/Sidebar.vue';

const props = defineProps({
    budgetRequests: Object,
    filters: Object
});

const filters = ref(props.filters || {
    search: '',
    status: ''
});

const showCompleteModal = ref(false);
const selectedRequest = ref(null);
const completeForm = ref({ b_no: '' });

const pendingCount = computed(() => {
    return props.budgetRequests?.data?.filter(br => br.status === 'pending').length || 0;
});

const doneCount = computed(() => {
    return props.budgetRequests?.data?.filter(br => br.status === 'Done').length || 0;
});

const statusClass = (status) => {
    if (status === 'Done') return 'bg-emerald-500/20 text-emerald-400';
    return 'bg-yellow-500/20 text-yellow-400';
};

const formatNumber = (num) => num ? parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 2 }) : '0';
const formatCurrency = (num) => num ? 'ETB ' + parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 2 }) : 'ETB 0.00';

const applyFilters = () => {
    router.get('/budget-requests', filters.value, {
        preserveState: true,
        preserveScroll: true
    });
};

const clearFilters = () => {
    filters.value = { search: '', status: '' };
    applyFilters();
};

const loadPage = (url) => {
    const urlObj = new URL(url);
    router.get(urlObj.pathname + urlObj.search, {}, {
        preserveState: true,
        preserveScroll: true
    });
};

const editRequest = (id) => {
    router.visit(`/budget-requests/${id}/edit`);
};

const completeRequest = (br) => {
    selectedRequest.value = br;
    completeForm.value.b_no = br.b_no || '';
    showCompleteModal.value = true;
};

const submitComplete = () => {
    router.put(`/budget-requests/${selectedRequest.value.id}/complete`, completeForm.value, {
        preserveState: false,
        onSuccess: () => {
            showCompleteModal.value = false;
        }
    });
};

const deleteRequest = (id) => {
    if (confirm('Are you sure you want to delete this budget request?')) {
        router.delete(`/budget-requests/${id}`, {
            preserveState: false
        });
    }
};
</script>

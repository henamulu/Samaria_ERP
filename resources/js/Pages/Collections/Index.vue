<template>
    <div class="min-h-screen bg-slate-900" lang="en">
        <Sidebar active="collections" />

        <div class="ml-64 p-8">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-white">Collections</h2>
                    <p class="text-slate-400">Manage sales and other collections</p>
                </div>
                <div class="flex gap-2">
                    <a href="/collections/create?type=sales" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg">
                        + Sales Collection
                    </a>
                    <a href="/collections/create?type=different" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                        + Different Collection
                    </a>
                </div>
            </div>

            <!-- Tabs -->
            <div class="flex gap-2 mb-6">
                <button @click="activeTab = 'sales'" :class="activeTab === 'sales' ? 'bg-emerald-600 text-white' : 'bg-slate-700 text-slate-300'" class="px-6 py-2 rounded-lg">
                    Sales Collection (SC)
                </button>
                <button @click="activeTab = 'different'" :class="activeTab === 'different' ? 'bg-blue-600 text-white' : 'bg-slate-700 text-slate-300'" class="px-6 py-2 rounded-lg">
                    Different Collection (DC)
                </button>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-4 gap-4 mb-6">
                <div class="bg-slate-800 rounded-xl p-4">
                    <p class="text-slate-400 text-sm">Total Collections</p>
                    <p class="text-2xl font-bold text-white">{{ collections.total }}</p>
                </div>
                <div class="bg-slate-800 rounded-xl p-4">
                    <p class="text-slate-400 text-sm">Pending Approval</p>
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

            <!-- Filters -->
            <div class="bg-slate-800 rounded-xl p-4 mb-6">
                <form @submit.prevent="applyFilters" class="flex gap-4">
                    <input v-model="filterForm.search" type="text" placeholder="Search by Collection No., Customer..." 
                        class="flex-1 bg-slate-700 border-0 rounded-lg px-4 py-2 text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500" />
                    
                    <select v-model="filterForm.status"
                        class="bg-slate-700 border-0 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-emerald-500">
                        <option value="">All Status</option>
                        <option value="Pending">Pending</option>
                        <option value="Checked">Checked</option>
                        <option value="Approved">Approved</option>
                    </select>

                    <input v-model="filterForm.date_from" type="date" lang="en"
                        class="bg-slate-700 border-0 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-emerald-500" />
                    
                    <input v-model="filterForm.date_to" type="date" lang="en"
                        class="bg-slate-700 border-0 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-emerald-500" />

                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded-lg">Filter</button>
                </form>
            </div>

            <!-- Table -->
            <div class="bg-slate-800 rounded-xl overflow-hidden shadow-lg">
                <table class="w-full">
                    <thead class="bg-slate-700">
                        <tr>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Collection No</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Type</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Date</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Customer/Source</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Bank</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Amount</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Status</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        <tr v-if="collections.data.length === 0">
                            <td colspan="8" class="px-4 py-8 text-center text-slate-400">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-12 h-12 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="text-lg">No collections found</p>
                                    <p class="text-sm text-slate-500">Try adjusting your filters or create a new collection</p>
                                </div>
                            </td>
                        </tr>
                        <tr v-for="c in collections.data" :key="c.id" class="hover:bg-slate-700/50">
                            <td class="px-4 py-4 text-white font-medium">{{ c.collection_no }}</td>
                            <td class="px-4 py-4">
                                <span :class="c.type === 'sales' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-blue-500/20 text-blue-400'" class="px-2 py-1 rounded text-xs">
                                    {{ c.type === 'sales' ? 'SC' : 'DC' }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-slate-300">{{ c.collection_date }}</td>
                            <td class="px-4 py-4 text-slate-300">{{ c.customer_name || c.source }}</td>
                            <td class="px-4 py-4 text-slate-300">{{ c.bank }}</td>
                            <td class="px-4 py-4 text-emerald-400 font-bold">{{ formatCurrency(c.amount) }}</td>
                            <td class="px-4 py-4"><span :class="statusClass(c.status)" class="px-2 py-1 rounded text-xs">{{ c.status }}</span></td>
                            <td class="px-4 py-4">
                                <div class="flex gap-2">
                                    <a :href="'/collections/' + c.id + '/edit'" class="text-blue-400 hover:text-blue-300">Edit</a>
                                    <button v-if="c.status === 'Pending'" @click="checkCollection(c)" class="text-yellow-400 hover:text-yellow-300">Check</button>
                                    <button v-if="c.status === 'Checked'" @click="approveCollection(c)" class="text-green-400 hover:text-green-300">Approve</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex justify-between items-center mt-6 text-slate-400">
                <span v-if="collections.total > 0">
                    Showing {{ collections.from }}-{{ collections.to }} of {{ collections.total }}
                </span>
                <span v-else>No records found</span>
                <div class="flex gap-2">
                    <a v-if="collections.prev_page_url" :href="collections.prev_page_url" class="px-4 py-2 bg-slate-700 rounded hover:bg-slate-600">Previous</a>
                    <a v-if="collections.next_page_url" :href="collections.next_page_url" class="px-4 py-2 bg-slate-700 rounded hover:bg-slate-600">Next</a>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import Sidebar from '../../Components/Sidebar.vue';

const props = defineProps({
    collections: Object,
    filters: { type: Object, default: () => ({}) },
    stats: { type: Object, default: () => ({ pending: 0, approved: 0, totalAmount: 0 }) }
});

const activeTab = ref(props.filters.type === 'different' ? 'different' : 'sales');

const filterForm = ref({
    search: props.filters.search || '',
    status: props.filters.status || '',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
    type: props.filters.type || activeTab.value
});

// Watch for tab changes and apply filter automatically
watch(activeTab, (newTab) => {
    filterForm.value.type = newTab;
    router.get('/collections', filterForm.value, { preserveState: true });
});

const applyFilters = () => {
    filterForm.value.type = activeTab.value;
    router.get('/collections', filterForm.value, { preserveState: true });
};

const formatCurrency = (val) => (parseFloat(val) || 0).toLocaleString('en-US', { minimumFractionDigits: 2 });

const statusClass = (status) => ({
    'Approved': 'bg-green-500/20 text-green-400',
    'Checked': 'bg-blue-500/20 text-blue-400',
    'Pending': 'bg-yellow-500/20 text-yellow-400'
}[status] || 'bg-slate-500/20 text-slate-400');

const checkCollection = (item) => {
    router.put('/collections/' + item.id + '/check');
};

const approveCollection = (item) => {
    router.put('/collections/' + item.id + '/approve');
};
</script>

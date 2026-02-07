<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="budgets" />
        <div class="ml-64 p-8">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h2 class="text-3xl font-bold text-white mb-2">Budgets</h2>
                    <p class="text-slate-400">Manage budgets and view balances</p>
                </div>
                <router-link to="/budgets/create" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg">
                    + Create Budget
                </router-link>
            </div>

            <!-- Budget Balances -->
            <div class="mb-6">
                <h3 class="text-xl font-semibold text-white mb-4">Budget Balances by Project</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div v-for="balance in budgetBalances" :key="balance.id" class="bg-slate-800 rounded-xl p-6">
                        <p class="text-slate-400 text-sm mb-1">{{ balance.project }}</p>
                        <p class="text-3xl font-bold text-emerald-400">{{ formatCurrency(balance.balance) }}</p>
                        <p class="text-xs text-slate-500 mt-2">{{ balance.status }}</p>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-slate-800 rounded-xl p-6 mb-6">
                <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-slate-400 text-sm mb-2">Search</label>
                        <input v-model="filters.search" type="text" placeholder="Search by Budget No, Project..." class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
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
                    <p class="text-slate-400 text-sm mb-1">Total Budgets</p>
                    <p class="text-3xl font-bold text-white">{{ budgets.total }}</p>
                </div>
                <div class="bg-slate-800 rounded-xl p-6">
                    <p class="text-slate-400 text-sm mb-1">Total Amount</p>
                    <p class="text-3xl font-bold text-emerald-400">{{ formatCurrency(totalAmount) }}</p>
                </div>
                <div class="bg-slate-800 rounded-xl p-6">
                    <p class="text-slate-400 text-sm mb-1">Total Balance</p>
                    <p class="text-3xl font-bold text-emerald-400">{{ formatCurrency(totalBalance) }}</p>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-slate-800 rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Budget No</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Project</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Total Amount</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Registered By</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            <tr v-for="budget in budgets.data" :key="budget.b_no" class="hover:bg-slate-700/50">
                                <td class="px-4 py-3 text-slate-300">{{ budget.b_no }}</td>
                                <td class="px-4 py-3 text-white">{{ budget.project }}</td>
                                <td class="px-4 py-3 text-emerald-400 font-medium">{{ formatCurrency(budget.total_amount) }}</td>
                                <td class="px-4 py-3">
                                    <span :class="statusClass(budget.status)" class="px-2 py-1 rounded text-xs">{{ budget.status }}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-300">{{ budget.registered_by }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ budget.registered_date }}</td>
                                <td class="px-4 py-3">
                                    <button @click="viewBudget(budget.b_no)" class="text-blue-400 hover:text-blue-300 text-sm">View</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div v-if="budgets.links && budgets.links.length > 3" class="p-4 border-t border-slate-700 flex justify-center gap-2">
                    <a v-for="link in budgets.links" :key="link.label" 
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
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import Sidebar from '../../Components/Sidebar.vue';

const props = defineProps({
    budgets: Object,
    budgetBalances: Array,
    filters: Object
});

const filters = ref(props.filters || {
    search: '',
    status: ''
});

const totalAmount = computed(() => {
    return props.budgets?.data?.reduce((sum, b) => sum + parseFloat(b.total_amount || 0), 0) || 0;
});

const totalBalance = computed(() => {
    return props.budgetBalances?.reduce((sum, b) => sum + parseFloat(b.balance || 0), 0) || 0;
});

const statusClass = (status) => {
    if (status === 'Done') return 'bg-emerald-500/20 text-emerald-400';
    return 'bg-yellow-500/20 text-yellow-400';
};

const formatCurrency = (num) => num ? 'ETB ' + parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 2 }) : 'ETB 0.00';

const applyFilters = () => {
    router.get('/budgets', filters.value, {
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

const viewBudget = (b_no) => {
    router.visit(`/budgets/${b_no}`);
};
</script>

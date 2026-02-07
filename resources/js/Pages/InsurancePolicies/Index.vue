<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="insurance-policies" />
        <div class="ml-64 p-8">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h2 class="text-3xl font-bold text-white mb-2">Insurance Policies</h2>
                    <p class="text-slate-400">Manage insurance policies</p>
                </div>
                <div class="flex gap-3">
                    <a href="/insurance-policies/create" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg">
                        + Create Policy
                    </a>
                    <a href="/insurance-policies/approve" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                        Approve Policies
                    </a>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-slate-800 rounded-xl p-6 mb-6">
                <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-slate-400 text-sm mb-2">Search</label>
                        <input v-model="filters.search" type="text" placeholder="Policy No, Company, C No, P No..." class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                    </div>
                    <div>
                        <label class="block text-slate-400 text-sm mb-2">Status</label>
                        <select v-model="filters.status" class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="Approved">Approved</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-400 text-sm mb-2">Insurance Company</label>
                        <select v-model="filters.insurance_company" class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white">
                            <option value="">All Companies</option>
                            <option v-for="ic in insuranceCompanies" :key="ic.insurance_name" :value="ic.insurance_name">{{ ic.insurance_name }}</option>
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
                    <p class="text-slate-400 text-sm mb-1">Total Policies</p>
                    <p class="text-3xl font-bold text-white">{{ policies.total }}</p>
                </div>
                <div class="bg-slate-800 rounded-xl p-6">
                    <p class="text-slate-400 text-sm mb-1">Pending</p>
                    <p class="text-3xl font-bold text-yellow-400">{{ pendingCount }}</p>
                </div>
                <div class="bg-slate-800 rounded-xl p-6">
                    <p class="text-slate-400 text-sm mb-1">Approved</p>
                    <p class="text-3xl font-bold text-emerald-400">{{ approvedCount }}</p>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-slate-800 rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Policy No</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Insurance Company</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Insurance Type</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">C No</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">P No</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Issued Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Premium</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            <tr v-for="policy in policies.data" :key="policy.id" class="hover:bg-slate-700/50">
                                <td class="px-4 py-3 text-white font-medium">{{ policy.i_no }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ policy.insurance_company }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ policy.insurance_type }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ policy.c_no }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ policy.p_no }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ policy.issued_date }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ formatCurrency(policy.premium_tariff) }}</td>
                                <td class="px-4 py-3">
                                    <span :class="statusClass(policy.status)" class="px-2 py-1 rounded text-xs">{{ policy.status }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-2">
                                        <a :href="`/insurance-policies/${policy.id}/edit`" class="text-blue-400 hover:text-blue-300 text-sm">Edit</a>
                                        <button @click="deletePolicy(policy.id)" class="text-red-400 hover:text-red-300 text-sm">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div v-if="policies.links && policies.links.length > 3" class="p-4 border-t border-slate-700 flex justify-center gap-2">
                    <a v-for="link in policies.links" :key="link.label" 
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
    policies: Object,
    insuranceCompanies: Array,
    filters: Object
});

const filters = ref(props.filters || {
    search: '',
    status: '',
    insurance_company: ''
});

const pendingCount = computed(() => {
    return props.policies?.data?.filter(p => p.status === 'pending').length || 0;
});

const approvedCount = computed(() => {
    return props.policies?.data?.filter(p => p.status === 'Approved').length || 0;
});

const statusClass = (status) => {
    if (status === 'Approved') return 'bg-emerald-500/20 text-emerald-400';
    return 'bg-yellow-500/20 text-yellow-400';
};

const formatCurrency = (num) => num ? 'ETB ' + parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 2 }) : 'ETB 0.00';

const applyFilters = () => {
    router.get('/insurance-policies', filters.value, {
        preserveState: true,
        preserveScroll: true
    });
};

const clearFilters = () => {
    filters.value = { search: '', status: '', insurance_company: '' };
    applyFilters();
};

const loadPage = (url) => {
    const urlObj = new URL(url);
    router.get(urlObj.pathname + urlObj.search, {}, {
        preserveState: true,
        preserveScroll: true
    });
};

const deletePolicy = (id) => {
    if (confirm('Are you sure you want to delete this insurance policy?')) {
        router.delete(`/insurance-policies/${id}`, {
            preserveState: false
        });
    }
};
</script>

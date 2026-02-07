<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="insurance-policies" />
        <div class="ml-64 p-8">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h2 class="text-3xl font-bold text-white mb-2">Approve Insurance Policies</h2>
                    <p class="text-slate-400">Review and approve pending insurance policies</p>
                </div>
                <router-link to="/insurance-policies" class="text-emerald-400 hover:text-emerald-300">← Back to List</router-link>
            </div>

            <!-- Filters -->
            <div class="bg-slate-800 rounded-xl p-6 mb-6">
                <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-slate-400 text-sm mb-2">Search</label>
                        <input v-model="filters.search" type="text" placeholder="Policy No, Company..." class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg">Apply</button>
                        <button type="button" @click="clearFilters" class="flex-1 bg-slate-600 hover:bg-slate-500 text-white px-4 py-2 rounded-lg">Clear</button>
                    </div>
                </form>
            </div>

            <!-- Summary Card -->
            <div class="bg-slate-800 rounded-xl p-6 mb-6">
                <p class="text-slate-400 text-sm mb-1">Pending Approvals</p>
                <p class="text-3xl font-bold text-yellow-400">{{ pendingPolicies.total }}</p>
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
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Registered By</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            <tr v-for="policy in pendingPolicies.data" :key="policy.id" class="hover:bg-slate-700/50">
                                <td class="px-4 py-3 text-white font-medium">{{ policy.i_no }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ policy.insurance_company }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ policy.insurance_type }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ policy.c_no }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ policy.p_no }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ policy.issued_date }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ formatCurrency(policy.premium_tariff) }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ policy.registered_by }}</td>
                                <td class="px-4 py-3">
                                    <button @click="approvePolicy(policy.id)" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1 rounded text-sm">Approve</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div v-if="pendingPolicies.links && pendingPolicies.links.length > 3" class="p-4 border-t border-slate-700 flex justify-center gap-2">
                    <a v-for="link in pendingPolicies.links" :key="link.label" 
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
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Sidebar from '../../Components/Sidebar.vue';

const props = defineProps({
    pendingPolicies: Object,
    filters: Object
});

const filters = ref(props.filters || {
    search: ''
});

const formatCurrency = (num) => num ? 'ETB ' + parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 2 }) : 'ETB 0.00';

const applyFilters = () => {
    router.get('/insurance-policies/approve', filters.value, {
        preserveState: true,
        preserveScroll: true
    });
};

const clearFilters = () => {
    filters.value = { search: '' };
    applyFilters();
};

const loadPage = (url) => {
    const urlObj = new URL(url);
    router.get(urlObj.pathname + urlObj.search, {}, {
        preserveState: true,
        preserveScroll: true
    });
};

const approvePolicy = (id) => {
    if (confirm('Are you sure you want to approve this insurance policy?')) {
        router.put(`/insurance-policies/${id}/approve`, {}, {
            preserveState: false
        });
    }
};
</script>

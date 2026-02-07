<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="transporter-agreements" />
        <div class="ml-64 p-8">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h2 class="text-3xl font-bold text-white mb-2">Transporter Agreements</h2>
                    <p class="text-slate-400">Manage transport contracts</p>
                </div>
                <a href="/transporter-agreements/create" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg">
                    + Create Agreement
                </a>
            </div>

            <!-- Filters -->
            <div class="bg-slate-800 rounded-xl p-6 mb-6">
                <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-slate-400 text-sm mb-2">Search</label>
                        <input v-model="filters.search" type="text" placeholder="Agreement No, Plate No, Owner..." class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                    </div>
                    <div>
                        <label class="block text-slate-400 text-sm mb-2">Status</label>
                        <select v-model="filters.status" class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white">
                            <option value="">All Statuses</option>
                            <option value="InActive">InActive</option>
                            <option value="Approved">Approved</option>
                            <option value="Active">Active</option>
                            <option value="Void">Void</option>
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
                    <p class="text-slate-400 text-sm mb-1">InActive</p>
                    <p class="text-3xl font-bold text-yellow-400">{{ stats.inactive }}</p>
                </div>
                <div class="bg-slate-800 rounded-xl p-6">
                    <p class="text-slate-400 text-sm mb-1">Approved</p>
                    <p class="text-3xl font-bold text-blue-400">{{ stats.approved }}</p>
                </div>
                <div class="bg-slate-800 rounded-xl p-6">
                    <p class="text-slate-400 text-sm mb-1">Active</p>
                    <p class="text-3xl font-bold text-emerald-400">{{ stats.active }}</p>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-slate-800 rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Agreement No</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Transporter</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Plate No</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Item</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Unit Price</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Owner</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Agreement Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            <tr v-for="agreement in agreements.data" :key="agreement.id" class="hover:bg-slate-700/50">
                                <td class="px-4 py-3 text-white font-medium">{{ agreement.a_no }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ agreement.t_id }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ agreement.plate_no }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ agreement.item }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ formatCurrency(agreement.unit_price) }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ agreement.owner || '-' }}</td>
                                <td class="px-4 py-3">
                                    <span :class="statusClass(agreement.status)" class="px-2 py-1 rounded text-xs">{{ agreement.status }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span :class="aggStatusClass(agreement.agg_status)" class="px-2 py-1 rounded text-xs">{{ agreement.agg_status }}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-300">{{ agreement.registered_date }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-2">
                                        <a v-if="agreement.status !== 'Active'" :href="`/transporter-agreements/${agreement.id}/edit`" class="text-blue-400 hover:text-blue-300 text-sm">Edit</a>
                                        <span v-else class="text-slate-500 text-sm cursor-not-allowed">Edit</span>
                                        <button v-if="agreement.agg_status === 'Pending'" @click="checkAgreement(agreement.id)" class="text-yellow-400 hover:text-yellow-300 text-sm">Check</button>
                                        <button v-if="agreement.agg_status === 'Checked'" @click="approveAgreement(agreement.id)" class="text-emerald-400 hover:text-emerald-300 text-sm">Approve</button>
                                        <button v-if="agreement.status === 'Approved'" @click="activateAgreement(agreement.id)" class="text-purple-400 hover:text-purple-300 text-sm">Activate</button>
                                        <button v-if="agreement.status !== 'Void'" @click="voidAgreement(agreement.id)" class="text-red-400 hover:text-red-300 text-sm">Void</button>
                                        <button @click="deleteAgreement(agreement.id)" class="text-red-400 hover:text-red-300 text-sm">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div v-if="agreements.links && agreements.links.length > 3" class="p-4 border-t border-slate-700 flex justify-center gap-2">
                    <a v-for="link in agreements.links" :key="link.label" 
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
    agreements: Object,
    stats: Object,
    filters: Object
});

const filters = ref(props.filters || {
    search: '',
    status: ''
});

const statusClass = (status) => {
    if (status === 'Active') return 'bg-emerald-500/20 text-emerald-400';
    if (status === 'Approved') return 'bg-blue-500/20 text-blue-400';
    if (status === 'Void') return 'bg-red-500/20 text-red-400';
    return 'bg-yellow-500/20 text-yellow-400';
};

const aggStatusClass = (status) => {
    if (status === 'Approved') return 'bg-emerald-500/20 text-emerald-400';
    if (status === 'Checked') return 'bg-blue-500/20 text-blue-400';
    if (status === 'Void') return 'bg-red-500/20 text-red-400';
    return 'bg-yellow-500/20 text-yellow-400';
};

const formatCurrency = (num) => num ? 'ETB ' + parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 2 }) : 'ETB 0.00';

const applyFilters = () => {
    router.get('/transporter-agreements', filters.value, {
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

const checkAgreement = (id) => {
    if (confirm('Are you sure you want to check this agreement?')) {
        router.put(`/transporter-agreements/${id}/check`, {}, {
            preserveState: false
        });
    }
};

const approveAgreement = (id) => {
    if (confirm('Are you sure you want to approve this agreement?')) {
        router.put(`/transporter-agreements/${id}/approve`, {}, {
            preserveState: false
        });
    }
};

const activateAgreement = (id) => {
    if (confirm('Are you sure you want to activate this agreement?')) {
        router.put(`/transporter-agreements/${id}/activate`, {}, {
            preserveState: false
        });
    }
};

const voidAgreement = (id) => {
    if (confirm('Are you sure you want to void this agreement?')) {
        router.put(`/transporter-agreements/${id}/void`, {}, {
            preserveState: false
        });
    }
};

const deleteAgreement = (id) => {
    if (confirm('Are you sure you want to delete this agreement? This action cannot be undone.')) {
        router.delete(`/transporter-agreements/${id}`, {
            preserveState: false
        });
    }
};
</script>

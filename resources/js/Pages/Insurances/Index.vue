<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="insurances" />
        <div class="ml-64 p-8">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h2 class="text-3xl font-bold text-white mb-2">Insurance Companies</h2>
                    <p class="text-slate-400">Manage insurance companies</p>
                </div>
                <router-link to="/insurances/create" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg">
                    + Add Insurance Company
                </router-link>
            </div>

            <!-- Filters -->
            <div class="bg-slate-800 rounded-xl p-6 mb-6">
                <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-slate-400 text-sm mb-2">Search</label>
                        <input v-model="filters.search" type="text" placeholder="Name, Contact, Branch..." class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                    </div>
                    <div>
                        <label class="block text-slate-400 text-sm mb-2">Status</label>
                        <select v-model="filters.status" class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white">
                            <option value="">All Statuses</option>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg">Apply</button>
                        <button type="button" @click="clearFilters" class="flex-1 bg-slate-600 hover:bg-slate-500 text-white px-4 py-2 rounded-lg">Clear</button>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="bg-slate-800 rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Insurance Name</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Branch</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Contact Person</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Phone Number</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            <tr v-for="insurance in insurances.data" :key="insurance.id" class="hover:bg-slate-700/50">
                                <td class="px-4 py-3 text-white font-medium">{{ insurance.insurance_name }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ insurance.branch || '-' }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ insurance.contact_person }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ insurance.phone_number }}</td>
                                <td class="px-4 py-3">
                                    <span :class="insurance.insurance_status === 'Active' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-500/20 text-slate-400'" class="px-2 py-1 rounded text-xs">{{ insurance.insurance_status }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-2">
                                        <router-link :to="`/insurances/${insurance.id}/edit`" class="text-blue-400 hover:text-blue-300 text-sm">Edit</router-link>
                                        <button @click="deleteInsurance(insurance.id)" class="text-red-400 hover:text-red-300 text-sm">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div v-if="insurances.links && insurances.links.length > 3" class="p-4 border-t border-slate-700 flex justify-center gap-2">
                    <a v-for="link in insurances.links" :key="link.label" 
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
    insurances: Object,
    filters: Object
});

const filters = ref(props.filters || {
    search: '',
    status: ''
});

const applyFilters = () => {
    router.get('/insurances', filters.value, {
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

const deleteInsurance = (id) => {
    if (confirm('Are you sure you want to delete this insurance company?')) {
        router.delete(`/insurances/${id}`, {
            preserveState: false
        });
    }
};
</script>

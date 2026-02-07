<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="banks" />

        <div class="ml-64 p-8">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-white">Bank List</h2>
                    <p class="text-slate-400">Manage all bank accounts</p>
                </div>
                <a href="/banks/create" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Bank
                </a>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-3 gap-4 mb-6">
                <div class="bg-slate-800 rounded-xl p-4">
                    <p class="text-slate-400 text-sm">Total Banks</p>
                    <p class="text-2xl font-bold text-white">{{ stats.total }}</p>
                </div>
                <div class="bg-slate-800 rounded-xl p-4">
                    <p class="text-slate-400 text-sm">Active</p>
                    <p class="text-2xl font-bold text-green-400">{{ stats.active }}</p>
                </div>
                <div class="bg-slate-800 rounded-xl p-4">
                    <p class="text-slate-400 text-sm">Inactive</p>
                    <p class="text-2xl font-bold text-red-400">{{ stats.inactive }}</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-slate-800 rounded-xl p-4 mb-6">
                <form @submit.prevent="applyFilters" class="flex gap-4">
                    <input v-model="filterForm.search" type="text" placeholder="Search bank name, branch, account..."
                        class="flex-1 bg-slate-700 border-0 rounded-lg px-4 py-2 text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500" />
                    <select v-model="filterForm.status"
                        class="bg-slate-700 border-0 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-emerald-500">
                        <option value="">All Status</option>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded-lg">Filter</button>
                </form>
            </div>

            <!-- Flash message -->
            <div v-if="$page.props.flash && $page.props.flash.success" class="bg-green-600/20 border border-green-500/30 text-green-300 px-4 py-3 rounded-lg mb-4">
                {{ $page.props.flash.success }}
            </div>

            <!-- Table -->
            <div class="bg-slate-800 rounded-xl overflow-hidden shadow-lg">
                <table class="w-full">
                    <thead class="bg-slate-700">
                        <tr>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">#</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Bank Name</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Branch</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Account No</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Status</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        <tr v-for="(bank, index) in banks" :key="bank.id" class="hover:bg-slate-700/50">
                            <td class="px-4 py-4 text-slate-400">{{ index + 1 }}</td>
                            <td class="px-4 py-4 text-white font-medium">{{ bank.bank_name }}</td>
                            <td class="px-4 py-4 text-slate-300">{{ bank.branch_name }}</td>
                            <td class="px-4 py-4 text-slate-300 font-mono text-sm">{{ bank.bank_ac_no }}</td>
                            <td class="px-4 py-4">
                                <span :class="bank.bank_status === 'Active' ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'"
                                    class="px-2 py-1 rounded text-xs font-medium">
                                    {{ bank.bank_status }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex gap-3">
                                    <a :href="'/banks/' + bank.id + '/edit'" class="text-blue-400 hover:text-blue-300 text-sm">Edit</a>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="banks.length === 0">
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500">No banks found</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Sidebar from '../../Components/Sidebar.vue';

const props = defineProps({
    banks:   { type: Array,  default: () => [] },
    stats:   { type: Object, default: () => ({ total: 0, active: 0, inactive: 0 }) },
    filters: { type: Object, default: () => ({}) },
});

const filterForm = ref({
    search: props.filters.search || '',
    status: props.filters.status || '',
});

const applyFilters = () => {
    router.get('/banks', filterForm.value, { preserveState: true });
};
</script>

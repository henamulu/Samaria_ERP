<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="customers" />

        <div class="ml-64 p-8">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-white">Customers</h2>
                    <p class="text-slate-400">Manage customer records</p>
                </div>
                <a href="/customers/create" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Customer
                </a>
            </div>

            <!-- Search -->
            <div class="bg-slate-800 rounded-xl p-4 mb-6">
                <form @submit.prevent="search" class="flex gap-4">
                    <input v-model="searchQuery" type="text" placeholder="Search by company, TIN, contact..." 
                        class="flex-1 bg-slate-700 border-0 rounded-lg px-4 py-2 text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500" />
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded-lg">Search</button>
                    <a v-if="filters.search" href="/customers" class="bg-slate-600 hover:bg-slate-500 text-white px-4 py-2 rounded-lg">Clear</a>
                </form>
            </div>

            <!-- Table -->
            <div class="bg-slate-800 rounded-xl overflow-hidden shadow-lg">
                <table class="w-full">
                    <thead class="bg-slate-700">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase">ID</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase">Company</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase">TIN</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase">Contact</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase">Phone</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        <tr v-for="c in customers.data" :key="c.id" class="hover:bg-slate-700/50">
                            <td class="px-6 py-4 text-slate-300">{{ c.id }}</td>
                            <td class="px-6 py-4 text-white font-medium">{{ c.company_name }}</td>
                            <td class="px-6 py-4 text-slate-300">{{ c.tin_no }}</td>
                            <td class="px-6 py-4 text-slate-300">{{ c.contact_person }}</td>
                            <td class="px-6 py-4 text-slate-300">{{ c.phone_no }}</td>
                            <td class="px-6 py-4">
                                <span :class="statusClass(c.status)" class="px-2 py-1 rounded text-xs">{{ c.status }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex gap-2">
                                    <a :href="'/customers/' + c.id + '/edit'" class="text-blue-400 hover:text-blue-300">Edit</a>
                                    <button @click="confirmDelete(c)" class="text-red-400 hover:text-red-300">Delete</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex justify-between items-center mt-6 text-slate-400">
                <span>Showing {{ customers.from }}-{{ customers.to }} of {{ customers.total }}</span>
                <div class="flex gap-2">
                    <a v-if="customers.prev_page_url" :href="customers.prev_page_url" class="px-4 py-2 bg-slate-700 rounded hover:bg-slate-600">Previous</a>
                    <a v-if="customers.next_page_url" :href="customers.next_page_url" class="px-4 py-2 bg-slate-700 rounded hover:bg-slate-600">Next</a>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-slate-800 rounded-xl p-6 max-w-md w-full mx-4">
                <h3 class="text-xl font-bold text-white mb-4">Confirm Delete</h3>
                <p class="text-slate-300 mb-6">Are you sure you want to delete "{{ deleteTarget?.company_name }}"?</p>
                <div class="flex gap-4 justify-end">
                    <button @click="showDeleteModal = false" class="px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-500">Cancel</button>
                    <button @click="deleteCustomer" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-500">Delete</button>
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
    customers: Object,
    filters: { type: Object, default: () => ({}) }
});

const searchQuery = ref(props.filters.search || '');
const showDeleteModal = ref(false);
const deleteTarget = ref(null);

const search = () => {
    router.get('/customers', { search: searchQuery.value }, { preserveState: true });
};

const statusClass = (status) => ({
    'Approved': 'bg-green-500/20 text-green-400',
    'Pending': 'bg-yellow-500/20 text-yellow-400',
    'Active': 'bg-blue-500/20 text-blue-400'
}[status] || 'bg-slate-500/20 text-slate-400');

const confirmDelete = (customer) => {
    deleteTarget.value = customer;
    showDeleteModal.value = true;
};

const deleteCustomer = () => {
    router.delete('/customers/' + deleteTarget.value.id, {
        onSuccess: () => {
            showDeleteModal.value = false;
            deleteTarget.value = null;
        }
    });
};
</script>

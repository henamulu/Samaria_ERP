<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="suppliers" />

        <div class="ml-64 p-8">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-white">Suppliers</h2>
                    <p class="text-slate-400">Manage supplier records</p>
                </div>
                <a href="/suppliers/create" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Supplier
                </a>
            </div>

            <!-- Search -->
            <div class="bg-slate-800 rounded-xl p-4 mb-6">
                <form @submit.prevent="search" class="flex gap-4">
                    <input v-model="searchQuery" type="text" placeholder="Search by name, TIN..." 
                        class="flex-1 bg-slate-700 border-0 rounded-lg px-4 py-2 text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500" />
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded-lg">Search</button>
                    <a v-if="filters.search" href="/suppliers" class="bg-slate-600 hover:bg-slate-500 text-white px-4 py-2 rounded-lg">Clear</a>
                </form>
            </div>

            <!-- Table -->
            <div class="bg-slate-800 rounded-xl overflow-hidden shadow-lg">
                <table class="w-full">
                    <thead class="bg-slate-700">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase">ID</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase">Name</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase">TIN</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase">Category</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase">Contact</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase">Phone</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        <tr v-for="s in suppliers.data" :key="s.id" class="hover:bg-slate-700/50">
                            <td class="px-6 py-4 text-slate-300">{{ s.id }}</td>
                            <td class="px-6 py-4 text-white font-medium">{{ s.supplier_name }}</td>
                            <td class="px-6 py-4 text-slate-300">{{ s.supplier_tin }}</td>
                            <td class="px-6 py-4 text-slate-300">{{ s.supplier_category }}</td>
                            <td class="px-6 py-4 text-slate-300">{{ s.contact_person }}</td>
                            <td class="px-6 py-4 text-slate-300">{{ s.phone_number }}</td>
                            <td class="px-6 py-4"><span class="px-2 py-1 rounded text-xs bg-green-500/20 text-green-400">{{ s.status }}</span></td>
                            <td class="px-6 py-4">
                                <div class="flex gap-3">
                                    <a :href="'/suppliers/' + s.id + '/edit'" class="text-blue-400 hover:text-blue-300">Edit</a>
                                    <a :href="'/suppliers/' + s.id + '/accounts'" class="text-amber-400 hover:text-amber-300">Bank</a>
                                    <button @click="confirmDelete(s)" class="text-red-400 hover:text-red-300">Delete</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex justify-between items-center mt-6 text-slate-400">
                <span>Showing {{ suppliers.from }}-{{ suppliers.to }} of {{ suppliers.total }}</span>
                <div class="flex gap-2">
                    <a v-if="suppliers.prev_page_url" :href="suppliers.prev_page_url" class="px-4 py-2 bg-slate-700 rounded hover:bg-slate-600">Previous</a>
                    <a v-if="suppliers.next_page_url" :href="suppliers.next_page_url" class="px-4 py-2 bg-slate-700 rounded hover:bg-slate-600">Next</a>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-slate-800 rounded-xl p-6 max-w-md w-full mx-4">
                <h3 class="text-xl font-bold text-white mb-4">Confirm Delete</h3>
                <p class="text-slate-300 mb-6">Are you sure you want to delete "{{ deleteTarget?.supplier_name }}"?</p>
                <div class="flex gap-4 justify-end">
                    <button @click="showDeleteModal = false" class="px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-500">Cancel</button>
                    <button @click="deleteItem" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-500">Delete</button>
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
    suppliers: Object,
    filters: { type: Object, default: () => ({}) }
});

const searchQuery = ref(props.filters.search || '');
const showDeleteModal = ref(false);
const deleteTarget = ref(null);

const search = () => {
    router.get('/suppliers', { search: searchQuery.value }, { preserveState: true });
};

const confirmDelete = (item) => {
    deleteTarget.value = item;
    showDeleteModal.value = true;
};

const deleteItem = () => {
    router.delete('/suppliers/' + deleteTarget.value.id, {
        onSuccess: () => {
            showDeleteModal.value = false;
            deleteTarget.value = null;
        }
    });
};
</script>

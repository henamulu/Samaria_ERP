<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="suppliers" />

        <div class="ml-64 p-8">
            <div class="mb-8">
                <a href="/suppliers" class="text-slate-400 hover:text-white mb-2 inline-block">&larr; Back to Suppliers</a>
                <h2 class="text-3xl font-bold text-white">{{ supplier.supplier_name }}</h2>
                <p class="text-slate-400">Manage bank accounts</p>
            </div>

            <!-- Supplier Info Card -->
            <div class="bg-slate-800 rounded-xl p-6 mb-6">
                <h3 class="text-xl font-bold text-white mb-4">Supplier Details</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <p class="text-slate-400 text-sm">Supplier Name</p>
                        <p class="text-white font-medium">{{ supplier.supplier_name }}</p>
                    </div>
                    <div>
                        <p class="text-slate-400 text-sm">TIN Number</p>
                        <p class="text-white font-medium">{{ supplier.supplier_tin || 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-400 text-sm">Contact Person</p>
                        <p class="text-white font-medium">{{ supplier.contact_person || 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-400 text-sm">Phone Number</p>
                        <p class="text-white font-medium">{{ supplier.phone_number || 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <!-- Add Bank Account Form -->
            <div class="bg-slate-800 rounded-xl p-6 mb-6">
                <h3 class="text-xl font-bold text-white mb-4">Add Bank Account</h3>
                <form @submit.prevent="addAccount" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-slate-300 mb-2">Bank *</label>
                        <select v-model="form.bank" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="">Select Bank</option>
                            <option v-for="bank in banks" :key="bank" :value="bank">{{ bank }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Account Holder Name *</label>
                        <input v-model="form.account_holder" type="text" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Account Number *</label>
                        <input v-model="form.account_no" type="text" required pattern="[0-9]+"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div class="flex items-end">
                        <button type="submit" :disabled="processing"
                            class="w-full bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-lg font-medium disabled:opacity-50">
                            {{ processing ? 'Adding...' : 'Add Account' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Bank Accounts List -->
            <div class="bg-slate-800 rounded-xl p-6">
                <h3 class="text-xl font-bold text-white mb-4">Bank Account List</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">#</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Bank</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Account Holder</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Account No</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            <tr v-for="(account, index) in accounts" :key="account.id" class="hover:bg-slate-700/50">
                                <td class="px-4 py-3 text-slate-300">{{ index + 1 }}</td>
                                <td class="px-4 py-3 text-white">{{ account.bank }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ account.account_holder }}</td>
                                <td class="px-4 py-3 text-slate-300 font-mono">{{ account.account_no }}</td>
                                <td class="px-4 py-3">
                                    <button @click="confirmDelete(account)" class="text-red-400 hover:text-red-300">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!accounts.length">
                                <td colspan="5" class="px-4 py-8 text-center text-slate-500">No bank accounts found</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-slate-800 rounded-xl p-6 max-w-md w-full mx-4">
                <h3 class="text-xl font-bold text-white mb-4">Confirm Delete</h3>
                <p class="text-slate-300 mb-6">Are you sure you want to delete this bank account?</p>
                <div class="flex gap-4 justify-end">
                    <button @click="showDeleteModal = false" class="px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-500">Cancel</button>
                    <button @click="deleteAccount" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-500">Delete</button>
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
    supplier: Object,
    accounts: { type: Array, default: () => [] },
    banks: { type: Array, default: () => [
        'Commercial Bank of Ethiopia',
        'Awash Bank',
        'Dashen Bank',
        'Bank of Abyssinia',
        'Wegagen Bank',
        'United Bank',
        'Nib International Bank',
        'Cooperative Bank of Oromia',
        'Lion International Bank',
        'Zemen Bank',
        'Oromia Bank',
        'Bunna International Bank',
        'Berhan Bank',
        'Abay Bank',
        'Addis International Bank',
        'Debub Global Bank',
        'Enat Bank',
        'Hijra Bank',
        'ZamZam Bank',
        'Ahadu Bank',
        'Siinqee Bank',
        'Tsedey Bank'
    ]}
});

const form = ref({
    bank: '',
    account_holder: '',
    account_no: ''
});

const processing = ref(false);
const showDeleteModal = ref(false);
const deleteTarget = ref(null);

const addAccount = () => {
    processing.value = true;
    router.post('/suppliers/' + props.supplier.id + '/accounts', form.value, {
        onSuccess: () => {
            form.value = { bank: '', account_holder: '', account_no: '' };
        },
        onFinish: () => processing.value = false
    });
};

const confirmDelete = (account) => {
    deleteTarget.value = account;
    showDeleteModal.value = true;
};

const deleteAccount = () => {
    router.delete('/suppliers/' + props.supplier.id + '/accounts/' + deleteTarget.value.id, {
        onSuccess: () => {
            showDeleteModal.value = false;
            deleteTarget.value = null;
        }
    });
};
</script>

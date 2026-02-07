<template>
    <div class="min-h-screen bg-slate-900" lang="en">
        <Sidebar active="collections" />

        <div class="ml-64 p-8">
            <div class="mb-8">
                <a href="/collections" class="text-slate-400 hover:text-white mb-2 inline-block">&larr; Back to Collections</a>
                <h2 class="text-3xl font-bold text-white">Create {{ collectionType === 'sales' ? 'Sales' : 'Different' }} Collection</h2>
            </div>

            <form @submit.prevent="submit" class="bg-slate-800 rounded-xl p-6 max-w-4xl">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-slate-300 mb-2">Collection Number *</label>
                        <input v-model="form.collection_no" type="text" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Collection Date *</label>
                        <input v-model="form.collection_date" type="date" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div v-if="collectionType === 'sales'">
                        <label class="block text-slate-300 mb-2">Customer *</label>
                        <select v-model="form.customer_id" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="">Select Customer</option>
                            <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.company_name }}</option>
                        </select>
                    </div>

                    <div v-else>
                        <label class="block text-slate-300 mb-2">Source *</label>
                        <input v-model="form.source" type="text" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Collection Type *</label>
                        <select v-model="form.collection_type" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="Cash">Cash</option>
                            <option value="Cheque">Cheque</option>
                            <option value="Transfer">Bank Transfer</option>
                            <option value="CPO">CPO</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Bank *</label>
                        <select v-model="form.bank" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="">Select Bank</option>
                            <option v-for="bank in banks" :key="bank.id" :value="bank.bank_name">{{ bank.bank_name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Amount *</label>
                        <input v-model="form.amount" type="number" step="0.01" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div v-if="form.collection_type === 'Cheque'">
                        <label class="block text-slate-300 mb-2">Cheque Number</label>
                        <input v-model="form.cheque_no" type="text"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div v-if="form.collection_type === 'Cheque'">
                        <label class="block text-slate-300 mb-2">Cheque Date</label>
                        <input v-model="form.cheque_date" type="date"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Reference Number</label>
                        <input v-model="form.reference_no" type="text"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Deposit Date</label>
                        <input v-model="form.deposit_date" type="date"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-slate-300 mb-2">Description/Remarks</label>
                        <textarea v-model="form.description" rows="3"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500"></textarea>
                    </div>
                </div>

                <div class="flex gap-4 mt-8">
                    <button type="submit" :disabled="processing"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-lg font-medium disabled:opacity-50">
                        {{ processing ? 'Saving...' : 'Create Collection' }}
                    </button>
                    <a href="/collections" class="bg-slate-600 hover:bg-slate-500 text-white px-6 py-3 rounded-lg">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import Sidebar from '../../Components/Sidebar.vue';

const props = defineProps({
    customers: { type: Array, default: () => [] },
    banks: { type: Array, default: () => [] }
});

const page = usePage();
const urlParams = new URLSearchParams(window.location.search);
const collectionType = urlParams.get('type') || 'sales';

const form = ref({
    collection_no: '',
    collection_date: new Date().toISOString().split('T')[0],
    type: collectionType,
    customer_id: '',
    source: '',
    collection_type: 'Transfer',
    bank: '',
    amount: '',
    cheque_no: '',
    cheque_date: '',
    reference_no: '',
    deposit_date: '',
    description: ''
});

const processing = ref(false);

const submit = () => {
    processing.value = true;
    router.post('/collections', form.value, {
        onFinish: () => processing.value = false
    });
};
</script>

<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="payments" />

        <div class="ml-64 p-8">
            <div class="mb-8">
                <a href="/payments" class="text-slate-400 hover:text-white mb-2 inline-block">&larr; Back to Payments</a>
                <h2 class="text-3xl font-bold text-white">Edit Payment #{{ payment.p_no }}</h2>
            </div>

            <form @submit.prevent="submit" class="bg-slate-800 rounded-xl p-6 max-w-4xl">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-slate-300 mb-2">Payment No *</label>
                        <input v-model="form.p_no" type="text" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Payment Date *</label>
                        <input v-model="form.payment_date" type="date" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Pay To *</label>
                        <input v-model="form.pay_to" type="text" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Supplier</label>
                        <select v-model="form.supplier_id"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="">Select Supplier</option>
                            <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                                {{ supplier.supplier_name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Payment Type *</label>
                        <select v-model="form.p_type" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="Cash">Cash</option>
                            <option value="Cheque">Cheque</option>
                            <option value="Transfer">Transfer</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Net Amount *</label>
                        <input v-model="form.net_amount" type="number" step="0.01" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Bank</label>
                        <input v-model="form.bank" type="text"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Branch</label>
                        <input v-model="form.branch" type="text"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Cheque No</label>
                        <input v-model="form.cheque_no" type="text"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Reference No</label>
                        <input v-model="form.payment_ref_no" type="text"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Status</label>
                        <select v-model="form.status"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="Pending">Pending</option>
                            <option value="Approved">Approved</option>
                            <option value="Paid">Paid</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>
                </div>

                <div class="mt-6">
                    <label class="block text-slate-300 mb-2">Description</label>
                    <textarea v-model="form.payment_description" rows="3"
                        class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500"></textarea>
                </div>

                <div class="flex gap-4 mt-8">
                    <button type="submit" :disabled="processing"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-lg font-medium disabled:opacity-50">
                        {{ processing ? 'Saving...' : 'Update Payment' }}
                    </button>
                    <a href="/payments" class="bg-slate-600 hover:bg-slate-500 text-white px-6 py-3 rounded-lg">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Sidebar from '../../Components/Sidebar.vue';

const props = defineProps({
    payment: Object,
    suppliers: { type: Array, default: () => [] }
});

const form = ref({
    p_no: props.payment.p_no || '',
    payment_date: props.payment.payment_date || '',
    pay_to: props.payment.pay_to || '',
    supplier_id: props.payment.supplier_id || '',
    p_type: props.payment.p_type || 'Cash',
    net_amount: props.payment.net_amount || '',
    bank: props.payment.bank || '',
    branch: props.payment.branch || '',
    cheque_no: props.payment.cheque_no || '',
    payment_ref_no: props.payment.payment_ref_no || '',
    status: props.payment.status || 'Pending',
    payment_description: props.payment.payment_description || ''
});

const processing = ref(false);

const submit = () => {
    processing.value = true;
    router.put('/payments/' + props.payment.id, form.value, {
        onFinish: () => processing.value = false
    });
};
</script>

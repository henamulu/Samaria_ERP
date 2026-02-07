<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="credit-payments" />

        <div class="ml-64 p-8">
            <div class="mb-8">
                <a href="/credit-payments" class="text-slate-400 hover:text-white mb-2 inline-block">&larr; Back to Credit Payments</a>
                <h2 class="text-3xl font-bold text-white">Create Credit Payment Request</h2>
            </div>

            <form @submit.prevent="submit" class="bg-slate-800 rounded-xl p-6 max-w-4xl">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-slate-300 mb-2">Request Number *</label>
                        <input v-model="form.request_no" type="text" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Request Date *</label>
                        <input v-model="form.request_date" type="date" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Supplier *</label>
                        <select v-model="form.supplier_id" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="">Select Supplier</option>
                            <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.company_name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Purchase Order</label>
                        <select v-model="form.po_id"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="">Select PO</option>
                            <option v-for="po in purchaseOrders" :key="po.id" :value="po.id">{{ po.po_no }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Invoice Number</label>
                        <input v-model="form.invoice_no" type="text"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Invoice Date</label>
                        <input v-model="form.invoice_date" type="date"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Amount *</label>
                        <input v-model="form.amount" type="number" step="0.01" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">VAT Amount</label>
                        <input v-model="form.vat_amount" type="number" step="0.01"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Withholding Tax</label>
                        <input v-model="form.withholding_tax" type="number" step="0.01"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Net Amount</label>
                        <input :value="calculatedNetAmount" type="text" readonly
                            class="w-full bg-slate-600 border-0 rounded-lg px-4 py-3 text-emerald-400 font-bold" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Due Date *</label>
                        <input v-model="form.due_date" type="date" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Payment Method</label>
                        <select v-model="form.payment_method"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="Transfer">Bank Transfer</option>
                            <option value="Cheque">Cheque</option>
                            <option value="Cash">Cash</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Bank</label>
                        <select v-model="form.bank"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="">Select Bank</option>
                            <option v-for="bank in banks" :key="bank.id" :value="bank.bank_name">{{ bank.bank_name }}</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-slate-300 mb-2">Remarks</label>
                        <textarea v-model="form.remarks" rows="3"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500"></textarea>
                    </div>
                </div>

                <div class="flex gap-4 mt-8">
                    <button type="submit" :disabled="processing"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-lg font-medium disabled:opacity-50">
                        {{ processing ? 'Saving...' : 'Create Request' }}
                    </button>
                    <a href="/credit-payments" class="bg-slate-600 hover:bg-slate-500 text-white px-6 py-3 rounded-lg">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import Sidebar from '../../Components/Sidebar.vue';

const props = defineProps({
    suppliers: { type: Array, default: () => [] },
    purchaseOrders: { type: Array, default: () => [] },
    banks: { type: Array, default: () => [] }
});

const form = ref({
    request_no: '',
    request_date: new Date().toISOString().split('T')[0],
    supplier_id: '',
    po_id: '',
    invoice_no: '',
    invoice_date: '',
    amount: '',
    vat_amount: 0,
    withholding_tax: 0,
    due_date: '',
    payment_method: 'Transfer',
    bank: '',
    remarks: ''
});

const processing = ref(false);

const calculatedNetAmount = computed(() => {
    const amount = parseFloat(form.value.amount) || 0;
    const vat = parseFloat(form.value.vat_amount) || 0;
    const wht = parseFloat(form.value.withholding_tax) || 0;
    return (amount + vat - wht).toFixed(2);
});

const submit = () => {
    processing.value = true;
    form.value.net_amount = calculatedNetAmount.value;
    router.post('/credit-payments', form.value, {
        onFinish: () => processing.value = false
    });
};
</script>

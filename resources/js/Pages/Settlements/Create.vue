<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="settlements" />

        <div class="ml-64 p-8">
            <div class="mb-8">
                <a href="/settlements" class="text-slate-400 hover:text-white mb-2 inline-block">&larr; Back to Settlements</a>
                <h2 class="text-3xl font-bold text-white">Create {{ typeLabels[settlementType] }}</h2>
            </div>

            <form @submit.prevent="submit" class="bg-slate-800 rounded-xl p-6 max-w-4xl">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-slate-300 mb-2">Settlement Number *</label>
                        <input v-model="form.settlement_no" type="text" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Settlement Date *</label>
                        <input v-model="form.settlement_date" type="date" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <!-- PO Settlement Fields -->
                    <div v-if="settlementType === 'pos'">
                        <label class="block text-slate-300 mb-2">Purchase Order *</label>
                        <select v-model="form.po_id" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="">Select PO</option>
                            <option v-for="po in purchaseOrders" :key="po.id" :value="po.id">{{ po.po_no }} - {{ po.supplier_name }}</option>
                        </select>
                    </div>

                    <!-- Advance/Transporter Settlement Fields -->
                    <div v-if="settlementType === 'advance' || settlementType === 'transporter'">
                        <label class="block text-slate-300 mb-2">{{ settlementType === 'transporter' ? 'Transporter' : 'Recipient' }} *</label>
                        <select v-model="form.recipient_id" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="">Select</option>
                            <option v-for="r in recipients" :key="r.id" :value="r.id">{{ r.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Reference Number</label>
                        <input v-model="form.reference_no" type="text"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Original Amount *</label>
                        <input v-model="form.original_amount" type="number" step="0.01" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Settled Amount *</label>
                        <input v-model="form.settled_amount" type="number" step="0.01" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Balance</label>
                        <input :value="calculatedBalance" type="text" readonly
                            class="w-full bg-slate-600 border-0 rounded-lg px-4 py-3 font-bold"
                            :class="calculatedBalance == 0 ? 'text-green-400' : 'text-red-400'" />
                    </div>

                    <div v-if="settlementType === 'pcs'">
                        <label class="block text-slate-300 mb-2">Petty Cash Account</label>
                        <input v-model="form.petty_cash_account" type="text"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
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
                        {{ processing ? 'Saving...' : 'Create Settlement' }}
                    </button>
                    <a href="/settlements" class="bg-slate-600 hover:bg-slate-500 text-white px-6 py-3 rounded-lg">Cancel</a>
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
    purchaseOrders: { type: Array, default: () => [] },
    recipients: { type: Array, default: () => [] },
    banks: { type: Array, default: () => [] }
});

const urlParams = new URLSearchParams(window.location.search);
const settlementType = urlParams.get('type') || 'pos';

const typeLabels = {
    pos: 'PO Settlement',
    pcs: 'Petty Cash Settlement',
    crs: 'CR Settlement',
    advance: 'Advance Settlement',
    transporter: 'Transporter Settlement'
};

const form = ref({
    settlement_no: '',
    settlement_date: new Date().toISOString().split('T')[0],
    type: settlementType,
    po_id: '',
    recipient_id: '',
    reference_no: '',
    original_amount: '',
    settled_amount: '',
    petty_cash_account: '',
    bank: '',
    remarks: ''
});

const processing = ref(false);

const calculatedBalance = computed(() => {
    const original = parseFloat(form.value.original_amount) || 0;
    const settled = parseFloat(form.value.settled_amount) || 0;
    return (original - settled).toFixed(2);
});

const submit = () => {
    processing.value = true;
    form.value.balance = calculatedBalance.value;
    router.post('/settlements', form.value, {
        onFinish: () => processing.value = false
    });
};
</script>

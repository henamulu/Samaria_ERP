<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="payment-refunds" />

        <div class="ml-64 p-8">
            <div class="mb-8">
                <a href="/payment-refunds" class="text-slate-400 hover:text-white mb-2 inline-block">&larr; Back to Payment Refunds</a>
                <h2 class="text-3xl font-bold text-white">Create Payment Refund</h2>
            </div>

            <form @submit.prevent="submit" class="bg-slate-800 rounded-xl p-6 max-w-4xl">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-slate-300 mb-2">Refund Number *</label>
                        <input v-model="form.refund_no" type="text" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Refund Date *</label>
                        <input v-model="form.refund_date" type="date" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Original Payment</label>
                        <select v-model="form.payment_id"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="">Select Payment (optional)</option>
                            <option v-for="p in payments" :key="p.id" :value="p.id">{{ p.p_no }} - {{ p.pay_to }} ({{ formatCurrency(p.net_amount) }})</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Payee Name *</label>
                        <input v-model="form.payee_name" type="text" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Amount *</label>
                        <input v-model="form.amount" type="number" step="0.01" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-slate-300 mb-2">Reason *</label>
                        <textarea v-model="form.reason" rows="3" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500"></textarea>
                    </div>
                </div>

                <div class="flex gap-4 mt-8">
                    <button type="submit" :disabled="processing"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-lg font-medium disabled:opacity-50">
                        {{ processing ? 'Saving...' : 'Create Refund' }}
                    </button>
                    <a href="/payment-refunds" class="bg-slate-600 hover:bg-slate-500 text-white px-6 py-3 rounded-lg">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import Sidebar from '../../Components/Sidebar.vue';

const props = defineProps({
    payments: { type: Array, default: () => [] }
});

const form = ref({
    refund_no: '',
    refund_date: new Date().toISOString().split('T')[0],
    payment_id: '',
    payee_name: '',
    amount: '',
    reason: ''
});

const processing = ref(false);

const formatCurrency = (val) => (parseFloat(val) || 0).toLocaleString('en-US', { minimumFractionDigits: 2 });

// Auto-fill payee name when a payment is selected
watch(() => form.value.payment_id, (newVal) => {
    if (newVal) {
        const payment = props.payments.find(p => p.id == newVal);
        if (payment) {
            form.value.payee_name = payment.pay_to || '';
            if (!form.value.amount) {
                form.value.amount = payment.net_amount || '';
            }
        }
    }
});

const submit = () => {
    processing.value = true;
    router.post('/payment-refunds', form.value, {
        onFinish: () => processing.value = false
    });
};
</script>

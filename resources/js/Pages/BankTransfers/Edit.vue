<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="bank-transfers" />

        <div class="ml-64 p-8">
            <div class="mb-8">
                <a href="/bank-transfers" class="text-slate-400 hover:text-white mb-2 inline-block">&larr; Back to Transfers</a>
                <h2 class="text-3xl font-bold text-white">Edit Bank Transfer #{{ transfer.t_no }}</h2>
            </div>

            <form @submit.prevent="submit" class="bg-slate-800 rounded-xl p-6 max-w-4xl">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-slate-300 mb-2">BTR Number *</label>
                        <input v-model="form.btr_no" type="text" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Transfer Date *</label>
                        <input v-model="form.transfer_date" type="date" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div class="md:col-span-2">
                        <h3 class="text-lg font-semibold text-emerald-400 mb-4 border-b border-slate-700 pb-2">From Account</h3>
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">From Bank *</label>
                        <select v-model="form.from_bank" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="">Select Bank</option>
                            <option v-for="bank in banks" :key="bank.id" :value="bank.bank_name">{{ bank.bank_name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">From Account Number *</label>
                        <input v-model="form.from_account" type="text" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div class="md:col-span-2">
                        <h3 class="text-lg font-semibold text-blue-400 mb-4 border-b border-slate-700 pb-2">To Account</h3>
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">To Bank *</label>
                        <select v-model="form.to_bank" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="">Select Bank</option>
                            <option v-for="bank in banks" :key="bank.id" :value="bank.bank_name">{{ bank.bank_name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">To Account Number *</label>
                        <input v-model="form.to_account" type="text" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div class="md:col-span-2">
                        <h3 class="text-lg font-semibold text-amber-400 mb-4 border-b border-slate-700 pb-2">Transfer Details</h3>
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Amount *</label>
                        <input v-model="form.amount" type="number" step="0.01" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>
                </div>

                <div class="flex gap-4 mt-8">
                    <button type="submit" :disabled="processing"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-lg font-medium disabled:opacity-50">
                        {{ processing ? 'Saving...' : 'Update Transfer' }}
                    </button>
                    <a href="/bank-transfers" class="bg-slate-600 hover:bg-slate-500 text-white px-6 py-3 rounded-lg">Cancel</a>
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
    transfer: Object,
    banks: { type: Array, default: () => [] }
});

const form = ref({
    btr_no: props.transfer.t_no || '',
    transfer_date: props.transfer.t_date || '',
    from_bank: props.transfer.from_bank || '',
    from_account: props.transfer.transfer_from || '',
    to_bank: props.transfer.to_bank || '',
    to_account: props.transfer.transfer_to || '',
    amount: props.transfer.i_amount || '',
});

const processing = ref(false);

const submit = () => {
    processing.value = true;
    router.put('/bank-transfers/' + props.transfer.id, form.value, {
        onFinish: () => processing.value = false
    });
};
</script>

<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="bank-reconciliation" />

        <div class="ml-64 p-8">
            <div class="mb-8">
                <a href="/bank-reconciliation" class="text-slate-400 hover:text-white mb-2 inline-block">&larr; Back to Reconciliations</a>
                <h2 class="text-3xl font-bold text-white">Create Bank Reconciliation</h2>
            </div>

            <form @submit.prevent="submit" class="bg-slate-800 rounded-xl p-6 max-w-4xl">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-slate-300 mb-2">BR Number *</label>
                        <input v-model="form.br_no" type="text" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Bank *</label>
                        <select v-model="form.bank_name" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="">Select Bank</option>
                            <option v-for="bank in banks" :key="bank.id" :value="bank.bank_name">{{ bank.bank_name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Period Month *</label>
                        <select v-model="form.period_month" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="1">January</option>
                            <option value="2">February</option>
                            <option value="3">March</option>
                            <option value="4">April</option>
                            <option value="5">May</option>
                            <option value="6">June</option>
                            <option value="7">July</option>
                            <option value="8">August</option>
                            <option value="9">September</option>
                            <option value="10">October</option>
                            <option value="11">November</option>
                            <option value="12">December</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Period Year *</label>
                        <select v-model="form.period_year" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="">Select Year</option>
                            <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Bank Statement Balance *</label>
                        <input v-model="form.bank_balance" type="number" step="0.01" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Book Balance *</label>
                        <input v-model="form.book_balance" type="number" step="0.01" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Difference</label>
                        <input :value="calculatedDifference" type="text" readonly
                            class="w-full bg-slate-600 border-0 rounded-lg px-4 py-3 font-bold"
                            :class="calculatedDifference == 0 ? 'text-green-400' : 'text-red-400'" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Outstanding Cheques</label>
                        <input v-model="form.outstanding_cheques" type="number" step="0.01"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Deposits in Transit</label>
                        <input v-model="form.deposits_in_transit" type="number" step="0.01"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Bank Charges</label>
                        <input v-model="form.bank_charges" type="number" step="0.01"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
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
                        {{ processing ? 'Saving...' : 'Create Reconciliation' }}
                    </button>
                    <a href="/bank-reconciliation" class="bg-slate-600 hover:bg-slate-500 text-white px-6 py-3 rounded-lg">Cancel</a>
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
    banks: { type: Array, default: () => [] }
});

const currentYear = new Date().getFullYear();
const startYear = 2000;
const endYear = currentYear + 10;
const years = Array.from({ length: endYear - startYear + 1 }, (_, i) => endYear - i);

const form = ref({
    br_no: '',
    bank_name: '',
    period_month: new Date().getMonth() + 1,
    period_year: new Date().getFullYear(),
    bank_balance: '',
    book_balance: '',
    outstanding_cheques: 0,
    deposits_in_transit: 0,
    bank_charges: 0,
    remarks: ''
});

const processing = ref(false);

const calculatedDifference = computed(() => {
    const bank = parseFloat(form.value.bank_balance) || 0;
    const book = parseFloat(form.value.book_balance) || 0;
    return (bank - book).toFixed(2);
});

const submit = () => {
    processing.value = true;
    form.value.difference = calculatedDifference.value;
    router.post('/bank-reconciliation', form.value, {
        onFinish: () => processing.value = false
    });
};
</script>

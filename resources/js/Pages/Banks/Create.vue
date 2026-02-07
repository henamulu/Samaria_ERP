<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="banks" />

        <div class="ml-64 p-8">
            <div class="mb-8">
                <a href="/banks" class="text-slate-400 hover:text-white mb-2 inline-block">&larr; Back to Banks</a>
                <h2 class="text-3xl font-bold text-white">Create Bank</h2>
            </div>

            <form @submit.prevent="submit" class="bg-slate-800 rounded-xl p-6 max-w-4xl">
                <!-- Basic Info -->
                <h3 class="text-lg font-semibold text-emerald-400 mb-4 border-b border-slate-700 pb-2">Basic Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-slate-300 mb-2">Bank Name *</label>
                        <input v-model="form.bank_name" type="text" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>
                    <div>
                        <label class="block text-slate-300 mb-2">Branch Name *</label>
                        <input v-model="form.branch_name" type="text" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>
                    <div>
                        <label class="block text-slate-300 mb-2">Account Number *</label>
                        <input v-model="form.bank_ac_no" type="text" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>
                    <div>
                        <label class="block text-slate-300 mb-2">Contact Person</label>
                        <input v-model="form.contact_person" type="text"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>
                    <div>
                        <label class="block text-slate-300 mb-2">Beginning Balance</label>
                        <input v-model="form.beginning_balance" type="number" step="0.01" value="0"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>
                    <div>
                        <label class="block text-slate-300 mb-2">Status</label>
                        <select v-model="form.bank_status"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <!-- Term Loan -->
                <h3 class="text-lg font-semibold text-blue-400 mb-4 border-b border-slate-700 pb-2">Term Loan</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-slate-300 mb-2">Has Term Loan</label>
                        <select v-model="form.have_termloan"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="No">No</option>
                            <option value="Yes">Yes</option>
                        </select>
                    </div>
                    <div v-if="form.have_termloan === 'Yes'">
                        <label class="block text-slate-300 mb-2">TL Amount</label>
                        <input v-model="form.tl_amount" type="number" step="0.01"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>
                    <div v-if="form.have_termloan === 'Yes'">
                        <label class="block text-slate-300 mb-2">TL Interest Rate (%)</label>
                        <input v-model="form.tl_interest_rate" type="number" step="0.01"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>
                </div>

                <!-- Overdraft -->
                <h3 class="text-lg font-semibold text-amber-400 mb-4 border-b border-slate-700 pb-2">Overdraft</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-slate-300 mb-2">Has Overdraft</label>
                        <select v-model="form.have_overdraft"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="No">No</option>
                            <option value="Yes">Yes</option>
                        </select>
                    </div>
                    <div v-if="form.have_overdraft === 'Yes'">
                        <label class="block text-slate-300 mb-2">OD Limit</label>
                        <input v-model="form.od_amount" type="number" step="0.01"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>
                    <div v-if="form.have_overdraft === 'Yes'">
                        <label class="block text-slate-300 mb-2">OD Interest Rate (%)</label>
                        <input v-model="form.od_interest_rate" type="number" step="0.01"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>
                    <div v-if="form.have_overdraft === 'Yes'">
                        <label class="block text-slate-300 mb-2">OD Current Balance</label>
                        <input v-model="form.od_balance" type="number" step="0.01"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>
                </div>

                <div class="flex gap-4 mt-8">
                    <button type="submit" :disabled="processing"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-lg font-medium disabled:opacity-50">
                        {{ processing ? 'Saving...' : 'Create Bank' }}
                    </button>
                    <a href="/banks" class="bg-slate-600 hover:bg-slate-500 text-white px-6 py-3 rounded-lg">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Sidebar from '../../Components/Sidebar.vue';

const form = ref({
    bank_name:         '',
    branch_name:       '',
    bank_ac_no:        '',
    contact_person:    '',
    beginning_balance: 0,
    bank_status:       'Active',
    have_termloan:     'No',
    tl_amount:         0,
    tl_interest_rate:  0,
    have_overdraft:    'No',
    od_amount:         0,
    od_interest_rate:  0,
    od_balance:        0,
});

const processing = ref(false);

const submit = () => {
    processing.value = true;
    router.post('/banks', form.value, {
        onFinish: () => processing.value = false
    });
};
</script>

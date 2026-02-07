<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="insurance-policies" />
        <div class="ml-64 p-8">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h2 class="text-3xl font-bold text-white mb-2">Edit Insurance Policy</h2>
                    <p class="text-slate-400">Update insurance policy information</p>
                </div>
                <router-link to="/insurance-policies" class="text-emerald-400 hover:text-emerald-300">← Back to List</router-link>
            </div>

            <div class="bg-slate-800 rounded-xl p-6 max-w-4xl">
                <form @submit.prevent="submitForm">
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-slate-400 text-sm mb-2">Policy No <span class="text-red-400">*</span></label>
                                <input v-model="form.i_no" type="text" required class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                            </div>

                            <div>
                                <label class="block text-slate-400 text-sm mb-2">Insurance Company <span class="text-red-400">*</span></label>
                                <select v-model="form.insurance_company" required class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white">
                                    <option value="">Select Insurance Company</option>
                                    <option v-for="ic in insuranceCompanies" :key="ic.insurance_name" :value="ic.insurance_name">{{ ic.insurance_name }}</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-slate-400 text-sm mb-2">Insurance Type <span class="text-red-400">*</span></label>
                                <input v-model="form.insurance_type" type="text" required class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                            </div>

                            <div>
                                <label class="block text-slate-400 text-sm mb-2">Policy Type <span class="text-red-400">*</span></label>
                                <input v-model="form.p_type" type="text" required class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                            </div>

                            <div>
                                <label class="block text-slate-400 text-sm mb-2">Insured <span class="text-red-400">*</span></label>
                                <textarea v-model="form.insured" required rows="3" class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white"></textarea>
                            </div>

                            <div>
                                <label class="block text-slate-400 text-sm mb-2">C No <span class="text-red-400">*</span></label>
                                <input v-model="form.c_no" type="text" required class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                            </div>

                            <div>
                                <label class="block text-slate-400 text-sm mb-2">P No <span class="text-red-400">*</span></label>
                                <input v-model="form.p_no" type="text" required class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                            </div>

                            <div>
                                <label class="block text-slate-400 text-sm mb-2">Issued Date <span class="text-red-400">*</span></label>
                                <input v-model="form.issued_date" type="date" required lang="en" class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                            </div>

                            <div>
                                <label class="block text-slate-400 text-sm mb-2">Period <span class="text-red-400">*</span></label>
                                <input v-model="form.period" type="text" required class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" placeholder="e.g., 1 Year" />
                            </div>

                            <div>
                                <label class="block text-slate-400 text-sm mb-2">Fund Tariff <span class="text-red-400">*</span></label>
                                <input v-model.number="form.fund_tariff" type="number" step="0.01" min="0" required class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                            </div>

                            <div>
                                <label class="block text-slate-400 text-sm mb-2">Premium Tariff <span class="text-red-400">*</span></label>
                                <input v-model.number="form.premium_tariff" type="number" step="0.01" min="0" required class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                            </div>

                            <div>
                                <label class="block text-slate-400 text-sm mb-2">Notification</label>
                                <input v-model="form.notification" type="text" class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                            </div>

                            <div>
                                <label class="block text-slate-400 text-sm mb-2">Previous</label>
                                <input v-model="form.previous" type="text" class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex gap-3">
                        <button type="submit" :disabled="loading" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-3 rounded-lg disabled:opacity-50">
                            {{ loading ? 'Updating...' : 'Update Insurance Policy' }}
                        </button>
                        <router-link to="/insurance-policies" class="flex-1 bg-slate-600 hover:bg-slate-500 text-white px-4 py-3 rounded-lg text-center">Cancel</router-link>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import Sidebar from '../../Components/Sidebar.vue';

const props = defineProps({
    policy: Object,
    insuranceCompanies: Array
});

const loading = ref(false);
const form = ref({
    i_no: '',
    insurance_type: '',
    p_type: '',
    insurance_company: '',
    insured: '',
    c_no: '',
    p_no: '',
    issued_date: '',
    period: '',
    fund_tariff: 0,
    premium_tariff: 0,
    notification: '',
    previous: ''
});

onMounted(() => {
    if (props.policy) {
        form.value = {
            i_no: props.policy.i_no || '',
            insurance_type: props.policy.insurance_type || '',
            p_type: props.policy.p_type || '',
            insurance_company: props.policy.insurance_company || '',
            insured: props.policy.insured || '',
            c_no: props.policy.c_no || '',
            p_no: props.policy.p_no || '',
            issued_date: props.policy.issued_date || '',
            period: props.policy.period || '',
            fund_tariff: parseFloat(props.policy.fund_tariff) || 0,
            premium_tariff: parseFloat(props.policy.premium_tariff) || 0,
            notification: props.policy.notification || '',
            previous: props.policy.previous || ''
        };
    }
});

const submitForm = () => {
    loading.value = true;
    router.put(`/insurance-policies/${props.policy.id}`, form.value, {
        preserveState: false,
        onFinish: () => {
            loading.value = false;
        }
    });
};
</script>

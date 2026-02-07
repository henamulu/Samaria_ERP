<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="budgets" />
        <div class="ml-64 p-8">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h2 class="text-3xl font-bold text-white mb-2">Create Budget</h2>
                    <p class="text-slate-400">Create a budget from approved budget request</p>
                </div>
                <router-link to="/budgets" class="text-emerald-400 hover:text-emerald-300">← Back to List</router-link>
            </div>

            <div class="bg-slate-800 rounded-xl p-6 max-w-2xl">
                <form @submit.prevent="submitForm">
                    <div class="space-y-6">
                        <div>
                            <label class="block text-slate-400 text-sm mb-2">Budget Request <span class="text-red-400">*</span></label>
                            <select v-model="form.b_id" @change="onBudgetRequestChange" required class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white">
                                <option value="">Select Budget Request</option>
                                <option v-for="br in availableRequests" :key="br.id" :value="br.id">
                                    BR {{ br.b_no || br.id }} - {{ br.item }} ({{ formatCurrency(br.balance) }} remaining)
                                </option>
                            </select>
                            <p v-if="selectedRequest" class="text-sm text-slate-400 mt-2">
                                Item: {{ selectedRequest.item }} | Quantity: {{ selectedRequest.quantity }} {{ selectedRequest.unit }} | 
                                Amount: {{ formatCurrency(selectedRequest.amount) }} | Balance: {{ formatCurrency(selectedRequest.balance) }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-slate-400 text-sm mb-2">Project <span class="text-red-400">*</span></label>
                            <select v-model="form.project" required class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white">
                                <option value="">Select Project</option>
                                <option v-for="project in projects" :key="project.id" :value="project.project">
                                    {{ project.project }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-slate-400 text-sm mb-2">Budget No <span class="text-red-400">*</span></label>
                            <input v-model="form.b_no" type="text" required class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" placeholder="e.g., BUD-001" />
                        </div>

                        <div v-if="selectedRequest" class="p-4 bg-slate-700 rounded-lg">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-slate-400">Budget Amount:</span>
                                <span class="text-emerald-400 font-bold text-xl">{{ formatCurrency(selectedRequest.balance) }}</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-2">This amount will be allocated to the selected project.</p>
                        </div>
                    </div>

                    <div class="mt-6 flex gap-3">
                        <button type="submit" :disabled="loading || !selectedRequest" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-3 rounded-lg disabled:opacity-50">
                            {{ loading ? 'Creating...' : 'Create Budget' }}
                        </button>
                        <router-link to="/budgets" class="flex-1 bg-slate-600 hover:bg-slate-500 text-white px-4 py-3 rounded-lg text-center">Cancel</router-link>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import Sidebar from '../../Components/Sidebar.vue';

const props = defineProps({
    budgetRequests: Array,
    projects: Array,
    b_id: String
});

const loading = ref(false);
const form = ref({
    b_id: '',
    project: '',
    b_no: ''
});

const selectedRequest = ref(null);

onMounted(() => {
    if (props.b_id) {
        form.value.b_id = props.b_id;
        onBudgetRequestChange();
    }
});

const availableRequests = computed(() => {
    return props.budgetRequests.filter(br => br.status === 'Done' && parseFloat(br.balance) > 0);
});

const formatCurrency = (num) => num ? 'ETB ' + parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 2 }) : 'ETB 0.00';

const onBudgetRequestChange = () => {
    if (form.value.b_id) {
        selectedRequest.value = props.budgetRequests.find(br => br.id == form.value.b_id);
    } else {
        selectedRequest.value = null;
    }
};

const submitForm = () => {
    loading.value = true;
    router.post('/budgets', form.value, {
        preserveState: false,
        onFinish: () => {
            loading.value = false;
        }
    });
};
</script>

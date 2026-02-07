<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="budgets" />
        <div class="ml-64 p-8">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h2 class="text-3xl font-bold text-white mb-2">Create Budget Request</h2>
                    <p class="text-slate-400">Add a new budget request</p>
                </div>
                <router-link to="/budget-requests" class="text-emerald-400 hover:text-emerald-300">← Back to List</router-link>
            </div>

            <div class="bg-slate-800 rounded-xl p-6 max-w-2xl">
                <form @submit.prevent="submitForm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-slate-400 text-sm mb-2">Item <span class="text-red-400">*</span></label>
                            <select v-model="form.item_id" @change="onItemChange" required class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white">
                                <option value="">Select Item</option>
                                <option v-for="item in items" :key="item.id" :value="item.id">{{ item.item }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-slate-400 text-sm mb-2">Unit <span class="text-red-400">*</span></label>
                            <select v-model="form.unit" required class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white">
                                <option value="">Select Unit</option>
                                <option v-for="unit in units" :key="unit.id" :value="unit.unit">{{ unit.unit }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-slate-400 text-sm mb-2">Unit Price <span class="text-red-400">*</span></label>
                            <input v-model.number="form.unit_price" type="number" step="0.01" min="0" required class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                        </div>

                        <div>
                            <label class="block text-slate-400 text-sm mb-2">Quantity <span class="text-red-400">*</span></label>
                            <input v-model.number="form.quantity" type="number" step="0.01" min="0" required class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                        </div>

                        <div>
                            <label class="block text-slate-400 text-sm mb-2">Profit (%)</label>
                            <input v-model.number="form.profit" type="number" step="0.01" min="0" max="100" class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                        </div>

                        <div>
                            <label class="block text-slate-400 text-sm mb-2">Overhead (%)</label>
                            <input v-model.number="form.overhead" type="number" step="0.01" min="0" max="100" class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                        </div>
                    </div>

                    <div class="mt-6 p-4 bg-slate-700 rounded-lg">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-400">Subtotal:</span>
                            <span class="text-white font-medium">{{ formatCurrency(subtotal) }}</span>
                        </div>
                        <div v-if="form.profit" class="flex justify-between items-center mt-2">
                            <span class="text-slate-400">After Profit ({{ form.profit }}%):</span>
                            <span class="text-white font-medium">{{ formatCurrency(afterProfit) }}</span>
                        </div>
                        <div v-if="form.overhead" class="flex justify-between items-center mt-2">
                            <span class="text-slate-400">After Overhead ({{ form.overhead }}%):</span>
                            <span class="text-white font-medium">{{ formatCurrency(afterOverhead) }}</span>
                        </div>
                        <div class="flex justify-between items-center mt-4 pt-4 border-t border-slate-600">
                            <span class="text-slate-300 font-semibold">Total Amount:</span>
                            <span class="text-emerald-400 font-bold text-xl">{{ formatCurrency(totalAmount) }}</span>
                        </div>
                    </div>

                    <div class="mt-6 flex gap-3">
                        <button type="submit" :disabled="loading" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-3 rounded-lg disabled:opacity-50">
                            {{ loading ? 'Creating...' : 'Create Budget Request' }}
                        </button>
                        <router-link to="/budget-requests" class="flex-1 bg-slate-600 hover:bg-slate-500 text-white px-4 py-3 rounded-lg text-center">Cancel</router-link>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import Sidebar from '../../Components/Sidebar.vue';

const props = defineProps({
    items: Array,
    units: Array
});

const loading = ref(false);
const form = ref({
    item_id: '',
    unit: '',
    unit_price: 0,
    quantity: 0,
    profit: 0,
    overhead: 0
});

const subtotal = computed(() => {
    return form.value.unit_price * form.value.quantity;
});

const afterProfit = computed(() => {
    if (!form.value.profit) return subtotal.value;
    return subtotal.value * (1 + form.value.profit / 100);
});

const afterOverhead = computed(() => {
    if (!form.value.overhead) return afterProfit.value;
    return afterProfit.value * (1 + form.value.overhead / 100);
});

const totalAmount = computed(() => {
    return afterOverhead.value;
});

const formatCurrency = (num) => num ? 'ETB ' + parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 2 }) : 'ETB 0.00';

const onItemChange = () => {
    const item = props.items.find(i => i.id == form.value.item_id);
    if (item && item.unit) {
        form.value.unit = item.unit;
    }
};

const submitForm = () => {
    loading.value = true;
    router.post('/budget-requests', form.value, {
        preserveState: false,
        onFinish: () => {
            loading.value = false;
        }
    });
};
</script>

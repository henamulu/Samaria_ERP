<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="customer-agreements" />

        <div class="ml-64 p-8">
            <div class="mb-8">
                <a href="/customer-agreements" class="text-slate-400 hover:text-white mb-2 inline-block">&larr; Back to Agreements</a>
                <h2 class="text-3xl font-bold text-white">Create Customer Agreement</h2>
            </div>

            <form @submit.prevent="submit" class="bg-slate-800 rounded-xl p-6 max-w-4xl">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-slate-300 mb-2">Agreement No *</label>
                        <input v-model="form.agg_no" type="text" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Customer *</label>
                        <select v-model="form.customer" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="">Select Customer</option>
                            <option v-for="c in customers" :key="c.id" :value="c.id">
                                {{ c.company_name || (c.firstname + ' ' + c.lastname) }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Item *</label>
                        <input v-model="form.item" type="text" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Unit *</label>
                        <select v-model="form.unit" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="M3">M3</option>
                            <option value="KG">KG</option>
                            <option value="PCS">PCS</option>
                            <option value="TON">TON</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Unit Price *</label>
                        <input v-model="form.unit_price" type="number" step="0.01" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Tax Percentage</label>
                        <input v-model="form.tax_p" type="text" placeholder="e.g., 15%"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Transport Unit</label>
                        <select v-model="form.transport_unit"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="">None</option>
                            <option value="M3">M3</option>
                            <option value="KM">KM</option>
                            <option value="TRIP">TRIP</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Transport Unit Price</label>
                        <input v-model="form.t_unit_price" type="number" step="0.01"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Status</label>
                        <select v-model="form.status"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="Done">Done</option>
                            <option value="Checked">Checked</option>
                            <option value="Approved">Approved</option>
                        </select>
                    </div>
                </div>

                <div class="flex gap-4 mt-8">
                    <button type="submit" :disabled="processing"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-lg font-medium disabled:opacity-50">
                        {{ processing ? 'Saving...' : 'Create Agreement' }}
                    </button>
                    <a href="/customer-agreements" class="bg-slate-600 hover:bg-slate-500 text-white px-6 py-3 rounded-lg">Cancel</a>
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
    customers: { type: Array, default: () => [] }
});

const form = ref({
    agg_no: '',
    customer: '',
    item: '',
    unit: 'M3',
    unit_price: '',
    tax_p: '',
    transport_unit: '',
    t_unit_price: '',
    status: 'Done'
});

const processing = ref(false);

const submit = () => {
    processing.value = true;
    router.post('/customer-agreements', form.value, {
        onFinish: () => processing.value = false
    });
};
</script>

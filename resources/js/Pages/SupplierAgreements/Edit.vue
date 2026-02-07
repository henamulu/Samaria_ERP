<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="supplier-agreements" />

        <div class="ml-64 p-8">
            <div class="mb-8">
                <a href="/supplier-agreements" class="text-slate-400 hover:text-white mb-2 inline-block">&larr; Back to Agreements</a>
                <h2 class="text-3xl font-bold text-white">Edit Agreement #{{ agreement.agg_no }}</h2>
            </div>

            <form @submit.prevent="submit" class="bg-slate-800 rounded-xl p-6 max-w-4xl">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-slate-300 mb-2">Agreement No *</label>
                        <input v-model="form.agg_no" type="text" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Supplier *</label>
                        <select v-model="form.supplier" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="">Select Supplier</option>
                            <option v-for="s in suppliers" :key="s.id" :value="s.id">
                                {{ s.supplier_name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Item *</label>
                        <input v-model="form.item" type="text" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Invoice Type</label>
                        <select v-model="form.invoice_type"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="VAT">VAT</option>
                            <option value="Non-VAT">Non-VAT</option>
                            <option value="Both">Both</option>
                        </select>
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
                        <label class="block text-slate-300 mb-2">Unit Price (before VAT) *</label>
                        <input v-model="form.unit_price" type="number" step="0.01" required
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
                        {{ processing ? 'Saving...' : 'Update Agreement' }}
                    </button>
                    <a href="/supplier-agreements" class="bg-slate-600 hover:bg-slate-500 text-white px-6 py-3 rounded-lg">Cancel</a>
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
    agreement: Object,
    suppliers: { type: Array, default: () => [] }
});

const form = ref({
    agg_no: props.agreement.agg_no || '',
    supplier: props.agreement.supplier || '',
    item: props.agreement.item || '',
    invoice_type: props.agreement.invoice_type || 'VAT',
    unit: props.agreement.unit || 'M3',
    unit_price: props.agreement.unit_price || '',
    status: props.agreement.status || 'Done'
});

const processing = ref(false);

const submit = () => {
    processing.value = true;
    router.put('/supplier-agreements/' + props.agreement.id, form.value, {
        onFinish: () => processing.value = false
    });
};
</script>

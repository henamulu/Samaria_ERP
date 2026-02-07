<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="proforma-invoices" />

        <div class="ml-64 p-8">
            <div class="mb-8">
                <a href="/proforma-invoices" class="text-slate-400 hover:text-white mb-2 inline-block">&larr; Back to Proforma Invoices</a>
                <h2 class="text-3xl font-bold text-white">Edit Proforma Invoice</h2>
            </div>

            <form @submit.prevent="submit" class="bg-slate-800 rounded-xl p-6 max-w-4xl">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-slate-300 mb-2">PI Number *</label>
                        <input v-model="form.pi_no" type="text" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Customer *</label>
                        <select v-model="form.customer" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="">Select Customer</option>
                            <option v-for="c in customers" :key="c.id" :value="c.id">
                                {{ c.company_name || c.firstname + ' ' + c.lastname }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Item *</label>
                        <select v-model="form.item_id" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="">Select Item</option>
                            <option v-for="i in items" :key="i.id" :value="i.id">{{ i.item }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Quantity *</label>
                        <input v-model="form.quantity" type="number" step="0.01" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Unit</label>
                        <input v-model="form.unit" type="text"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Unit Price *</label>
                        <input v-model="form.unit_price" type="number" step="0.01" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Tax %</label>
                        <input v-model="form.tax_p" type="number" step="0.01"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Line Total</label>
                        <input :value="lineTotal" type="text" readonly
                            class="w-full bg-slate-600 border-0 rounded-lg px-4 py-3 text-white" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Validity (Days)</label>
                        <input v-model="form.validity" type="number"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Delivery Time</label>
                        <input v-model="form.d_time" type="date"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Term of Payment</label>
                        <select v-model="form.term_of_payment"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="full payment">Full Payment</option>
                            <option value="partial">Partial</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Payment %</label>
                        <input v-model="form.payment_percent" type="number" step="0.01"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Proforma Date</label>
                        <input v-model="form.p_date" type="date"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Transport</label>
                        <select v-model="form.transport"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="No">No</option>
                            <option value="Yes">Yes</option>
                        </select>
                    </div>

                    <div v-if="form.transport === 'Yes'">
                        <label class="block text-slate-300 mb-2">Transport Unit Price</label>
                        <input v-model="form.t_unit_price" type="number" step="0.01"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div v-if="form.transport === 'Yes'">
                        <label class="block text-slate-300 mb-2">Transport Unit</label>
                        <input v-model="form.transport_unit" type="text"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div v-if="form.transport === 'Yes'">
                        <label class="block text-slate-300 mb-2">Transport Tax %</label>
                        <input v-model="form.t_tax_p" type="number" step="0.01"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div v-if="form.transport === 'Yes'">
                        <label class="block text-slate-300 mb-2">Distance (KM)</label>
                        <input v-model="form.t_km" type="number" step="0.01"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-slate-300 mb-2">Location</label>
                        <input v-model="form.location" type="text"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-slate-300 mb-2">Remarks</label>
                        <textarea v-model="form.remark" rows="3"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500"></textarea>
                    </div>
                </div>

                <div class="flex gap-4 mt-8">
                    <button type="submit" :disabled="processing"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-lg font-medium disabled:opacity-50">
                        {{ processing ? 'Updating...' : 'Update Proforma Invoice' }}
                    </button>
                    <a href="/proforma-invoices" class="bg-slate-600 hover:bg-slate-500 text-white px-6 py-3 rounded-lg">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import Sidebar from '../../Components/Sidebar.vue';

const props = defineProps({
    proforma: Object,
    customers: { type: Array, default: () => [] },
    items: { type: Array, default: () => [] }
});

const form = ref({
    pi_no: props.proforma?.pi_no || '',
    customer: props.proforma?.customer || '',
    item_id: props.proforma?.item_id || '',
    quantity: props.proforma?.quantity || '',
    unit: props.proforma?.unit || '',
    unit_price: props.proforma?.unit_price || '',
    tax_p: props.proforma?.tax_p || '15',
    validity: props.proforma?.validity || '30',
    d_time: props.proforma?.d_time || '',
    term_of_payment: props.proforma?.term_of_payment || 'full payment',
    payment_percent: props.proforma?.payment_percent || '0',
    p_date: props.proforma?.p_date || new Date().toISOString().split('T')[0],
    remark: props.proforma?.remark || '',
    transport: props.proforma?.transport || 'No',
    t_unit_price: props.proforma?.t_unit_price || '',
    transport_unit: props.proforma?.transport_unit || '',
    t_tax_p: props.proforma?.t_tax_p || '',
    t_km: props.proforma?.t_km || '',
    location: props.proforma?.location || ''
});

const processing = ref(false);

const lineTotal = computed(() => {
    const qty = parseFloat(form.value.quantity) || 0;
    const price = parseFloat(form.value.unit_price) || 0;
    return (qty * price).toFixed(2);
});

// Auto-fill unit from item
watch(() => form.value.item_id, (newItemId) => {
    if (newItemId) {
        const item = props.items.find(i => i.id == newItemId);
        if (item) {
            form.value.unit = item.unit;
        }
    }
});

const submit = () => {
    processing.value = true;
    router.put('/proforma-invoices/' + props.proforma.id, form.value, {
        onFinish: () => processing.value = false
    });
};
</script>

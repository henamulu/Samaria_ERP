<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="sales-orders" />

        <div class="ml-64 p-8">
            <div class="mb-8">
                <a href="/sales-orders" class="text-slate-400 hover:text-white mb-2 inline-block">&larr; Back to Sales Orders</a>
                <h2 class="text-3xl font-bold text-white">Edit Sales Order #{{ salesOrder.so_no }}</h2>
            </div>

            <form @submit.prevent="submit" class="bg-slate-800 rounded-xl p-6 max-w-4xl">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-slate-300 mb-2">SO Number *</label>
                        <input v-model="form.so_no" type="text" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Order Date *</label>
                        <input v-model="form.registered_date" type="date" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Customer *</label>
                        <select v-model="form.customer" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="">Select Customer</option>
                            <option v-for="c in customers" :key="c.id" :value="c.id">
                                {{ c.company_name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Item *</label>
                        <input v-model="form.item" type="text" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Unit</label>
                        <select v-model="form.unit"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="M3">M3</option>
                            <option value="KG">KG</option>
                            <option value="PCS">PCS</option>
                            <option value="TON">TON</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Quantity *</label>
                        <input v-model="form.quantity" type="number" step="0.01" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Unit Price *</label>
                        <input v-model="form.unit_price" type="number" step="0.01" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Total</label>
                        <input :value="calculatedTotal" type="text" readonly
                            class="w-full bg-slate-600 border-0 rounded-lg px-4 py-3 text-emerald-400 font-bold" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Payment Type</label>
                        <select v-model="form.payment_type"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="Cash">Cash</option>
                            <option value="Credit">Credit</option>
                            <option value="Advance">Advance</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Status</label>
                        <select v-model="form.status"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="Pending">Pending</option>
                            <option value="Approved">Approved</option>
                            <option value="Completed">Completed</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Location</label>
                        <input v-model="form.location" type="text"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>
                </div>

                <div class="mt-6">
                    <label class="block text-slate-300 mb-2">Remark</label>
                    <textarea v-model="form.remark" rows="3"
                        class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500"></textarea>
                </div>

                <div class="flex gap-4 mt-8">
                    <button type="submit" :disabled="processing"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-lg font-medium disabled:opacity-50">
                        {{ processing ? 'Saving...' : 'Update Sales Order' }}
                    </button>
                    <a href="/sales-orders" class="bg-slate-600 hover:bg-slate-500 text-white px-6 py-3 rounded-lg">Cancel</a>
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
    salesOrder: Object,
    customers: { type: Array, default: () => [] }
});

const form = ref({
    so_no: props.salesOrder.so_no || '',
    registered_date: props.salesOrder.registered_date || '',
    customer: props.salesOrder.customer || '',
    item: props.salesOrder.item || '',
    unit: props.salesOrder.unit || 'M3',
    quantity: props.salesOrder.quantity || '',
    unit_price: props.salesOrder.unit_price || '',
    payment_type: props.salesOrder.payment_type || 'Cash',
    status: props.salesOrder.status || 'Pending',
    location: props.salesOrder.location || '',
    remark: props.salesOrder.remark || ''
});

const processing = ref(false);

const calculatedTotal = computed(() => {
    const qty = parseFloat(form.value.quantity) || 0;
    const price = parseFloat(form.value.unit_price) || 0;
    return 'ETB ' + (qty * price).toLocaleString('en-US', { minimumFractionDigits: 2 });
});

const submit = () => {
    processing.value = true;
    form.value.total = (parseFloat(form.value.quantity) || 0) * (parseFloat(form.value.unit_price) || 0);
    router.put('/sales-orders/' + props.salesOrder.id, form.value, {
        onFinish: () => processing.value = false
    });
};
</script>

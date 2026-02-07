<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="deliveries" />

        <div class="ml-64 p-8">
            <div class="mb-8">
                <a href="/deliveries" class="text-slate-400 hover:text-white mb-2 inline-block">&larr; Back to Deliveries</a>
                <h2 class="text-3xl font-bold text-white">Edit Delivery #{{ delivery.d_no }}</h2>
            </div>

            <form @submit.prevent="submit" class="bg-slate-800 rounded-xl p-6 max-w-4xl">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-slate-300 mb-2">Delivery No *</label>
                        <input v-model="form.d_no" type="text" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Issue Date *</label>
                        <input v-model="form.issue_date" type="date" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Customer *</label>
                        <select v-model="form.project" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="">Select Customer</option>
                            <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                                {{ customer.company_name }}
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
                        <label class="block text-slate-300 mb-2">Status</label>
                        <select v-model="form.status"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="Pending">Pending</option>
                            <option value="Done">Done</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Driver Name</label>
                        <input v-model="form.driver_name" type="text"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Truck Plate No</label>
                        <input v-model="form.truck_plate_no" type="text"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Delivered To</label>
                        <input v-model="form.delivered_to" type="text"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Source</label>
                        <input v-model="form.source" type="text"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Supplier Qty (M3)</label>
                        <input v-model="form.supplier_qty" type="number" step="0.01"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Category</label>
                        <select v-model="form.category"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="">Select Category</option>
                            <option value="Aggregate">Aggregate</option>
                            <option value="Sand">Sand</option>
                            <option value="Gravel">Gravel</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Sales Order (SIV)</label>
                        <select v-model="form.siv_id"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="">Select Sales Order</option>
                            <option v-for="so in salesOrders" :key="so.id" :value="so.id">
                                {{ so.so_no }} - {{ so.customer }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">GIT Record</label>
                        <select v-model="form.git_id"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="">Select GIT</option>
                            <option v-for="git in gits" :key="git.id" :value="git.id">
                                {{ git.git_no || `GIT #${git.id}` }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Signed By Customer</label>
                        <select v-model="form.signed_by_customer"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="">Select</option>
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Confirm Signed</label>
                        <select v-model="form.confirm_signed"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="">Select</option>
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Accepted By</label>
                        <input v-model="form.accepted_by" type="text"
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
                        {{ processing ? 'Saving...' : 'Update Delivery' }}
                    </button>
                    <a href="/deliveries" class="bg-slate-600 hover:bg-slate-500 text-white px-6 py-3 rounded-lg">Cancel</a>
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
    delivery: Object,
    customers: { type: Array, default: () => [] },
    salesOrders: { type: Array, default: () => [] },
    gits: { type: Array, default: () => [] },
});

const form = ref({
    d_no: props.delivery.d_no || '',
    issue_date: props.delivery.issue_date || '',
    project: props.delivery.project || '',
    item: props.delivery.item || '',
    unit: props.delivery.unit || 'M3',
    quantity: props.delivery.quantity || '',
    unit_price: props.delivery.unit_price || '',
    status: props.delivery.status || 'Pending',
    driver_name: props.delivery.driver_name || '',
    truck_plate_no: props.delivery.truck_plate_no || '',
    delivered_to: props.delivery.delivered_to || '',
    remark: props.delivery.remark || '',
    source: props.delivery.source || '',
    supplier_qty: props.delivery.supplier_qty || '',
    category: props.delivery.category || '',
    siv_id: props.delivery.siv_id || '',
    git_id: props.delivery.git_id || '',
    signed_by_customer: props.delivery.signed_by_customer || '',
    confirm_signed: props.delivery.confirm_signed || '',
    accepted_by: props.delivery.accepted_by || ''
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
    router.put('/deliveries/' + props.delivery.id, form.value, {
        onFinish: () => processing.value = false
    });
};
</script>

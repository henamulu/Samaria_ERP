<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="goods-in-transit" />

        <div class="ml-64 p-8">
            <div class="mb-8">
                <a href="/goods-in-transit" class="text-slate-400 hover:text-white mb-2 inline-block">&larr; Back to GIT</a>
                <h2 class="text-3xl font-bold text-white">Edit Goods In Transit</h2>
            </div>

            <form @submit.prevent="submit" class="bg-slate-800 rounded-xl p-6 max-w-4xl">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-slate-300 mb-2">GIT Number *</label>
                        <input v-model="form.git_no" type="text" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">GIT Date *</label>
                        <input v-model="form.git_date" type="date" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Purchase Order</label>
                        <select v-model="form.po_id"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="">Select PO</option>
                            <option v-for="po in purchaseOrders" :key="po.id" :value="po.id">{{ po.po_no }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Supplier *</label>
                        <select v-model="form.supplier_id" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="">Select Supplier</option>
                            <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.company_name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Customer *</label>
                        <select v-model="form.customer_id" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="">Select Customer</option>
                            <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.company_name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Item *</label>
                        <input v-model="form.item_name" type="text" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Quantity *</label>
                        <input v-model="form.quantity" type="number" step="0.01" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Unit Price</label>
                        <input v-model="form.unit_price" type="number" step="0.01"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Transporter *</label>
                        <select v-model="form.transporter_id" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="">Select Transporter</option>
                            <option v-for="t in transporters" :key="t.id" :value="t.id">{{ t.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Plate Number *</label>
                        <input v-model="form.plate_no" type="text" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Driver Name</label>
                        <input v-model="form.driver_name" type="text"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-slate-300 mb-2">Destination</label>
                        <input v-model="form.destination" type="text"
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
                        {{ processing ? 'Updating...' : 'Update GIT' }}
                    </button>
                    <a href="/goods-in-transit" class="bg-slate-600 hover:bg-slate-500 text-white px-6 py-3 rounded-lg">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import Sidebar from '../../Components/Sidebar.vue';

const props = defineProps({
    git: Object,
    purchaseOrders: { type: Array, default: () => [] },
    suppliers: { type: Array, default: () => [] },
    customers: { type: Array, default: () => [] },
    transporters: { type: Array, default: () => [] },
    items: { type: Array, default: () => [] }
});

const form = ref({
    git_no: props.git?.g_no || '',
    git_date: props.git?.issued_date || new Date().toISOString().split('T')[0],
    po_id: props.git?.po_id || '',
    supplier_id: props.git?.supplier_id || '',
    customer_id: props.git?.customer || '',
    item_name: props.git?.item || '',
    quantity: props.git?.quantity || '',
    unit_price: props.git?.unit_price || '',
    transporter_id: props.git?.ta_id || '',
    plate_no: props.git?.truck_plate_no || '',
    driver_name: props.git?.driver_name || '',
    destination: props.git?.delivered_to || '',
    remarks: props.git?.remark || ''
});

const processing = ref(false);

const submit = () => {
    processing.value = true;
    router.put('/goods-in-transit/' + props.git.id, form.value, {
        onFinish: () => processing.value = false
    });
};
</script>

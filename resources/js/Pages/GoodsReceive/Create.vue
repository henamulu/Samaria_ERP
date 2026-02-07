<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="goods-receive" />
        <div class="ml-64 p-8">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h2 class="text-3xl font-bold text-white mb-2">Create Goods Receive</h2>
                    <p class="text-slate-400">Register received goods from purchase order</p>
                </div>
                <router-link to="/goods-receive" class="text-emerald-400 hover:text-emerald-300">← Back to List</router-link>
            </div>

            <div class="bg-slate-800 rounded-xl p-6 max-w-4xl">
                <form @submit.prevent="submitForm">
                    <div class="space-y-6">
                        <div v-if="poData" class="p-4 bg-slate-700 rounded-lg mb-4">
                            <p class="text-slate-300 text-sm mb-2"><strong>Pre-filled from PO:</strong> {{ poData.po_no }}</p>
                            <p class="text-slate-300 text-sm"><strong>Supplier:</strong> {{ poData.supplier }}</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-slate-400 text-sm mb-2">GR No <span class="text-red-400">*</span></label>
                                <input v-model="form.gr_no" type="text" required class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                            </div>

                            <div>
                                <label class="block text-slate-400 text-sm mb-2">PO No <span class="text-red-400">*</span></label>
                                <select v-model="form.po_no" @change="loadPODetails" required class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white">
                                    <option value="">Select Purchase Order</option>
                                    <option v-for="po in purchaseOrders" :key="po.id" :value="po.po_no">{{ po.po_no }}</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-slate-400 text-sm mb-2">Supplier <span class="text-red-400">*</span></label>
                                <select v-model="form.supplier_id" required class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white">
                                    <option value="">Select Supplier</option>
                                    <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.supplier_name }}</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-slate-400 text-sm mb-2">Item <span class="text-red-400">*</span></label>
                                <select v-model="form.item" @change="onItemChange" required class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white">
                                    <option value="">Select Item</option>
                                    <option v-for="item in items" :key="item.id" :value="item.item">{{ item.item }}</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-slate-400 text-sm mb-2">Ordered Quantity <span class="text-red-400">*</span></label>
                                <input v-model.number="form.quantity" type="number" step="0.01" min="0" required class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                            </div>

                            <div>
                                <label class="block text-slate-400 text-sm mb-2">Received Quantity <span class="text-red-400">*</span></label>
                                <input v-model.number="form.received_qty" type="number" step="0.01" min="0" required class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                            </div>

                            <div>
                                <label class="block text-slate-400 text-sm mb-2">Unit</label>
                                <input v-model="form.unit" type="text" class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                            </div>

                            <div>
                                <label class="block text-slate-400 text-sm mb-2">Unit Price</label>
                                <input v-model.number="form.unit_price" type="number" step="0.01" min="0" class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                            </div>

                            <div>
                                <label class="block text-slate-400 text-sm mb-2">GRV Date</label>
                                <input v-model="form.grv_date" type="date" lang="en" class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-slate-400 text-sm mb-2">Remark</label>
                                <textarea v-model="form.remark" rows="3" class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white"></textarea>
                            </div>
                        </div>

                        <!-- Summary -->
                        <div class="p-4 bg-slate-700 rounded-lg">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-slate-300">Remaining:</span>
                                <span class="text-white font-medium">{{ remainingQty }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-300">Total Amount:</span>
                                <span class="text-emerald-400 font-bold">{{ formatCurrency(totalAmount) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex gap-3">
                        <button type="submit" :disabled="loading" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-3 rounded-lg disabled:opacity-50">
                            {{ loading ? 'Creating...' : 'Create Goods Receive' }}
                        </button>
                        <router-link to="/goods-receive" class="flex-1 bg-slate-600 hover:bg-slate-500 text-white px-4 py-3 rounded-lg text-center">Cancel</router-link>
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
    suppliers: Array,
    purchaseOrders: Array,
    items: Array,
    poData: Object
});

const loading = ref(false);
const form = ref({
    gr_no: '',
    po_no: props.poData?.po_no || '',
    supplier_id: props.poData?.supplier || '',
    item: props.poData?.item || '',
    item_id: '',
    quantity: props.poData?.quantity || 0,
    received_qty: 0,
    unit: props.poData?.unit || '',
    unit_price: props.poData?.unit_price || 0,
    grv_date: new Date().toISOString().split('T')[0],
    remark: ''
});

const remainingQty = computed(() => {
    return Math.max(0, (form.value.quantity || 0) - (form.value.received_qty || 0));
});

const totalAmount = computed(() => {
    return (form.value.received_qty || 0) * (form.value.unit_price || 0);
});

const formatCurrency = (num) => num ? 'ETB ' + parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 2 }) : 'ETB 0.00';

const onItemChange = () => {
    const item = props.items.find(i => i.item === form.value.item);
    if (item && item.unit) {
        form.value.unit = item.unit;
        form.value.item_id = item.id;
    }
};

const loadPODetails = async () => {
    if (!form.value.po_no) return;
    
    const po = props.purchaseOrders.find(p => p.po_no === form.value.po_no);
    if (po) {
        form.value.supplier_id = po.supplier;
        form.value.item = po.item;
        form.value.quantity = parseFloat(po.quantity) || 0;
        form.value.unit = po.unit || '';
        form.value.unit_price = parseFloat(po.unit_price) || 0;
    }
};

onMounted(() => {
    if (props.poData) {
        form.value.po_no = props.poData.po_no;
        form.value.supplier_id = props.poData.supplier;
        form.value.item = props.poData.item;
        form.value.quantity = props.poData.quantity;
        form.value.unit = props.poData.unit;
        form.value.unit_price = props.poData.unit_price;
    }
});

const submitForm = () => {
    loading.value = true;
    router.post('/goods-receive', form.value, {
        preserveState: false,
        onFinish: () => {
            loading.value = false;
        }
    });
};
</script>

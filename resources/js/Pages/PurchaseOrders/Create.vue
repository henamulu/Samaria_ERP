<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="purchase-orders" />

        <div class="ml-64 p-8">
            <div class="mb-8">
                <a href="/purchase-orders" class="text-slate-400 hover:text-white mb-2 inline-block">&larr; Back to Purchase Orders</a>
                <h2 class="text-3xl font-bold text-white">Create New Purchase Order</h2>
            </div>

            <form @submit.prevent="submit" class="bg-slate-800 rounded-xl p-6 max-w-4xl">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-slate-300 mb-2">PO Number *</label>
                        <input v-model="form.po_no" type="text" required readonly
                            class="w-full bg-slate-600 border-0 rounded-lg px-4 py-3 text-emerald-400 font-medium cursor-not-allowed" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Order Date *</label>
                        <input v-model="form.registered_date" type="date" required
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
                        <div class="flex gap-2">
                            <select v-model="selectedItemId" required @change="onItemChange"
                                class="flex-1 bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                                <option value="">Select Item</option>
                                <option v-for="item in items" :key="item.id" :value="item.id">
                                    {{ item.item_name || item.item }}
                                </option>
                            </select>
                            <button type="button" @click="showAddItemModal = true"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg">
                                + Add New
                            </button>
                        </div>
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
                            <option value="Approved">Approved</option>
                            <option value="Completed">Completed</option>
                        </select>
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
                        {{ processing ? 'Saving...' : 'Create Purchase Order' }}
                    </button>
                    <a href="/purchase-orders" class="bg-slate-600 hover:bg-slate-500 text-white px-6 py-3 rounded-lg">Cancel</a>
                </div>
            </form>
        </div>

        <!-- Add Item Modal -->
        <div v-if="showAddItemModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="showAddItemModal = false">
            <div class="bg-slate-800 rounded-xl p-6 max-w-md w-full mx-4" @click.stop>
                <h3 class="text-xl font-bold text-white mb-4">Add New Item</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-slate-300 mb-2">Item Name *</label>
                        <input v-model="newItem.item" type="text" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>
                    <div>
                        <label class="block text-slate-300 mb-2">Display Name</label>
                        <input v-model="newItem.item_name" type="text"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>
                    <div>
                        <label class="block text-slate-300 mb-2">Category</label>
                        <input v-model="newItem.category" type="text"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>
                    <div>
                        <label class="block text-slate-300 mb-2">Unit</label>
                        <select v-model="newItem.unit"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="M3">M3</option>
                            <option value="KG">KG</option>
                            <option value="PCS">PCS</option>
                            <option value="TON">TON</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-300 mb-2">Item Type</label>
                        <select v-model="newItem.item_type"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="sales">Sales</option>
                            <option value="purchase">Purchase</option>
                            <option value="both">Both</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-300 mb-2">Description</label>
                        <textarea v-model="newItem.description" rows="2"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500"></textarea>
                    </div>
                </div>
                <div class="flex gap-4 mt-6">
                    <button @click="addNewItem" :disabled="addingItem"
                        class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg disabled:opacity-50">
                        {{ addingItem ? 'Adding...' : 'Add Item' }}
                    </button>
                    <button @click="showAddItemModal = false"
                        class="flex-1 bg-slate-600 hover:bg-slate-500 text-white px-4 py-2 rounded-lg">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import Sidebar from '../../Components/Sidebar.vue';

const props = defineProps({
    suppliers: { type: Array, default: () => [] },
    items: { type: Array, default: () => [] },
    prData: { type: Object, default: null },
    nextPoNo: { type: String, default: 'PO1' }
});

const form = ref({
    po_no: props.nextPoNo,
    registered_date: new Date().toISOString().split('T')[0],
    supplier: '',
    item: props.prData?.item_desc || '',
    unit: props.prData?.unit || 'M3',
    quantity: props.prData?.quantity || '',
    unit_price: '',
    status: 'Pending',
    remark: props.prData ? `From PR: ${props.prData.pr_no}` : '',
    pr_id: props.prData?.pr_id || ''
});
const selectedItemId = ref('');

const processing = ref(false);
const showAddItemModal = ref(false);
const newItem = ref({
    item: '',
    item_name: '',
    category: '',
    unit: 'M3',
    item_type: 'purchase',
    description: ''
});
const addingItem = ref(false);

const calculatedTotal = computed(() => {
    const qty = parseFloat(form.value.quantity) || 0;
    const price = parseFloat(form.value.unit_price) || 0;
    return 'ETB ' + (qty * price).toLocaleString('en-US', { minimumFractionDigits: 2 });
});

// Auto-update item name and unit when item is selected
const onItemChange = () => {
    if (selectedItemId.value && props.items) {
        const selectedItem = props.items.find(i => i.id == selectedItemId.value);
        if (selectedItem) {
            form.value.item = selectedItem.item;
            if (selectedItem.unit) {
                form.value.unit = selectedItem.unit;
            }
        }
    }
};

const addNewItem = async () => {
    if (!newItem.value.item) {
        alert('Item name is required');
        return;
    }
    
    addingItem.value = true;
    try {
        const response = await fetch('/api/items', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify(newItem.value)
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            alert(data.error || 'Failed to create item');
            return;
        }
        
        if (Array.isArray(props.items)) {
            props.items.push(data.item);
        }
        selectedItemId.value = data.item.id;
        form.value.item = data.item.item;
        if (data.item.unit) {
            form.value.unit = data.item.unit;
        }
        showAddItemModal.value = false;
        newItem.value = { item: '', item_name: '', category: '', unit: 'M3', item_type: 'purchase', description: '' };
    } catch (error) {
        alert('Network error: ' + error.message);
    } finally {
        addingItem.value = false;
    }
};

const submit = () => {
    processing.value = true;
    form.value.total = (parseFloat(form.value.quantity) || 0) * (parseFloat(form.value.unit_price) || 0);
    router.post('/purchase-orders', form.value, {
        onFinish: () => processing.value = false
    });
};
</script>


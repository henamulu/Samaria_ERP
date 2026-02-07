<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="deliveries" />

        <div class="ml-64 p-8">
            <div class="mb-8">
                <a href="/deliveries" class="text-slate-400 hover:text-white mb-2 inline-block">&larr; Back to Deliveries</a>
                <h2 class="text-3xl font-bold text-white">Create New Delivery</h2>
            </div>

            <form @submit.prevent="submit" class="bg-slate-800 rounded-xl p-6 max-w-4xl">
                <FormErrors />
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-slate-300 mb-2">Delivery No *</label>
                        <input v-model="form.d_no" type="text" required readonly
                            class="w-full bg-slate-600 border-0 rounded-lg px-4 py-3 text-emerald-400 font-medium cursor-not-allowed" />
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
                        <div class="flex gap-2">
                            <select v-model="form.item" required @change="onItemChange"
                                class="flex-1 bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                                <option value="">Select Item</option>
                                <option v-for="item in items" :key="item.id" :value="item.item">
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
                            <option value="Done">Done</option>
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
                </div>

                <div class="mt-6">
                    <label class="block text-slate-300 mb-2">Remark</label>
                    <textarea v-model="form.remark" rows="3"
                        class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500"></textarea>
                </div>

                <div class="flex gap-4 mt-8">
                    <button type="submit" :disabled="processing"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-lg font-medium disabled:opacity-50">
                        {{ processing ? 'Saving...' : 'Create Delivery' }}
                    </button>
                    <a href="/deliveries" class="bg-slate-600 hover:bg-slate-500 text-white px-6 py-3 rounded-lg">Cancel</a>
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
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import Sidebar from '../../Components/Sidebar.vue';
import FormErrors from '../../Components/FormErrors.vue';

// Auto-update unit when item is selected
const onItemChange = () => {
    if (form.value.item && props.items) {
        const selectedItem = props.items.find(i => i.item === form.value.item);
        if (selectedItem && selectedItem.unit) {
            form.value.unit = selectedItem.unit;
        }
    }
};

const props = defineProps({
    customers: { type: Array, default: () => [] },
    items: { type: Array, default: () => [] },
    salesOrders: { type: Array, default: () => [] },
    gits: { type: Array, default: () => [] },
    nextDNo: { type: String, default: 'DEL1' }
});

const form = ref({
    d_no: props.nextDNo,
    issue_date: new Date().toISOString().split('T')[0],
    project: '',
    item: '',
    unit: 'M3',
    quantity: '',
    unit_price: '',
    status: 'Pending',
    driver_name: '',
    truck_plate_no: '',
    delivered_to: '',
    remark: '',
    source: '',
    supplier_qty: '',
    category: '',
    siv_id: '',
    git_id: ''
});

const processing = ref(false);
const showAddItemModal = ref(false);
const newItem = ref({
    item: '',
    item_name: '',
    category: '',
    unit: 'M3',
    item_type: 'sales',
    description: ''
});
const addingItem = ref(false);

const calculatedTotal = computed(() => {
    const qty = parseFloat(form.value.quantity) || 0;
    const price = parseFloat(form.value.unit_price) || 0;
    return 'ETB ' + (qty * price).toLocaleString('en-US', { minimumFractionDigits: 2 });
});

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
        form.value.item = data.item.item;
        if (data.item.unit) {
            form.value.unit = data.item.unit;
        }
        showAddItemModal.value = false;
        newItem.value = { item: '', item_name: '', category: '', unit: 'M3', item_type: 'sales', description: '' };
    } catch (error) {
        alert('Network error: ' + error.message);
    } finally {
        addingItem.value = false;
    }
};

const submit = () => {
    processing.value = true;
    form.value.total = (parseFloat(form.value.quantity) || 0) * (parseFloat(form.value.unit_price) || 0);
    router.post('/deliveries', form.value, {
        preserveScroll: true,
        onFinish: () => processing.value = false,
        onError: (errors) => {
            console.error('Validation errors:', errors);
        }
    });
};
</script>


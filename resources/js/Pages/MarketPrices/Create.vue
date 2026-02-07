<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="market-prices" />
        <div class="ml-64 p-8">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h2 class="text-3xl font-bold text-white mb-2">Update Sales Price</h2>
                    <p class="text-slate-400">Create or update market price for sales items</p>
                </div>
                <router-link to="/market-prices" class="text-emerald-400 hover:text-emerald-300">← Back to List</router-link>
            </div>

            <div class="bg-slate-800 rounded-xl p-6 max-w-3xl">
                <form @submit.prevent="submitForm">
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-slate-400 text-sm mb-2">Customer</label>
                                <select v-model="form.customer" @change="loadCustomerAgreements" class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white">
                                    <option value="">Select Customer (Optional)</option>
                                    <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.company_name }}</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-slate-400 text-sm mb-2">Price Type <span class="text-red-400">*</span></label>
                                <select v-model="form.price_type" @change="onPriceTypeChange" required class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white">
                                    <option value="">Select Type</option>
                                    <option value="agreement">Agreement</option>
                                    <option value="market">Market</option>
                                </select>
                            </div>

                            <div v-if="form.price_type === 'agreement'">
                                <label class="block text-slate-400 text-sm mb-2">Agreement No</label>
                                <select v-model="form.agg_no" class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white">
                                    <option value="">Select Agreement</option>
                                    <option v-for="agg in customerAgreements" :key="agg.a_no" :value="agg.a_no">
                                        {{ agg.a_no }} - {{ agg.item }}
                                    </option>
                                </select>
                            </div>

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
                                <label class="block text-slate-400 text-sm mb-2">Tax % <span class="text-red-400">*</span></label>
                                <input v-model.number="form.tax_p" type="number" step="0.01" min="0" max="100" required class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                            </div>
                        </div>

                        <!-- Transport Options -->
                        <div class="border-t border-slate-700 pt-6">
                            <h3 class="text-lg font-semibold text-white mb-4">Transport Options (Optional)</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-slate-400 text-sm mb-2">Include Transport</label>
                                    <select v-model="form.transport" class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white">
                                        <option value="No">No</option>
                                        <option value="Yes">Yes</option>
                                    </select>
                                </div>
                                <div v-if="form.transport === 'Yes'">
                                    <label class="block text-slate-400 text-sm mb-2">Transport Unit</label>
                                    <input v-model="form.transport_unit" type="text" class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                                </div>
                                <div v-if="form.transport === 'Yes'">
                                    <label class="block text-slate-400 text-sm mb-2">Transport Unit Price</label>
                                    <input v-model.number="form.t_unit_price" type="number" step="0.01" min="0" class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                                </div>
                                <div v-if="form.transport === 'Yes'">
                                    <label class="block text-slate-400 text-sm mb-2">Transport Tax %</label>
                                    <input v-model="form.t_tax_p" type="text" class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                                </div>
                            </div>
                        </div>

                        <!-- Price Summary -->
                        <div class="p-4 bg-slate-700 rounded-lg">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-slate-400">Unit Price:</span>
                                <span class="text-white font-medium">{{ formatCurrency(form.unit_price) }}</span>
                            </div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-slate-400">Tax ({{ form.tax_p }}%):</span>
                                <span class="text-white font-medium">{{ formatCurrency(taxAmount) }}</span>
                            </div>
                            <div class="flex justify-between items-center mt-4 pt-4 border-t border-slate-600">
                                <span class="text-slate-300 font-semibold">Price with VAT:</span>
                                <span class="text-emerald-400 font-bold text-xl">{{ formatCurrency(priceWithVAT) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex gap-3">
                        <button type="submit" :disabled="loading" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-3 rounded-lg disabled:opacity-50">
                            {{ loading ? 'Creating...' : 'Create Market Price' }}
                        </button>
                        <router-link to="/market-prices" class="flex-1 bg-slate-600 hover:bg-slate-500 text-white px-4 py-3 rounded-lg text-center">Cancel</router-link>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import Sidebar from '../../Components/Sidebar.vue';

const props = defineProps({
    customers: Array,
    items: Array,
    units: Array,
    customerAgreements: Array,
    selectedCustomer: String
});

const loading = ref(false);
const form = ref({
    customer: props.selectedCustomer || '',
    price_type: '',
    agg_no: '',
    item_id: '',
    unit: '',
    unit_price: 0,
    tax_p: 15,
    transport: 'No',
    transport_unit: '',
    t_unit_price: 0,
    t_tax_p: ''
});

const customerAgreements = ref(props.customerAgreements || []);

const taxAmount = computed(() => {
    return (form.value.unit_price * form.value.tax_p) / 100;
});

const priceWithVAT = computed(() => {
    return form.value.unit_price + taxAmount.value;
});

const formatCurrency = (num) => num ? 'ETB ' + parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 2 }) : 'ETB 0.00';

const onItemChange = () => {
    const item = props.items.find(i => i.id == form.value.item_id);
    if (item && item.unit) {
        form.value.unit = item.unit;
    }
};

const onPriceTypeChange = () => {
    if (form.value.price_type === 'agreement' && !form.value.customer) {
        alert('Please select a customer first for agreement type prices.');
        form.value.price_type = '';
    }
};

const loadCustomerAgreements = async () => {
    if (!form.value.customer) {
        customerAgreements.value = [];
        return;
    }
    
    try {
        const response = await fetch(`/api/customer-agreements?customer=${form.value.customer}`);
        const data = await response.json();
        customerAgreements.value = data;
    } catch (error) {
        console.error('Error loading customer agreements:', error);
        customerAgreements.value = [];
    }
};

const submitForm = () => {
    loading.value = true;
    router.post('/market-prices', form.value, {
        preserveState: false,
        onFinish: () => {
            loading.value = false;
        }
    });
};
</script>

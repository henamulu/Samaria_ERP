<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="coupons" />
        <div class="ml-64 p-8">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h2 class="text-3xl font-bold text-white mb-2">Request Coupon</h2>
                    <p class="text-slate-400">Create a new coupon request</p>
                </div>
                <router-link to="/coupons" class="text-emerald-400 hover:text-emerald-300">← Back to List</router-link>
            </div>

            <div class="bg-slate-800 rounded-xl p-6 max-w-3xl">
                <form @submit.prevent="submitForm">
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-slate-400 text-sm mb-2">Supplier <span class="text-red-400">*</span></label>
                                <select v-model="form.supplier" @change="loadPurchaseOrders" required class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white">
                                    <option value="">Select Supplier</option>
                                    <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.company_name }}</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-slate-400 text-sm mb-2">Purchase Order No <span class="text-red-400">*</span></label>
                                <input v-model="form.po_no" type="text" required class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" placeholder="PO Number" />
                            </div>

                            <div>
                                <label class="block text-slate-400 text-sm mb-2">Reference No</label>
                                <input v-model="form.ref_no" type="text" class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" placeholder="Reference Number" />
                            </div>

                            <div>
                                <label class="block text-slate-400 text-sm mb-2">Purchase Type</label>
                                <input v-model="form.purchase_type" type="text" class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" placeholder="Purchase Type" />
                            </div>

                            <div>
                                <label class="block text-slate-400 text-sm mb-2">Coupon Tag</label>
                                <select v-model="form.cupon_tag" class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white">
                                    <option value="">Select Tag</option>
                                    <option value="ETH">ETH</option>
                                    <option value="ETC">ETC</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex gap-3">
                        <button type="submit" :disabled="loading" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-3 rounded-lg disabled:opacity-50">
                            {{ loading ? 'Creating...' : 'Create Coupon Request' }}
                        </button>
                        <router-link to="/coupons" class="flex-1 bg-slate-600 hover:bg-slate-500 text-white px-4 py-3 rounded-lg text-center">Cancel</router-link>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Sidebar from '../../Components/Sidebar.vue';

const props = defineProps({
    suppliers: Array,
    purchaseOrders: Array,
    selectedPO: String
});

const loading = ref(false);
const form = ref({
    supplier: '',
    po_no: props.selectedPO || '',
    ref_no: '',
    purchase_type: '',
    cupon_tag: ''
});

const loadPurchaseOrders = () => {
    // Could load PO list for selected supplier
};

const submitForm = () => {
    loading.value = true;
    router.post('/coupons/request', form.value, {
        preserveState: false,
        onFinish: () => {
            loading.value = false;
        }
    });
};
</script>

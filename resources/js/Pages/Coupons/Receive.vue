<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="coupons" />
        <div class="ml-64 p-8">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h2 class="text-3xl font-bold text-white mb-2">Receive Coupon</h2>
                    <p class="text-slate-400">Receive and register coupon numbers</p>
                </div>
                <router-link to="/coupons" class="text-emerald-400 hover:text-emerald-300">← Back to List</router-link>
            </div>

            <div class="bg-slate-800 rounded-xl p-6 max-w-4xl">
                <form @submit.prevent="submitForm">
                    <div class="space-y-6">
                        <div>
                            <label class="block text-slate-400 text-sm mb-2">Coupon Request <span class="text-red-400">*</span></label>
                            <select v-model="form.r_id" @change="loadCouponDetails" required class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white">
                                <option value="">Select Coupon Request</option>
                                <option v-for="c in coupons" :key="c.id" :value="c.id">
                                    {{ c.r_no }} - {{ c.supplier_name }} ({{ c.po_no }})
                                </option>
                            </select>
                        </div>

                        <div v-if="couponDetails" class="p-4 bg-slate-700 rounded-lg">
                            <p class="text-slate-300 text-sm mb-2"><strong>Supplier:</strong> {{ couponDetails.supplier_name }}</p>
                            <p class="text-slate-300 text-sm mb-2"><strong>PO No:</strong> {{ couponDetails.po_no }}</p>
                            <p class="text-slate-300 text-sm"><strong>Ref No:</strong> {{ couponDetails.ref_no || '-' }}</p>
                        </div>

                        <div>
                            <label class="block text-slate-400 text-sm mb-2">Order Type <span class="text-red-400">*</span></label>
                            <select v-model="form.order_type" required class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white">
                                <option value="">Select Order Type</option>
                                <option value="con">Construction</option>
                                <option value="other">Other</option>
                            </select>
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

                        <div>
                            <label class="block text-slate-400 text-sm mb-2">Coupon Numbers <span class="text-red-400">*</span></label>
                            <div class="space-y-2">
                                <div v-for="(num, index) in form.cupon_numbers" :key="index" class="flex gap-2">
                                    <input v-model="form.cupon_numbers[index]" type="text" required class="flex-1 bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" placeholder="Coupon Number" />
                                    <button v-if="form.cupon_numbers.length > 1" @click.prevent="removeCouponNumber(index)" type="button" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">Remove</button>
                                </div>
                                <button @click.prevent="addCouponNumber" type="button" class="bg-slate-600 hover:bg-slate-500 text-white px-4 py-2 rounded-lg">+ Add Another</button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex gap-3">
                        <button type="submit" :disabled="loading" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-3 rounded-lg disabled:opacity-50">
                            {{ loading ? 'Receiving...' : 'Receive Coupons' }}
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
    coupons: Array,
    selectedCoupon: String
});

const loading = ref(false);
const couponDetails = ref(null);
const form = ref({
    r_id: props.selectedCoupon || '',
    cupon_numbers: [''],
    order_type: 'con',
    cupon_tag: ''
});

const loadCouponDetails = async () => {
    if (!form.value.r_id) {
        couponDetails.value = null;
        return;
    }
    
    try {
        const response = await fetch(`/api/coupon-request/${form.value.r_id}`);
        const data = await response.json();
        couponDetails.value = data.coupon;
        if (data.coupon.cupon_tag) {
            form.value.cupon_tag = data.coupon.cupon_tag;
        }
    } catch (error) {
        console.error('Error loading coupon details:', error);
        couponDetails.value = null;
    }
};

const addCouponNumber = () => {
    form.value.cupon_numbers.push('');
};

const removeCouponNumber = (index) => {
    form.value.cupon_numbers.splice(index, 1);
};

const submitForm = () => {
    // Filter out empty coupon numbers
    form.value.cupon_numbers = form.value.cupon_numbers.filter(num => num.trim() !== '');
    
    if (form.value.cupon_numbers.length === 0) {
        alert('Please add at least one coupon number.');
        return;
    }
    
    loading.value = true;
    router.post('/coupons/receive', form.value, {
        preserveState: false,
        onFinish: () => {
            loading.value = false;
        }
    });
};
</script>

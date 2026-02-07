<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="coupons" />
        <div class="ml-64 p-8">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h2 class="text-3xl font-bold text-white mb-2">Handover Coupon</h2>
                    <p class="text-slate-400">Hand over coupons to dispatcher</p>
                </div>
                <router-link to="/coupons" class="text-emerald-400 hover:text-emerald-300">← Back to List</router-link>
            </div>

            <div class="bg-slate-800 rounded-xl p-6 max-w-4xl">
                <form @submit.prevent="submitForm">
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-slate-400 text-sm mb-2">Dispatcher <span class="text-red-400">*</span></label>
                                <input v-model="form.dispatcher" type="text" required class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" placeholder="Dispatcher Name" />
                            </div>

                            <div>
                                <label class="block text-slate-400 text-sm mb-2">Passed By <span class="text-red-400">*</span></label>
                                <input v-model="form.pass_by" type="text" required class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" placeholder="Person Name" />
                            </div>

                            <div>
                                <label class="block text-slate-400 text-sm mb-2">Handover No</label>
                                <input v-model="form.h_no" type="text" class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" placeholder="Handover Number" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-slate-400 text-sm mb-2">Select Coupons to Handover <span class="text-red-400">*</span></label>
                            <div class="bg-slate-700 rounded-lg p-4 max-h-96 overflow-y-auto">
                                <div v-if="couponNumbers.length === 0" class="text-slate-400 text-center py-8">
                                    No coupons available for handover
                                </div>
                                <div v-else class="space-y-2">
                                    <div v-for="cn in couponNumbers" :key="cn.id" class="flex items-center gap-3 p-3 bg-slate-600 rounded-lg hover:bg-slate-500">
                                        <input type="checkbox" :value="cn.id" v-model="form.cupon_ids" class="w-5 h-5 text-emerald-600 rounded" />
                                        <div class="flex-1">
                                            <p class="text-white font-medium">Coupon No: {{ cn.cupon_no }}</p>
                                            <p class="text-slate-300 text-sm">Request: {{ getCouponInfo(cn.r_id)?.r_no || '-' }} | Supplier: {{ getCouponInfo(cn.r_id)?.supplier_name || '-' }}</p>
                                        </div>
                                        <span :class="statusClass(cn.status)" class="px-2 py-1 rounded text-xs">{{ cn.status }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex gap-3">
                        <button type="submit" :disabled="loading || form.cupon_ids.length === 0" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-3 rounded-lg disabled:opacity-50">
                            {{ loading ? 'Handing Over...' : 'Handover Selected Coupons' }}
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
    couponNumbers: Array,
    coupons: Object
});

const loading = ref(false);
const form = ref({
    cupon_ids: [],
    dispatcher: '',
    pass_by: '',
    h_no: ''
});

const getCouponInfo = (rId) => {
    return props.coupons[rId] || null;
};

const statusClass = (status) => {
    if (status === 'pending') return 'bg-yellow-500/20 text-yellow-400';
    if (status === 'received') return 'bg-blue-500/20 text-blue-400';
    return 'bg-slate-500/20 text-slate-400';
};

const submitForm = () => {
    if (form.value.cupon_ids.length === 0) {
        alert('Please select at least one coupon to handover.');
        return;
    }
    
    loading.value = true;
    router.post('/coupons/handover', form.value, {
        preserveState: false,
        onFinish: () => {
            loading.value = false;
        }
    });
};
</script>

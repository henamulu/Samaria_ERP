<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="coupons" />
        <div class="ml-64 p-8">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h2 class="text-3xl font-bold text-white mb-2">Coupon Management</h2>
                    <p class="text-slate-400">Track and manage delivery coupons</p>
                </div>
                <div class="flex gap-3">
                    <a href="/coupons/request" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg">
                        + Request Coupon
                    </a>
                    <a href="/coupons/receive" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                        Receive Coupon
                    </a>
                    <a href="/coupons/handover" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg">
                        Handover Coupon
                    </a>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-slate-800 rounded-xl p-6 mb-6">
                <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-slate-400 text-sm mb-2">Search</label>
                        <input v-model="filters.search" type="text" placeholder="R No, Supplier, PO No, Ref No..." class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                    </div>
                    <div>
                        <label class="block text-slate-400 text-sm mb-2">Status</label>
                        <select v-model="filters.status" class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="received">Received</option>
                            <option value="handed_over">Handed Over</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-400 text-sm mb-2">Supplier</label>
                        <select v-model="filters.supplier" class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white">
                            <option value="">All Suppliers</option>
                            <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.supplier_name }}</option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg">Apply</button>
                        <button type="button" @click="clearFilters" class="flex-1 bg-slate-600 hover:bg-slate-500 text-white px-4 py-2 rounded-lg">Clear</button>
                    </div>
                </form>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-slate-800 rounded-xl p-6">
                    <p class="text-slate-400 text-sm mb-1">Total Coupons</p>
                    <p class="text-3xl font-bold text-white">{{ coupons.total }}</p>
                </div>
                <div class="bg-slate-800 rounded-xl p-6">
                    <p class="text-slate-400 text-sm mb-1">Pending</p>
                    <p class="text-3xl font-bold text-yellow-400">{{ pendingCount }}</p>
                </div>
                <div class="bg-slate-800 rounded-xl p-6">
                    <p class="text-slate-400 text-sm mb-1">Received</p>
                    <p class="text-3xl font-bold text-blue-400">{{ receivedCount }}</p>
                </div>
                <div class="bg-slate-800 rounded-xl p-6">
                    <p class="text-slate-400 text-sm mb-1">Handed Over</p>
                    <p class="text-3xl font-bold text-purple-400">{{ handedOverCount }}</p>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-slate-800 rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">R No</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Supplier</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">PO No</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Ref No</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Purchase Type</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Coupon Tag</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Registered By</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            <tr v-for="coupon in coupons.data" :key="coupon.id" class="hover:bg-slate-700/50">
                                <td class="px-4 py-3 text-white font-medium">{{ coupon.r_no }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ coupon.supplier_name }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ coupon.po_no }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ coupon.ref_no || '-' }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ coupon.purchase_type || '-' }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ coupon.cupon_tag || '-' }}</td>
                                <td class="px-4 py-3">
                                    <span :class="statusClass(coupon.status)" class="px-2 py-1 rounded text-xs">{{ coupon.status }}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-300">{{ coupon.registered_by }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ coupon.registered_date }}</td>
                                <td class="px-4 py-3">
                                    <button @click="deleteCoupon(coupon.id)" class="text-red-400 hover:text-red-300 text-sm">Delete</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div v-if="coupons.links && coupons.links.length > 3" class="p-4 border-t border-slate-700 flex justify-center gap-2">
                    <a v-for="link in coupons.links" :key="link.label" 
                       :href="link.url || '#'" 
                       @click.prevent="link.url && loadPage(link.url)"
                       v-html="link.label"
                       :class="[
                           'px-4 py-2 rounded-lg',
                           link.active ? 'bg-emerald-600 text-white' : 'bg-slate-700 text-slate-300 hover:bg-slate-600',
                           !link.url && 'opacity-50 cursor-not-allowed'
                       ]"></a>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import Sidebar from '../../Components/Sidebar.vue';

const props = defineProps({
    coupons: Object,
    suppliers: Array,
    filters: Object
});

const filters = ref(props.filters || {
    search: '',
    status: '',
    supplier: ''
});

const pendingCount = computed(() => {
    return props.coupons?.data?.filter(c => c.status === 'pending').length || 0;
});

const receivedCount = computed(() => {
    return props.coupons?.data?.filter(c => c.status === 'received').length || 0;
});

const handedOverCount = computed(() => {
    return props.coupons?.data?.filter(c => c.status === 'handed_over').length || 0;
});

const statusClass = (status) => {
    if (status === 'received') return 'bg-blue-500/20 text-blue-400';
    if (status === 'handed_over') return 'bg-purple-500/20 text-purple-400';
    return 'bg-yellow-500/20 text-yellow-400';
};

const applyFilters = () => {
    router.get('/coupons', filters.value, {
        preserveState: true,
        preserveScroll: true
    });
};

const clearFilters = () => {
    filters.value = { search: '', status: '', supplier: '' };
    applyFilters();
};

const loadPage = (url) => {
    const urlObj = new URL(url);
    router.get(urlObj.pathname + urlObj.search, {}, {
        preserveState: true,
        preserveScroll: true
    });
};

const deleteCoupon = (id) => {
    if (confirm('Are you sure you want to delete this coupon? This will also delete all related coupon numbers.')) {
        router.delete(`/coupons/${id}`, {
            preserveState: false
        });
    }
};
</script>

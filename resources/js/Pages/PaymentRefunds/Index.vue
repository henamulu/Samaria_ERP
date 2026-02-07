<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="payment-refunds" />

        <div class="ml-64 p-8">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-white">Payment Refunds</h2>
                    <p class="text-slate-400">Manage payment refund requests</p>
                </div>
                <a href="/payment-refunds/create" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Create Refund
                </a>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-4 gap-4 mb-6">
                <div class="bg-slate-800 rounded-xl p-4">
                    <p class="text-slate-400 text-sm">Total Requests</p>
                    <p class="text-2xl font-bold text-white">{{ refunds.total }}</p>
                </div>
                <div class="bg-slate-800 rounded-xl p-4">
                    <p class="text-slate-400 text-sm">Pending</p>
                    <p class="text-2xl font-bold text-yellow-400">{{ stats.pending }}</p>
                </div>
                <div class="bg-slate-800 rounded-xl p-4">
                    <p class="text-slate-400 text-sm">Approved</p>
                    <p class="text-2xl font-bold text-green-400">{{ stats.approved }}</p>
                </div>
                <div class="bg-slate-800 rounded-xl p-4">
                    <p class="text-slate-400 text-sm">Total Amount</p>
                    <p class="text-2xl font-bold text-red-400">{{ formatCurrency(stats.totalAmount) }}</p>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-slate-800 rounded-xl overflow-hidden shadow-lg">
                <table class="w-full">
                    <thead class="bg-slate-700">
                        <tr>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Refund No</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Date</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Original Payment</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Supplier/Customer</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Reason</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Amount</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Status</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        <tr v-for="r in refunds.data" :key="r.id" class="hover:bg-slate-700/50">
                            <td class="px-4 py-4 text-white font-medium">{{ r.refund_no }}</td>
                            <td class="px-4 py-4 text-slate-300">{{ r.refund_date }}</td>
                            <td class="px-4 py-4 text-slate-400">{{ r.original_payment_no }}</td>
                            <td class="px-4 py-4 text-slate-300">{{ r.payee_name }}</td>
                            <td class="px-4 py-4 text-slate-400 text-sm">{{ r.reason }}</td>
                            <td class="px-4 py-4 text-red-400 font-bold">{{ formatCurrency(r.amount) }}</td>
                            <td class="px-4 py-4"><span :class="statusClass(r.status)" class="px-2 py-1 rounded text-xs">{{ r.status }}</span></td>
                            <td class="px-4 py-4">
                                <div class="flex gap-2">
                                    <a :href="'/payment-refunds/' + r.id + '/edit'" class="text-blue-400 hover:text-blue-300">Edit</a>
                                    <button v-if="r.status === 'Pending'" @click="approveRefund(r)" class="text-green-400 hover:text-green-300">Approve</button>
                                    <button v-if="r.status === 'Approved'" @click="processRefund(r)" class="text-emerald-400 hover:text-emerald-300">Process</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex justify-between items-center mt-6 text-slate-400">
                <span>Showing {{ refunds.from }}-{{ refunds.to }} of {{ refunds.total }}</span>
                <div class="flex gap-2">
                    <a v-if="refunds.prev_page_url" :href="refunds.prev_page_url" class="px-4 py-2 bg-slate-700 rounded hover:bg-slate-600">Previous</a>
                    <a v-if="refunds.next_page_url" :href="refunds.next_page_url" class="px-4 py-2 bg-slate-700 rounded hover:bg-slate-600">Next</a>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Sidebar from '../../Components/Sidebar.vue';

const props = defineProps({
    refunds: Object,
    stats: { type: Object, default: () => ({ pending: 0, approved: 0, totalAmount: 0 }) }
});

const formatCurrency = (val) => (parseFloat(val) || 0).toLocaleString('en-US', { minimumFractionDigits: 2 });

const statusClass = (status) => ({
    'Processed': 'bg-emerald-500/20 text-emerald-400',
    'Approved': 'bg-green-500/20 text-green-400',
    'Pending': 'bg-yellow-500/20 text-yellow-400'
}[status] || 'bg-slate-500/20 text-slate-400');

const approveRefund = (item) => {
    router.put('/payment-refunds/' + item.id + '/approve');
};

const processRefund = (item) => {
    router.put('/payment-refunds/' + item.id + '/process');
};
</script>

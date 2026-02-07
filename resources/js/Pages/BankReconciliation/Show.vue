<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="bank-reconciliation" />

        <div class="ml-64 p-8">
            <div class="mb-8">
                <a href="/bank-reconciliation" class="text-slate-400 hover:text-white mb-2 inline-block">&larr; Back to Reconciliations</a>
                <h2 class="text-3xl font-bold text-white">Bank Reconciliation Details</h2>
            </div>

            <div class="bg-slate-800 rounded-xl p-6 max-w-4xl mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-slate-400 text-sm mb-1">BR Number</label>
                        <p class="text-white text-lg font-medium">{{ reconciliation.br_no }}</p>
                    </div>

                    <div>
                        <label class="block text-slate-400 text-sm mb-1">Bank</label>
                        <p class="text-white text-lg font-medium">{{ reconciliation.bank_name }}</p>
                    </div>

                    <div>
                        <label class="block text-slate-400 text-sm mb-1">Period Month</label>
                        <p class="text-white text-lg">{{ reconciliation.month }}</p>
                    </div>

                    <div>
                        <label class="block text-slate-400 text-sm mb-1">Period Year</label>
                        <p class="text-white text-lg">{{ reconciliation.year }}</p>
                    </div>

                    <div>
                        <label class="block text-slate-400 text-sm mb-1">Bank Statement Balance</label>
                        <p class="text-blue-400 text-lg font-medium">{{ formatCurrency(reconciliation.balance) }}</p>
                    </div>

                    <div>
                        <label class="block text-slate-400 text-sm mb-1">Book Balance</label>
                        <p class="text-emerald-400 text-lg font-medium">{{ formatCurrency(reconciliation.beginning_balance) }}</p>
                    </div>

                    <div>
                        <label class="block text-slate-400 text-sm mb-1">Difference</label>
                        <p class="text-lg font-medium" :class="reconciliation.diff == 0 ? 'text-green-400' : 'text-red-400'">
                            {{ formatCurrency(reconciliation.diff) }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-slate-400 text-sm mb-1">Status</label>
                        <span :class="statusClass(reconciliation.status)" class="px-3 py-1 rounded text-sm">
                            {{ reconciliation.status }}
                        </span>
                    </div>

                    <div>
                        <label class="block text-slate-400 text-sm mb-1">Payment Type</label>
                        <p class="text-white">{{ reconciliation.payment_type || 'N/A' }}</p>
                    </div>

                    <div>
                        <label class="block text-slate-400 text-sm mb-1">Amount</label>
                        <p class="text-white">{{ formatCurrency(reconciliation.amount) }}</p>
                    </div>

                    <div>
                        <label class="block text-slate-400 text-sm mb-1">OD Balance</label>
                        <p class="text-white">{{ formatCurrency(reconciliation.od_balance) }}</p>
                    </div>

                    <div>
                        <label class="block text-slate-400 text-sm mb-1">Registered By</label>
                        <p class="text-white">{{ reconciliation.registered_by || 'N/A' }}</p>
                    </div>

                    <div>
                        <label class="block text-slate-400 text-sm mb-1">Date Registered</label>
                        <p class="text-white">{{ reconciliation.date_registered || 'N/A' }}</p>
                    </div>

                    <div v-if="reconciliation.checked_by">
                        <label class="block text-slate-400 text-sm mb-1">Checked By</label>
                        <p class="text-white">{{ reconciliation.checked_by }}</p>
                    </div>

                    <div v-if="reconciliation.approved_by">
                        <label class="block text-slate-400 text-sm mb-1">Approved By</label>
                        <p class="text-white">{{ reconciliation.approved_by }}</p>
                    </div>
                </div>

                <div v-if="reconciliation.statement" class="mt-6">
                    <label class="block text-slate-400 text-sm mb-2">Bank Statement</label>
                    <a :href="'/storage/' + reconciliation.statement" target="_blank" 
                       class="text-blue-400 hover:text-blue-300 underline">
                        View Statement
                    </a>
                </div>
            </div>

            <!-- Related Records (Payment and Collection) -->
            <div v-if="relatedRecords && relatedRecords.length > 1" class="bg-slate-800 rounded-xl p-6">
                <h3 class="text-xl font-bold text-white mb-4">Related Records</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">ID</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Payment Type</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Amount</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Balance</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            <tr v-for="record in relatedRecords" :key="record.id" 
                                :class="record.id === reconciliation.id ? 'bg-emerald-500/10' : 'hover:bg-slate-700/50'">
                                <td class="px-4 py-3 text-white">{{ record.id }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ record.payment_type || 'N/A' }}</td>
                                <td class="px-4 py-3 text-blue-400">{{ formatCurrency(record.amount) }}</td>
                                <td class="px-4 py-3 text-emerald-400">{{ formatCurrency(record.balance) }}</td>
                                <td class="px-4 py-3">
                                    <span :class="statusClass(record.status)" class="px-2 py-1 rounded text-xs">
                                        {{ record.status }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-4 mt-6">
                <a href="/bank-reconciliation" class="bg-slate-600 hover:bg-slate-500 text-white px-6 py-3 rounded-lg">Back</a>
                <button v-if="reconciliation.status === 'Pending'" @click="checkRecon" 
                    class="bg-yellow-600 hover:bg-yellow-700 text-white px-6 py-3 rounded-lg">
                    Check
                </button>
                <button v-if="reconciliation.status === 'Checked'" @click="approveRecon" 
                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg">
                    Approve
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3';
import Sidebar from '../../Components/Sidebar.vue';

const props = defineProps({
    reconciliation: Object,
    relatedRecords: { type: Array, default: () => [] }
});

const formatCurrency = (val) => (parseFloat(val) || 0).toLocaleString('en-US', { minimumFractionDigits: 2 });

const statusClass = (status) => ({
    'Approved': 'bg-green-500/20 text-green-400',
    'Checked': 'bg-blue-500/20 text-blue-400',
    'Pending': 'bg-yellow-500/20 text-yellow-400'
}[status] || 'bg-slate-500/20 text-slate-400');

const checkRecon = () => {
    router.put('/bank-reconciliation/' + props.reconciliation.id + '/check');
};

const approveRecon = () => {
    router.put('/bank-reconciliation/' + props.reconciliation.id + '/approve');
};
</script>

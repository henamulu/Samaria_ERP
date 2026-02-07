<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="budgets" />
        <div class="ml-64 p-8">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h2 class="text-3xl font-bold text-white mb-2">Budget Details</h2>
                    <p class="text-slate-400">Budget No: {{ budget.b_no }}</p>
                </div>
                <div class="flex gap-3">
                    <button @click="printBudget" class="bg-slate-600 hover:bg-slate-500 text-white px-4 py-2 rounded-lg">Print</button>
                    <router-link to="/budgets" class="text-emerald-400 hover:text-emerald-300">← Back to List</router-link>
                </div>
            </div>

            <!-- Budget Summary -->
            <div class="bg-slate-800 rounded-xl p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <p class="text-slate-400 text-sm mb-1">Budget No</p>
                        <p class="text-xl font-bold text-white">{{ budget.b_no }}</p>
                    </div>
                    <div>
                        <p class="text-slate-400 text-sm mb-1">Project</p>
                        <p class="text-xl font-bold text-white">{{ budget.project }}</p>
                    </div>
                    <div>
                        <p class="text-slate-400 text-sm mb-1">Total Amount</p>
                        <p class="text-xl font-bold text-emerald-400">{{ formatCurrency(budget.total_amount || 0) }}</p>
                    </div>
                    <div>
                        <p class="text-slate-400 text-sm mb-1">Status</p>
                        <span :class="statusClass(budget.status)" class="px-3 py-1 rounded text-sm">{{ budget.status }}</span>
                    </div>
                    <div>
                        <p class="text-slate-400 text-sm mb-1">Registered By</p>
                        <p class="text-white">{{ budget.registered_by }}</p>
                    </div>
                    <div>
                        <p class="text-slate-400 text-sm mb-1">Date</p>
                        <p class="text-white">{{ budget.registered_date }}</p>
                    </div>
                </div>
            </div>

            <!-- Budget Details Table -->
            <div class="bg-slate-800 rounded-xl overflow-hidden">
                <div class="p-4 border-b border-slate-700">
                    <h3 class="text-lg font-semibold text-white">Budget Items</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Item</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Quantity</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Unit Price</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            <tr v-for="detail in budgetDetails" :key="detail.id" class="hover:bg-slate-700/50">
                                <td class="px-4 py-3 text-white">{{ detail.project || '-' }}</td>
                                <td class="px-4 py-3 text-slate-300">-</td>
                                <td class="px-4 py-3 text-slate-300">-</td>
                                <td class="px-4 py-3 text-emerald-400 font-medium">{{ formatCurrency(detail.amount) }}</td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-slate-700">
                            <tr>
                                <td colspan="3" class="px-4 py-3 text-right text-white font-semibold">Total:</td>
                                <td class="px-4 py-3 text-emerald-400 font-bold">{{ formatCurrency(totalAmount) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import Sidebar from '../../Components/Sidebar.vue';

const props = defineProps({
    budget: Object,
    budgetDetails: Array
});

const totalAmount = computed(() => {
    return props.budgetDetails?.reduce((sum, d) => sum + parseFloat(d.amount || 0), 0) || 0;
});

const statusClass = (status) => {
    if (status === 'Done') return 'bg-emerald-500/20 text-emerald-400';
    return 'bg-yellow-500/20 text-yellow-400';
};

const formatCurrency = (num) => num ? 'ETB ' + parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 2 }) : 'ETB 0.00';

const printBudget = () => {
    window.print();
};
</script>

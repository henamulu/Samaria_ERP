<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="bank-balance" />

        <div class="ml-64 p-8">
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-white">Bank Balance Detail</h2>
                <p class="text-slate-400">Collection & payment overview per bank</p>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-3 gap-4 mb-6">
                <div class="bg-slate-800 rounded-xl p-4">
                    <p class="text-slate-400 text-sm">Total Bank Balance</p>
                    <p class="text-2xl font-bold text-emerald-400">{{ formatCurrency(totalBalance) }}</p>
                </div>
                <div class="bg-slate-800 rounded-xl p-4">
                    <p class="text-slate-400 text-sm">Total Collections</p>
                    <p class="text-2xl font-bold text-blue-400">{{ formatCurrency(totalCollections) }}</p>
                </div>
                <div class="bg-slate-800 rounded-xl p-4">
                    <p class="text-slate-400 text-sm">Total Payments</p>
                    <p class="text-2xl font-bold text-red-400">{{ formatCurrency(totalPayments) }}</p>
                </div>
            </div>

            <!-- Chart -->
            <div class="bg-slate-800 rounded-xl p-6 mb-6">
                <h3 class="text-white font-semibold mb-4">Collection & Payment per Bank</h3>
                <div class="flex items-end gap-2 h-48 px-2">
                    <div v-for="row in rows" :key="row.id" class="flex-1 flex flex-col items-center gap-1">
                        <div class="flex items-end gap-1 h-36 w-full justify-center">
                            <!-- Collection bar -->
                            <div class="w-5 bg-blue-500 rounded-t"
                                :style="{ height: barHeight(row.total_collection) }"
                                :title="'Collection: ' + formatCurrency(row.total_collection)">
                            </div>
                            <!-- Payment bar -->
                            <div class="w-5 bg-red-500 rounded-t"
                                :style="{ height: barHeight(row.total_payment) }"
                                :title="'Payment: ' + formatCurrency(row.total_payment)">
                            </div>
                        </div>
                        <span class="text-slate-400 text-xs text-center truncate w-full">{{ row.bank_name.split(' ').slice(0,2).join(' ') }}</span>
                    </div>
                </div>
                <!-- Legend -->
                <div class="flex gap-6 mt-4 justify-center">
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 bg-blue-500 rounded"></div>
                        <span class="text-slate-300 text-sm">Collection</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 bg-red-500 rounded"></div>
                        <span class="text-slate-300 text-sm">Payment</span>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-slate-800 rounded-xl overflow-hidden shadow-lg">
                <table class="w-full">
                    <thead class="bg-slate-700">
                        <tr>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Bank</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Branch</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Account No</th>
                            <th class="px-4 py-4 text-right text-xs font-medium text-slate-300 uppercase">Current Balance</th>
                            <th class="px-4 py-4 text-right text-xs font-medium text-slate-300 uppercase">Collection</th>
                            <th class="px-4 py-4 text-right text-xs font-medium text-slate-300 uppercase">Payment</th>
                            <th class="px-4 py-4 text-left text-xs font-medium text-slate-300 uppercase">Alert</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        <tr v-for="row in rows" :key="row.id" class="hover:bg-slate-700/50">
                            <td class="px-4 py-4 text-white font-medium">{{ row.bank_name }}</td>
                            <td class="px-4 py-4 text-slate-300">{{ row.branch_name }}</td>
                            <td class="px-4 py-4 text-slate-300 font-mono text-sm">{{ row.bank_ac_no }}</td>
                            <td class="px-4 py-4 text-right text-emerald-400 font-bold">{{ formatCurrency(row.balance) }}</td>
                            <td class="px-4 py-4 text-right text-blue-400">{{ formatCurrency(row.total_collection) }}</td>
                            <td class="px-4 py-4 text-right text-red-400">{{ formatCurrency(row.total_payment) }}</td>
                            <td class="px-4 py-4">
                                <span v-if="row.status === 'error'" class="bg-red-500/20 text-red-400 px-2 py-1 rounded text-xs font-medium">
                                    Low Balance
                                </span>
                                <span v-else class="text-slate-500 text-xs">—</span>
                            </td>
                        </tr>
                        <tr v-if="rows.length === 0">
                            <td colspan="7" class="px-4 py-8 text-center text-slate-500">No active banks</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import Sidebar from '../../Components/Sidebar.vue';

const props = defineProps({
    rows:         { type: Array,  default: () => [] },
    totalBalance: { type: Number, default: 0 },
});

const totalCollections = computed(() => props.rows.reduce((sum, r) => sum + r.total_collection, 0));
const totalPayments    = computed(() => props.rows.reduce((sum, r) => sum + r.total_payment, 0));

const maxValue = computed(() => {
    let max = 1;
    props.rows.forEach(r => {
        if (r.total_collection > max) max = r.total_collection;
        if (r.total_payment > max)    max = r.total_payment;
    });
    return max;
});

// Returns a CSS height string (percentage of 144px max)
const barHeight = (value) => {
    const pct = (value / maxValue.value) * 100;
    return Math.max(pct, 2) + '%';
};

const formatCurrency = (val) => (parseFloat(val) || 0).toLocaleString('en-US', { minimumFractionDigits: 2 });
</script>

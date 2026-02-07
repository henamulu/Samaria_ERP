<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="stock-balance" />

        <div class="ml-64 p-8">
            <div class="mb-8">
                <a href="/stock-balance" class="text-slate-400 hover:text-white mb-2 inline-block">&larr; Back to Stock Balance</a>
                <h2 class="text-3xl font-bold text-white">Stock Balance Details</h2>
            </div>

            <!-- Item Info Card -->
            <div class="bg-slate-800 rounded-xl p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <label class="block text-slate-400 text-sm mb-1">Item Name</label>
                        <p class="text-white text-lg font-medium">{{ item.item }}</p>
                    </div>
                    <div>
                        <label class="block text-slate-400 text-sm mb-1">Category</label>
                        <p class="text-white text-lg">{{ item.item_category }}</p>
                    </div>
                    <div>
                        <label class="block text-slate-400 text-sm mb-1">Unit</label>
                        <p class="text-white text-lg">{{ item.unit }}</p>
                    </div>
                    <div>
                        <label class="block text-slate-400 text-sm mb-1">Current Balance</label>
                        <p class="text-3xl font-bold" :class="balanceClass(currentBalance)">
                            {{ formatNumber(currentBalance) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-3 gap-6 mb-6">
                <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl p-6">
                    <p class="text-emerald-100 text-sm font-medium">Total Incoming</p>
                    <p class="text-3xl font-bold text-white mt-1">{{ formatNumber(totalIncoming) }}</p>
                </div>
                <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl p-6">
                    <p class="text-red-100 text-sm font-medium">Total Outgoing</p>
                    <p class="text-3xl font-bold text-white mt-1">{{ formatNumber(totalOutgoing) }}</p>
                </div>
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6">
                    <p class="text-blue-100 text-sm font-medium">Net Balance</p>
                    <p class="text-3xl font-bold text-white mt-1">{{ formatNumber(currentBalance) }}</p>
                </div>
            </div>

            <!-- Tabs -->
            <div class="bg-slate-800 rounded-xl overflow-hidden">
                <div class="border-b border-slate-700">
                    <div class="flex">
                        <button @click="activeTab = 'incoming'" 
                            :class="activeTab === 'incoming' ? 'border-b-2 border-emerald-500 text-emerald-400' : 'text-slate-400 hover:text-white'"
                            class="px-6 py-4 font-medium">
                            Incoming ({{ incoming.length }})
                        </button>
                        <button @click="activeTab = 'outgoing'" 
                            :class="activeTab === 'outgoing' ? 'border-b-2 border-red-500 text-red-400' : 'text-slate-400 hover:text-white'"
                            class="px-6 py-4 font-medium">
                            Outgoing ({{ outgoing.length }})
                        </button>
                        <button @click="activeTab = 'registered'" 
                            :class="activeTab === 'registered' ? 'border-b-2 border-blue-500 text-blue-400' : 'text-slate-400 hover:text-white'"
                            class="px-6 py-4 font-medium">
                            Registered Balances ({{ registeredBalances.length }})
                        </button>
                    </div>
                </div>

                <div class="p-6">
                    <!-- Incoming Tab -->
                    <div v-if="activeTab === 'incoming'">
                        <table class="w-full">
                            <thead class="bg-slate-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">GRV No</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Date</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-slate-300 uppercase">Received Qty</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-slate-300 uppercase">Remaining</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Registered By</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700">
                                <tr v-if="incoming.length === 0">
                                    <td colspan="5" class="px-4 py-8 text-center text-slate-400">No incoming records</td>
                                </tr>
                                <tr v-for="record in incoming" :key="record.id" class="hover:bg-slate-700/50">
                                    <td class="px-4 py-3 text-white">{{ record.grv_no || 'N/A' }}</td>
                                    <td class="px-4 py-3 text-slate-300">{{ record.date || 'N/A' }}</td>
                                    <td class="px-4 py-3 text-right text-emerald-400">{{ formatNumber(record.quantity) }}</td>
                                    <td class="px-4 py-3 text-right text-emerald-400">{{ formatNumber(record.remaning) }}</td>
                                    <td class="px-4 py-3 text-slate-300">{{ record.registered_by || 'N/A' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Outgoing Tab -->
                    <div v-if="activeTab === 'outgoing'">
                        <table class="w-full">
                            <thead class="bg-slate-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Delivery No</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Date</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-slate-300 uppercase">Quantity</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Project</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Registered By</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700">
                                <tr v-if="outgoing.length === 0">
                                    <td colspan="5" class="px-4 py-8 text-center text-slate-400">No outgoing records</td>
                                </tr>
                                <tr v-for="record in outgoing" :key="record.id" class="hover:bg-slate-700/50">
                                    <td class="px-4 py-3 text-white">{{ record.d_no || 'N/A' }}</td>
                                    <td class="px-4 py-3 text-slate-300">{{ record.date || 'N/A' }}</td>
                                    <td class="px-4 py-3 text-right text-red-400">{{ formatNumber(record.quantity) }}</td>
                                    <td class="px-4 py-3 text-slate-300">{{ record.project || 'N/A' }}</td>
                                    <td class="px-4 py-3 text-slate-300">{{ record.registered_by || 'N/A' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Registered Balances Tab -->
                    <div v-if="activeTab === 'registered'">
                        <table class="w-full">
                            <thead class="bg-slate-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">SR No</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Source</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Used For</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-slate-300 uppercase">SR Quantity</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-slate-300 uppercase">Stock Balance</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700">
                                <tr v-if="registeredBalances.length === 0">
                                    <td colspan="6" class="px-4 py-8 text-center text-slate-400">No registered balances</td>
                                </tr>
                                <tr v-for="record in registeredBalances" :key="record.id" class="hover:bg-slate-700/50">
                                    <td class="px-4 py-3 text-white">{{ record.sr_no || 'N/A' }}</td>
                                    <td class="px-4 py-3 text-slate-300">{{ record.sb_from || 'N/A' }}</td>
                                    <td class="px-4 py-3 text-slate-300">{{ record.used_for || 'N/A' }}</td>
                                    <td class="px-4 py-3 text-right text-slate-300">{{ formatNumber(record.sr_quantity) }}</td>
                                    <td class="px-4 py-3 text-right text-blue-400 font-medium">{{ formatNumber(record.stock_balance) }}</td>
                                    <td class="px-4 py-3 text-slate-300">{{ record.registered_date || 'N/A' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import Sidebar from '../../Components/Sidebar.vue';

const props = defineProps({
    item: Object,
    currentBalance: Number,
    totalIncoming: Number,
    totalOutgoing: Number,
    incoming: { type: Array, default: () => [] },
    outgoing: { type: Array, default: () => [] },
    registeredBalances: { type: Array, default: () => [] }
});

const activeTab = ref('incoming');

const formatNumber = (num) => (parseFloat(num) || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const balanceClass = (balance) => {
    if (balance <= 0) return 'text-red-400';
    if (balance < 100) return 'text-yellow-400';
    return 'text-green-400';
};
</script>

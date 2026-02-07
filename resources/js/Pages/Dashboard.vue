<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="dashboard" />

        <div class="ml-64 p-8">
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-white mb-2">Dashboard</h2>
                <p class="text-slate-400">System overview and statistics</p>
            </div>

            <!-- Main Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 shadow-lg">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-blue-100 text-sm font-medium">Customers</p>
                            <p class="text-3xl font-bold text-white mt-1">{{ formatNumber(stats.customers) }}</p>
                        </div>
                        <a href="/customers" class="text-white/60 hover:text-white text-xs">View all →</a>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl p-6 shadow-lg">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-emerald-100 text-sm font-medium">Suppliers</p>
                            <p class="text-3xl font-bold text-white mt-1">{{ formatNumber(stats.suppliers) }}</p>
                        </div>
                        <a href="/suppliers" class="text-white/60 hover:text-white text-xs">View all →</a>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl p-6 shadow-lg">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-amber-100 text-sm font-medium">Transporters</p>
                            <p class="text-3xl font-bold text-white mt-1">{{ formatNumber(stats.transporters) }}</p>
                        </div>
                        <a href="/transporters" class="text-white/60 hover:text-white text-xs">View all →</a>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-6 shadow-lg">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-purple-100 text-sm font-medium">Users</p>
                            <p class="text-3xl font-bold text-white mt-1">{{ formatNumber(stats.users) }}</p>
                        </div>
                        <a href="/users" class="text-white/60 hover:text-white text-xs">View all →</a>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Deliveries Chart -->
                <div class="bg-slate-800 rounded-xl p-6 shadow-lg">
                    <h3 class="text-lg font-semibold text-white mb-4">Deliveries by Status</h3>
                    <div class="h-64">
                        <canvas ref="deliveriesChart"></canvas>
                    </div>
                </div>

                <!-- Payments Chart -->
                <div class="bg-slate-800 rounded-xl p-6 shadow-lg">
                    <h3 class="text-lg font-semibold text-white mb-4">Monthly Activity</h3>
                    <div class="h-64">
                        <canvas ref="activityChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Financial Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 shadow-lg">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-blue-100 text-sm font-medium">Bank Balance</p>
                            <p class="text-3xl font-bold text-white mt-1">{{ formatCurrency(stats.bankBalance) }}</p>
                        </div>
                        <svg class="w-8 h-8 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-teal-500 to-teal-600 rounded-xl p-6 shadow-lg">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-teal-100 text-sm font-medium">Uncollected</p>
                            <p class="text-3xl font-bold text-white mt-1">{{ formatCurrency(stats.uncollected) }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="/reports" class="text-white/60 hover:text-white text-xs">Detail</a>
                            <svg class="w-4 h-4 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-teal-500 to-teal-600 rounded-xl p-6 shadow-lg">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-teal-100 text-sm font-medium">Unpaid Supplier</p>
                            <p class="text-3xl font-bold text-white mt-1">{{ formatCurrency(stats.unpaidSupplier) }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="/reports" class="text-white/60 hover:text-white text-xs">Detail</a>
                            <svg class="w-4 h-4 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- More Financial Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-gradient-to-br from-teal-500 to-teal-600 rounded-xl p-6 shadow-lg">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-teal-100 text-sm font-medium">Unpaid Transport</p>
                            <p class="text-3xl font-bold text-white mt-1">{{ formatCurrency(stats.unpaidTransport) }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="/transporter-payments" class="text-white/60 hover:text-white text-xs">Detail</a>
                            <svg class="w-4 h-4 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 shadow-lg">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-blue-100 text-sm font-medium">Expected VAT</p>
                            <p class="text-3xl font-bold text-white mt-1">{{ formatCurrency(stats.expectedVAT) }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="/reports" class="text-white/60 hover:text-white text-xs">Detail</a>
                            <svg class="w-4 h-4 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 shadow-lg">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-blue-100 text-sm font-medium">Unearned Income</p>
                            <p class="text-3xl font-bold text-white mt-1">{{ formatCurrency(stats.unearnedIncome) }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="/reports" class="text-white/60 hover:text-white text-xs">Detail</a>
                            <svg class="w-4 h-4 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Operations Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-slate-800 rounded-xl p-6 shadow-lg">
                    <h3 class="text-lg font-semibold text-white mb-4">Deliveries</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-400">Total</span>
                            <span class="text-2xl font-bold text-white">{{ formatNumber(stats.totalDeliveries) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-400">Pending</span>
                            <span class="text-lg font-medium text-yellow-400">{{ formatNumber(stats.pendingDeliveries) }}</span>
                        </div>
                        <div class="w-full bg-slate-700 rounded-full h-2 mt-2">
                            <div class="bg-emerald-500 h-2 rounded-full" :style="{ width: deliveryProgress + '%' }"></div>
                        </div>
                        <p class="text-xs text-slate-500">{{ deliveryProgress.toFixed(1) }}% completed</p>
                    </div>
                    <a href="/deliveries" class="mt-4 inline-block text-emerald-400 hover:text-emerald-300 text-sm">View deliveries →</a>
                </div>
                <div class="bg-slate-800 rounded-xl p-6 shadow-lg">
                    <h3 class="text-lg font-semibold text-white mb-4">Payments</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-400">Total</span>
                            <span class="text-2xl font-bold text-white">{{ formatNumber(stats.totalPayments) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-400">Pending</span>
                            <span class="text-lg font-medium text-yellow-400">{{ formatNumber(stats.pendingPayments) }}</span>
                        </div>
                        <div class="w-full bg-slate-700 rounded-full h-2 mt-2">
                            <div class="bg-blue-500 h-2 rounded-full" :style="{ width: paymentProgress + '%' }"></div>
                        </div>
                        <p class="text-xs text-slate-500">{{ paymentProgress.toFixed(1) }}% processed</p>
                    </div>
                    <a href="/payments" class="mt-4 inline-block text-emerald-400 hover:text-emerald-300 text-sm">View payments →</a>
                </div>
                <div class="bg-slate-800 rounded-xl p-6 shadow-lg">
                    <h3 class="text-lg font-semibold text-white mb-4">Orders</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-400">Sales Orders</span>
                            <span class="text-2xl font-bold text-white">{{ formatNumber(stats.salesOrders) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-400">Purchase Orders</span>
                            <span class="text-lg font-medium text-white">{{ formatNumber(stats.purchaseOrders) }}</span>
                        </div>
                    </div>
                    <a href="/sales-orders" class="mt-4 inline-block text-emerald-400 hover:text-emerald-300 text-sm">View orders →</a>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-slate-800 rounded-xl p-6 shadow-lg">
                <h3 class="text-xl font-bold text-white mb-4">Quick Actions</h3>
                <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
                    <a href="/customers/create" class="flex flex-col items-center p-4 bg-slate-700 hover:bg-slate-600 rounded-lg transition">
                        <svg class="w-8 h-8 text-blue-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                        <span class="text-white text-sm">New Customer</span>
                    </a>
                    <a href="/suppliers/create" class="flex flex-col items-center p-4 bg-slate-700 hover:bg-slate-600 rounded-lg transition">
                        <svg class="w-8 h-8 text-emerald-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
                        </svg>
                        <span class="text-white text-sm">New Supplier</span>
                    </a>
                    <a href="/transporters/create" class="flex flex-col items-center p-4 bg-slate-700 hover:bg-slate-600 rounded-lg transition">
                        <svg class="w-8 h-8 text-amber-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                        <span class="text-white text-sm">New Transporter</span>
                    </a>
                    <a href="/users/create" class="flex flex-col items-center p-4 bg-slate-700 hover:bg-slate-600 rounded-lg transition">
                        <svg class="w-8 h-8 text-purple-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <span class="text-white text-sm">New User</span>
                    </a>
                    <a href="/reports" class="flex flex-col items-center p-4 bg-slate-700 hover:bg-slate-600 rounded-lg transition">
                        <svg class="w-8 h-8 text-cyan-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span class="text-white text-sm">Reports</span>
                    </a>
                    <a href="/deliveries" class="flex flex-col items-center p-4 bg-slate-700 hover:bg-slate-600 rounded-lg transition">
                        <svg class="w-8 h-8 text-rose-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <span class="text-white text-sm">Deliveries</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Chart, registerables } from 'chart.js';
import Sidebar from '../Components/Sidebar.vue';

Chart.register(...registerables);

const props = defineProps({ 
    stats: { type: Object, default: () => ({}) },
    chartData: { type: Object, default: () => ({}) }
});

const deliveriesChart = ref(null);
const activityChart = ref(null);

const formatNumber = (num) => num ? num.toLocaleString('en-US') : '0';
const formatCurrency = (num) => num ? parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '0.00';

const deliveryProgress = computed(() => {
    const total = props.stats.totalDeliveries || 1;
    const pending = props.stats.pendingDeliveries || 0;
    return ((total - pending) / total) * 100;
});

const paymentProgress = computed(() => {
    const total = props.stats.totalPayments || 1;
    const pending = props.stats.pendingPayments || 0;
    return ((total - pending) / total) * 100;
});

onMounted(() => {
    // Deliveries by Status Chart
    if (deliveriesChart.value) {
        new Chart(deliveriesChart.value, {
            type: 'doughnut',
            data: {
                labels: ['Completed', 'Pending', 'In Progress'],
                datasets: [{
                    data: [
                        (props.stats.totalDeliveries || 0) - (props.stats.pendingDeliveries || 0),
                        props.stats.pendingDeliveries || 0,
                        Math.floor((props.stats.pendingDeliveries || 0) * 0.3)
                    ],
                    backgroundColor: ['#10b981', '#f59e0b', '#3b82f6'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: '#94a3b8' }
                    }
                }
            }
        });
    }

    // Monthly Activity Chart
    if (activityChart.value) {
        new Chart(activityChart.value, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [
                    {
                        label: 'Deliveries',
                        data: [1200, 1900, 1500, 2100, 1800, 2400],
                        backgroundColor: '#10b981'
                    },
                    {
                        label: 'Payments',
                        data: [800, 1200, 1100, 1400, 1300, 1600],
                        backgroundColor: '#3b82f6'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: '#94a3b8' }
                    }
                },
                scales: {
                    x: {
                        ticks: { color: '#94a3b8' },
                        grid: { color: '#334155' }
                    },
                    y: {
                        ticks: { color: '#94a3b8' },
                        grid: { color: '#334155' }
                    }
                }
            }
        });
    }
});
</script>

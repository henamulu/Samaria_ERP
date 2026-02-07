<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="reports" />

        <div class="ml-64 p-8">
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-white mb-2">Reports</h2>
                <p class="text-slate-400">Generate and export business reports</p>
            </div>

            <!-- Sales Reports -->
            <div class="mb-8">
                <h3 class="text-xl font-semibold text-white mb-4">Sales Reports</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <a href="/reports/delivered-items" class="p-4 rounded-xl bg-slate-800 border-2 border-slate-700 hover:border-emerald-500 text-left transition">
                        <h4 class="text-lg font-semibold text-white mb-1">Delivered Items</h4>
                        <p class="text-sm text-slate-400">All delivered items by date range</p>
                    </a>
                    <a href="/reports/delivered-by-category" class="p-4 rounded-xl bg-slate-800 border-2 border-slate-700 hover:border-emerald-500 text-left transition">
                        <h4 class="text-lg font-semibold text-white mb-1">Delivered by Category</h4>
                        <p class="text-sm text-slate-400">Deliveries grouped by item category</p>
                    </a>
                    <a href="/reports/sales-order-by-customer" class="p-4 rounded-xl bg-slate-800 border-2 border-slate-700 hover:border-emerald-500 text-left transition">
                        <h4 class="text-lg font-semibold text-white mb-1">Sales Order by Customer</h4>
                        <p class="text-sm text-slate-400">Sales orders grouped by customer</p>
                    </a>
                    <a href="/reports/uncollected-sales-orders" class="p-4 rounded-xl bg-slate-800 border-2 border-slate-700 hover:border-emerald-500 text-left transition">
                        <h4 class="text-lg font-semibold text-white mb-1">Uncollected Sales Orders</h4>
                        <p class="text-sm text-slate-400">Sales orders created but not paid</p>
                    </a>
                    <a href="/reports/git-by-customer" class="p-4 rounded-xl bg-slate-800 border-2 border-slate-700 hover:border-emerald-500 text-left transition">
                        <h4 class="text-lg font-semibold text-white mb-1">GIT by Customer</h4>
                        <p class="text-sm text-slate-400">Goods in transit grouped by customer</p>
                    </a>
                    <a href="/reports/git-not-delivered" class="p-4 rounded-xl bg-slate-800 border-2 border-slate-700 hover:border-emerald-500 text-left transition">
                        <h4 class="text-lg font-semibold text-white mb-1">GIT Not Delivered</h4>
                        <p class="text-sm text-slate-400">GIT created but not yet delivered</p>
                    </a>
                </div>
            </div>

            <!-- Purchase Reports -->
            <div class="mb-8">
                <h3 class="text-xl font-semibold text-white mb-4">Purchase Reports</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <a href="/reports/purchase-balance" class="p-4 rounded-xl bg-slate-800 border-2 border-slate-700 hover:border-emerald-500 text-left transition">
                        <h4 class="text-lg font-semibold text-white mb-1">Purchase Balance</h4>
                        <p class="text-sm text-slate-400">Outstanding purchase order balances</p>
                    </a>
                    <a href="/reports/po-not-paid" class="p-4 rounded-xl bg-slate-800 border-2 border-slate-700 hover:border-emerald-500 text-left transition">
                        <h4 class="text-lg font-semibold text-white mb-1">PO Not Paid</h4>
                        <p class="text-sm text-slate-400">Purchase orders created but not paid</p>
                    </a>
                    <a href="/reports/grn-not-delivered" class="p-4 rounded-xl bg-slate-800 border-2 border-slate-700 hover:border-emerald-500 text-left transition">
                        <h4 class="text-lg font-semibold text-white mb-1">GRN Not Delivered</h4>
                        <p class="text-sm text-slate-400">Goods received but not delivered</p>
                    </a>
                </div>
            </div>

            <!-- Financial Reports -->
            <div class="mb-8">
                <h3 class="text-xl font-semibold text-white mb-4">Financial Reports</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <a href="/reports/summary" class="p-4 rounded-xl bg-slate-800 border-2 border-slate-700 hover:border-emerald-500 text-left transition">
                        <h4 class="text-lg font-semibold text-white mb-1">Summary Report</h4>
                        <p class="text-sm text-slate-400">Sales & Purchase summary by date</p>
                    </a>
                    <a href="/reports/supplier-finance" class="p-4 rounded-xl bg-slate-800 border-2 border-slate-700 hover:border-emerald-500 text-left transition">
                        <h4 class="text-lg font-semibold text-white mb-1">Supplier Finance</h4>
                        <p class="text-sm text-slate-400">Supplier financial analysis</p>
                    </a>
                    <a href="/reports/payment-summary" class="p-4 rounded-xl bg-slate-800 border-2 border-slate-700 hover:border-emerald-500 text-left transition">
                        <h4 class="text-lg font-semibold text-white mb-1">Payment Summary</h4>
                        <p class="text-sm text-slate-400">Payment transactions summary</p>
                    </a>
                    <a href="/reports/advance-balance" class="p-4 rounded-xl bg-slate-800 border-2 border-slate-700 hover:border-emerald-500 text-left transition">
                        <h4 class="text-lg font-semibold text-white mb-1">Advance Balance</h4>
                        <p class="text-sm text-slate-400">Outstanding advance payments</p>
                    </a>
                    <a href="/reports/unpaid-transport" class="p-4 rounded-xl bg-slate-800 border-2 border-slate-700 hover:border-emerald-500 text-left transition">
                        <h4 class="text-lg font-semibold text-white mb-1">Unpaid Transport</h4>
                        <p class="text-sm text-slate-400">Unpaid transporter payments</p>
                    </a>
                </div>
            </div>

            <!-- Transporter Reports -->
            <div class="mb-8">
                <h3 class="text-xl font-semibold text-white mb-4">Transporter Reports</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <a href="/reports/transporter-payment" class="p-4 rounded-xl bg-slate-800 border-2 border-slate-700 hover:border-emerald-500 text-left transition">
                        <h4 class="text-lg font-semibold text-white mb-1">Transporter Payment</h4>
                        <p class="text-sm text-slate-400">Transporter payment transactions</p>
                    </a>
                    <a href="/reports/transporter-delivered-not-paid" class="p-4 rounded-xl bg-slate-800 border-2 border-slate-700 hover:border-emerald-500 text-left transition">
                        <h4 class="text-lg font-semibold text-white mb-1">Delivered Not Paid</h4>
                        <p class="text-sm text-slate-400">Delivered but payment not requested</p>
                    </a>
                    <a href="/reports/transporter-requested-not-paid" class="p-4 rounded-xl bg-slate-800 border-2 border-slate-700 hover:border-emerald-500 text-left transition">
                        <h4 class="text-lg font-semibold text-white mb-1">Requested Not Paid</h4>
                        <p class="text-sm text-slate-400">Payment requested but not paid</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import Sidebar from '../../Components/Sidebar.vue';
</script>

<template>
    <div class="fixed inset-y-0 left-0 w-64 bg-slate-800 shadow-xl flex flex-col">
        <div class="p-6 border-b border-slate-700">
            <h1 class="text-2xl font-bold text-emerald-400">Samaria ERP</h1>
            <p class="text-slate-400 text-sm">Management System</p>
        </div>
        <nav class="p-4 flex-1 overflow-y-auto">
            <ul class="space-y-1">
                <li>
                    <a href="/dashboard" :class="linkClass('dashboard')">
                        <span class="mr-3">📊</span>
                        Dashboard
                    </a>
                </li>

                <li class="pt-3">
                    <p class="px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Entities</p>
                </li>
                <li>
                    <a href="/customers" :class="linkClass('customers')">
                        <span class="mr-3">👥</span>
                        Customers
                    </a>
                </li>
                <li>
                    <a href="/customer-agreements" :class="linkClass('customer-agreements')">
                        <span class="mr-3">📋</span>
                        Customer Agreements
                    </a>
                </li>
                <li>
                    <a href="/suppliers" :class="linkClass('suppliers')">
                        <span class="mr-3">🏭</span>
                        Suppliers
                    </a>
                </li>
                <li>
                    <a href="/supplier-agreements" :class="linkClass('supplier-agreements')">
                        <span class="mr-3">📄</span>
                        Supplier Agreements
                    </a>
                </li>
                <li>
                    <a href="/transporters" :class="linkClass('transporters')">
                        <span class="mr-3">🚚</span>
                        Transporters
                    </a>
                </li>
                <li>
                    <a href="/transporter-agreements" :class="linkClass('transporter-agreements')">
                        <span class="mr-3">📄</span>
                        Transporter Agreements
                    </a>
                </li>

                <li class="pt-3">
                    <p class="px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Sales & Purchasing</p>
                </li>
                <li>
                    <a href="/sales-orders" :class="linkClass('sales-orders')">
                        <span class="mr-3">📝</span>
                        Sales Orders
                    </a>
                </li>
                <li>
                    <a href="/proforma-invoices" :class="linkClass('proforma-invoices')">
                        <span class="mr-3">📄</span>
                        Proforma Invoices
                    </a>
                </li>
                <li>
                    <a href="/purchase-orders" :class="linkClass('purchase-orders')">
                        <span class="mr-3">🛒</span>
                        Purchase Orders
                    </a>
                </li>
                <li>
                    <a href="/purchase-requisitions" :class="linkClass('purchase-requisitions')">
                        <span class="mr-3">📋</span>
                        Purchase Requisitions
                    </a>
                </li>
                <li>
                    <a href="/store-requisitions" :class="linkClass('store-requisitions')">
                        <span class="mr-3">📋</span>
                        Store Requisitions
                    </a>
                </li>
                <li>
                    <a href="/deliveries" :class="linkClass('deliveries')">
                        <span class="mr-3">📦</span>
                        Deliveries
                    </a>
                </li>
                <li>
                    <a href="/deliveries/reconciliation" :class="linkClass('reconciliation')">
                        <span class="mr-3">📊</span>
                        Reconciliation
                    </a>
                </li>
                <li>
                    <a href="/goods-in-transit" :class="linkClass('goods-in-transit')">
                        <span class="mr-3">🚛</span>
                        Goods In Transit
                    </a>
                </li>
                <li>
                    <a href="/goods-receive" :class="linkClass('goods-receive')">
                        <span class="mr-3">📥</span>
                        Goods Receive (GRN)
                    </a>
                </li>
                <li>
                    <a href="/collections" :class="linkClass('collections')">
                        <span class="mr-3">💵</span>
                        Collections
                    </a>
                </li>
                <li>
                    <a href="/stock-balance" :class="linkClass('stock-balance')">
                        <span class="mr-3">📦</span>
                        Stock Balance
                    </a>
                </li>

                <li class="pt-3">
                    <p class="px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Finance</p>
                </li>
                <li>
                    <a href="/payments" :class="linkClass('payments')">
                        <span class="mr-3">💳</span>
                        Payments
                    </a>
                </li>
                <li>
                    <a href="/credit-payments" :class="linkClass('credit-payments')">
                        <span class="mr-3">💸</span>
                        Credit Payments
                    </a>
                </li>
                <li>
                    <a href="/transporter-payments" :class="linkClass('transporter-payments')">
                        <span class="mr-3">🚚</span>
                        Transporter Payments
                    </a>
                </li>
                <li>
                    <a href="/payment-refunds" :class="linkClass('payment-refunds')">
                        <span class="mr-3">↩️</span>
                        Payment Refunds
                    </a>
                </li>
                <li>
                    <a href="/bank-transfers" :class="linkClass('bank-transfers')">
                        <span class="mr-3">🏦</span>
                        Bank Transfers
                    </a>
                </li>
                <li>
                    <a href="/bank-reconciliation" :class="linkClass('bank-reconciliation')">
                        <span class="mr-3">📊</span>
                        Bank Reconciliation
                    </a>
                </li>
                <li>
                    <a href="/banks" :class="linkClass('banks')">
                        <span class="mr-3">🏦</span>
                        Bank List
                    </a>
                </li>
                <li>
                    <a href="/bank-balance" :class="linkClass('bank-balance')">
                        <span class="mr-3">💰</span>
                        Bank Balance
                    </a>
                </li>
                <li>
                    <a href="/settlements" :class="linkClass('settlements')">
                        <span class="mr-3">✅</span>
                        Settlements
                    </a>
                </li>

                <li class="pt-3">
                    <p class="px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Management</p>
                </li>
                <li>
                    <a href="/approvals" :class="[linkClass('approvals'), 'relative']">
                        <span class="mr-3">👍</span>
                        Approvals
                        <span v-if="pendingApprovals > 0" class="absolute top-1 right-1 bg-red-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                            {{ pendingApprovals > 99 ? '99+' : pendingApprovals }}
                        </span>
                    </a>
                </li>
                <li>
                    <a href="/reports" :class="linkClass('reports')">
                        <span class="mr-3">📈</span>
                        Reports
                    </a>
                </li>
                <li>
                    <a href="/reports/summary" :class="linkClass('summary-report')">
                        <span class="mr-3 ml-4">📊</span>
                        Summary Report
                    </a>
                </li>
                <li>
                    <a href="/reports/supplier-finance" :class="linkClass('supplier-finance')">
                        <span class="mr-3 ml-4">💰</span>
                        Supplier Finance
                    </a>
                </li>
                <li>
                    <a href="/budget-requests" :class="linkClass('budget-requests')">
                        <span class="mr-3">💰</span>
                        Budget Management
                    </a>
                </li>
                <li>
                    <a href="/market-prices" :class="linkClass('market-prices')">
                        <span class="mr-3">📊</span>
                        Market Price
                    </a>
                </li>
                <li>
                    <a href="/coupons" :class="linkClass('coupons')">
                        <span class="mr-3">🎫</span>
                        Coupon Management
                    </a>
                </li>
                <li>
                    <a href="/insurance-policies" :class="linkClass('insurance-policies')">
                        <span class="mr-3">🛡️</span>
                        Insurance Policy
                    </a>
                </li>
                <li>
                    <a href="/users" :class="linkClass('users')">
                        <span class="mr-3">👤</span>
                        Users
                    </a>
                </li>
            </ul>
        </nav>
        <div class="p-4 border-t border-slate-700">
            <button @click="logout" class="w-full flex items-center px-4 py-3 text-slate-300 hover:bg-red-600/20 hover:text-red-400 rounded-lg transition">
                <span class="mr-3">🚪</span>
                Logout
            </button>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

const props = defineProps({
    active: { type: String, default: 'dashboard' }
});

const page = usePage();
const pendingApprovals = computed(() => page.props.notifications?.pendingApprovals || 0);

const linkClass = (name) => {
    const base = 'flex items-center px-4 py-2 rounded-lg transition text-sm relative';
    return name === props.active 
        ? base + ' text-white bg-emerald-600' 
        : base + ' text-slate-300 hover:bg-slate-700';
};

const logout = () => {
    localStorage.removeItem('auth_token');
    router.post('/logout');
};
</script>

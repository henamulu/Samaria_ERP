<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="approvals" />

        <div class="ml-64 p-8">
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-white mb-2">Pending Approvals</h2>
                <p class="text-slate-400">Review, check and approve pending items</p>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-slate-800 rounded-xl p-6 border-l-4 border-yellow-500">
                    <p class="text-slate-400 text-sm">Deliveries</p>
                    <p class="text-3xl font-bold text-white mt-1">{{ pendingDeliveries.length + checkedDeliveries.length }}</p>
                    <p class="text-xs text-slate-500 mt-1">{{ pendingDeliveries.length }} to check &middot; {{ checkedDeliveries.length }} to approve</p>
                </div>
                <div class="bg-slate-800 rounded-xl p-6 border-l-4 border-blue-500">
                    <p class="text-slate-400 text-sm">Payments</p>
                    <p class="text-3xl font-bold text-white mt-1">{{ pendingPayments.length + checkedPayments.length }}</p>
                    <p class="text-xs text-slate-500 mt-1">{{ pendingPayments.length }} to check &middot; {{ checkedPayments.length }} to approve</p>
                </div>
                <div class="bg-slate-800 rounded-xl p-6 border-l-4 border-purple-500">
                    <p class="text-slate-400 text-sm">Sales Orders</p>
                    <p class="text-3xl font-bold text-white mt-1">{{ pendingSalesOrders.length + checkedSalesOrders.length }}</p>
                    <p class="text-xs text-slate-500 mt-1">{{ pendingSalesOrders.length }} to check &middot; {{ checkedSalesOrders.length }} to approve</p>
                </div>
                <div class="bg-slate-800 rounded-xl p-6 border-l-4 border-emerald-500">
                    <p class="text-slate-400 text-sm">Customers</p>
                    <p class="text-3xl font-bold text-white mt-1">{{ pendingCustomers.length + checkedCustomers.length }}</p>
                    <p class="text-xs text-slate-500 mt-1">{{ pendingCustomers.length }} to check &middot; {{ checkedCustomers.length }} to approve</p>
                </div>
            </div>

            <!-- Tabs -->
            <div class="flex gap-2 mb-6">
                <button @click="activeTab = 'deliveries'" :class="tabClass('deliveries')" class="px-4 py-2 rounded-lg font-medium transition">
                    Deliveries ({{ pendingDeliveries.length + checkedDeliveries.length }})
                </button>
                <button @click="activeTab = 'payments'" :class="tabClass('payments')" class="px-4 py-2 rounded-lg font-medium transition">
                    Payments ({{ pendingPayments.length + checkedPayments.length }})
                </button>
                <button @click="activeTab = 'salesOrders'" :class="tabClass('salesOrders')" class="px-4 py-2 rounded-lg font-medium transition">
                    Sales Orders ({{ pendingSalesOrders.length + checkedSalesOrders.length }})
                </button>
                <button @click="activeTab = 'customers'" :class="tabClass('customers')" class="px-4 py-2 rounded-lg font-medium transition">
                    Customers ({{ pendingCustomers.length + checkedCustomers.length }})
                </button>
            </div>

            <!-- ==================== DELIVERIES TAB ==================== -->
            <div v-if="activeTab === 'deliveries'">
                <!-- Pending: Needs Check -->
                <div v-if="canCheck" class="mb-6">
                    <h3 class="text-lg font-semibold text-yellow-400 mb-3">Needs Check (Pending)</h3>
                    <div class="bg-slate-800 rounded-xl overflow-hidden">
                        <table class="w-full">
                            <thead class="bg-slate-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">D.No</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Customer</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Item</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Quantity</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Total</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Registered By</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700">
                                <tr v-for="item in pendingDeliveries" :key="item.id" class="hover:bg-slate-700/50">
                                    <td class="px-4 py-3 text-slate-300">{{ item.d_no }}</td>
                                    <td class="px-4 py-3 text-slate-300">{{ item.issue_date }}</td>
                                    <td class="px-4 py-3 text-white">{{ item.customer?.company_name || item.project }}</td>
                                    <td class="px-4 py-3 text-slate-300">{{ item.item }}</td>
                                    <td class="px-4 py-3 text-slate-300">{{ item.quantity }}</td>
                                    <td class="px-4 py-3 text-emerald-400">{{ formatCurrency(item.total) }}</td>
                                    <td class="px-4 py-3 text-slate-400">{{ item.registered_by }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex gap-2">
                                            <button @click="check('delivery', item.id)" class="bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-1 rounded text-sm">Check</button>
                                            <button @click="reject('delivery', item.id)" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm">Reject</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!pendingDeliveries.length">
                                    <td colspan="8" class="px-4 py-6 text-center text-slate-500">No pending deliveries to check</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- Checked: Needs Approval -->
                <div v-if="canApprove">
                    <h3 class="text-lg font-semibold text-emerald-400 mb-3">Needs Approval (Checked)</h3>
                    <div class="bg-slate-800 rounded-xl overflow-hidden">
                        <table class="w-full">
                            <thead class="bg-slate-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">D.No</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Customer</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Item</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Quantity</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Total</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Checked By</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700">
                                <tr v-for="item in checkedDeliveries" :key="item.id" class="hover:bg-slate-700/50">
                                    <td class="px-4 py-3 text-slate-300">{{ item.d_no }}</td>
                                    <td class="px-4 py-3 text-slate-300">{{ item.issue_date }}</td>
                                    <td class="px-4 py-3 text-white">{{ item.customer?.company_name || item.project }}</td>
                                    <td class="px-4 py-3 text-slate-300">{{ item.item }}</td>
                                    <td class="px-4 py-3 text-slate-300">{{ item.quantity }}</td>
                                    <td class="px-4 py-3 text-emerald-400">{{ formatCurrency(item.total) }}</td>
                                    <td class="px-4 py-3 text-slate-400">{{ item.checked_by }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex gap-2">
                                            <button @click="approve('delivery', item.id)" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1 rounded text-sm">Approve</button>
                                            <button @click="reject('delivery', item.id)" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm">Reject</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!checkedDeliveries.length">
                                    <td colspan="8" class="px-4 py-6 text-center text-slate-500">No checked deliveries awaiting approval</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ==================== PAYMENTS TAB ==================== -->
            <div v-if="activeTab === 'payments'">
                <div v-if="canCheck" class="mb-6">
                    <h3 class="text-lg font-semibold text-yellow-400 mb-3">Needs Check (Pending)</h3>
                    <div class="bg-slate-800 rounded-xl overflow-hidden">
                        <table class="w-full">
                            <thead class="bg-slate-700"><tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">P.No</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Pay To</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Bank</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Amount</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Registered By</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Actions</th>
                            </tr></thead>
                            <tbody class="divide-y divide-slate-700">
                                <tr v-for="item in pendingPayments" :key="item.id" class="hover:bg-slate-700/50">
                                    <td class="px-4 py-3 text-slate-300">{{ item.p_no }}</td>
                                    <td class="px-4 py-3 text-slate-300">{{ item.payment_date }}</td>
                                    <td class="px-4 py-3 text-white">{{ item.pay_to }}</td>
                                    <td class="px-4 py-3 text-slate-300">{{ item.bank }}</td>
                                    <td class="px-4 py-3 text-emerald-400">{{ formatCurrency(item.net_amount) }}</td>
                                    <td class="px-4 py-3 text-slate-400">{{ item.registered_by }}</td>
                                    <td class="px-4 py-3"><div class="flex gap-2">
                                        <button @click="check('payment', item.id)" class="bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-1 rounded text-sm">Check</button>
                                        <button @click="reject('payment', item.id)" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm">Reject</button>
                                    </div></td>
                                </tr>
                                <tr v-if="!pendingPayments.length"><td colspan="7" class="px-4 py-6 text-center text-slate-500">No pending payments to check</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div v-if="canApprove">
                    <h3 class="text-lg font-semibold text-emerald-400 mb-3">Needs Approval (Checked)</h3>
                    <div class="bg-slate-800 rounded-xl overflow-hidden">
                        <table class="w-full">
                            <thead class="bg-slate-700"><tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">P.No</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Pay To</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Bank</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Amount</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Checked By</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Actions</th>
                            </tr></thead>
                            <tbody class="divide-y divide-slate-700">
                                <tr v-for="item in checkedPayments" :key="item.id" class="hover:bg-slate-700/50">
                                    <td class="px-4 py-3 text-slate-300">{{ item.p_no }}</td>
                                    <td class="px-4 py-3 text-slate-300">{{ item.payment_date }}</td>
                                    <td class="px-4 py-3 text-white">{{ item.pay_to }}</td>
                                    <td class="px-4 py-3 text-slate-300">{{ item.bank }}</td>
                                    <td class="px-4 py-3 text-emerald-400">{{ formatCurrency(item.net_amount) }}</td>
                                    <td class="px-4 py-3 text-slate-400">{{ item.checked_by }}</td>
                                    <td class="px-4 py-3"><div class="flex gap-2">
                                        <button @click="approve('payment', item.id)" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1 rounded text-sm">Approve</button>
                                        <button @click="reject('payment', item.id)" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm">Reject</button>
                                    </div></td>
                                </tr>
                                <tr v-if="!checkedPayments.length"><td colspan="7" class="px-4 py-6 text-center text-slate-500">No checked payments awaiting approval</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ==================== SALES ORDERS TAB ==================== -->
            <div v-if="activeTab === 'salesOrders'">
                <div v-if="canCheck" class="mb-6">
                    <h3 class="text-lg font-semibold text-yellow-400 mb-3">Needs Check (Pending)</h3>
                    <div class="bg-slate-800 rounded-xl overflow-hidden">
                        <table class="w-full">
                            <thead class="bg-slate-700"><tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">SO.No</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Customer</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Item</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Quantity</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Total</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Actions</th>
                            </tr></thead>
                            <tbody class="divide-y divide-slate-700">
                                <tr v-for="item in pendingSalesOrders" :key="item.id" class="hover:bg-slate-700/50">
                                    <td class="px-4 py-3 text-slate-300">{{ item.so_no }}</td>
                                    <td class="px-4 py-3 text-slate-300">{{ item.registered_date }}</td>
                                    <td class="px-4 py-3 text-white">{{ item.customer }}</td>
                                    <td class="px-4 py-3 text-slate-300">{{ item.item }}</td>
                                    <td class="px-4 py-3 text-slate-300">{{ item.quantity }}</td>
                                    <td class="px-4 py-3 text-emerald-400">{{ formatCurrency(item.total) }}</td>
                                    <td class="px-4 py-3"><div class="flex gap-2">
                                        <button @click="check('salesOrder', item.id)" class="bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-1 rounded text-sm">Check</button>
                                        <button @click="reject('salesOrder', item.id)" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm">Reject</button>
                                    </div></td>
                                </tr>
                                <tr v-if="!pendingSalesOrders.length"><td colspan="7" class="px-4 py-6 text-center text-slate-500">No pending sales orders to check</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div v-if="canApprove">
                    <h3 class="text-lg font-semibold text-emerald-400 mb-3">Needs Approval (Checked)</h3>
                    <div class="bg-slate-800 rounded-xl overflow-hidden">
                        <table class="w-full">
                            <thead class="bg-slate-700"><tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">SO.No</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Customer</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Item</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Quantity</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Total</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Checked By</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Actions</th>
                            </tr></thead>
                            <tbody class="divide-y divide-slate-700">
                                <tr v-for="item in checkedSalesOrders" :key="item.id" class="hover:bg-slate-700/50">
                                    <td class="px-4 py-3 text-slate-300">{{ item.so_no }}</td>
                                    <td class="px-4 py-3 text-slate-300">{{ item.registered_date }}</td>
                                    <td class="px-4 py-3 text-white">{{ item.customer }}</td>
                                    <td class="px-4 py-3 text-slate-300">{{ item.item }}</td>
                                    <td class="px-4 py-3 text-slate-300">{{ item.quantity }}</td>
                                    <td class="px-4 py-3 text-emerald-400">{{ formatCurrency(item.total) }}</td>
                                    <td class="px-4 py-3 text-slate-400">{{ item.checked_by }}</td>
                                    <td class="px-4 py-3"><div class="flex gap-2">
                                        <button @click="approve('salesOrder', item.id)" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1 rounded text-sm">Approve</button>
                                        <button @click="reject('salesOrder', item.id)" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm">Reject</button>
                                    </div></td>
                                </tr>
                                <tr v-if="!checkedSalesOrders.length"><td colspan="8" class="px-4 py-6 text-center text-slate-500">No checked sales orders awaiting approval</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ==================== CUSTOMERS TAB ==================== -->
            <div v-if="activeTab === 'customers'">
                <div v-if="canCheck" class="mb-6">
                    <h3 class="text-lg font-semibold text-yellow-400 mb-3">Needs Check (Pending)</h3>
                    <div class="bg-slate-800 rounded-xl overflow-hidden">
                        <table class="w-full">
                            <thead class="bg-slate-700"><tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">ID</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Company</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Contact</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Phone</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">TIN</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Registered By</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Actions</th>
                            </tr></thead>
                            <tbody class="divide-y divide-slate-700">
                                <tr v-for="item in pendingCustomers" :key="item.id" class="hover:bg-slate-700/50">
                                    <td class="px-4 py-3 text-slate-300">{{ item.id }}</td>
                                    <td class="px-4 py-3 text-white">{{ item.company_name }}</td>
                                    <td class="px-4 py-3 text-slate-300">{{ item.contact_person }}</td>
                                    <td class="px-4 py-3 text-slate-300">{{ item.phone_no }}</td>
                                    <td class="px-4 py-3 text-slate-300">{{ item.tin_no }}</td>
                                    <td class="px-4 py-3 text-slate-400">{{ item.registered_by }}</td>
                                    <td class="px-4 py-3"><div class="flex gap-2">
                                        <button @click="check('customer', item.id)" class="bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-1 rounded text-sm">Check</button>
                                        <button @click="reject('customer', item.id)" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm">Reject</button>
                                    </div></td>
                                </tr>
                                <tr v-if="!pendingCustomers.length"><td colspan="7" class="px-4 py-6 text-center text-slate-500">No pending customers to check</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div v-if="canApprove">
                    <h3 class="text-lg font-semibold text-emerald-400 mb-3">Needs Approval (Checked)</h3>
                    <div class="bg-slate-800 rounded-xl overflow-hidden">
                        <table class="w-full">
                            <thead class="bg-slate-700"><tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">ID</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Company</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Contact</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Phone</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">TIN</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Checked By</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Actions</th>
                            </tr></thead>
                            <tbody class="divide-y divide-slate-700">
                                <tr v-for="item in checkedCustomers" :key="item.id" class="hover:bg-slate-700/50">
                                    <td class="px-4 py-3 text-slate-300">{{ item.id }}</td>
                                    <td class="px-4 py-3 text-white">{{ item.company_name }}</td>
                                    <td class="px-4 py-3 text-slate-300">{{ item.contact_person }}</td>
                                    <td class="px-4 py-3 text-slate-300">{{ item.phone_no }}</td>
                                    <td class="px-4 py-3 text-slate-300">{{ item.tin_no }}</td>
                                    <td class="px-4 py-3 text-slate-400">{{ item.checked_by }}</td>
                                    <td class="px-4 py-3"><div class="flex gap-2">
                                        <button @click="approve('customer', item.id)" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1 rounded text-sm">Approve</button>
                                        <button @click="reject('customer', item.id)" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm">Reject</button>
                                    </div></td>
                                </tr>
                                <tr v-if="!checkedCustomers.length"><td colspan="7" class="px-4 py-6 text-center text-slate-500">No checked customers awaiting approval</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toast -->
        <div v-if="showToast" class="fixed bottom-4 right-4 bg-emerald-600 text-white px-6 py-3 rounded-lg shadow-lg flex items-center gap-2 z-50">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ toastMessage }}
        </div>
        <!-- Error Toast -->
        <div v-if="showError" class="fixed bottom-4 right-4 bg-red-600 text-white px-6 py-3 rounded-lg shadow-lg flex items-center gap-2 z-50">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            {{ errorMessage }}
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import Sidebar from '../../Components/Sidebar.vue';

const props = defineProps({
    pendingDeliveries: { type: Array, default: () => [] },
    pendingPayments: { type: Array, default: () => [] },
    pendingSalesOrders: { type: Array, default: () => [] },
    pendingCustomers: { type: Array, default: () => [] },
    checkedDeliveries: { type: Array, default: () => [] },
    checkedPayments: { type: Array, default: () => [] },
    checkedSalesOrders: { type: Array, default: () => [] },
    checkedCustomers: { type: Array, default: () => [] },
});

const page = usePage();
const userRole = computed(() => page.props.auth?.role || page.props.auth?.user?.role || 'User');

// Role-based visibility: Supervisor+ can check, Admin can approve
const canCheck = computed(() => ['Admin', 'Supervisor'].includes(userRole.value));
const canApprove = computed(() => userRole.value === 'Admin');

const activeTab = ref('deliveries');
const showToast = ref(false);
const toastMessage = ref('');
const showError = ref(false);
const errorMessage = ref('');

const tabClass = (tab) => activeTab.value === tab ? 'bg-emerald-600 text-white' : 'bg-slate-700 text-slate-300 hover:bg-slate-600';
const formatCurrency = (num) => num ? 'ETB ' + parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 2 }) : 'ETB 0.00';


const getCsrfToken = () => {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
};

const apiCall = async (url, successMsg) => {
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
        });
        const data = await response.json();
        if (!response.ok) {
            showErrorNotification(data.error || 'Operation failed');
            return;
        }
        showToastNotification(successMsg);
        router.reload({ preserveScroll: true });
    } catch (error) {
        showErrorNotification('Network error');
    }
};

const check = (type, id) => {
    apiCall(`/api/approvals/${type}/${id}/check`, 'Item checked successfully!');
};

const approve = (type, id) => {
    apiCall(`/api/approvals/${type}/${id}/approve`, 'Item approved successfully!');
};

const reject = (type, id) => {
    if (!confirm('Are you sure you want to reject this item?')) return;
    apiCall(`/api/approvals/${type}/${id}/reject`, 'Item rejected');
};

const showToastNotification = (message) => { toastMessage.value = message; showToast.value = true; setTimeout(() => { showToast.value = false; }, 3000); };
const showErrorNotification = (message) => { errorMessage.value = message; showError.value = true; setTimeout(() => { showError.value = false; }, 4000); };
</script>

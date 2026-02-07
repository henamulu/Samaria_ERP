<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="reconciliation" />

        <div class="ml-64 p-8">
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-white mb-2">Daily Reconciliation (COB)</h2>
                <p class="text-slate-400">Compare delivered quantities with supplier-reported quantities</p>
            </div>

            <!-- Date Filter -->
            <div class="bg-slate-800 rounded-xl p-6 mb-6">
                <div class="flex items-center gap-4">
                    <div>
                        <label class="block text-slate-300 mb-2">Filter by Date</label>
                        <input v-model="filterDate" type="date" 
                            class="bg-slate-700 border-0 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>
                    <div class="flex items-end">
                        <button @click="loadDeliveries" 
                            class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded-lg">
                            Load Deliveries
                        </button>
                    </div>
                </div>
            </div>

            <!-- Reconciliation Table -->
            <div class="bg-slate-800 rounded-xl overflow-hidden">
                <table class="w-full">
                    <thead class="bg-slate-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">D.No</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Driver</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Plate</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Delivered Qty</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Supplier Qty</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Difference</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-300 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        <tr v-for="delivery in deliveries" :key="delivery.id" class="hover:bg-slate-700/50">
                            <td class="px-4 py-3 text-slate-300">{{ delivery.d_no }}</td>
                            <td class="px-4 py-3 text-slate-300">{{ delivery.issue_date }}</td>
                            <td class="px-4 py-3 text-white">{{ delivery.driver_name || '-' }}</td>
                            <td class="px-4 py-3 text-slate-300">{{ delivery.truck_plate_no || '-' }}</td>
                            <td class="px-4 py-3 text-slate-300">{{ delivery.quantity }} {{ delivery.unit }}</td>
                            <td class="px-4 py-3">
                                <input v-model.number="delivery.supplier_qty" type="number" step="0.01"
                                    class="w-24 bg-slate-700 border-0 rounded px-2 py-1 text-white focus:ring-2 focus:ring-emerald-500" />
                            </td>
                            <td class="px-4 py-3" :class="getDifferenceClass(delivery)">
                                {{ calculateDifference(delivery) }}
                            </td>
                            <td class="px-4 py-3 text-slate-400">{{ delivery.daily_cob || 'Pending' }}</td>
                        </tr>
                        <tr v-if="!deliveries.length">
                            <td colspan="8" class="px-4 py-6 text-center text-slate-500">
                                No unreconciled deliveries found for the selected date
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Actions -->
            <div class="mt-6 flex gap-4">
                <button @click="submitReconciliation" :disabled="processing || !deliveries.length"
                    class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-lg font-medium disabled:opacity-50">
                    {{ processing ? 'Processing...' : 'Save & Submit Reconciliation' }}
                </button>
            </div>

            <!-- Toast -->
            <div v-if="showToast" class="fixed bottom-4 right-4 bg-emerald-600 text-white px-6 py-3 rounded-lg shadow-lg flex items-center gap-2 z-50">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ toastMessage }}
            </div>
            <!-- Error Toast -->
            <div v-if="showError" class="fixed bottom-4 right-4 bg-red-600 text-white px-6 py-3 rounded-lg shadow-lg flex items-center gap-2 z-50">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                {{ errorMessage }}
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import Sidebar from '../../Components/Sidebar.vue';

const props = defineProps({
    deliveries: { type: Array, default: () => [] },
    defaultDate: { type: String, default: '' }
});

const filterDate = ref(props.defaultDate || new Date(Date.now() - 86400000).toISOString().split('T')[0]);
const deliveries = ref([...props.deliveries]);
const processing = ref(false);
const showToast = ref(false);
const toastMessage = ref('');
const showError = ref(false);
const errorMessage = ref('');

const calculateDifference = (delivery) => {
    const delivered = parseFloat(delivery.quantity) || 0;
    const supplier = parseFloat(delivery.supplier_qty) || 0;
    const diff = supplier - delivered;
    if (diff > 0) return `+${diff.toFixed(2)} (Shortage)`;
    if (diff < 0) return `${diff.toFixed(2)} (Surplus)`;
    return '0.00';
};

const getDifferenceClass = (delivery) => {
    const delivered = parseFloat(delivery.quantity) || 0;
    const supplier = parseFloat(delivery.supplier_qty) || 0;
    const diff = supplier - delivered;
    if (diff > 0) return 'text-yellow-400 font-semibold';
    if (diff < 0) return 'text-emerald-400 font-semibold';
    return 'text-slate-300';
};

const loadDeliveries = () => {
    router.get('/deliveries/reconciliation', { date: filterDate.value }, {
        preserveState: true,
        preserveScroll: true,
        only: ['deliveries']
    });
};

const submitReconciliation = () => {
    processing.value = true;
    const data = deliveries.value.map(d => ({
        id: d.id,
        supplier_qty: d.supplier_qty || 0
    }));

    router.post('/deliveries/reconciliation', { deliveries: data, date: filterDate.value }, {
        preserveScroll: true,
        onSuccess: () => {
            showToastNotification('Reconciliation submitted successfully!');
            loadDeliveries();
        },
        onError: (errors) => {
            showErrorNotification(errors.error || 'Failed to submit reconciliation');
        },
        onFinish: () => {
            processing.value = false;
        }
    });
};

const showToastNotification = (message) => {
    toastMessage.value = message;
    showToast.value = true;
    setTimeout(() => { showToast.value = false; }, 3000);
};

const showErrorNotification = (message) => {
    errorMessage.value = message;
    showError.value = true;
    setTimeout(() => { showError.value = false; }, 4000);
};
</script>

<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="store-requisitions" />

        <div class="ml-64 p-8">
            <div class="mb-8">
                <a href="/store-requisitions" class="text-slate-400 hover:text-white mb-2 inline-block">&larr; Back to Store Requisitions</a>
                <h2 class="text-3xl font-bold text-white">Edit Store Requisition</h2>
            </div>

            <form @submit.prevent="submit" class="bg-slate-800 rounded-xl p-6 max-w-4xl">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-slate-300 mb-2">SR Number *</label>
                        <input v-model="form.sr_no" type="text" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Item *</label>
                        <select v-model="form.item" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="">Select Item</option>
                            <option v-for="i in items" :key="i.id" :value="i.id">{{ i.item }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Item Description</label>
                        <input v-model="form.item_desc" type="text"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Quantity *</label>
                        <input v-model="form.sr_quantity" type="number" step="0.01" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Unit</label>
                        <input v-model="form.unit" type="text"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Expected Delivery Date</label>
                        <input v-model="form.expected_delivery_date" type="date"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Priority *</label>
                        <select v-model="form.priority" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="Normal">Normal</option>
                            <option value="High">High</option>
                            <option value="Medium">Medium</option>
                            <option value="Low">Low</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Request Type</label>
                        <select v-model="form.request_type"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="internal">Internal</option>
                            <option value="external">External</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">From *</label>
                        <input v-model="form.sr_from" type="text" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">To</label>
                        <input v-model="form.sr_to" type="text"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Used For</label>
                        <input v-model="form.used_for" type="text"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-slate-300 mb-2">Urgency Reason</label>
                        <textarea v-model="form.urgency_reason" rows="2"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500"></textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-slate-300 mb-2">Remarks</label>
                        <textarea v-model="form.remark" rows="3"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500"></textarea>
                    </div>
                </div>

                <div class="flex gap-4 mt-8">
                    <button type="submit" :disabled="processing"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-lg font-medium disabled:opacity-50">
                        {{ processing ? 'Updating...' : 'Update Store Requisition' }}
                    </button>
                    <a href="/store-requisitions" class="bg-slate-600 hover:bg-slate-500 text-white px-6 py-3 rounded-lg">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import Sidebar from '../../Components/Sidebar.vue';

const props = defineProps({
    requisition: Object,
    items: { type: Array, default: () => [] }
});

const form = ref({
    sr_no: props.requisition?.sr_no || '',
    item: props.requisition?.item || '',
    item_desc: props.requisition?.item_desc || '',
    sr_quantity: props.requisition?.sr_quantity || '',
    unit: props.requisition?.unit || '',
    expected_delivery_date: props.requisition?.expected_delivery_date || new Date().toISOString().split('T')[0],
    priority: props.requisition?.priority || 'Normal',
    urgency_reason: props.requisition?.urgency_reason || '',
    remark: props.requisition?.remark || '',
    used_for: props.requisition?.used_for || '',
    sr_from: props.requisition?.sr_from || '',
    sr_to: props.requisition?.sr_to || '',
    request_type: props.requisition?.request_type || 'internal',
    stock_status: props.requisition?.stock_status || 'pending'
});

const processing = ref(false);

// Auto-fill unit from item
watch(() => form.value.item, (newItemId) => {
    if (newItemId) {
        const item = props.items.find(i => i.id == newItemId);
        if (item) {
            form.value.unit = item.unit;
            form.value.item_desc = item.item;
        }
    }
});

const submit = () => {
    processing.value = true;
    router.put('/store-requisitions/' + props.requisition.id, form.value, {
        onFinish: () => processing.value = false
    });
};
</script>

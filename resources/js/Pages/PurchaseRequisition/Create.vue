<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="purchase-requisitions" />

        <div class="ml-64 p-8">
            <div class="mb-8">
                <a href="/purchase-requisitions" class="text-slate-400 hover:text-white mb-2 inline-block">&larr; Back to Purchase Requisitions</a>
                <h2 class="text-3xl font-bold text-white">Create Purchase Requisition</h2>
            </div>

            <form @submit.prevent="submit" class="bg-slate-800 rounded-xl p-6 max-w-4xl">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-slate-300 mb-2">PR Number *</label>
                        <input v-model="form.pr_no" type="text" required readonly
                            class="w-full bg-slate-600 border-0 rounded-lg px-4 py-3 text-emerald-400 font-medium cursor-not-allowed" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">PR Type *</label>
                        <select v-model="form.pr_type" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="direct">Direct</option>
                            <option value="with_store">With Store</option>
                        </select>
                    </div>

                    <div v-if="form.pr_type === 'with_store'">
                        <label class="block text-slate-300 mb-2">Store Requisition</label>
                        <select v-model="form.sr_id"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="">Select Store Requisition</option>
                            <option v-for="sr in storeRequisitions" :key="sr.id" :value="sr.id">
                                SR {{ sr.sr_no }} - {{ sr.item_desc }} ({{ sr.sr_quantity }} {{ sr.unit }})
                            </option>
                        </select>
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
                        <input v-model="form.pr_quantity" type="number" step="0.01" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Unit</label>
                        <input v-model="form.unit" type="text"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Request From *</label>
                        <input v-model="form.request_from" type="text" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Used For</label>
                        <input v-model="form.used_for" type="text"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Expected Date</label>
                        <input v-model="form.e_date" type="date"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
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
                        {{ processing ? 'Saving...' : 'Create Purchase Requisition' }}
                    </button>
                    <a href="/purchase-requisitions" class="bg-slate-600 hover:bg-slate-500 text-white px-6 py-3 rounded-lg">Cancel</a>
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
    items: { type: Array, default: () => [] },
    storeRequisitions: { type: Array, default: () => [] },
    srData: { type: Object, default: null },
    nextPrNo: { type: String, default: 'PR1' }
});

const form = ref({
    pr_no: props.nextPrNo,
    sr_id: props.srData?.sr_id || '',
    pr_type: props.srData ? 'with_store' : 'direct',
    item: props.srData?.item || '',
    item_desc: props.srData?.item_desc || '',
    pr_quantity: props.srData?.sr_quantity || '',
    unit: props.srData?.unit || '',
    request_from: props.srData?.request_from || '',
    used_for: props.srData?.used_for || '',
    e_date: new Date().toISOString().split('T')[0],
    remark: ''
});

const processing = ref(false);

// Auto-fill from Store Requisition
watch(() => form.value.sr_id, (newSrId) => {
    if (newSrId) {
        const sr = props.storeRequisitions.find(s => s.id == newSrId);
        if (sr) {
            form.value.item = sr.item;
            form.value.item_desc = sr.item_desc;
            form.value.pr_quantity = sr.sr_quantity;
            form.value.unit = sr.unit;
            form.value.used_for = sr.used_for;
        }
    }
});

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
    router.post('/purchase-requisitions', form.value, {
        onFinish: () => processing.value = false
    });
};
</script>

<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="transporter-agreements" />
        <div class="ml-64 p-8">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h2 class="text-3xl font-bold text-white mb-2">Create Transporter Agreement</h2>
                    <p class="text-slate-400">Register a new transport contract</p>
                </div>
                <router-link to="/transporter-agreements" class="text-emerald-400 hover:text-emerald-300">← Back to List</router-link>
            </div>

            <div class="bg-slate-800 rounded-xl p-6 max-w-4xl">
                <form @submit.prevent="submitForm">
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-slate-400 text-sm mb-2">Agreement No <span class="text-red-400">*</span></label>
                                <input v-model="form.a_no" type="text" required class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                            </div>

                            <div>
                                <label class="block text-slate-400 text-sm mb-2">Transporter <span class="text-red-400">*</span></label>
                                <select v-model="form.t_id" required class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white">
                                    <option value="">Select Transporter</option>
                                    <option v-for="t in transporters" :key="t.id" :value="t.id">{{ t.company_name }}</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-slate-400 text-sm mb-2">Plate No <span class="text-red-400">*</span></label>
                                <input v-model="form.plate_no" type="text" required class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                            </div>

                            <div>
                                <label class="block text-slate-400 text-sm mb-2">Item</label>
                                <select v-model="form.item_id" @change="onItemChange" class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white">
                                    <option value="">Select Item</option>
                                    <option v-for="item in items" :key="item.id" :value="item.id">{{ item.item }}</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-slate-400 text-sm mb-2">Unit Price <span class="text-red-400">*</span></label>
                                <input v-model.number="form.unit_price" type="number" step="0.01" min="0" required class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                            </div>

                            <div>
                                <label class="block text-slate-400 text-sm mb-2">Tax %</label>
                                <input v-model.number="form.tax_p" type="number" step="0.01" min="0" max="100" class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                            </div>

                            <div>
                                <label class="block text-slate-400 text-sm mb-2">Size</label>
                                <input v-model="form.size" type="text" class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                            </div>

                            <div>
                                <label class="block text-slate-400 text-sm mb-2">Owner</label>
                                <input v-model="form.owner" type="text" class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                            </div>

                            <div>
                                <label class="block text-slate-400 text-sm mb-2">Site (Customer)</label>
                                <select v-model="form.site" class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white">
                                    <option value="">Select Customer</option>
                                    <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.company_name }}</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-slate-400 text-sm mb-2">Supplier</label>
                                <select v-model="form.supplier" class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white">
                                    <option value="">Select Supplier</option>
                                    <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.supplier_name }}</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-slate-400 text-sm mb-2">Valid From</label>
                                <input v-model="form.valid_from" type="date" lang="en" class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                            </div>

                            <div>
                                <label class="block text-slate-400 text-sm mb-2">Valid To</label>
                                <input v-model="form.valid_to" type="date" lang="en" class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-slate-400 text-sm mb-2">Remark</label>
                                <textarea v-model="form.remark" rows="3" class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex gap-3">
                        <button type="submit" :disabled="loading" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-3 rounded-lg disabled:opacity-50">
                            {{ loading ? 'Creating...' : 'Create Agreement' }}
                        </button>
                        <router-link to="/transporter-agreements" class="flex-1 bg-slate-600 hover:bg-slate-500 text-white px-4 py-3 rounded-lg text-center">Cancel</router-link>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Sidebar from '../../Components/Sidebar.vue';

const props = defineProps({
    transporters: Array,
    suppliers: Array,
    customers: Array,
    items: Array
});

const loading = ref(false);
const form = ref({
    a_no: '',
    t_id: '',
    item_id: '',
    item: '',
    unit_price: 0,
    tax_p: 0,
    size: '',
    plate_no: '',
    site: '',
    owner: '',
    supplier: '',
    valid_from: '',
    valid_to: '',
    remark: ''
});

const onItemChange = () => {
    const item = props.items.find(i => i.id == form.value.item_id);
    if (item) {
        form.value.item = item.item;
    }
};

const submitForm = () => {
    loading.value = true;
    router.post('/transporter-agreements', form.value, {
        preserveState: false,
        onFinish: () => {
            loading.value = false;
        }
    });
};
</script>

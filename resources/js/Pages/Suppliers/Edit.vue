<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="suppliers" />

        <div class="ml-64 p-8">
            <div class="mb-8">
                <a href="/suppliers" class="text-slate-400 hover:text-white mb-2 inline-block">&larr; Back to Suppliers</a>
                <h2 class="text-3xl font-bold text-white">Edit Supplier</h2>
            </div>

            <form @submit.prevent="submit" class="bg-slate-800 rounded-xl p-6 max-w-2xl">
                <div class="grid grid-cols-2 gap-6">
                    <div class="col-span-2">
                        <label class="block text-slate-300 mb-2">Supplier Name *</label>
                        <input v-model="form.supplier_name" type="text" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">TIN Number *</label>
                        <input v-model="form.supplier_tin" type="text" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Category</label>
                        <select v-model="form.supplier_category"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="">Select category</option>
                            <option value="Material">Material</option>
                            <option value="Service">Service</option>
                            <option value="Equipment">Equipment</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Contact Person</label>
                        <input v-model="form.contact_person" type="text"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Phone Number</label>
                        <input v-model="form.phone_number" type="text"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Address</label>
                        <input v-model="form.address" type="text"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Status</label>
                        <select v-model="form.status"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="flex gap-4 mt-8">
                    <button type="submit" :disabled="processing"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-lg font-medium disabled:opacity-50">
                        {{ processing ? 'Saving...' : 'Update Supplier' }}
                    </button>
                    <a href="/suppliers" class="bg-slate-600 hover:bg-slate-500 text-white px-6 py-3 rounded-lg">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Sidebar from '../../Components/Sidebar.vue';

const props = defineProps({ supplier: Object });

const form = ref({
    supplier_name: props.supplier.supplier_name || '',
    supplier_tin: props.supplier.supplier_tin || '',
    supplier_category: props.supplier.supplier_category || '',
    contact_person: props.supplier.contact_person || '',
    phone_number: props.supplier.phone_number || '',
    address: props.supplier.address || '',
    status: props.supplier.status || 'Active'
});

const processing = ref(false);

const submit = () => {
    processing.value = true;
    router.put('/suppliers/' + props.supplier.id, form.value, {
        onFinish: () => processing.value = false
    });
};
</script>

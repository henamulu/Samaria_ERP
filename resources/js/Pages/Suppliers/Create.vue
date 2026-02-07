<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="suppliers" />

        <div class="ml-64 p-8">
            <div class="mb-8">
                <a href="/suppliers" class="text-slate-400 hover:text-white mb-2 inline-block">&larr; Back to Suppliers</a>
                <h2 class="text-3xl font-bold text-white">Add New Supplier</h2>
            </div>

            <form @submit.prevent="submit" class="bg-slate-800 rounded-xl p-6 max-w-2xl">
                <FormErrors />
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

                    <div class="col-span-2">
                        <label class="block text-slate-300 mb-2">Address</label>
                        <input v-model="form.address" type="text"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>
                </div>

                <div class="flex gap-4 mt-8">
                    <button type="submit" :disabled="processing"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-lg font-medium disabled:opacity-50">
                        {{ processing ? 'Saving...' : 'Create Supplier' }}
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
import FormErrors from '../../Components/FormErrors.vue';

const form = ref({
    supplier_name: '',
    supplier_tin: '',
    supplier_category: '',
    contact_person: '',
    phone_number: '',
    address: ''
});

const processing = ref(false);

const submit = () => {
    processing.value = true;
    router.post('/suppliers', form.value, {
        preserveScroll: true,
        onFinish: () => processing.value = false,
        onError: (errors) => {
            console.error('Validation errors:', errors);
        }
    });
};
</script>

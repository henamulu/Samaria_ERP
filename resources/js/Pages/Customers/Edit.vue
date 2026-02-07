<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="customers" />

        <div class="ml-64 p-8">
            <div class="mb-8">
                <a href="/customers" class="text-slate-400 hover:text-white mb-2 inline-block">&larr; Back to Customers</a>
                <h2 class="text-3xl font-bold text-white">Edit Customer</h2>
            </div>

            <form @submit.prevent="submit" class="bg-slate-800 rounded-xl p-6 max-w-2xl">
                <FormErrors />
                <div class="grid grid-cols-2 gap-6">
                    <div class="col-span-2">
                        <label class="block text-slate-300 mb-2">Company Name *</label>
                        <input v-model="form.company_name" type="text" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Customer Type *</label>
                        <select v-model="form.customer_type" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="">Select type</option>
                            <option value="Company">Company</option>
                            <option value="Individual">Individual</option>
                            <option value="Government">Government</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">TIN Number *</label>
                        <input v-model="form.tin_no" type="text" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Contact Person</label>
                        <input v-model="form.contact_person" type="text"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Phone Number</label>
                        <input v-model="form.phone_no" type="text"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Email</label>
                        <input v-model="form.email" type="email"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Office Location</label>
                        <input v-model="form.office_location" type="text"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Status</label>
                        <select v-model="form.status"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="Pending">Pending</option>
                            <option value="Approved">Approved</option>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="flex gap-4 mt-8">
                    <button type="submit" :disabled="processing"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-lg font-medium disabled:opacity-50">
                        {{ processing ? 'Saving...' : 'Update Customer' }}
                    </button>
                    <a href="/customers" class="bg-slate-600 hover:bg-slate-500 text-white px-6 py-3 rounded-lg">Cancel</a>
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

const props = defineProps({
    customer: Object
});

const form = ref({
    company_name: props.customer.company_name || '',
    customer_type: props.customer.customer_type || '',
    tin_no: props.customer.tin_no || '',
    contact_person: props.customer.contact_person || '',
    phone_no: props.customer.phone_no || '',
    email: props.customer.email || '',
    office_location: props.customer.office_location || '',
    status: props.customer.status || 'Pending'
});

const processing = ref(false);

const submit = () => {
    processing.value = true;
    router.put('/customers/' + props.customer.id, form.value, {
        preserveScroll: true,
        onFinish: () => processing.value = false,
        onError: (errors) => {
            console.error('Validation errors:', errors);
        }
    });
};
</script>

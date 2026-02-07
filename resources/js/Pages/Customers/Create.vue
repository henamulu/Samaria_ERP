<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="customers" />

        <div class="ml-64 p-8">
            <div class="mb-8">
                <a href="/customers" class="text-slate-400 hover:text-white mb-2 inline-block">&larr; Back to Customers</a>
                <h2 class="text-3xl font-bold text-white">Add New Customer</h2>
            </div>

            <form @submit.prevent="submit" class="bg-slate-800 rounded-xl p-6 max-w-2xl">
                <FormErrors />
                <div class="grid grid-cols-2 gap-6">
                    <div class="col-span-2">
                        <label class="block text-slate-300 mb-2">Company Name *</label>
                        <input v-model="form.company_name" type="text" required
                            :class="['w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500', $page.props.errors?.company_name ? 'border-red-500 border' : '']" />
                        <p v-if="$page.props.errors?.company_name" class="text-red-400 text-sm mt-1">
                            {{ Array.isArray($page.props.errors.company_name) ? $page.props.errors.company_name[0] : $page.props.errors.company_name }}
                        </p>
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
                </div>

                <div class="flex gap-4 mt-8">
                    <button type="submit" :disabled="processing"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-lg font-medium disabled:opacity-50">
                        {{ processing ? 'Saving...' : 'Create Customer' }}
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

const form = ref({
    company_name: '',
    customer_type: '',
    tin_no: '',
    contact_person: '',
    phone_no: '',
    email: '',
    office_location: ''
});

const processing = ref(false);

const submit = () => {
    processing.value = true;
    router.post('/customers', form.value, {
        preserveScroll: true,
        onFinish: () => processing.value = false,
        onError: (errors) => {
            console.error('Validation errors:', errors);
        }
    });
};
</script>

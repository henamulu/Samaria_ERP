<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="transporters" />

        <div class="ml-64 p-8">
            <div class="mb-8">
                <a href="/transporters" class="text-slate-400 hover:text-white mb-2 inline-block">&larr; Back to Transporters</a>
                <h2 class="text-3xl font-bold text-white">Edit Transporter</h2>
            </div>

            <form @submit.prevent="submit" class="bg-slate-800 rounded-xl p-6 max-w-2xl">
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-slate-300 mb-2">Transporter Type *</label>
                        <select v-model="form.transporter_type" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="Company">Company</option>
                            <option value="Individual">Individual</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">TIN Number *</label>
                        <input v-model="form.tin_no" type="text" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div class="col-span-2">
                        <label class="block text-slate-300 mb-2">Company Name</label>
                        <input v-model="form.company_name" type="text"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">First Name</label>
                        <input v-model="form.firstname" type="text"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Last Name</label>
                        <input v-model="form.lastname" type="text"
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
                        {{ processing ? 'Saving...' : 'Update Transporter' }}
                    </button>
                    <a href="/transporters" class="bg-slate-600 hover:bg-slate-500 text-white px-6 py-3 rounded-lg">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Sidebar from '../../Components/Sidebar.vue';

const props = defineProps({ transporter: Object });

const form = ref({
    transporter_type: props.transporter.transporter_type || 'Company',
    company_name: props.transporter.company_name || '',
    firstname: props.transporter.firstname || '',
    lastname: props.transporter.lastname || '',
    tin_no: props.transporter.tin_no || '',
    phone_no: props.transporter.phone_no || '',
    email: props.transporter.email || '',
    status: props.transporter.status || 'Active'
});

const processing = ref(false);

const submit = () => {
    processing.value = true;
    router.put('/transporters/' + props.transporter.id, form.value, {
        onFinish: () => processing.value = false
    });
};
</script>

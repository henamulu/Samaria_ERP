<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="transporters" />

        <div class="ml-64 p-8">
            <div class="mb-8">
                <a href="/transporters" class="text-slate-400 hover:text-white mb-2 inline-block">&larr; Back to Transporters</a>
                <h2 class="text-3xl font-bold text-white">Add New Transporter</h2>
            </div>

            <form @submit.prevent="submit" class="bg-slate-800 rounded-xl p-6 max-w-2xl">
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-slate-300 mb-2">Transporter Type *</label>
                        <select v-model="form.transporter_type" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="">Select type</option>
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
                        <label class="block text-slate-300 mb-2">Contact Person</label>
                        <input v-model="form.contact_person" type="text"
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
                        {{ processing ? 'Saving...' : 'Create Transporter' }}
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

const form = ref({
    transporter_type: '',
    company_name: '',
    firstname: '',
    lastname: '',
    tin_no: '',
    phone_no: '',
    email: '',
    contact_person: '',
    office_location: ''
});

const processing = ref(false);

const submit = () => {
    processing.value = true;
    router.post('/transporters', form.value, {
        onFinish: () => processing.value = false
    });
};
</script>

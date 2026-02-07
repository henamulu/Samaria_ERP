<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="insurances" />
        <div class="ml-64 p-8">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h2 class="text-3xl font-bold text-white mb-2">Add Insurance Company</h2>
                    <p class="text-slate-400">Create a new insurance company</p>
                </div>
                <router-link to="/insurances" class="text-emerald-400 hover:text-emerald-300">← Back to List</router-link>
            </div>

            <div class="bg-slate-800 rounded-xl p-6 max-w-3xl">
                <form @submit.prevent="submitForm">
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-slate-400 text-sm mb-2">Insurance Name <span class="text-red-400">*</span></label>
                                <input v-model="form.insurance_name" type="text" required class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                            </div>

                            <div>
                                <label class="block text-slate-400 text-sm mb-2">Branch</label>
                                <input v-model="form.branch" type="text" class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                            </div>

                            <div>
                                <label class="block text-slate-400 text-sm mb-2">Contact Person <span class="text-red-400">*</span></label>
                                <input v-model="form.contact_person" type="text" required class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                            </div>

                            <div>
                                <label class="block text-slate-400 text-sm mb-2">Phone Number <span class="text-red-400">*</span></label>
                                <input v-model="form.phone_number" type="text" required class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white" />
                            </div>

                            <div>
                                <label class="block text-slate-400 text-sm mb-2">Status <span class="text-red-400">*</span></label>
                                <select v-model="form.insurance_status" required class="w-full bg-slate-700 border-0 rounded-lg px-4 py-2 text-white">
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex gap-3">
                        <button type="submit" :disabled="loading" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-3 rounded-lg disabled:opacity-50">
                            {{ loading ? 'Creating...' : 'Create Insurance Company' }}
                        </button>
                        <router-link to="/insurances" class="flex-1 bg-slate-600 hover:bg-slate-500 text-white px-4 py-3 rounded-lg text-center">Cancel</router-link>
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

const loading = ref(false);
const form = ref({
    insurance_name: '',
    branch: '',
    contact_person: '',
    phone_number: '',
    insurance_status: 'Active'
});

const submitForm = () => {
    loading.value = true;
    router.post('/insurances', form.value, {
        preserveState: false,
        onFinish: () => {
            loading.value = false;
        }
    });
};
</script>

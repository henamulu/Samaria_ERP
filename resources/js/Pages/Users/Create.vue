<template>
    <div class="min-h-screen bg-slate-900">
        <Sidebar active="users" />

        <div class="ml-64 p-8">
            <div class="mb-8">
                <a href="/users" class="text-slate-400 hover:text-white mb-2 inline-block">&larr; Back to Users</a>
                <h2 class="text-3xl font-bold text-white">Add New User</h2>
            </div>

            <form @submit.prevent="submit" class="bg-slate-800 rounded-xl p-6 max-w-2xl">
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-slate-300 mb-2">Username *</label>
                        <input v-model="form.user_name" type="text" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Password *</label>
                        <input v-model="form.password" type="password" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">First Name *</label>
                        <input v-model="form.firstname" type="text" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Last Name *</label>
                        <input v-model="form.lastname" type="text" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Email</label>
                        <input v-model="form.user_email" type="email"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Phone</label>
                        <input v-model="form.phone_no" type="text"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Role *</label>
                        <select v-model="form.role" required
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="">Select role</option>
                            <option v-for="role in roles" :key="role" :value="role">{{ role }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 mb-2">Department</label>
                        <input v-model="form.department" type="text"
                            class="w-full bg-slate-700 border-0 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" />
                    </div>
                </div>

                <div class="flex gap-4 mt-8">
                    <button type="submit" :disabled="processing"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-lg font-medium disabled:opacity-50">
                        {{ processing ? 'Saving...' : 'Create User' }}
                    </button>
                    <a href="/users" class="bg-slate-600 hover:bg-slate-500 text-white px-6 py-3 rounded-lg">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Sidebar from '../../Components/Sidebar.vue';

defineProps({
    roles: { type: Array, default: () => ['Admin', 'Supervisor', 'User', 'Cashier'] }
});

const form = ref({
    user_name: '',
    password: '',
    firstname: '',
    lastname: '',
    user_email: '',
    phone_no: '',
    role: '',
    department: ''
});

const processing = ref(false);

const submit = () => {
    processing.value = true;
    router.post('/users', form.value, {
        onFinish: () => processing.value = false
    });
};
</script>

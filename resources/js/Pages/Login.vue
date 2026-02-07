<template>
    <div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 48px 16px;">
        <div style="max-width: 400px; width: 100%; background: #1e293b; border-radius: 16px; padding: 40px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); border: 1px solid #334155;">
            <div style="text-align: center; margin-bottom: 32px;">
                <h1 style="font-size: 32px; font-weight: bold; color: #10b981; margin: 0;">Samaria ERP</h1>
                <p style="color: #94a3b8; margin-top: 8px;">Enterprise Management System</p>
            </div>
            
            <form @submit.prevent="submit">
                <div style="margin-bottom: 20px;">
                    <label style="display: block; color: #e2e8f0; margin-bottom: 8px; font-size: 14px;">Username</label>
                    <input v-model="form.user_name" type="text" required placeholder="Enter your username"
                        style="width: 100%; padding: 12px 16px; background: #0f172a; border: 2px solid #334155; border-radius: 8px; color: white; font-size: 16px; outline: none; box-sizing: border-box;" />
                </div>
                
                <div style="margin-bottom: 24px;">
                    <label style="display: block; color: #e2e8f0; margin-bottom: 8px; font-size: 14px;">Password</label>
                    <input v-model="form.password" type="password" required placeholder="Enter your password"
                        style="width: 100%; padding: 12px 16px; background: #0f172a; border: 2px solid #334155; border-radius: 8px; color: white; font-size: 16px; outline: none; box-sizing: border-box;" />
                </div>

                <div v-if="errors" style="background: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; border-radius: 8px; padding: 12px; margin-bottom: 20px; color: #ef4444; font-size: 14px;">
                    {{ errors }}
                </div>

                <button type="submit" :disabled="processing"
                    style="width: 100%; padding: 14px; background: linear-gradient(135deg, #10b981, #059669); border: none; border-radius: 8px; color: white; font-size: 16px; font-weight: 600; cursor: pointer;">
                    <span v-if="processing">Signing in...</span>
                    <span v-else>Sign In</span>
                </button>
            </form>
            
            <p style="text-align: center; color: #64748b; margin-top: 24px; font-size: 12px;">© 2026 Samaria ERP - All rights reserved</p>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const form = ref({ user_name: '', password: '' });
const errors = ref(null);
const processing = ref(false);

const submit = () => {
    processing.value = true;
    errors.value = null;
    
    router.post('/login', form.value, {
        onError: (e) => {
            errors.value = e.user_name || e.password || 'Login failed. Please check your credentials.';
        },
        onFinish: () => {
            processing.value = false;
        }
    });
};
</script>

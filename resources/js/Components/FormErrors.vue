<template>
    <div v-if="hasErrors" class="mb-6 p-4 bg-red-900/20 border border-red-500 rounded-lg">
        <div class="flex items-center mb-2">
            <svg class="w-5 h-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
            <h3 class="text-red-400 font-semibold">Please fix the following errors:</h3>
        </div>
        <ul class="list-disc list-inside text-red-300 space-y-1">
            <li v-for="(error, field) in errors" :key="field">
                <strong>{{ field }}:</strong> {{ Array.isArray(error) ? error[0] : error }}
            </li>
        </ul>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

const errors = computed(() => {
    const pageErrors = page.props.errors;
    if (!pageErrors || typeof pageErrors !== 'object') {
        return {};
    }
    return pageErrors;
});

const hasErrors = computed(() => {
    const errs = errors.value;
    return errs && typeof errs === 'object' && Object.keys(errs).length > 0;
});
</script>

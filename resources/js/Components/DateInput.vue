<template>
    <input
        ref="dateInput"
        :type="type"
        :value="modelValue"
        :placeholder="placeholder"
        :required="required"
        :disabled="disabled"
        :class="inputClass"
        @input="handleInput"
    />
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';

const props = defineProps({
    modelValue: {
        type: String,
        default: ''
    },
    type: {
        type: String,
        default: 'date'
    },
    placeholder: {
        type: String,
        default: ''
    },
    required: {
        type: Boolean,
        default: false
    },
    disabled: {
        type: Boolean,
        default: false
    },
    inputClass: {
        type: String,
        default: ''
    },
    dateFormat: {
        type: String,
        default: 'Y-m-d'
    }
});

const emit = defineEmits(['update:modelValue']);

const dateInput = ref(null);
let flatpickrInstance = null;

onMounted(() => {
    if (props.type === 'date' && dateInput.value) {
        flatpickrInstance = flatpickr(dateInput.value, {
            dateFormat: props.dateFormat,
            locale: 'en',
            allowInput: true,
            clickOpens: true,
            onChange: (selectedDates, dateStr) => {
                emit('update:modelValue', dateStr);
            }
        });
    }
});

onUnmounted(() => {
    if (flatpickrInstance) {
        flatpickrInstance.destroy();
    }
});

watch(() => props.modelValue, (newValue) => {
    if (flatpickrInstance && newValue !== flatpickrInstance.input.value) {
        flatpickrInstance.setDate(newValue, false);
    }
});

const handleInput = (event) => {
    emit('update:modelValue', event.target.value);
};
</script>

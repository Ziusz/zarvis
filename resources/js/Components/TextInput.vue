<script setup>
import { onMounted, ref } from 'vue';

defineProps({
    modelValue: String,
    size: {
        type: String,
        default: 'md'
    },
    variant: {
        type: String,
        default: 'bordered' // bordered, ghost, primary
    }
});

defineEmits(['update:modelValue']);

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        ref="input"
        :class="[
            'input',
            `input-${variant}`,
            size === 'sm' ? 'input-sm' : '',
            size === 'lg' ? 'input-lg' : '',
        ]"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
    >
</template>

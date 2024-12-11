<script setup>
import { computed } from 'vue';

const emit = defineEmits(['update:checked']);

const props = defineProps({
    checked: {
        type: [Array, Boolean],
        default: false,
    },
    value: {
        type: String,
        default: null,
    },
    size: {
        type: String,
        default: 'md', // lg, md, sm, xs
    },
    color: {
        type: String,
        default: 'primary', // primary, secondary, accent, success, warning, info, error
    }
});

const proxyChecked = computed({
    get() {
        return props.checked;
    },
    set(val) {
        emit('update:checked', val);
    },
});

const checkboxClasses = computed(() => {
    return [
        'checkbox',
        `checkbox-${props.color}`,
        props.size !== 'md' && `checkbox-${props.size}`,
    ].filter(Boolean);
});
</script>

<template>
    <input
        v-model="proxyChecked"
        type="checkbox"
        :value="value"
        :class="checkboxClasses"
    >
</template>

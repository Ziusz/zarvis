<script setup>
defineProps({
    value: String,
    required: {
        type: Boolean,
        default: false
    }
});
</script>

<template>
    <label class="label">
        <span class="label-text">
            <span v-if="value">{{ value }}</span>
            <span v-else><slot /></span>
            <span v-if="required" class="text-error">*</span>
        </span>
    </label>
</template>

<script setup>
defineProps({
    vertical: {
        type: Boolean,
        default: false
    },
    color: {
        type: String,
        default: 'base-200'
    }
});
</script>

<template>
    <div 
        :class="[
            'divider',
            vertical ? 'divider-horizontal' : 'divider-vertical',
            `bg-${color}`
        ]"
    />
</template>

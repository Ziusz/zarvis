<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
    size: {
        type: String,
        default: 'md',
    }
});
</script>

<template>
    <button 
        :type="type" 
        :class="[
            'btn btn-secondary',
            size === 'sm' ? 'btn-sm' : '',
            size === 'lg' ? 'btn-lg' : '',
        ]"
    >
        <slot />
    </button>
</template>

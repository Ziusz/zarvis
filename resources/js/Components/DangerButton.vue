<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
    size: {
        type: String,
        default: 'md', // lg, md, sm, xs
    },
    outline: {
        type: Boolean,
        default: false,
    }
});
</script>

<template>
    <button 
        :type="type" 
        :class="[
            'btn',
            'btn-error',
            outline && 'btn-outline',
            size !== 'md' && `btn-${size}`,
        ]"
    >
        <slot />
    </button>
</template>

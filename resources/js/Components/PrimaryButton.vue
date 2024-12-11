<script setup>
defineProps({
    type: {
        type: String,
        default: 'submit',
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
            'btn btn-primary',
            size === 'sm' ? 'btn-sm' : '',
            size === 'lg' ? 'btn-lg' : '',
        ]"
    >
        <slot />
    </button>
</template>

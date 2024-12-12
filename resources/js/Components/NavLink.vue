<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
    external: {
        type: Boolean,
        default: false,
    },
    variant: {
        type: String,
        default: 'default', // 'default', 'primary', 'secondary', 'ghost'
    },
    size: {
        type: String,
        default: 'md', // 'sm', 'md', 'lg'
    }
});

const classes = computed(() => {
    const baseClasses = 'btn';
    const variantClasses = {
        default: 'btn-ghost hover:bg-base-200',
        primary: 'btn-primary',
        secondary: 'btn-secondary',
        ghost: 'btn-ghost'
    };
    const sizeClasses = {
        sm: 'btn-sm',
        md: '',
        lg: 'btn-lg'
    };
    
    return [
        baseClasses,
        variantClasses[props.variant],
        sizeClasses[props.size],
        props.active ? 'bg-base-200' : ''
    ].filter(Boolean).join(' ');
});
</script>

<template>
    <Link v-if="!external" :href="href" :class="classes">
        <slot />
    </Link>
    <a v-else :href="href" :class="classes">
        <slot />
    </a>
</template>

<style scoped>
.menu-item {
    @apply px-4 py-2 rounded-lg;
}
</style>

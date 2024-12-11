<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: String,
    active: Boolean,
    variant: {
        type: String,
        default: 'tab', // tab, menu
    },
    color: {
        type: String,
        default: 'primary', // primary, secondary, accent, ghost
    }
});

const classes = computed(() => {
    const baseClasses = props.variant === 'tab' 
        ? ['tab', props.active && 'tab-active', `tab-${props.color}`]
        : ['menu-item', props.active && 'active', `text-${props.color}`];

    return [
        ...baseClasses,
        'hover:bg-base-200 transition-colors duration-200',
    ].filter(Boolean);
});
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>

<style scoped>
.menu-item {
    @apply px-4 py-2 rounded-lg;
}
</style>

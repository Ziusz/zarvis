<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    active: Boolean,
    href: String,
    as: String,
    color: {
        type: String,
        default: 'primary'
    }
});

const classes = computed(() => {
    return [
        'menu-item',
        'w-full',
        'flex items-center gap-2',
        'px-4 py-2',
        'hover:bg-base-200',
        'transition-colors duration-200',
        props.active && 'active bg-base-200',
        `text-${props.color}`,
        props.active && `border-s-4 border-${props.color}`
    ].filter(Boolean);
});
</script>

<template>
    <div class="w-full">
        <button 
            v-if="as == 'button'" 
            :class="classes"
        >
            <slot />
        </button>

        <a 
            v-else-if="as == 'a'" 
            :class="classes"
            :href="href"
        >
            <slot />
        </a>

        <Link 
            v-else 
            :href="href" 
            :class="classes"
        >
            <slot />
        </Link>
    </div>
</template>

<style scoped>
.menu-item {
    @apply rounded-lg;
}
.active {
    @apply font-medium;
}
</style>

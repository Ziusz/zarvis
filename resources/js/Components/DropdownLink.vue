<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: String,
    as: String,
    active: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <li>
        <button 
            v-if="as == 'button'" 
            type="submit" 
            class="w-full text-start"
            :class="{ 'active': active }"
        >
            <slot />
        </button>

        <a 
            v-else-if="as == 'a'" 
            :href="href"
            :class="{ 'active': active }"
        >
            <slot />
        </a>

        <Link 
            v-else 
            :href="href"
            :class="{ 'active': active }"
        >
            <slot />
        </Link>
    </li>
</template>

<style scoped>
/* DaisyUI menu items are already styled, we just need to ensure proper structure */
li > * {
    @apply block w-full;
}
</style>

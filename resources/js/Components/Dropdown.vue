<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    align: {
        type: String,
        default: 'end', // start, end
    },
    hover: {
        type: Boolean,
        default: false,
    },
    size: {
        type: String,
        default: 'md', // xs, sm, md, lg
    },
});

const open = ref(false);

const closeOnEscape = (e) => {
    if (open.value && e.key === 'Escape') {
        open.value = false;
    }
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));
onUnmounted(() => document.removeEventListener('keydown', closeOnEscape));

const dropdownClasses = computed(() => {
    return [
        'dropdown',
        props.hover && 'dropdown-hover',
        `dropdown-${props.align}`,
        props.size !== 'md' && `dropdown-${props.size}`,
    ].filter(Boolean);
});
</script>

<template>
    <div :class="dropdownClasses">
        <div 
            tabindex="0" 
            role="button" 
            class="focus:outline-none"
            @click="open = !open"
        >
            <slot name="trigger" />
        </div>

        <ul 
            tabindex="0" 
            class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box"
            :class="{ 'hidden': !open && !hover }"
            @click="open = false"
        >
            <slot name="content" />
        </ul>
    </div>
</template>

<style scoped>
.dropdown-content {
    @apply min-w-[12rem];
}
</style>

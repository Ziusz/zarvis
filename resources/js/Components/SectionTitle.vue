<script setup>
defineProps({
    color: {
        type: String,
        default: 'primary'
    }
});
</script>

<template>
    <div class="flex justify-between items-start gap-4 mb-6">
        <div class="flex-1">
            <h3 :class="[
                'text-lg font-bold',
                `text-${color}`
            ]">
                <slot name="title" />
            </h3>

            <p class="mt-1 text-sm text-base-content/70">
                <slot name="description" />
            </p>
        </div>

        <div class="flex-none">
            <slot name="aside" />
        </div>
    </div>
</template>

<script setup>
import SectionTitle from './SectionTitle.vue';

defineProps({
    variant: {
        type: String,
        default: 'bordered' // bordered, glass, normal
    }
});
</script>

<template>
    <div class="w-full">
        <SectionTitle>
            <template #title>
                <slot name="title" />
            </template>
            <template #description>
                <slot name="description" />
            </template>
        </SectionTitle>

        <div class="mt-5">
            <div :class="[
                'card',
                'bg-base-100',
                `card-${variant}`,
                'w-full'
            ]">
                <div class="card-body">
                    <slot name="content" />
                </div>
            </div>
        </div>
    </div>
</template>

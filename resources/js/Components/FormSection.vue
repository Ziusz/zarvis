<script setup>
import { computed, useSlots } from 'vue';
import SectionTitle from './SectionTitle.vue';

defineEmits(['submitted']);

const hasActions = computed(() => !! useSlots().actions);

defineProps({
    variant: {
        type: String,
        default: 'bordered' // bordered, compact, normal
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
            <form @submit.prevent="$emit('submitted')">
                <div :class="[
                    'card',
                    'bg-base-100',
                    `card-${variant}`,
                    'w-full'
                ]">
                    <div class="card-body">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <slot name="form" />
                        </div>

                        <div v-if="hasActions" class="card-actions justify-end mt-6">
                            <slot name="actions" />
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

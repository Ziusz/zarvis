<template>
    <div class="text-center py-12">
        <div class="w-full max-w-[400px] mx-auto">
            <div class="flex flex-col items-center gap-4">
                <div class="w-20 h-20 rounded-full bg-base-200 flex items-center justify-center">
                    <slot name="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-base-content/50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                    </slot>
                </div>
                <div class="space-y-2">
                    <h3 class="text-lg font-medium">{{ title }}</h3>
                    <p class="text-base-content/70">{{ description }}</p>
                </div>
                <slot name="action">
                    <Link v-if="actionLink && actionLabel" :href="actionLink" class="btn btn-primary">
                        {{ actionLabel }}
                    </Link>
                </slot>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        required: true,
    },
    actionLink: {
        type: String,
        default: '',
    },
    actionLabel: {
        type: String,
        default: '',
    },
});</script> 
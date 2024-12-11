<script setup>
import { ref, watchEffect, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const show = ref(true);
const style = ref('success');
const message = ref('');

watchEffect(async () => {
    style.value = page.props.jetstream.flash?.bannerStyle || 'success';
    message.value = page.props.jetstream.flash?.banner || '';
    show.value = true;
});

const alertClasses = computed(() => {
    return [
        'alert',
        'shadow-lg',
        style.value === 'success' ? 'alert-success' : 'alert-error'
    ];
});
</script>

<template>
    <div v-if="show && message" class="w-full">
        <div :class="alertClasses">
            <div class="flex-1">
                <svg v-if="style == 'success'" 
                    xmlns="http://www.w3.org/2000/svg" 
                    class="stroke-current shrink-0 h-6 w-6" 
                    fill="none" 
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>

                <svg v-else 
                    xmlns="http://www.w3.org/2000/svg" 
                    class="stroke-current shrink-0 h-6 w-6" 
                    fill="none" 
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>

                <span>{{ message }}</span>
            </div>
            <div class="flex-none">
                <button 
                    class="btn btn-sm btn-ghost"
                    @click="show = false"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>

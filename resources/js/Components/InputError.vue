<script setup>
defineProps({
    message: String,
});
</script>

<template>
    <div v-show="message" class="mt-1">
        <div class="alert alert-error text-sm p-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-4 w-4" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ message }}</span>
        </div>
    </div>
</template>

<script setup>
defineProps({
    variant: {
        type: String,
        default: 'bordered' // bordered, glass
    }
});
</script>

<template>
    <div class="min-h-screen hero bg-base-200">
        <div class="hero-content flex-col">
            <div class="text-center mb-4">
                <slot name="logo" />
            </div>

            <div :class="[
                'card',
                'w-full sm:w-[28rem]',
                'bg-base-100',
                variant === 'glass' ? 'glass' : `card-${variant}`,
                'shadow-xl'
            ]">
                <div class="card-body">
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>

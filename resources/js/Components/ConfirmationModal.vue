<script setup>
import Modal from './Modal.vue';

const emit = defineEmits(['close']);

defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    size: {
        type: String,
        default: 'sm',
    },
    closeable: {
        type: Boolean,
        default: true,
    },
    type: {
        type: String,
        default: 'error' // error, warning, info
    }
});

const close = () => {
    emit('close');
};

const iconMap = {
    error: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />`,
    warning: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />`,
    info: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />`
};
</script>

<template>
    <Modal
        :show="show"
        :size="size"
        :closeable="closeable"
        @close="close"
    >
        <div :class="[
            'alert',
            `alert-${type}`,
            'shadow-lg mb-4'
        ]">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <svg 
                        xmlns="http://www.w3.org/2000/svg" 
                        class="stroke-current shrink-0 h-6 w-6" 
                        fill="none" 
                        viewBox="0 0 24 24"
                        v-html="iconMap[type]"
                    />
                </div>

                <div>
                    <h3 class="font-bold text-lg">
                        <slot name="title" />
                    </h3>

                    <div class="mt-2 text-sm opacity-90">
                        <slot name="content" />
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-action">
            <slot name="footer" />
        </div>
    </Modal>
</template>
